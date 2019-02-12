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
	if (isset($_COOKIES['SESSION_ID'])) {
		session_id($_COOKIES['SESSION_ID']);
	}
	session_start();
	$class_name = $parsed_uri->class;
	$app = new $class_name();