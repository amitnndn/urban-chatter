<?php
	class Default_CreatePostController extends Zend_Controller_Action {
		public function init(){
			//Default init function
			$data = $this->session_authenticate();
			$_userid = $data["userid"];
			if($data["status"] == 0){
				$response = array(
						"message" => "User is not logged in.",
						"userid" => $_userid
				);
				echo json_encode($response);
				exit;
			}
		}
		public function indexAction(){
			
		}
		protected function session_authenticate(){
			$session = new Zend_Session_Namespace('login');
			$username = $session->username;
			$userid = $session->userid;
			if(empty($session->username)){
				$data = array(
						"status" => 0,
						"userid" => 0
				);
				return $data;
			}
			else{
				$data = array(
						"status" => 1,
						"username" => $username,
						"userid" => $userid
				);
				return $data;
			}
		}
	}