<?php
    /**
     * This class contains all the methods to handlle users requests
     * @param Method : String
     * @param Id : String | Null
     * @param Sub collection : String | Null
     * @param Params : Array | Null
     * @return Object
     */
    class users extends Module {
        // Setting parameters
        protected $user_ID;
        protected $room_ID;
        protected $username;
        protected $sub_collection;
        protected $params;

        // Class constructor
        function __construct($method, $user_ID = null, $sub_collection = null, $params = null) {
            // Setup all parameters
            if ($user_ID == null) {
                if (!isset($_SESSION['user_ID'])) {
                    $id = "ID".uniqid();
                    $_SESSION['user_ID'] = $id;
                    $this->user_ID = $id;
                }else {
                    $this->user_ID = $_SESSION['user_ID'];
                }
            }else {
                $this->user_ID = $user_ID;
            }

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
            $this->username = isset($request_data->username) ? htmlentities($request_data->username) : null;
            $this->room_ID = $request_data->room_ID;
            // Check if room exist
            $this->room_exist(true);
            // Join room
            return $this->join_room();

        }
        // This method is used to add user to a room
        public function join_room($username = null, $room_ID = null) {
            if ($username !== null && $room_ID !== null) {
                $this->username = $username;
                $this->room_ID = $room_ID;
            }
            // Add user to room
            if (!$this->db->check_row("users", array("user_ID" => $this->user_ID, "room_ID" => $this->room_ID))) {
                $user_query = $this->db->query("INSERT INTO users (user_ID, room_ID, username, session_ID)
                            VALUES ('$this->user_ID', '$this->room_ID', '$this->username', '".SESSID."')");
            }else {
                $user_query = $this->im_here();
            }
            
            if ($user_query) {
                if ($this->is_admin($this->room_ID)) {
                    $this->set_option("admin", 1);
                }
                // Get room name
                $room_name = $this->db->query("SELECT room_name FROM rooms WHERE room_ID = '$this->room_ID'");
                $this->clear_inactive_users();
                $result = array();
                $result['user_ID']    = $this->user_ID;
                $result['username']   = $this->username;
                $result['room_ID']    = $this->room_ID;
                $result['room_name']  = $room_name[0]['room_name'];
                $result['users']      = $this->get_users();
                $result['session_ID'] = SESSID;

                return $result;
            }else {
                $this->write_error("User join error!");
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
            if ($this->db->check_row("users_options", array("user_ID" => $this->user_ID, "room_ID" => $this->room_ID))) {
                $option_query = $this->db->query("UPDATE users_options SET $option = '$value' WHERE user_ID = '$this->user_ID' AND room_ID = '$this->room_ID'");
                if (!$option_query) {
                    $this->write_error("Option query error!");
                }
            }else {
                $option_query = $this->db->query("INSERT INTO users_options (user_ID, room_ID, $option) VALUES ('$this->user_ID', '$this->room_ID', '$value')");
                if (!$option_query) {
                    $this->write_error("Option query error!");
                }
            }
        }
    }

    /*
    Join room request body
    ----------------------
    {
        "username" : "blackiso",
        "room_ID" : "ID5c64294b6cbfd"
    }
    
    Join room request returns
    -------------------------
    {
        "user_ID": "ID5c6429fa1ee39",
        "room_ID": "ID5c64294b6cbfd",
        "username": "blackiso",
        "session_ID": "SID5c6429fa1b661"
    }
    */