<?php
	class Default_LoginController extends Zend_Controller_Action {
		public function init(){
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$this->getResponse()
			->setHeader('Content-type','application/json');
		}
		public function indexAction(){
			//Login index Controller
		}
		public function checkLoginAction(){
			$bootstrap = $this->getInvokeArg('bootstrap');
			$config = $bootstrap->getOptions();
			$data = $this->session_authenticate();
			if($data["status"] == 0){
				$response = array(
					"loggedin" => false,
					"html_content" => $config['loggedin']['false']
				);
				echo json_encode($response);
				return;
			}
			elseif($data["status"] == 1){
				$username = $data["username"];
				$response = array(
					"username" => $username,
					"loggedin" => true,
					"html_content" => $config['loggedin']['true']
				);
				echo json_encode($response);
			}
		}
		public function userLoginAction(){
			$bootstrap = $this->getInvokeArg('bootstrap');
			$config = $bootstrap->getOptions();
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$session = new Zend_Session_Namespace('login');
			$db = Zend_Db_Table::getDefaultAdapter();
			$userName = $input->username;
			$select = $db->select()
					   ->from("users")
					   ->where("email LIKE \"$userName\"");
			$data = $db->query($select)->fetchAll();
			if(empty($data)){
				$response = array(
					"status" => 0,
					"message" => "User does not exist.",
					"html_content" => $config['loggedin']['false']
				);
			}
			else{
				foreach($data as $a){
					if(!$a["fb_login"]){
						$passwd = md5($input->password);
						if($passwd == $a["passwd"]){
							$session->username = $a["fname"]." ".$a["lname"];
							$session->userid = $a["id"];
							$session->setExpirationSeconds(180000);
							$response = array(
								"status" => 1,
								"user_name" => $session->username,
								"html_content" => $config['loggedin']['true'],
								"user_id" => $session->userid
							);
						}
						else{
							$response = array(
								"status" => 0,
								"html_content" => $config['loggedin']['false'],
								"message" => "Invalid Username/Password"
							);
						}
					}
					else{
						$session->username = $a["fname"]." ".$a["lname"];
						$session->userid = $a["id"];
						$session->serExpirationSeconds(1800);
						$response = array(
							"status" => 1,
							"user_name" => $session->username,
							"html_content" => $config['loggedin']['true'],
							"user_id" => $session->userid
						);
					}
				}
			}
			echo json_encode($response);
		}
		public function changePasswordAction(){
			$session = $this->session_authenticate();
			$db = Zend_Db_Table::getDefaultAdapter();
			$user_id = $session['userid'];
			if($session['status'] == 0){	
				$response = array(
					"status" => 0,
					"message" => "User not logged in"
				);
				echo json_encode($response);
				return;
			}
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$old_password = md5($input->old_password);
			$new_password = md5($input->new_password);
			$select = $db->select()
					 	 ->from("users")
					 	 ->where("passwd LIKE '$old_password' AND id = $user_id");
			$data = $db->query($select)->fetchAll();
			if(empty($data)){
				$response = array(
					"status" => 0,
					"message" => "Invalid Password"
				);
				echo json_encode($response);
				return;
			}
			$data = array(
				"passwd" => $new_password
			);
			$where = array(
				"id = ?" => $user_id
			);
			try{
				$db->update("users",$data,$where);
			}
			catch(Exception $exception){
				$response = array(
					"status" => 0,
					"message" => "Something went wrong. Please try again"
				);
				echo json_encode($response);
				return;
			}
			$response = array(
				"status" => 1,
				"message" => "Password changed"
			);
			echo json_encode($response);
		}
		protected function session_authenticate(){
			$session = new Zend_Session_Namespace('login');
			$username = $session->username;
			if(empty($session->username)){
				$data = array(
					"status" => 0
				);
				return $data;
			}
			else{
				$data = array(
					"status" => 1,
					"username" => $username,
					"userid" => $session->userid
				);
				return $data;
			}
		}
	}