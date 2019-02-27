<?php 
	
	/**
	 * Class to handle WS requests for chat application
	 */
	class Handler {

		protected $users;
		protected $m_user;
		protected $room_ID;
		protected $message;

		function __construct($users, $socket, $buffer) {
			$this->users = $users;
			$this->m_user = $this->get_user_by_socket($socket);
			$this->message = json_decode($this->unmask($buffer));
		}

		public function init() {
			if ($this->message == null) {
				$this->error("Not Valid JSON");
			}else {

			}
		}

		protected function get_user_by_socket($socket) {
			foreach ($this->users as $user) {
				if ($user->socket == $socket) {
					return $user;
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
	}