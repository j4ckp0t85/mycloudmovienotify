<?php

ini_set ('memory_limit','256M');

require '/trailer/vendor/autoload.php';

if (php_sapi_name() != 'cli') {
    throw new Exception('This application must be run on the command line.');
}

/**
 * Returns an authorized API client.
 * @return Google_Client the authorized client object
 */
function getClient()
{
    $client = new Google_Client();
    $client->setApplicationName('Google Drive API PHP WDMyCloud');
    $client->setAuthConfig('/trailer/credentials.json');
    $client->setScopes('https://www.googleapis.com/auth/drive');
    
    $client->setAccessType('offline');
    $client->setPrompt('select_account consent');

    // Load previously authorized token from a file, if it exists.
    // The file token.json stores the user's access and refresh tokens, and is
    // created automatically when the authorization flow completes for the first
    // time.
    $tokenPath = '/trailer/token.json';
    if (file_exists($tokenPath)) {
        $accessToken = json_decode(file_get_contents($tokenPath), true);
        $client->setAccessToken($accessToken);
    }

    // If there is no previous token or it's expired.
    if ($client->isAccessTokenExpired()) {
        // Refresh the token if possible, else fetch a new one.
        // Refresh the token if possible, else fetch a new one.
        if ($client->getRefreshToken()) {
            $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        } else {
            // Request authorization from the user.
            $authUrl = $client->createAuthUrl();
            printf("Open the following link in your browser:\n%s\n", $authUrl);
            print 'Enter verification code: ';
            $authCode = trim(fgets(STDIN));

            // Exchange authorization code for an access token.
            $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
            $client->setAccessToken($accessToken);

            // Check to see if there was an error.
            if (array_key_exists('error', $accessToken)) {
                throw new Exception(join(', ', $accessToken));
            }
        }
        // Save the token to a file.
        if (!file_exists(dirname($tokenPath))) {
            mkdir(dirname($tokenPath), 0700, true);
        }
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    }
    return $client;
}

function createFolder($service,$parentFolderId,$folderName) {
	$fileMetadata = new Google_Service_Drive_DriveFile(array(
		'parents'=>array($parentFolderId),	
		'name' => $folderName,
		'mimeType' => 'application/vnd.google-apps.folder')
		);
	$file = $service->files->create($fileMetadata, array(
		'fields' => 'id'));
	//printf("Folder ID: %s\n", $file->id); 	
	return $file->id;
}

function deleteFile($service,$fileId){	
	try {
		$service->files->delete($fileId);
	} catch (Exception $e) {
    	//print "An error occurred: " . $e->getMessage();
  	}
}


function uploadToRoot($service,$file){
	$fileMetadata = new Google_Service_Drive_DriveFile(array('name' => $file));
	$content = file_get_contents('/'.$file);
	$finfo = finfo_open(FILEINFO_MIME_TYPE);
	$mime_type = finfo_file($finfo, '/'.$file);
	$file = $service->files->create($fileMetadata, array(
		'data' => $content,
		'mimeType' => $mime_type,
		'uploadType' => 'multipart',
		'ignoreDefaultVisibility' => true,     
		'fields' => 'id'));
	$permissionService = new Google_Service_Drive_Permission();
	$permissionService->role = "reader";
	$permissionService->type = "anyone"; // anyone with the link can view the file
	$service->permissions->create($file->id, $permissionService);
	
	return $file->id;
	
	unset($content);
	unset($finfo);
	unset($mime_type);
	unset($file);
}


// Get the API client and construct the service object.
$client = getClient();
$service = new Google_Service_Drive($client);

$fileId=uploadToRoot($service,'trailer.png');

file_put_contents("trailerurl.txt",$fileId);

?>