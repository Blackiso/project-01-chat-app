<?php
	// Headers
	header('Content-Type: application/json');
	header("Access-Control-Allow-Origin: *");
	header("Access-Control-Allow-Credentials: true");
	header("Access-Control-Allow-Methods: GET, POST");
	header("Access-Control-Allow-Headers: Content-Type, *");

	// Init Config files
	require_once('config/database.php');
	require_once('config/core.php');

	// Init Modules files
	require_once('modules/users.php');
	require_once('modules/rooms.php');
	require_once('modules/messeges.php');

	// Init Deve mode
	deve_mode(1);
	// Define folder name
	define("M_FLDR", basename(__DIR__));

	// Setup uri and request method
	$request_uri = $_SERVER['REQUEST_URI'];
	$parsed_uri = parse_request_uri($request_uri);
	$request_method = $_SERVER['REQUEST_METHOD'];

	// Init Session
	if (isset($_COOKIE['SESSION_ID'])) {
		$sid = $_COOKIE['SESSION_ID'];
	}else {
		$sid = "SID".uniqid();
	}

	define("SID", $sid);
	session_id(SID);
	session_start(array("name" => "SESSION_ID"));

	// Init App
	$class_name = $parsed_uri->class;
	if (array_search($class_name, $routes)) {
		$app = new $class_name($request_method);
	}else {
		header("HTTP/1.1 401 Unauthorized");
	}
	