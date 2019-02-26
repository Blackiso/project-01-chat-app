<?php 
	error_reporting(E_ALL);
	set_time_limit(0);
	ob_implicit_flush();

	// Init Config files
	require_once('../config/database.php');
	require_once('../config/core.php');
	// Init Modules files
	require_once('../modules/users.php');
	require_once('../modules/rooms.php');
	require_once('../modules/messages.php');
	// Init Socket
	require_once("classes/Websocket.php");

	$arguments = array();
	for ($i=1; $i < sizeof($argv); $i++) { 
		$xpld = explode("=", $argv[$i]);
		$arguments[$xpld[0]] = $xpld[1];
	}

	$server = new Websocket($arguments["address"], $arguments["port"]);
	$server->run();
 ?>