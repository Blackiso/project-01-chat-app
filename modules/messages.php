<?php
	/**
	 * This class contains all the methods to handlle users requests
	 * @param Method : String
	 * @param Id : String | Null
	 * @param Sub collection : String | Null
	 * @param Params : Array | Null
	 * @return Object
	 */
    class messages extends Module {
    	protected $room_ID;
    	protected $user_ID;
    	protected $username;
    	protected $sub_collection;
    	protected $params;
    	protected $session_ID = SESSID;
    	protected $message;
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
    	* This method is for handling GET requests for messages class
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
		// Get all messages
		protected function get_all() {
			$messages = $this->db->query("SELECT username, message, msg_time FROM messages 
				WHERE room_ID = '$this->room_ID'");
			if ($messages) {
				return $messages;
			}
		}
		// Get new messages
		protected function get_new() {
            $time = date("Y-m-d")." ".$this->params->time;
            while (1) {
            	$messages = $this->db->query("SELECT username, message, msg_time FROM messages 
				WHERE room_ID = '$this->room_ID' AND msg_time >= '$time'");
				if ($messages) {
					return $messages;
				}else {
					sleep(1);
				}
            }
		}
		/**
    	* This method is for handling POST requests for messages class
    	* @return Array 
    	*/
		protected function post() {
			$this->run_check();
			// Get request body
			$request_data = $this->get_request_body();
			$this->message = $request_data->message;
			// Insert username in database
			$message_insert = $this->db->query("INSERT INTO messages 
				(room_ID, user_ID, username, message, session_ID) 
				VALUES ('$this->room_ID', '$this->user_ID', '$this->username', '$this->message', '$this->session_ID')", false);

			if ($message_insert) {
				$result = array();
				$result['user_ID']  = $this->user_ID;
                $result['username'] = $this->username;
                $result['message']  = $this->message;
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
    Send message request body
    ------------------------
    {
        "message" : "Hello World!"
    }

    Send message request returns 
    -------------------------------
	{
		"user_ID": "ID5c657d8007328",
	    "username": "blackiso",
	    "message": "Hello World!",
	    "time": "2019-02-15 15:25:22"
	}
    */