<?php
	// Define routes
	const ROUTES = [
		"messages",
		"rooms",
		"users"
	];
	// Define allowed methods
	const METHODS = [
		"POST",
		"GET",
		"PUT",
		"DELETE"
	];
	// Core functions
	/**
	* Deve Mode enable function
	* @param status : Boolean
	*/
	function deve_mode($state) {
		define("DEVEMODE", $state);

		if ($state) {
			ini_set('display_errors', true);
			error_reporting(E_ALL);
		}
	}

	function boole_me($string) {
		return filter_var($string, FILTER_VALIDATE_BOOLEAN);
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
		if (empty($uri_parts)) {
			echo json_encode(array("error" => "Please Enter a Valid URL!"));
			exit;
		}
		// Get class name from uri parts
		$return_obj->class = $uri_parts[0];
		// Get collection id if available
		if (sizeof($uri_parts) > 1) {
			for ($i=1; $i < sizeof($uri_parts); $i++) {
				$current_part = $uri_parts[$i];
				// Check uri parts
				if ($arr_param = strip_params($current_part)) {
					$return_obj->params = $arr_param['params'];
					$current_part = $arr_param['string'];
				}

				if (if_collection_id($current_part)) {
					$return_obj->collection_id = $current_part;
				}else {
					$return_obj->sub_collection = $current_part;
				}
			}
		}
	
		return $return_obj;
	}

	/**
	* Takes a string strip params from it and returns string and params
	* @param  string : String
	* @return Array | Boolean
	*/
	function strip_params($string) {
		$params = clear_empty_array(explode('?', $string));
		if (sizeof($params) > 1) {
			$return_array = array();
			$return_array['string'] = $params[0];
			$return_array['params'] = array();

			$params_array = explode('&', $params[1]);
			foreach ($params_array as $value) {
				$return_array['params'][explode("=", $value)[0]] = explode("=", $value)[1];
			}

			return $return_array;
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
		if (preg_match('/^(ID)/', $string)) {
			return true;
		}else {
			return false;
		}
	}

	/**
	 * Template for the modules, and conatins global methods
	 */
	abstract class Module {
		// Database conatiner
		protected $db;
		protected $method;

		function __construct($method) {
			// Init Database connection
			$this->db = new DB();
			// Get request method
			$this->method = $method;
			// Clear users
            $this->clear_inactive_users();
            // Clear rooms
            if ($method !== null) $this->clear_inactive_rooms();
		}
		// Call method based on type of request
		public function init() {
			if (in_array($this->method, METHODS)) {
				$mth = strtolower($this->method);
				if (method_exists($this, $mth)) {
					return $this->$mth();
				}else {
					$this->write_error('HTTP Method Not Allowed!');
				}
			}else {
				$this->write_error('HTTP Method Not Allowed!');
			}
		}
		// Check if current users is the admin
		protected function is_admin($room_ID, $user_ID) {
			$admin = $this->db->query("SELECT * FROM  users_options WHERE user_ID = '$user_ID' AND room_ID = '$room_ID'");
			if (!empty($admin) AND $admin[0]['admin']) {
				return true;
			}else {
				return false;
			}
		}
		/**
        * Check if the current room exist
        * @param kill : Boolean
        * @return Boolean
        */ 
        protected function room_exist($kill = false) {
            // check if room exist
            if (!$this->db->check_row("rooms", array("room_ID" => $this->room_ID))) {
                if ($kill) {
                    $this->write_error("Room dosen't exist!");
                }else {
                    return false;
                }
            }
            return true;
        }
        /**
        * This method is for getting online users in the room
        * @return Array 
        */        
        protected function get_users() {
            if ($this->room_exist()) {
                $users = $this->db->query("SELECT user_ID, room_ID, username, last_seen 
                    FROM users WHERE room_ID = '$this->room_ID'");
                foreach ($users as $i => $user) {
                	if ($this->is_admin($this->room_ID, $user['user_ID'])) {
                		$users[$i]['admin'] = true;
                	}else {
                		$users[$i]['admin'] = false;
                	}
                }
                return $users;
            }else {
                $this->write_error("Room dosent exist!");
            }
        }
        // Update last seen
        protected function im_here() {
        	$now = date("Y-m-d H:i:s");
        	$update_qr = $this->db->query("UPDATE users SET last_seen = '$now' WHERE user_ID = '$this->user_ID' AND room_ID = '$this->room_ID'");
        	if ($update_qr) {
        		return true;
        	}
        }
        // Delete inactive users
        protected function clear_inactive_users() {
        	$this->db->query("DELETE FROM users WHERE last_seen < (NOW() - INTERVAL 5 MINUTE)");
        }
        // Delete inactive rooms
        protected function clear_inactive_rooms() {
        	$this->db->query("DELETE rooms FROM rooms WHERE room_ID NOT IN (SELECT DISTINCT room_ID FROM users)");
        	$this->db->query("DELETE users_options FROM users_options WHERE room_ID NOT IN (SELECT DISTINCT room_ID FROM rooms)");
        }
    	// Method to extract request body
		protected function get_request_body() {
			$entity_body = file_get_contents('php://input');
			return json_decode($entity_body);
		}
		// Error handller
		protected function write_error($msg) {
			echo json_encode(array("error" => $msg));
			die();
		}
	}