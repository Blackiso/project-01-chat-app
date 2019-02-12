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
		// Class constructor
		function __construct($method, $id = null, $sub_collection = null, $params = null) {
			parent::__construct($method);
		}

    	protected function post() {
    		echo "post";
    	}
	}