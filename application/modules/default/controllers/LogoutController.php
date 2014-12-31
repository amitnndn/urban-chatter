<?php
	class Default_LogoutController extends Zend_Controller_Action {
		public function init(){
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$this->getResponse()
				 ->setHeader('Content-type','application/json');
			$data = $this->session_authenticate();
		}
		public function indexAction(){
			$bootstrap = $this->getInvokeArg('bootstrap');
			$config = $bootstrap->getOptions();
			$session = new Zend_Session_Namespace('login');
			$session->username = "";
			$session->userid = 0;
			$session->setExpirationSeconds(1);
			$response = array(
				"status" => 1,
				"html_content" => $config['loggedin']['false'] 
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
						"username" => $username
				);
				return $data;
			}
		}
	}