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
		protected $id;
		protected $sub_collection;
		protected $params;

		// Class constructor
		function __construct($method, $id = null, $sub_collection = null, $params = null) {
			parent::__construct($method);
			$this->id = $id;
			$this->sub_collection = $sub_collection;
			$this->params = $params;
		}
		// Join a chat room
    	protected function post() {
    		// Create a user
    		if ($this->id = null) $this->id = "ID".uniqid();
    		$user_query = $this->db->query("")
    	}
    	// Change / add options
    	protected function put() {
    		
    	}
	}