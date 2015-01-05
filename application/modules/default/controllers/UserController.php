<?php
	class Default_UserController extends Zend_Controller_Action {
		public function init(){
			//Default init function
		    $this->_helper->layout->disableLayout();
		    $this->_helper->viewRenderer->setNoRender(TRUE);
		    $this->getResponse()
		    ->setHeader('Content-type','application/json');
		   
		}
		public function indexAction(){
			
		}
		public function updateAction(){
			$session = $this->session_authenticate();
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			if($session['status'] == 0){
				$reponse = array(
					"status" => 0,
					"message" => "User must be logged in"
				);
				echo json_response($response);
				return;
			}
			$user_id = $session['userid'];
			$db = Zend_Db_Table::getDefaultAdapter();
			$fname = $input->first_name;
			$lname = $input->last_name;
			$email = $input->email;
			$input = array(
				"fname" => $fname,
				"lname" => $lname,
				"email" => $email
			);
			$where = array(
				"id = ?" => $user_id
			);
			try{
				$db->update("users",$input,$where);
			}
			catch(Exception $exception){
				$response = array(
					"status" => 0,
					"message" => "Oops! Something went wrong"
				);
				echo json_encode($response);
				return;
			}
			$response = array(
				"status" => 1,
				"message" => "Updated"
			);
			echo json_encode($response);
		}
		public function getAction(){
		    $params = $this->_getAllParams();
		    $id = $params['id'];
		    $db = Zend_Db_Table::getDefaultAdapter();
		    $select = $db->select()
		    			 ->from("users")
		    			 ->where("id = $id");
		    $data = $db->query($select)->fetchAll();
		    if(empty($data)){
		    	echo json_encode($data);
		    	return;
		    }
		    else{
			    foreach($data as $a){
			    	print_r($a["id"] . $a["type"]);
			    }
		    }
		}
		public function createAction(){
			$bootstrap = $this->getInvokeArg('bootstrap');
			$config = $bootstrap->getOptions();
			$params = $this->_getAllParams();
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$d = $this->session_authenticate();
			if($d["status"] == 1){
				echo "User already logged in ".$d['username'];
				return;
			}
			$data = json_decode($rawBody);
			$fname = $data->first_name;
			$lname = $data->last_name;
			$email = $data->email;
			$passwd = $data->password;
			$passwd_org = $passwd;
			if($data->fb_login == false)
				$fb_login = 0;
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("users")
						 ->where("email LIKE \"$email\"");
			$data = $db->query($select)->fetchAll();
			if(!empty($data)){
				$response = array(
					"status" => 0,
					"message" => "User Already Exists",
					"html_content" => $config['loggedin']['false']
					);
				echo json_encode($response);
				return;
			}
			if($fb_login == 0){
				$passwd = md5($passwd);
			}
			$data = array (
					"title" => "null",
					"fname" => "$fname",
					"lname" => "$lname",
					"email" => "$email",
					"passwd" => "$passwd",
					"sex" => 'no',
					"fb_login" => $fb_login,
					"image" => 'NULL',
					"desc" => 'NULL'
			);
			try {
				$db->insert("users",$data);
			}
			catch(Exception $exception){
				print_r($exception);
				switch(get_class($exception)){
					case 'Zend_Argument_Exception': $message = 'Argument Error.'; break;
					case 'Zend_Db_Statment_Exception': $message = 'Database Error.'; break;
					case 'default': $message = "Unknown Error."; break;
				}
				$response = array(
					"status" => "0",
					"message" => "There was a backend problem. Please try after some time.",
					"html_content" => $config['loggedin']['false']
				);
				echo json_encode($response);
				return;
			}
			$last_insert_id = $db->lastInsertId();
			$q = new ZendJobQueue();
			$q->createHttpJob("http://".$_SERVER['HTTP_HOST']."/mail/welcome-mail/?id=$last_insert_id");
			$login = $this->_forward("user-login","login",array(
				"username" => $email,
				"passwd" => $passwd_org,
				"fb_login" => $fb_login
			));
			$response = array(
				"status" => 1,
				"message" => "Successful",
				"user_id" => $last_insert_id,
				"user_name" => $fname." ".$lname,
				"html_content" => $config['loggedin']['true']
			);
			echo json_encode($response);
		}
		public function deleteAction(){
			
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