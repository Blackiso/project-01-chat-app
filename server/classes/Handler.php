<?php 
	
	/**
	 * Class to handle WS requests for chat application
	 */
	class Handler {

		protected $users;
		protected $m_user;
		protected $room_ID;
		protected $message;

		private $db;

		function __construct($users, $socket, $buffer) {
			$this->db = new DB();
			$this->users = $users;
			$this->m_user = $this->get_user_by_socket($socket);
			$this->message = json_decode($this->unmask($buffer));
		}

		public function init() {
			if ($this->message == null) {
				$this->error("Not valid JSON");
			}else {
				if (isset($this->message->type) AND method_exists($this, $this->message->type)) {
					$type = $this->message->type;
					$this->$type();
				}else {
					$this->error("Error Request Type");
				}
			}
		}

		protected function ping() {
			$now = date("Y-m-d H:i:s");
			$user_ID = $this->m_user->user_ID;
			$room_ID = $this->m_user->room_ID;
        	$update_qr = $this->db->query("UPDATE users SET last_seen = '$now' WHERE user_ID = '$user_ID' AND room_ID = '$room_ID'");
		}

		protected function msg() {
			$msg_ID = $this->message->body->msg_ID;
			$room_ID = $this->m_user->room_ID;
			$result = $this->db->query("SELECT id, user_ID, username, message, msg_time FROM messages 
						WHERE id = '$msg_ID' AND room_ID = '$room_ID'");
			$this->send_to_all("new_msg", $result);
		}

		public function user_joined() {
			$user = $this->m_user;
			$this->log("User $user->user_ID Joined Room $user->room_ID");
			// Notify other users
			$body = (object) [];
			$body->username = $user->username;
			$body->user_ID = $user->user_ID;
			$body->admin = $user->is_admin();
			$this->send_to_all("user_joined", $body);
		}

		public function user_left() {
			$user = $this->m_user;
			$this->db->query("DELETE FROM users WHERE user_ID = '$user->user_ID' AND room_ID = '$user->room_ID'");
			$this->log("User $user->user_ID Left Room $user->room_ID");
			// Notify other users
			$body = (object) [];
			$body->username = $user->username;
			$body->user_ID = $user->user_ID;
			$body->admin = $user->is_admin();
			$this->send_to_all("user_left", $body);
		}

		protected function get_user_by_socket($socket) {
			foreach ($this->users as $user) {
				if ($user->socket == $socket) {
					return $user;
				}
			}
		}

		protected function send_to_all($type, $body) {
			foreach ($this->users as $_user) {
				$m_room = trim($this->m_user->room_ID);
				$u_room = trim($_user->room_ID);
				if ($m_room == $u_room && $_user !== $this->m_user) {
					$_user->send($type, $body);
				}
			}
		}

		protected function unmask($buffer) {
		    $length = ord($buffer[1]) & 127;

		    if($length == 126) {
		        $masks = substr($buffer, 4, 4);
		        $data = substr($buffer, 8);
		    }
		    elseif($length == 127) {
		        $masks = substr($buffer, 10, 4);
		        $data = substr($buffer, 14);
		    }
		    else {
		        $masks = substr($buffer, 2, 4);
		        $data = substr($buffer, 6);
		    }

		    $text = '';
		    for ($i = 0; $i < strlen($data); ++$i) {
		        $text .= $data[$i] ^ $masks[$i%4];
		    }
		    return $text;
		}

		protected function error($msg) {
			$error = json_encode(array("error" => $msg));
			$response = chr(129) . chr(strlen($error)) . $error;
			socket_write($this->m_user->socket, $response);
		}

		public function log($msg) {
			$txt = "[".date("H:i:s")."] ".trim($msg)."\n";
			$myfile = file_put_contents('log.txt', $txt.PHP_EOL , FILE_APPEND | LOCK_EX);
			echo $txt;
		}
	}