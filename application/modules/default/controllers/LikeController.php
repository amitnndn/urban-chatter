<?php
	class Default_LikeController extends Zend_Controller_Action {
		public function init(){
			//default init action
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$data = $this->session_authenticate();
			if($data["status"] == 0){
				echo "User Not Logged in.";
				return;
			}
		}
		public function addAction(){
			$params = $this->_getAllParams();
			$post_id = $params["post_id"];
			$user_id = $params["user_id"];
			$db = Zend_Db_Table::getDefaultAdapter();
			$data = array(
					"like" => new Zend_Db_Expr("like+1")
			);
			$where = array(
				"post_id" => $post_id
			);
			try{
				$db->update("posts",$data,$where);
			}
			catch(Exception $exception){
				switch(get_class($exception)){
					case 'Zend_Argument_Exception': $message = 'Argument Error.'; break;
					case 'Zend_Db_Statment_Exception': $message = 'Database Error.'; break;
					case 'default': $message = "Unknown Error."; break;
				}
			}
			$select = $db->select()
						 ->from("posts")
						 ->where("id = $post_id");
			$data = $db->query($select)->fetchAll();
			foreach($data as $a){
				echo "{\"likes\":\"".$a["likes"]."\"}";
			}
			$data = array(
				"post_id" => $post_id,
				"user_id" => $user_id
			);
			$db->insert("likes",$data);
		}
		public function removeAction(){
			$params = $this->_getAllParams();
			$post_id = $params["post_id"];
			$user_id = $params["user_id"];
						
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