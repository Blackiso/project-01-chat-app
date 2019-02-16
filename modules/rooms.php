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
            $this->params = (Object)$params;

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
        * This method is for handling PUT requests for rooms class
        * @return Array 
        */
        protected function put() {
        	// Update room options
            
        }
        /**
        * This method is for handling DELETE requests for rooms class
        * @return Array 
        */
        protected function delete($server = false) {
            // Check if room exist
            if ($this->room_exist(true)) {
                // Check if admin 
                if (!$server) {
                    if (!$this->is_admin($this->room_ID)) {
                        $this->write_error("Error only the admin can delete this room!");
                    }
                }
                // Delete all users from room
                $clear_users = $this->db->query("DELETE FROM users WHERE room_ID = '$this->room_ID'", false);
                // Delete all users options
                $clear_users_op = $this->db->query("DELETE FROM users_options WHERE room_ID = '$this->room_ID'", false);
                // Delete all room options
                $clear_options = $this->db->query("DELETE FROM rooms_options WHERE room_ID = '$this->room_ID'", false);
                // Delete room   
                $delete_room = $this->db->query("DELETE FROM rooms WHERE room_ID = '$this->room_ID'", false);

                if ($clear_users && $clear_users_op && $clear_options && $delete_room) {
                    return array("success" => true);
                }else {
                    $this->write_error("Error deleting room!");
                }
            }
        }
         /**
        * This method is for handling GET requests for rooms class
        * @return Array 
        */
        protected function get() {
            $subc = "get_".$this->sub_collection;
            if ($subc !== null && method_exists($this, $subc)) {
                if ($this->sub_collection == "users") {
                    $current_users = intval($this->params->number);
                    while (1) {
                        $this->clear_inactive_users();
                        $users = $this->get_users();
                        if (sizeof($users) !== $current_users) {
                            return $users;
                        }else {
                            sleep(1);
                        }
                    }
                }
                // return $this->$subc();
            }else {
                $this->write_error("Request error!");
            }
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