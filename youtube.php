
<?php
date_default_timezone_set('UTC');
/**
 * Library Requirements
 *
 * 1. Install composer (https://getcomposer.org)
 * 2. On the command line, change to this directory (api-samples/php)
 * 3. Require the google/apiclient library
 *    $ composer require google/apiclient:~2.0
 */
if (!file_exists($file = '/vendor/autoload.php')) {
  throw new \Exception('please run "composer require google/apiclient:~2.0" in "' . __DIR__ .'"');
}
require_once '/vendor/autoload.php';
session_start();

/*
 * Set $DEVELOPER_KEY to the "API key" value from the "Access" tab of the
 * Google Developers Console: https://console.developers.google.com/
 * Please ensure that you have enabled the YouTube Data API for your project.
 */
define('CREDENTIALS_PATH', 'php-yt-oauth2.json');

function getClient() {
  $client = new Google_Client();
  $client->setApplicationName('API Samples');
  $client->setScopes('https://www.googleapis.com/auth/youtube.force-ssl');
  // Set to name/location of your client_secrets.json file.
  $client->setAuthConfig('client_secrets.json');
  $client->setAccessType('offline');

  // Load previously authorized credentials from a file.
  $credentialsPath = expandHomeDirectory(CREDENTIALS_PATH);
  if (file_exists($credentialsPath)) {
    $accessToken = json_decode(file_get_contents($credentialsPath), true);
  } else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    printf("Open the following link in your browser:\n%s\n", $authUrl);
    print 'Enter verification code: ';
    $authCode = trim(fgets(STDIN));

    // Exchange authorization code for an access token.
    $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);

    // Store the credentials to disk.
    if(!file_exists(dirname($credentialsPath))) {
      mkdir(dirname($credentialsPath), 0700, true);
    }
    file_put_contents($credentialsPath, json_encode($accessToken));
    printf("Credentials saved to %s\n", $credentialsPath);
  }
  $client->setAccessToken($accessToken);

  // Refresh the token if it's expired.
  if ($client->isAccessTokenExpired()) {
    $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
    file_put_contents($credentialsPath, json_encode($client->getAccessToken()));
  }
  return $client;
}

/**
 * Expands the home directory alias '~' to the full path.
 * @param string $path the path to expand.
 * @return string the expanded path.
 */
function expandHomeDirectory($path) {
  $homeDirectory = getenv('HOME');
  if (empty($homeDirectory)) {
    $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
  }
  return str_replace('~', realpath($homeDirectory), $path);
}

// Define an object that will be used to make all API requests.
$client = getClient();
$service = new Google_Service_YouTube($client);

if (isset($_GET['code'])) {
  if (strval($_SESSION['state']) !== strval($_GET['state'])) {
    die('The session state did not match.');
  }

  $client->authenticate($_GET['code']);
  $_SESSION['token'] = $client->getAccessToken();
  header('Location: ' . $redirect);
}

if (isset($_SESSION['token'])) {
  $client->setAccessToken($_SESSION['token']);
}

if (!$client->getAccessToken()) {
  print("no access token, whaawhaaa");
  exit;
}

// Add a property to the resource.
function addPropertyToResource(&$ref, $property, $value) {
    $keys = explode(".", $property);
    $is_array = false;
    foreach ($keys as $key) {
        // For properties that have array values, convert a name like
        // "snippet.tags[]" to snippet.tags, and set a flag to handle
        // the value as an array.
        if (substr($key, -2) == "[]") {
            $key = substr($key, 0, -2);
            $is_array = true;
        }
        $ref = &$ref[$key];
    }

    // Set the property value. Make sure array values are handled properly.
    if ($is_array && $value) {
        $ref = $value;
        $ref = explode(",", $value);
    } elseif ($is_array) {
        $ref = array();
    } else {
        $ref = $value;
    }
}


/***** END BOILERPLATE CODE *****/

// Sample php code for search.list

function searchListByKeyword($service, $part, $params) {
    $params = array_filter($params);
    $response = $service->search->listSearch(
        $part,
        $params
    );

    file_put_contents("response.json",json_encode($response));
}

function trailerThumb() {
	// Load the stamp and the photo to apply the watermark to
	$im = imagecreatefromjpeg('mqdefault.jpg');
	$stamp = imagecreatefrompng('youtube.png');
	
	$marge_right = 10;
	$marge_bottom = 10;
	$sx = imagesx($stamp);
	$sy = imagesy($stamp);
	
	// Merge the stamp onto our photo with an opacity of 50%
	imagecopymerge($im, $stamp, imagesx($im) - $sx - $marge_right, imagesy($im) - $sy - $marge_bottom, 0, 0, imagesx($stamp), imagesy($stamp), 50);
	
	// Save the image to file and free memory
	imagepng($im, 'trailer.png');
	imagedestroy($im);
}
?>
  