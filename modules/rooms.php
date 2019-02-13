<?php
	/**
	 * This class contains all the methods to handlle users requests
	 * @param Method : String
	 * @param Id : String | Null
	 * @param Sub collection : String | Null
	 * @param Params : Array | Null
	 * @return Object
	 */
	class rooms extends Module {
        // Setting parameters
        protected $room_ID;
        protected $session_ID = SESSID;
        protected $room_name;
        protected $sub_collection;
        protected $params;
        protected $options;

        // Class constructor
        function __construct($method, $room_ID = null, $sub_collection = null, $params = null) {
        	// Setup all parameters
        	$this->room_ID = $room_ID !== null ? $room_ID : "ID".uniqid();
            $this->sub_collection = $sub_collection;
            $this->params = $params;

        	// Run parent constructor
        	parent::__construct($method);
        }
        /**
        * This method is for handling POST requests for users class
        * @return Array 
        */
        protected function post() {
            // Get data form request body
            $request_data = $this->get_request_body();
            $this->room_name = htmlentities($request_data->room_name);
            $this->options = $request_data->options;

            if (!$this->db->check_row("rooms", array("room_name" => $this->room_name))) {
                $access = $this->options->accsess;
                $tags = $this->options->tags;
                $add_room_qr = "INSERT INTO rooms (room_ID, session_ID, room_name, access, tags)
                                 VALUES ('$this->room_ID', '$this->session_ID', '$this->room_name', '$access', '$tags')";
                $add_room = $this->db->query($add_room_qr, false);
                if ($add_room) {
                    $result = array();
                    $result['room_ID']    = $this->room_ID;
                    $result['room_name']    = $this->room_name;
                    $result['session_ID']    = $this->session_ID;

                    return $result;
                }else {
                    $this->write_error("Error creating room!");
                }
            }else {
                $this->write_error("Room name already exist!");
            }
        }
        /**
        * This method is for handling PUT requests for users class
        * @return Array 
        */
        protected function put() {
        	
        }
        /**
        * This method is for changing user option in database
        * @param option : String
        * @param value  : Boolean
        * @return Null
        */
        private function set_option($option, $value) {

        }
	}


    /*
    Create room request body
    ------------------------
    {
        "room_name" : "black room",
        "options"   : {
            "accsess" : "private",
            "tags"    : "action, shit" || null
        }
    }

    Creating a room request returns 
    -------------------------------
    {
        "room_ID": "ID5c64294b6cbfd",
        "room_name": "black room"
    }
    */