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
	private $anonymiser = 'http://anonymouse.org/cgi-bin/anon-www.cgi/';	// URL that will be prepended to the generated API URL.
	public $summary = true;			// Set to true to return a summary of the film's details. Set to false to return everything.
	public $titlesLimit = 0;		// TODO: Limit the number of films returned by find_by_title(). 0 = unlimited.
			
	function __construct($anonymise=false, $summary=true, $titlesLimit=0){
		if($anonymise) 		$this->baseurl = $this->anonymiser . $this->baseurl;	// prepend anonymizer to baseurl if needed
		if(!$summary) 		$this->summary=false;									// overriding the default?
		if($titlesLimit>0)	$this->titlesLimit = $titlesLimit;						// Set titles limit if required
	}
	
	// Build URL based on the given parameters
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
		if(empty($json->data->results)){
			// IMDb doesn't return a proper error response in the event of 0 results being returned
			// so set our own failure message.
			$error->message = "No results found.";
			$matches = $this->errorResponse($error);
		}
		else{
			$results = $json->data->results;
			$matches = array();
			
			for($i=0; $i<count($results); $i++){
				$matches = array_merge($matches, $results[$i]->list);
			}
		}
		
		return $matches;
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
	
	// Summarise - only return the most pertinent data
	// TODO: Make this work with data from find_by_title (multiple results)
	function summarise($obj){	
		
		// ID with and without 'tt' prefix
		$s->id = substr($obj->tconst, 2);
		$s->tconst = $obj->tconst;
		
		// Title
		$s->title = $obj->title;
		
		// Year
		$s->year = $obj->year;
		
		// Plot
		$s->plot = $obj->plot->outline;
		
		// Votes + Rating
		$s->rating = $obj->rating;
		$s->votes = $obj->num_votes;
		
		// Comma-seperated list of genres (this is always an array)
		$s->genre = implode(", ", $obj->genres);
		
		// Comma-seperated list of writer(s)
		if(is_array($obj->writers_summary)){
			foreach($obj->writers_summary as $writers){ $writer[] = $writers->name->name; }
			$s->writer = implode(", ", $writer);
		}else{
			$s->writer = "";
		}
		
		// Comma-seperated list of director(s)
		if(is_array($obj->directors_summary)){
			foreach($obj->directors_summary as $directors){ $director[] = $directors->name->name; }
			$s->director = implode(", ", $director);
		}else{
			$s->director = "";
		}
		
		// Comma-seperated list of actors
		if(is_array($obj->directors_summary)){
			foreach($obj->cast_summary as $cast){ $actor[] = $cast->name->name; }
			$s->actors = implode(", ", $actor);
		}else{
			$s->actors = "";
		}
		
		// Shorthand release date in the format of 'd MMM YYYY' and datestamp
		$s->released = !empty($obj->release_date->normal) ? date('j M Y', strtotime($obj->release_date->normal)) : "";
		$s->release_datestamp = $obj->release_date->normal;
		
		// Certificate
		$s->certificate = $obj->certificate->certificate;
		
		// Poster
		$s->poster = $obj->image->url;
		
		// Response messages
		$s->response = 1;
		$s->response_msg = "Success";
		
		return $s;
	}
	
	// Basic error handling
	function errorResponse($obj){
		$s->status = $obj->status;
		$s->code = $obj->code;
		$s->message = $obj->message;
		$s->response = 0;
		$s->response_msg = "Fail";
		
		return $s;
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