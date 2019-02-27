<?php 
	 /**
	  * The user class for WS server
	  */
	 class User {

	 	public $user_ID;
	 	public $room_ID;
	 	public $session_ID;
	 	public $socket;
	 	public $username;
	 	public $handshake = false;

	 	private $db;

	 	function __construct($socket, $user_ID) {
	 		$this->socket = $socket;
	 		$this->user_ID = $user_ID;
	 		$this->db = new DB();
	 		$this->create_user();
	 	}

	 	private function create_user() {
	 		$result = $this->db->query("SELECT user_ID, room_ID, username, session_ID FROM users WHERE user_ID = '$this->user_ID'");
	 		$this->username = $result[0]['username'];
	 		$this->room_ID = $result[0]['room_ID'];
	 		$this->session_ID = $result[0]['session_ID'];
	 	}
	 }