<?php
	$CLI = php_sapi_name() == "cli" ? true : false;

	if (!$CLI) {
		ob_end_clean();
		header("Connection: close");
		ignore_user_abort();
		ob_start();
	}
	
	error_reporting(E_ALL);
	set_time_limit(0);
	ob_implicit_flush();

	include("classes/Websocket.php");

	$arguments = array();

	if ($CLI) {
		for ($i=1; $i < sizeof($argv); $i++) { 
			$xpld = explode("=", $argv[$i]);
			$arguments[$xpld[0]] = $xpld[1];
		}
	}else {
		$arguments = $_GET;
	}

	if (!$CLI) {
?>
	<!DOCTYPE html>
	<html lang="en">
	<head>
		<meta charset="UTF-8">
		<title>Document</title>
		<style>
			body {
				margin:0;
				padding: 0;
			}
			#log {
				height: 100vh;
				width: 100%;
				background-color: gray;
				color: black;
				overflow-y: auto;
			}
		</style>
	</head>
	<body>
		<div id="log">
			
		</div>
		<script>
			function ajax(metod, link) {
				http = new XMLHttpRequest();
				http.open(metod, link);
				http.send();
				http.onload = () => {
					var data = http.responseText;
					log.innerHTML = data;
				}
			}
			
			setInterval(()=> {
				ajax('GET', 'log.txt');
			}, 1000);
		</script>
	</body>
	</html>
<?php
		$size = ob_get_length();
		header("Content-Length: $size");
		ob_end_flush();
		flush();
		session_write_close();
	}
	$i = 0;
	while (1) {
		if (connection_aborted()) die();
		$i++;
		$txt = "Debug ".$i;
		$myfile = file_put_contents('log.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);

		sleep(5);
	}
?>
