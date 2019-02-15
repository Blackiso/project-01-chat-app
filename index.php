<?php
	// Headers
	header('Content-Type: application/json');
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Methods: GET, POST");
	header("Access-Control-Allow-Headers: Content-Type, *");

	// Init Session
	$sid;

	if (isset($_COOKIE['SESSION_ID'])) {
		start_session($_COOKIE['SESSION_ID']);

		if (!isset($_SESSION['active'])) {
			new_session();
		}else {
			$sid = $_COOKIE['SESSION_ID'];
		}
	}else {
		new_session();
	}

	function new_session() {
		global $sid;
		$sid = "SID".uniqid();
		start_session($sid);
		$_SESSION['active'] = true;
	}

	function start_session($id) {
		if (session_status() === PHP_SESSION_ACTIVE) { session_destroy(); }
		session_id($id);
		session_start(array("name" => "SESSION_ID"));
	}

	// Define constents
	define("M_FLDR", basename(__DIR__));
	define("SESSID", $sid);

	// Init Config files
	require_once('config/database.php');
	require_once('config/core.php');

	// Init Modules files
	require_once('modules/users.php');
	require_once('modules/rooms.php');
	require_once('modules/messeges.php');

	// Init Deve mode
	deve_mode(1);

	// Setup uri and request method
	$request_uri = $_SERVER['REQUEST_URI'];
	$parsed_uri = parse_request_uri($request_uri);
	$request_method = $_SERVER['REQUEST_METHOD'];

	// Init App
	$class_name = $parsed_uri->class;

	if (in_array($class_name, ROUTES)) {
		$collection_id = isset($parsed_uri->collection_id) ? $parsed_uri->collection_id : null;
		$sub_collection = isset($parsed_uri->sub_collection) ? $parsed_uri->sub_collection : null;
		$params = isset($parsed_uri->params) ? $parsed_uri->params : null;

		$app = new $class_name(
			$request_method, 
			$collection_id, 
			$sub_collection, 
			$params
		);
		echo json_encode($app->init());
	}else {
		header("HTTP/1.1 401 Unauthorized");
	}

	
