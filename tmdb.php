<?php
	require_once 'youtube.php';
	
	$apikey="tmdb_api_key";
	
	$genereArray=array(  // better to map it instead of query the genres everytime
		'28' => 'Azione',
		'12' => 'Avventura',
		'16' => 'Animazione',
		'35' => 'Commedia',
		'80' => 'Crime',
		'99' => 'Documentario',
		'18' => 'Dramma',
		'10751' => 'Famiglia',
		'14' => 'Fantasy',
		'36' => 'Storia',
		'27' => 'Horror',
		'10402' => 'Musica',
		'9648' => 'Mistero',
		'10749' => 'Romance',
		'878' => 'Fantascienza',
		'10880' => 'Televisione film',
		'53' => 'Thriller',
		'10752' => 'Guerra',
		'37' => 'Western'
	)
	;
	
	function sanitize($stringToSanitize){
		$arr = array(/*"4" => "A","3" => "E","1" => "L","0" => "O","5" => "S" ,"7" => "T"*/); 
		return strtr(preg_replace('#[^\pL\pN/]+#', ' ', $stringToSanitize),$arr);
	}
	
	function cambia_acc($dato){
		$dato = str_replace("€", "euro", $dato);
		$dato = str_replace("à", "a", $dato);
		$dato = str_replace("à", "a", $dato);
		$dato = str_replace("ä", "a", $dato);
		$dato = str_replace("À", "A", $dato);
		$dato = str_replace("á", "a", $dato);
		$dato = str_replace("È", "E", $dato);
		$dato = str_replace("è", "e", $dato);
		$dato = str_replace("é", "e", $dato);
		$dato = str_replace("ò", "o", $dato);
		$dato = str_replace("ó", "o", $dato);
		$dato = str_replace("ö", "o", $dato);
		$dato = str_replace("ì", "i", $dato);
		$dato = str_replace("í", "i", $dato);
		$dato = str_replace("ù", "u", $dato);
		$dato = str_replace("ú", "u", $dato);
		$dato = str_replace("ü", "u", $dato);
		return $dato;
	}

	$argomentoFile=addslashes(cambia_acc($argv[1]));
	$argomentoFile=preg_replace('/([^A-Za-z0-9])/i', '\\\\$1', $argomentoFile);
	
	$path_info = pathinfo($argv[2].'/'.$argomentoFile);
	
	if(strcasecmp($path_info['extension'],'mkv')==0){ 
				
			$searchString=sanitize($argomentoFile);
								
			$esplodi = explode(" ",$searchString); 
			
			$stringaFilmPulita=''; 
			$i=0;
			$length=sizeof($esplodi);
			
			
			do {
				$stringaFilmPulita.=$esplodi[$i];
				$stringaFilmPulita.=" ";
				$i++;
				$anno=$esplodi[$i];
			}while(($i<$length)&&((int)$esplodi[$i]<2017)&&(strcasecmp($esplodi[$i],"1080p")!=0)&&(strcasecmp($esplodi[$i],"720p")!=0)&&(strcasecmp($esplodi[$i],"WebRip")!=0)&&(strcasecmp($esplodi[$i],"WEBDL")!=0)&&(strcasecmp($esplodi[$i],"ITA")!=0)&&(strcasecmp($esplodi[$i],"Italian")!=0)&&(strcasecmp($esplodi[$i],"mkv")!=0)&&($esplodi[$i]!="1080")&&($esplodi[$i]!="720"));
			
			
			searchListByKeyword($service,
				    'snippet', 
				    array('maxResults' => 5, 'q' => $stringaFilmPulita.' trailer', 'regionCode' => 'IT', 'relevanceLanguage' => 'it', 'type' => 'video', 'videoEmbeddable' => 'true')); 
			
			
			$bkpTitoloPulito=$stringaFilmPulita;
			
			$stringaFilmPulita=rawurlencode($stringaFilmPulita);
			
			//CHIAMATA API A TMDB. RESTITUISCE UN JSON CON DIVERSI RISULTATI. IN CIMA IL PIU PERTINENTE    
		    file_put_contents("tmdb.json",exec("curl -s --request GET \
		    --url \"https://api.themoviedb.org/3/search/movie?query=$stringaFilmPulita&language=it-IT&certification_country=IT&api_key=$apikey\" \
		    --header 'content-type: application/json' \
		    --data '{}'
		"));
		    
		    //LAVORO IL JSON RICEVUTO E SALVATO
		    $res=json_decode(file_get_contents("tmdb.json"));
		
			
			$file = 'film.html';
			$current = '';
				
			if($res->{'total_results'}==0) { //nessun risultato trovato
				$current .= "<!doctype html><html>
				<head><meta charset='utf-8'>
				<title>Nessun risultato</title>
				</head><body style=\"width: 75%\"><h3>Scheda film '$bkpTitoloPulito' non trovata.</h3>
				<a href=\"https://www.filmtv.it/cerca/?q=$stringaFilmPulita&t=film\">Prova ricerca manualmente</a>
				</body></html>
				";
			}
			else { 
				
				if(($anno>1999)&&((int)substr($res->results['0']->{'release_date'},0,4))<2015){	    
					file_put_contents("tmdb.json",exec("curl -s --request GET \
					--url \"https://api.themoviedb.org/3/search/movie?query=$stringaFilmPulita&language=it-IT&year=$anno&certification_country=IT&api_key=$apikey\" \
					--header 'content-type: application/json' \
					--data '{}'
					"));
					$res=json_decode(file_get_contents("tmdb.json")); //ricarico il json con le info aggiornate del film con l'anno corretto
				}
				
				$posterUrl=$res->results['0']->{'poster_path'};
				$titolo=$res->results['0']->{'title'};
				$trama=htmlentities($res->results['0']->{'overview'});
				$dataPubblicaz=$res->results['0']->{'release_date'};
				$voto=$res->results['0']->{'vote_average'};
				$generiFilm=$res->results['0']->{'genre_ids'};
				
				$genereOutput='';
				
				
				for($i=0;$i<sizeof($generiFilm); $i++){
					$genereOutput.=$genereArray[$generiFilm[$i]];
					if(($i>=0)&&(sizeof($generiFilm)>1)&&($i<sizeof($generiFilm)-1)){
					 	$genereOutput.=' / ';
					 }
				}
				
								    
				$risultati=json_decode(file_get_contents("response.json"));
				
				$urlThumb=$risultati->{'items'}[0]->{'snippet'}->{'thumbnails'}->{'medium'}->{'url'};
				
				print($urlThumb);
				
				exec('curl -O '.$urlThumb);
				    
				trailerThumb(); 
				
				exec('php5 /trailer/gdrive.php');
				
				$idUploadGdrive=file_get_contents('trailerurl.txt');
				   
				if($risultati->{'pageInfo'}->{'resultsPerPage'}>0){
						$idUrl=$risultati->{'items'}[0]->{'id'}->{'videoId'};
					}
    			
    			
    			if($trama==''){
					file_put_contents("tmdb.json",exec("curl -s --request GET \
					--url \"https://api.themoviedb.org/3/search/movie?query=$stringaFilmPulita&language=en-US&api_key=$apikey\" \
					--header 'content-type: application/json' \
					--data '{}'
					"));
					$risultatiEN=json_decode(file_get_contents("tmdb.json"));
					$tramaEN=htmlentities($risultatiEN->results['0']->{'overview'});
					exec('php translate.php '.escapeshellarg($tramaEN));
					$trama=htmlentities(file_get_contents("traduzione.txt"));
				}
				
				$current .= "<!doctype html><html>
				<head><meta charset='utf-8'>
				<title>Scheda Film</title>
				</head><body style=\"width: 75%\"><h3>$titolo</h3>
				<table>
				<tr><td><img src=\"http://image.tmdb.org/t/p/w185/$posterUrl\"></td></tr>
				<tr><td>Genere: </td><td>$genereOutput</td></tr>
				<tr><td>Data pubblicazione: </td><td>$dataPubblicaz</td></tr>
				<tr><td>Voto: </td><td>$voto</td></tr>
				</table>
				<p>$trama</p>
				<a href=\"https://www.youtube.com/watch?v=$idUrl\"><img src =\"https://drive.google.com/uc?export=view&id=$idUploadGdrive \" alt=\"trailer\" /></a>
				</body></html>
				";
			}
			
			// Write the contents back to the file
			file_put_contents($file, $current);
	}
?>