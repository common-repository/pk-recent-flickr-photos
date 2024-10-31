<?php 

/*
 * This class encapsulates functions for making unsigned Flickr API method calls.
 *
 * @author 			Saophalkun Ponlu
 * @email			phalkunz@gmail.com
 * @website			http://phalkunz.com
 * @date			Jan, 27 2008
 */
 
class FlickrAPI {
	
	const API_KEY = 'df67e2edfd595fe1c1b2db47f8983918';
	const SECRET = 'f22f74b8cb12fd8f';
	const REST_URL = 'http://flickr.com/services/rest/?';
	
	/*
	 * Returns a url to invoke $method
	 * @return 	string			url
 	 * @param	$method			the name of the mothod to be called. e.g flickr.photos.search
 	 * @param 	$parameters		array of parameters of the method
	 */
	function generateMethodUrl($method, $parameters) {
		// get api signature
		$sig = $this->generateMethodSig($method, $parameters);
		// stores the string of the method's parameters
		$paraString = "";
		$firstPara = true;
		foreach ($parameters as $key => $value) {
			if($firstPara){
				$paraString .= $key."=".urlencode($value);	
				$firstPara=false;	
			}
			else {
				$paraString .=  "&".$key."=".urlencode($value);
			}
		}
	
		// url: method + parameters + auth_token + sig
		$method_url = FlickrAPI::REST_URL."method=".$method."&api_key=".FlickrAPI::API_KEY."&".$paraString."&api_sig=".$sig;
		return $method_url;		
	}

	/*
	 * Returns an api signature of the $method method
	 * @return 	string			api signature 
 	 * @param	$method			the name of the mothod to be called. e.g flickr.photos.search
 	 * @param 	$parameters		array of parameters of the method
	 */
	function generateMethodSig($method, $parameters) {
		$elementsToBeSigned = array();
		$elementsToBeSigned = $parameters;
		$elementsToBeSigned['method'] = $method;
		$elementsToBeSigned['api_key'] = FlickrAPI::API_KEY;
		ksort($elementsToBeSigned);
		
		$stringToBeSigned = '';
		foreach($elementsToBeSigned as $key => $value){
			$stringToBeSigned .= $key.$value;
		}
		$stringToBeSigned = FlickrAPI::SECRET.$stringToBeSigned;
		return md5($stringToBeSigned);
	}
	
	/*
	 * Makes an unsigned Flickr API method calls
	 * @return 	string			response (xml format) 
 	 * @param	$url			the url to the method 
	 */
	function callMethod($url) {
		$ch = curl_init($url);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
		$result = curl_exec($ch);
		curl_close($ch);
		return $result;
	}

} // end of class FlickrAPI

/*
 * Returns a Flickr userid of the $username, empty string if the username is invalid
 * @return 	string			Flickr userid 
 * @param	$flickr			FlickrAPI class
 * @param 	$username		string of Flickr username
 */
function getUserId($flickr, $username) {
	$paras = array();
	$paras['username'] = $username;
	$methodUrl = $flickr->generateMethodUrl('flickr.people.findByUsername', $paras);
	$response_str = $flickr->callMethod($methodUrl);
	// if the username is invalid, return an empty string
	if( strpos($response_str,'<rsp stat="fail">') ) {
		return '';
	}
	$xml =  simplexml_load_string ( $response_str );
	// get attributes (array) of the user node
	$userAttrs = $xml->user[0]->attributes();
	// returns the uid
	return $userAttrs[0];
}

/*
 * Returns a set of array[id,farm,server,secret,title] that each represents a photos
 * @return 	array			array of array[id,farm,server,secret,title] 
 * @param	$flickr			FlickrAPI class
 * @param 	$userId			string of Flickr userId
 * @param 	$number			number of photos to be returned
 * @param 	$format			'_s', '_t', '_m', '' 
 *
 * @info					infor on the format http://www.flickr.com/services/api/misc.urls.html
 */
function getRecentPhotos($flickr, $userId, $num=10, $format='') {
	$photos = array();
	
	$paras = array();
	$paras['user_id'] = $userId;
	$paras['per_page'] = $num;
	$methodUrl = $flickr->generateMethodUrl('flickr.photos.search', $paras);
	$xml =  simplexml_load_string ( $flickr->callMethod($methodUrl) );
	

	foreach ($xml->photos[0]->photo as $p) {
		$attrs = $p->attributes();
		
		$id = $attrs[0];
		$farm = $attrs[4];
		$server = $attrs[3];
		$secret = $attrs[2];
		$title = $attrs[5];
		
		$photo = array('id'=>$id, 'farm'=>$farm, 'server'=>$server, 'secret'=>$secret, 'title'=>$title);
		$photos[] = $photo;
		
	}
	
	return $photos;
}
?>
