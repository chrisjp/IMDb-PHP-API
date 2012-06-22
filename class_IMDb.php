<?php
class IMDb
{
	
	private $baseurl = 'https://app.imdb.com/';
	private $params = array(
						'api'		=> 'v1',
						'appid'		=> 'iphone1_1',
						'apiPolicy'	=> 'app1_1',
						'apiKey'	=> '2wex6aeu6a8q9e49k7sfvufd6rhh0n',
						'locale'	=> 'en_US',
						'timestamp'	=> '0',
					  );
	private $anonymiser = 'http://anonymouse.org/cgi-bin/anon-www.cgi/';	// URL that will be prepended to the generated API URL.
	public $anonymise = false;
	public $summary = true;			// Set to true to return a summary of the film's details. Set to false to return everything.
	public $titlesLimit = 0;		// Limit the number of films returned by find_by_title() when summarised. 0 = unlimited (NOTE: IMDb returns a maximum of 50 results).
	
	// You can prevent certain types of titles being returned. IMDb classifies titles under one of the following categories:
	// feature, short, documentary, video, tv_series, tv_special, video_game
	// Titles whose category is in the $ignoreTypes array below will be removed from your results
	public $ignoreTypes = array('tv_series','tv_special','video_game');
	
	// By default, X rated titles and titles in the genre 'Adult' will also be ignored. Set to false to allow them.
	public $ignoreAdult = true;
	
	// Setting the following option to true will allow you to override any ignore options and force the title to be returned with find_by_id()
	public $forceReturn = false;
			
	function __construct($anonymise=false, $summary=true, $titlesLimit=0){
		$this->anonymise = $anonymise;		// should we anonymise requests?
		if(!$summary) $this->summary=false;	// overriding the default?
		if(intval($titlesLimit)>0)	$this->titlesLimit = intval($titlesLimit);		// Set titles limit if required
	}
	
	// Build URL based on the given parameters
	function build_url($method, $query="", $parameter=""){
		
		// Set timestamp parameter to current time
		$this->params['timestamp'] = $_SERVER['REQUEST_TIME'];
		
		// Build the URL and append query if we have one
		$unsignedUrl = $this->baseurl.$method.'?'.http_build_query($this->params);
		if(!empty($parameter) AND !empty($query)) $unsignedUrl .= '&'.$parameter.'='.urlencode($query);
		
		// Generate a signature and append to unsignedUrl to sign it.
		$sig = hash_hmac('sha1', $unsignedUrl, $this->params['apiKey']);
		$signedUrl = $unsignedUrl.'&sig=app1-'.$sig;
		
		// Anonymise the request?
		$signedUrl = $this->anonymise ? $this->anonymiser.$signedUrl : $signedUrl;
		
		return $signedUrl;
	}
	
	// Search IMDb by ID of film
	function find_by_id($id){
		if(strpos($id, "tt")!==0) $id = "tt".$id;
		$requestURL = $this->build_url('title/maindetails', $id, 'tconst');
		$json = $this->fetchJSON($requestURL);

		if(is_object($json->error)){
			$data = $this->errorResponse($json->error);
		}
		else{
			$data = $this->summary ? $this->summarise($json->data) : $json->data;
		}
		
		return $data;
	}
	
	// Search IMDb by title of film
	function find_by_title($title, $year=0){
		$requestURL = $this->build_url('find', $title, 'q');		
		$json = $this->fetchJSON($requestURL);
		
		// We'll usually have several "lists" returned in the JSON. Combine all these into one array.
		if(empty($json->data->results)){
			// IMDb doesn't return a proper error response in the event of 0 results being returned
			// so set our own failure message.
			$error->message = "No results found.";
			$matches = $this->errorResponse($error, true);
		}
		else{
			$results = $json->data->results;
			$matches = array();
			
			if($this->summary){
				$matches = $this->summarise_titles($results, intval($year));
			}
			else{
				for($i=0; $i<count($results); $i++){
					$matches = array_merge($matches, $results[$i]->list);
				}
			}
		}
		
		return $matches;
	}
	
	// Search IMDb by ID of person
	function person_by_id($id){
		if(strpos($id, "nm")!==0) $id = "nm".$id;
		$requestURL = $this->build_url('name/maindetails', $id, 'nconst');
		$json = $this->fetchJSON($requestURL);

		if(is_object($json->error)){
			$data = $this->errorResponse($json->error);
		}
		else{
			$data = $this->summary ? $this->summarise_person($json->data) : $json->data;
		}
		
		return $data;
	}
	
	// Search IMDb by name of person
	function person_by_name($name){
		$requestURL = $this->build_url('find', $name, 'q');		
		$json = $this->fetchJSON($requestURL);
		
		// We'll usually have several "lists" returned in the JSON. Combine all these into one array.
		if(empty($json->data->results)){
			// IMDb doesn't return a proper error response in the event of 0 results being returned
			// so set our own failure message.
			$error->message = "No results found.";
			$matches = $this->errorResponse($error, true);
		}
		else{
			$results = $json->data->results;
			$matches = array();
			
			if($this->summary){
				$matches = $this->summarise_people($results);
			}
			else{
				for($i=0; $i<count($results); $i++){
					$matches = array_merge($matches, $results[$i]->list);
				}
			}
		}
		
		return $matches;
	}
	
