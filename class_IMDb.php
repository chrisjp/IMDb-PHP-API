<?php
class IMDb
{
	
	private $baseurl = 'http://app.imdb.com/';
	private $params = array(
						'api'		=> 'v1',
						'appid'		=> 'iphone1_1',
						'apiPolicy'	=> 'app1_1',
						'apiKey'	=> '2wex6aeu6a8q9e49k7sfvufd6rhh0n',
						'locale'	=> 'en_US',
					  );
	private $anonymizer = 'http://anonymouse.org/cgi-bin/anon-www.cgi/';	// Real URL will be appended to this
	
			
	function __construct($anonymize=false){
		if($anonymize) $this->baseurl = $this->anonymizer . $this->baseurl;	// prepend anonymizer to baseurl if needed
	}
	
	
	function build_url($method, $query, $parameter){
		$url = $this->baseurl.$method.'?';
		
		// Loop through parameters
		foreach($this->params as $key => $value){
			$url .= $key.'='.$value.'&';
		}
		
		// Add timestamp
		$url .= 'timestamp='.$_SERVER['REQUEST_TIME'].'&';
		
		// Add URLEncode'd query
		$url .= $parameter.'='.urlencode($query);
		
		return $url;
	}
	
	// Search IMDb by title of film
	function find_by_title($title){
		$requestURL = $this->build_url('find', $title, 'q');		
		$json = $this->fetchJSON($requestURL);
		
		// We'll usually have several "lists" returned in the JSON. Combine all these into one array.
		$results = $json->data->results;
		$matches = array();
		
		for($i=0; $i<count($results); $i++){
			$matches = array_merge($matches, $results[$i]->list);
		}
		
		return $matches;
	}
	
	// Search IMDb by ID of film
	function find_by_id($id){
		if(strpos($id, "tt")!==0) $id = "tt".$id;
		$requestURL = $this->build_url('title/maindetails', $id, 'tconst');
		$json = $this->fetchJSON($requestURL);

		$data = $this->handleData($json->data);
		
		return $data;
	}
	
	// Perform some operations on some of the data we're holding
	// Adds the following data to the object:
	//
	//  + id - IMDb ID without the 'tt' prefix
	//  + genre - comma-seperated list of genres
	//  + writer - comma-seperated list of writer(s)
	//  + director - comma-separated list of director(s)
	//  + actors - comma-seperated list of actors
	//  + released - shorthand release date in the format of 'd MMM YYYY'
	//
	// TODO: Make this work with data from find_by_title
	function handleData($obj){
		// ID without 'tt' prefix
		$obj->id = substr($obj->tconst, 2);
		
		// Comma-seperated list of genres (this is always an array)
		$obj->genre = implode(", ", $obj->genres);
		
		// Comma-seperated list of writer(s)
		if(is_array($obj->writers_summary)){
			foreach($obj->writers_summary as $writers){ $writer[] = $writers->name->name; }
			$obj->writer = implode(", ", $writer);
		}else{
			$obj->writer = "";
		}
		
		// Comma-seperated list of director(s)
		if(is_array($obj->directors_summary)){
			foreach($obj->directors_summary as $directors){ $director[] = $directors->name->name; }
			$obj->director = implode(", ", $director);
		}else{
			$obj->director = "";
		}
		
		// Comma-seperated list of actors
		if(is_array($obj->directors_summary)){
			foreach($obj->cast_summary as $cast){ $actor[] = $cast->name->name; }
			$obj->actors = implode(", ", $actor);
		}else{
			$obj->actors = "";
		}
		
		// Shorthand release date in the format of 'd MMM YYYY'
		$obj->released = !empty($obj->release_date->normal) ? date('j M Y', strtotime($obj->release_date->normal)) : "";
		
		return $obj;
	}
	
	
	// Perform CURL request on the API URL to fetch the JSON data
	function fetchJSON($apiUrl){
		$ch = curl_init($apiUrl);
		$headers[] = 'Connection: Keep-Alive'; 
		$headers[] = 'Content-type: text/plain;charset=UTF-8'; 
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers); 
		curl_setopt($ch, CURLOPT_HEADER, 0); 
		curl_setopt($ch, CURLOPT_TIMEOUT, 15); 
		curl_setopt($ch, CURLOPT_ENCODING , 'deflate');
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
		curl_setopt($ch, CURLOPT_VERBOSE, 1); 
		$json = curl_exec($ch);
		$curl_errno = curl_errno($ch);
		$curl_error = curl_error($ch);
		curl_close($ch);
		
		// Errors?
		if ($curl_errno > 0) {
			print 'cURL Error '.$curl_errno.': '.$curl_error;
		}
        
		// Return the JSON
		if(!empty($json)) return json_decode($json);
	}

}
?>