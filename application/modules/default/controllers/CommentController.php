<?php
	class Default_CommentController extends Zend_Controller_Action {
		public function init(){
			//Default init function
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$data = $this->session_authenticate();
			if($data["status"] == 0){
				echo "User is not logged in!";
				return;
			}
		}
		public function getAction(){
			
		}
		public function createAction(){
			
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
						"username" => $username
				);
				return $data;
			}
		}
	}