	// Top 250 Chart
	function chart_top(){
		$requestURL = $this->build_url('chart/top');
		$json = $this->fetchJSON($requestURL);

		if(is_object($json->error)){
			$data = $this->errorResponse($json->error);
		}
		else{
			$data = $json->data->list->list;
		}
		
		return $data;
	}
	
	// Bottom 100 Chart
	function chart_bottom(){
		$requestURL = $this->build_url('chart/bottom');
		$json = $this->fetchJSON($requestURL);

		if(is_object($json->error)){
			$data = $this->errorResponse($json->error);
		}
		else{
			$data = $json->data->list->list;
		}
		
		return $data;
	}
	
	// Box Office (US)
	function boxoffice(){
		$requestURL = $this->build_url('boxoffice');
		$json = $this->fetchJSON($requestURL);

		if(is_object($json->error)){
			$data = $this->errorResponse($json->error);
		}
		else{
			$data = $json->data;
		}
		
		return $data;
	}
	
	// Summarise - only return the most pertinent data (when returning data from IMDb ID)
	function summarise($obj){
	
		// If this is not an ignored type...
		if(!$this->is_ignored($obj->type, $obj->certificate->certificate, $obj->genres)){
			// ID with and without 'tt' prefix
			$s->id = substr($obj->tconst, 2);
			$s->tconst = $obj->tconst;
			
			// Title
			$s->title = $obj->title;
			
			// Year
			$s->year = $obj->year;
			
			// Plot and Tagline
			$s->plot = $obj->plot->outline;
			$s->tagline = $obj->tagline;
			
			// Votes + Rating
			$s->rating = $obj->rating;
			$s->votes = $obj->num_votes;
			
			// Genres
			if(is_array($obj->genres)){
				$s->genre = implode(", ", $obj->genres);
				$s->genres = $obj->genres;
			}else{
				$s->genre = "";
				$s->genres = "";
			}
			
			// Writers
			if(is_array($obj->writers_summary)){
				$i=0;
				foreach($obj->writers_summary as $writers){
					$writer[$i] = $writers->name->name;
					$s->writers_summary[$i]['nconst'] = $writers->name->nconst;
					$s->writers_summary[$i]['name'] = $writers->name->name;
					$s->writers_summary[$i]['attr'] = $writers->attr;
					$i++;
				}
				$s->writer = implode(", ", $writer);
			}else{
				$s->writer = "";
				$s->writers_summary = "";
			}
			
			// Directors
			if(is_array($obj->directors_summary)){
				$i=0;
				foreach($obj->directors_summary as $directors){
					$director[] = $directors->name->name;
					$s->directors_summary[$i]['nconst'] = $directors->name->nconst;
					$s->directors_summary[$i]['name'] = $directors->name->name;
					$i++;
				}
				$s->director = implode(", ", $director);
			}else{
				$s->director = "";
				$s->directors_summary = "";
			}
			
			// Cast
			if(is_array($obj->cast_summary)){
				$i=0;
				foreach($obj->cast_summary as $cast){
					$actor[] = $cast->name->name;
					$s->cast_summary[$i]['nconst'] = $cast->name->nconst;
					$s->cast_summary[$i]['name'] = $cast->name->name;
					$s->cast_summary[$i]['char'] = $cast->char;
					$i++;
				}
				$s->actors = implode(", ", $actor);
			}else{
				$s->actors = "";
				$s->cast_summary = "";
			}
			
			// Shorthand release date in the format of 'd MMM YYYY' and datestamp
			$s->released = !empty($obj->release_date->normal) ? date('j M Y', strtotime($obj->release_date->normal)) : "";
			$s->release_datestamp = $obj->release_date->normal;
			
			// Runtime
			$s->runtime = round($obj->runtime->time/60);
			
			// Certificate
			$s->certificate = $obj->certificate->certificate;
			
			// Poster
			$s->poster = $obj->image->url;
			
			// Type
			$s->type = $obj->type;
			
			// Response messages
			$s->response = 1;
			$s->response_msg = "Success";
		}
		else{
			// Response messages
			$s->response = 0;
			$s->response_msg = "Fail";
			$s->message = "Film type is ignored.";
		}
		
		return $s;
	}
	
