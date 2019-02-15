<?php
	/**
	 * This class contains all the methods to handlle users requests
	 * @param Method : String
	 * @param Id : String | Null
	 * @param Sub collection : String | Null
	 * @param Params : Array | Null
	 * @return Object
	 */
    class messeges extends Module {
    	protected $room_ID;
    	protected $user_ID;
    	protected $username;
    	protected $sub_collection;
    	protected $params;
    	protected $session_ID = SESSID;
    	protected $messege;
    	protected $time;

    	// Class constructor
		function __construct($method, $room_ID = null, $sub_collection = null, $params = null) {
			// Setup all parameters
			$this->room_ID = $room_ID !== null ? $room_ID : $this->write_error("Request Error!");
			$this->user_ID = isset($_SESSION['user_ID']) ? $_SESSION['user_ID'] : $this->write_error("Request Error!");
			$this->sub_collection = $sub_collection;
			$this->params = (object)$params;
			// Run parent constructor
			parent::__construct($method);
		}
		/**
    	* This method is for handling GET requests for messeges class
    	* @return Array 
    	*/
		protected function get() {
			$this->run_check();
			// Filter request
			$subc = "get_";
			$subc .= isset($this->params->filter) ? $this->params->filter : "all";
            if ($subc !== null && method_exists($this, $subc)) {
                return $this->$subc();
            }else {
                $this->write_error("Request error!");
            }
		}
		// Get all messeges
		protected function get_all() {
			$messeges = $this->db->query("SELECT username, messege, msg_time FROM messeges 
				WHERE room_ID = '$this->room_ID'");
			if ($messeges) {
				return $messeges;
			}
		}
		// Get new messeges
		protected function get_new() {
			$request_data = $this->get_request_body();
            $this->time = $request_data->time;
            while (1) {
            	$messeges = $this->db->query("SELECT username, messege, msg_time FROM messeges 
				WHERE room_ID = '$this->room_ID' AND msg_time >= '$this->time'");
				if ($messeges) {
					return $messeges;
				}else {
					sleep(10);
				}
            }
		}
		/**
    	* This method is for handling POST requests for messeges class
    	* @return Array 
    	*/
		protected function post() {
			$this->run_check();
			// Get request body
			$request_data = $this->get_request_body();
			$this->messege = $request_data->message;
			// Insert username in database
			$message_insert = $this->db->query("INSERT INTO messeges 
				(room_ID, user_ID, username, messege, session_ID) 
				VALUES ('$this->room_ID', '$this->user_ID', '$this->username', '$this->messege', '$this->session_ID')", false);

			if ($message_insert) {
				$result = array();
				$result['user_ID']  = $this->user_ID;
                $result['username'] = $this->username;
                $result['messege']  = $this->messege;
                $result['time']     = date("Y-m-d H:i:s");

                return $result;
			}
		}

		protected function run_check() {
			// Check if user is in the room
			if (!$this->db->check_row("users", array("user_ID" => $this->user_ID, "room_ID" => $this->room_ID))) {
				$this->write_error("Request Error!");
			}else {
				$username = $this->db->query("SELECT username FROM users WHERE user_ID = '$this->user_ID' AND room_ID = '$this->room_ID'");
				$this->username = $username[0]['username'];
			}
		}
    }

    /*
    Send messege request body
    ------------------------
    {
        "message" : "Hello World!"
    }

    Send messege request returns 
    -------------------------------
	{
		"user_ID": "ID5c657d8007328",
	    "username": "blackiso",
	    "messege": "Hello World!",
	    "time": "2019-02-15 15:25:22"
	}
    */