<?php

	define("DEVEMODE", 1);
	/**
	 * Database Creation and Managment Class
	 * @param NONE
	 * @return Database object
	 */
	class DB {
		// Define database details
		private const DB_NAME =  "chat_api";
		private const DB_HOST =  "127.0.0.1";
		private const DB_PASS =  "";
		private const DB_USER =  "root";

		// Database connection container
		private $conn;

		function __construct() {
			// If the DEVEMODE is true create run init database
			if (DEVEMODE) {
				$this->init_database();
			}else {
				// Connect to database
				$this->connect(1);
			}
		}

		// Checks if database is not available and create it
		private function init_database() {
			$this->connect(0);
			// Check if database available
 			$db_check_qr = $this->conn->prepare("SELECT SCHEMA_NAME 
 				FROM INFORMATION_SCHEMA.SCHEMATA 
 				WHERE SCHEMA_NAME = :DBName");
 			$db_check_qr->execute(array(':DBName' => self::DB_NAME));
 			$db_check_ex = $db_check_qr->fetchAll();
 			// If array empty then create database
 			if (empty($db_check_ex)) {
 				// Setup querys
 				// create database
 				try {
	 				$db_create_qr = $this->conn->prepare("CREATE DATABASE ".self::DB_NAME);
	 				if($db_create_qr->execute()) {
	 					$this->connect(1);
	 					// create tables
	 					$msg_table_qr = $this->conn->prepare("CREATE TABLE `messeges` (
						  `msg_ID` int(255) NOT NULL,
						  `room_ID` varchar(255) NOT NULL,
						  `username` varchar(255) NOT NULL,
						  `session_ID` varchar(255) NOT NULL,
						  `msg_text` longtext NOT NULL,
						  `msg_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
						) ENGINE=InnoDB DEFAULT CHARSET=latin1");

						$rooms_table_qr = $this->conn->prepare("CREATE TABLE `rooms` (
						  `room_ID` int(255) NOT NULL,
						  `room_name` varchar(255) NOT NULL,
						  `admin_ID` varchar(255) NOT NULL,
						  `option_ID` int(255) NOT NULL,
						  `tags` varchar(255) NOT NULL,
						  `creation_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
						) ENGINE=InnoDB DEFAULT CHARSET=latin1");

						$rooms_options_table_qr = $this->conn->prepare("CREATE TABLE `rooms_options` (
						  `option_ID` int(255) NOT NULL,
						  `option_name` varchar(255) NOT NULL,
						  `option_value` varchar(255) NOT NULL
						) ENGINE=InnoDB DEFAULT CHARSET=latin1");

						$users_table_qr = $this->conn->prepare("CREATE TABLE `users` (
						  `user_ID` varchar(255) NOT NULL,
						  `room_ID` varchar(255) NOT NULL,
						  `username` varchar(255) NOT NULL,
						  `last_seen` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
						) ENGINE=InnoDB DEFAULT CHARSET=latin1");

						$users_options_qr = $this->conn->prepare("CREATE TABLE `users_options` (
						  `user_ID` varchar(255) NOT NULL,
						  `room_ID` varchar(255) NOT NULL,
						  `banned` tinyint(1) NOT NULL DEFAULT '0',
						  `moderator` tinyint(1) NOT NULL DEFAULT '0'
						) ENGINE=InnoDB DEFAULT CHARSET=latin1");

						// Execute querys
						$msg_table_qr->execute();
						$rooms_table_qr->execute();
						$rooms_options_table_qr->execute();
						$users_table_qr->execute();
						$users_options_qr->execute();

						// Setup primary keys
						$pk_change_msg = $this->conn->prepare("ALTER TABLE `messeges`
						  ADD PRIMARY KEY (`msg_ID`)");
						$pk_change_rooms = $this->conn->prepare("ALTER TABLE `rooms`
	  					  ADD PRIMARY KEY (`room_ID`)");
						$pk_change_rooms_options = $this->conn->prepare("ALTER TABLE `rooms_options`
						  ADD PRIMARY KEY (`option_ID`)");
						$pk_change_users = $this->conn->prepare("ALTER TABLE `users`
						  ADD PRIMARY KEY (`user_ID`)");
						$pk_change_users_options = $this->conn->prepare("ALTER TABLE `users_options`
						  ADD PRIMARY KEY (`user_ID`)");

						// Execute querys
						$pk_change_msg->execute();
						$pk_change_rooms->execute();
						$pk_change_rooms_options->execute();
						$pk_change_users->execute();
						$pk_change_users_options->execute();
	 				}
	 			} catch (PDOException $e) {
				    $this->db_error($e->getMessage());
				}
 			}else {
 				$this->connect(1);
 			}
		}
		/**
		* Function to connect to database
		* @param use_db : boolen
		*/
		private function connect($use_db) {
			try {
				// init PDO object
				$pdo_init = 'mysql:host='.self::DB_HOST;
				if ($use_db) $pdo_init .= ';dbname='.self::DB_NAME;
			
				$this->conn = new PDO($pdo_init, self::DB_USER, self::DB_PASS);
				if (DEVEMODE) {
					// Setup errors mod
					$this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
				}
			} catch (PDOException $e) {
			    $this->db_error($e->getMessage());
			}
		}
		/**
		* Function to check in a row is available in table
		* @param  table   : String
		* @param  options : array( condition => value )
		* @param  return  : Boolean
		* @return Boolean
		*/
		public function check_row($table, $conditions) {
			// Building the query
			$row_check_qr = "SELECT *";
			$row_check_qr .= " FROM $table";
			$row_check_qr .= " WHERE";

			$first = true;
			foreach ($conditions as $condition => $value) {
				$row_check_qr .= !$first ? " AND" : "";
				$row_check_qr .= " " . $condition . " = '".$value."'";
				$first = false;
			}
			// Execute the query
			$row_check_qr = $this->conn->prepare($row_check_qr);
			$row_check_qr->execute();
			$result = $row_check_qr->fetchAll();
			// Return the results
			if (!empty($result)) {
				return true;
			}else {
				return false;
			}
		}
		/**
		* Function to execut querys on database
		* @param  statment : String
		* @param  Return   : Boolean
		* @return Array | null
		*/
		public function query($statment, $return = true) {
			$query = $this->conn->prepare($statment);
			$query->execute();
			$result = $query->fetchAll();
			return $result;
		}

		private function db_error($msg) {
			// Return error message as json and kill the script
			echo json_encode(array('DB Error' => $msg));
			die();
		}
	}

	$dd = new DB();
	echo $dd->query("SELECT * FROM users");