	// Summarise - only return the most pertinent data (when returning multiple title data)
	function summarise_titles($objs, $yearMatch=0){
		
		$t=0;
				
		for($i=0; $i<count($objs); $i++){
			$list = $objs[$i]->list;
			
			// In each "list" of results we only want to return titles so ignore other results such as actors, characters etc.
			foreach($list as $obj){
				if(!empty($obj->tconst) AND !$this->is_ignored($obj->type) AND $this->yearMatch($obj->year, $yearMatch)){
					// ID with and without 'tt' prefix
					$s[$t]->id = substr($obj->tconst, 2);
					$s[$t]->tconst = $obj->tconst;
					
					// Title
					$s[$t]->title = $obj->title;
					
					// Year
					$s[$t]->year = $obj->year;
					
					// Comma-seperated list of actors
					$actor = array();
					if(is_array($obj->principals)){
						foreach($obj->principals as $cast){ $actor[] = $cast->name; }
						$s[$t]->actors = implode(", ", $actor);
					}else{
						$s[$t]->actors = "";
					}
					
					// Poster
					$s[$t]->poster = $obj->image->url;
					
					// Type
					$s[$t]->type = $obj->type;
					
					$t++;
					
					// Reached limit of titles?
					if($t==$this->titlesLimit) break 2;
				}
			}
		}
		
		// Response messages
		if($t>0){
			$s['response'] = 1;
			$s['response_msg'] = "Success";
		}
		else{
			$s['response'] = 0;
			$s['response_msg'] = "Fail";
		}
		
		return $s;
	}
	
	// Summarise Person - only return the most pertinent data (when returning data from IMDb ID)
	function summarise_person($obj){
	
		// ID with and without 'tt' prefix
		$s->id = substr($obj->nconst, 2);
		$s->nconst = $obj->nconst;
			
		// Name
		$s->name = $obj->name;
		$s->real_name = $obj->real_name;
			
		// Biography
		$s->bio = $obj->bio;
			
		// Birth and Death
		$s->birthday = !empty($obj->birth->date->normal) ? date('j M Y', strtotime($obj->birth->date->normal)) : "";
		$s->birthday_datestamp = $obj->birth->date->normal;
		$s->birthplace = $obj->birth->place;
		$s->deathday = !empty($obj->death->date->normal) ? date('j M Y', strtotime($obj->death->date->normal)) : "";
		$s->deathday_datestamp = $obj->death->date->normal;
		$s->deathplace = $obj->death->place;
		
		// Known For
		$s->known_for = $obj->known_for;
					
		// Image
		$s->image = $obj->image->url;
		$s->photos = $obj->photos;
			
		// Response messages
		$s->response = 1;
		$s->response_msg = "Success";
		
		return $s;
	}
	
	// Summarise - only return the most pertinent data (when returning multiple title data)
	function summarise_people($objs){
		
		$t=0;
				
		for($i=0; $i<count($objs); $i++){
			$list = $objs[$i]->list;
			
			// In each "list" of results we only want to return titles so ignore other results such as actors, characters etc.
			foreach($list as $obj){
				if(!empty($obj->nconst)){
					// ID with and without 'tt' prefix
					$s[$t]->id = substr($obj->nconst, 2);
					$s[$t]->nconst = $obj->nconst;
					
					// Name
					$s[$t]->name = $obj->name;
					
					// Known For
					$s[$t]->known_for = $obj->known_for;
					
					// Image
					$s[$t]->image = $obj->image->url;
					
					$t++;
					
					// Reached limit of titles?
					if($t==$this->titlesLimit) break 2;
				}
			}
		}
		
		// Response messages
		if($t>0){
			$s['response'] = 1;
			$s['response_msg'] = "Success";
		}
		else{
			$s['response'] = 0;
			$s['response_msg'] = "Fail";
		}
		
		return $s;
	}
	
	// Check if a title should be ignored. Returns true for yes, false for no.
	function is_ignored($type, $cert="", $genre=array()){
		if($this->forceReturn) return false;
		if(in_array($type, $this->ignoreTypes)) return true;
		if($this->ignoreAdult AND ($cert=="X" OR (!empty($genre) AND in_array("Adult",$genre)))) return true;
		
		return false;
	}
	
	// Check year matches
	function yearMatch($year, $yearMatch){
		if($yearMatch===0) return true;
		if($yearMatch==$year) return true;
		return false;
	}
	
	// Basic error handling
	function errorResponse($obj, $returnArray=false){
		$s->status = $obj->status;
		$s->code = $obj->code;
		$s->message = $obj->message;
		$s->response = 0;
		$s->response_msg = "Fail";
		
		if($returnArray) return (array)$s;
		else return $s;
	}
	
	
	// Perform CURL request on the API URL to fetch the JSON data
	function fetchJSON($apiUrl){
		$ch = curl_init($apiUrl);
		$headers[] = 'Connection: Keep-Alive'; 
		$headers[] = 'Content-type: text/plain;charset=UTF-8'; 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 0); 
		curl_setopt($ch, CURLOPT_ENCODING , 'deflate');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		$json = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		
		// Errors?
		if ($curl_errno > 0){
			$data->error->message = 'cURL Error '.$curl_errno.': '.$curl_error;
		}
		else{	        
			// Decode the JSON response
			$data = json_decode($json);
		}
		
		return $data;
	}

}
?>