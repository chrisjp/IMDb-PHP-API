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
		$requestURL = $this->build_url('title/maindetails', $id, 'tconst');
		$json = $this->fetchJSON($requestURL);

		$data = $json->data;
		
		return $data;
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
	
	function __destruct(){
		// nothing to do here...
	}

}
?>