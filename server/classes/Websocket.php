<?php 
	/**
	* Websocket Class
	* @param address (String)
	* @param port    (int)
	* @param bufferLength (int)
	* @return Websocket object
	*/
	class Websocket {
		
		protected $maxBufferSize;
		protected $master;
		protected $sockets = array();
		protected $users = array();
		
		function __construct($address, $port, $bufferLength = 2048) {
			$this->maxBufferSize = $bufferLength;
			// Creating the master socket
			$this->master = socket_create(AF_INET, SOCK_STREAM, SOL_TCP)
				 or die($this->log("Error Creating Master Socket!"));
			// Setting options
			socket_set_option($this->master, SOL_SOCKET, SO_REUSEADDR, 1)
				 or die($this->log("Error Setting Option!"));
			// Bind addr and port
			socket_bind($this->master, $address, $port);
			// Listen to requests
			if(socket_listen($this->master)) {
				$this->log("Server Started on ".$address.":".$port);
				$this->sockets['m'] = $this->master;
			}else {
				$this->log("Error Listening on ".$address.":".$port);
			}
		}

		public function run() {
			while (1) {
				$sockets = $this->sockets;
				$write = $except = null;
				socket_select($sockets, $write, $except, 1);

				foreach ($sockets as $socket) {
					if ($socket == $this->master) {
						// Accept new Connection
						$client = socket_accept($socket);
						// Connect Client
						$id = uniqid();
						$this->sockets[$id] = $client;
						$this->log("Client Connected to Master Socket $socket");
					}else {
						// Receve data from socket
						$num_bytes = socket_recv($socket, $buffer, $this->maxBufferSize, 0);
						if (!$num_bytes) {
							$error_txt = socket_strerror(socket_last_error());
							$this->log($error_txt);
							$this->disconnect($socket, $buffer);
						}else if($num_bytes == 0) {
							$this->disconnect($socket, $buffer);
						}else {
							// Check handshake
							if ($this->check_handshake($socket)) {
								$header = $this->parse_headers($buffer);
								if ($header['opcode'] !== 8) {
									$this->log("Message Recived From Socket $socket");
									$handler = new Handler($this->users, $socket, $buffer);
									$handler->init();
								}else {
									$this->log("Socket $socket Disconnected");
									$this->disconnect($socket, $buffer);
								}
							}else {
								$tmp = str_replace("\r", '', $buffer);
								if (strpos($tmp, "\n\n") === false ) {
									continue;
								}
								//Create a hanshake ...
								$user = $this->connect($socket, $buffer);
								if(!$user) {
									$this->log("Socket $socket Disconnected");
									$this->disconnect($socket, $buffer, false);
								}else {
									$this->handshake($user, $buffer);
									$handler = new Handler($this->users, $socket, $buffer);
									$handler->user_joined();
								}
							}
						}
					}
				}
			}
		}

		protected function connect($socket, $buffer) {
			// Clear old socket
			foreach ($this->sockets as $id => $_socket) {
				if ($_socket == $socket) {
					unset($this->sockets[$id]);
				}
			}
			if(preg_match("/Sec-WebSocket-Protocol: (.*)\r\n/", $buffer, $match)){
				$info = explode(", ", $match[1]);
	            $user_ID = $info[0];
	            $room_ID = $info[1];
	        }
			$user = new User($socket, $user_ID, $room_ID);
			if ($user->error !== null) {
				return false;
			}else {
				$this->users[$user_ID] = $user;
				$this->sockets[$user_ID] = $socket;
				$this->log("User $user_ID Connected on $socket");
				return $user;
			}
		}

		protected function disconnect($socket, $buffer, $joined = true) {
			if ($joined) {
				$handler = new Handler($this->users, $socket, $buffer);
				$handler->user_left();
				$user = $this->get_user_by_socket($socket);
				unset($this->users[$user->user_ID]);
				unset($this->sockets[$user->user_ID]);
			}
			
			$this->log("User Disconnected from $socket");
		}

		protected function check_handshake($socket) {
			foreach ($this->users as $user) {
				if ($user->socket == $socket) {
					return true;
				}
			}
			return false;
		}

		protected function get_user_by_socket($socket) {
			foreach ($this->users as $user) {
				if ($user->socket == $socket) {
					return $user;
				}
			}
		}

		protected function handshake($user, $buffer) {
			if(preg_match("/GET (.*) HTTP/", $buffer, $match)){
            	$root = $match[1];
			}
	        if(preg_match("/Host: (.*)\r\n/", $buffer, $match)){
	            $host = $match[1];
	        }
	        if(preg_match("/Origin: (.*)\r\n/", $buffer, $match)){
	            $origin = $match[1];
	        }
	        if(preg_match("/Sec-WebSocket-Key: (.*)\r\n/", $buffer, $match)){
	            $key = $match[1];
	        }
	        if(preg_match("/Sec-WebSocket-Protocol: (.*)\r\n/", $buffer, $match)){
	        	$protocol = explode(", ", $match[1]);
	            $protocol = $protocol[0];
	        }
	        $this->log("Generating Handshake for $user->user_ID");
	        $acceptKey = $key.'258EAFA5-E914-47DA-95CA-C5AB0DC85B11';
	        $handshakeToken = base64_encode(sha1($acceptKey, true));
            $handshakeResponse = "HTTP/1.1 101 Switching Protocols\r\n".
                   "Upgrade: websocket\r\n".
                   "Connection: Upgrade\r\n".
                   "Sec-WebSocket-Accept: $handshakeToken\r\n".
                   "Sec-WebSocket-Protocol: $protocol".
                   "\r\n\r\n";

            $write = socket_write($user->socket, $handshakeResponse);
            if($write !== false && $write > 0) {
            	$this->log("User $user->user_ID Established a Handshake Successfully");
            	$this->users[$user->user_ID]->handshake = true;
            	return true;
            }else {
            	$this->log("Error Establishing Handshake for $user->user_ID");
            }
		}

		protected function parse_headers($message) {
			$header = array('fin'     => $message[0] & chr(128),
					'rsv1'    => $message[0] & chr(64),
					'rsv2'    => $message[0] & chr(32),
					'rsv3'    => $message[0] & chr(16),
					'opcode'  => ord($message[0]) & 15,
					'hasmask' => $message[1] & chr(128),
					'length'  => 0,
					'mask'    => "");
			$header['length'] = (ord($message[1]) >= 128) ? ord($message[1]) - 128 : ord($message[1]);
			if ($header['length'] == 126) {
				if ($header['hasmask']) {
					$header['mask'] = $message[4] . $message[5] . $message[6] . $message[7];
				}
				$header['length'] = ord($message[2]) * 256 + ord($message[3]);
			} 
			elseif ($header['length'] == 127) {
				if ($header['hasmask']) {
					$header['mask'] = $message[10] . $message[11] . $message[12] . $message[13];
				}
				$header['length'] = ord($message[2]) * 65536 * 65536 * 65536 * 256 
				+ ord($message[3]) * 65536 * 65536 * 65536
				+ ord($message[4]) * 65536 * 65536 * 256
				+ ord($message[5]) * 65536 * 65536
				+ ord($message[6]) * 65536 * 256
				+ ord($message[7]) * 65536 
				+ ord($message[8]) * 256
				+ ord($message[9]);
			} 
			elseif ($header['hasmask']) {
				$header['mask'] = $message[2] . $message[3] . $message[4] . $message[5];
			}

			return $header;
		}

		public function log($msg) {
			$txt = "[".date("H:i:s")."] ".trim($msg)."\n";
			$myfile = file_put_contents('log.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
			echo $txt;
		}
	}