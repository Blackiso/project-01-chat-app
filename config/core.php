<?php
	
	// Core functions
	/**
	* Deve Mode enable function
	* @param status : Boolean
	*/
	function deve_mode($status) {
		define("DEVEMODE", $status);

		if ($status) {
			ini_set('display_errors', true);
			error_reporting(E_ALL);
		}
	}

	/**
	* Clear all empty items in array
	* @param array : Array
	* @return Array
	*/
	function clear_empty_array($array) {
		$new_aaray  = array();
		foreach ($array as $value) {
			if ($value !== "" AND $value !== " " AND $value !== M_FLDR) {
				array_push($new_aaray, $value);
			}
		}
		return $new_aaray;
	}

	/**
	* Parse elements of the request uri and get params
	* @param  uri : String
	* @return Object
	*/
	function parse_request_uri($uri) {
		$return_obj = (object) array();
		// Get all uri parts
		$uri_parts = clear_empty_array(explode('/', $uri));
		// Get class name from uri parts
		$return_obj->class = $uri_parts[0];
		// Get collection id if available
		if (sizeof($uri_parts) > 1) {
			for ($i=1; $i < sizeof($uri_parts); $i++) {
				// Check if collection id
				if (strip_params($uri_parts[$i])) {
					
				}else {

				}
			}
		}

		// print_r($uri_parts);
		strip_params($uri_parts[1]);
	
		// return $return_obj;
	}

	/**
	* Takes a string strip params from it and returns string and params
	* @param  string : String
	* @return Array
	*/
	function strip_params($string) {
		$params = clear_empty_array(explode('?', $string));
		if (sizeof($params) > 1) {
			
		}else {
			return false;
		}
	}

	/**
	* Check if string hase the format of a collection ID
	* @param  string : String
	* @return Boolean
	*/
	function if_collection_id($string) {
		if (!preg_match('/^(ID)/', $string)) {
			return true;
		}else {
			return false;
		}
	}