<?php 
	 /**
	  * The user class for WS server
	  */
	 class User {

	 	public $user_ID;
	 	public $room_ID;
	 	public $session_ID;
	 	public $socket;
	 	public $username;
	 	public $handshake = false;
	 	public $error = null;

	 	private $db;

	 	function __construct($socket, $user_ID, $room_ID) {
	 		$this->socket = $socket;
	 		$this->user_ID = $user_ID;
	 		$this->room_ID = $room_ID;
	 		$this->db = new DB();
	 		$this->create_user();
	 	}

	 	private function create_user() {
	 		$result = $this->db->query("SELECT user_ID, room_ID, username, session_ID FROM users WHERE user_ID = '$this->user_ID' AND room_ID = '$this->room_ID'");
	 		if (empty($result)) {
	 			$this->error = true;
	 		}else {
	 			$this->username = $result[0]['username'];
	 			$this->room_ID = $result[0]['room_ID'];
	 			$this->session_ID = $result[0]['session_ID'];
	 		}
	 	}

	 	public function send($type, $body) {
			$msg = (object) [];
			$msg->type = $type;
			$msg->body = $body;
			$msg = json_encode($this->utf8ize($msg));
			$response = $this->mask($msg);
			$write = socket_write($this->socket, $response);
			$error_txt = socket_strerror(socket_last_error());
			$this->log($error_txt);
		}

		public function close() {
			socket_close($this->socket);
		}

		public function log($msg) {
			$txt = "[".date("H:i:s")."] ".trim($msg)."\n";
			$myfile = file_put_contents('log.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
			echo $txt;
		}

		private function utf8ize($d) {
		    if (is_array($d)) {
		        foreach ($d as $k => $v) {
		            $d[$k] = $this->utf8ize($v);
		        }
		    }else if(is_object($d)) {
		        foreach ($d as $k => $v) {
		            $d->$k = $this->utf8ize($v);
		        }
		    }else {
		        return @utf8_encode($d);
		    }

		    return $d;
		}

		private function mask($msg) {
			$len = strlen($msg);
			if ($len <= 125) {
				$header = pack('C*', 129, $len);
			}else if (($len > 125) && ($len < 65536)) {
				$header = pack('C*', 129, 126, ($len >> 8) & 255, $len & 255);
			}else if ($len >= 65536) {
				$header = pack('C*', 129, 127, ($len >> 56) & 255, ($len >> 48) & 255, ($len >> 40) & 255, ($len >> 32) & 255, ($len >> 24) & 255, ($len >> 16) & 255, ($len >> 8) & 255, $len & 255);
			}
			return $header . $msg;
		}
	 }