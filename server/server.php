<?php 
	set_time_limit(0);
	ob_implicit_flush();

	// Init Config files
	require_once('../config/core.php');
	require_once('../config/database.php');
	// Init Deve mode
	deve_mode(1);
	// Init Socket Classes
	require_once("classes/Websocket.php");
	require_once("classes/User.php");
	require_once("classes/Handler.php");

	$arguments = array();
	for ($i=1; $i < sizeof($argv); $i++) { 
		$xpld = explode("=", $argv[$i]);
		$arguments[$xpld[0]] = $xpld[1];
	}

	$server = new Websocket($arguments["address"], $arguments["port"]);
	$server->run();
 ?>