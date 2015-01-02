<?php
	class Default_DislikeController extends Zend_Controller_Action {
		public function init(){
			//default init function
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$data = $this->session_authenticate();
			$this->getResponse()
			->setHeader('Content-type','application/json');
			if($data["status"] == 0){
				$response = array(
						"message" => "User Not Logged in."
				);
				echo json_encode($response);
				exit;
			}
		}
		public function indexAction(){
			$db = Zend_Db_Table::getDefaultAdapter();
			$params = $this->_getAllParams();
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$post_id = $input->post_id;
			$session = $this->session_authenticate();
			$user_id = $session['userid'];
			$select = $db->select()
						 ->from("likes")
						 ->where("user_id = $user_id AND post_id = $post_id");
			$data = $db->query($select)->fetchAll();
			if(!empty($data)){
				$response = array(
						"status" => 0,
						"message" => "User has already liked."
				);
				echo json_encode($response);
				return;
			}
			$select = $db->select()
						 ->from("post_unlikes")
						 ->where("user_id = $user_id AND post_id = $post_id");	
			$data = $db->query($select)->fetchAll();
			if(!empty($data)){
				foreach($data as $a){
					$where = $db->quoteInto("id = ?", $a['id']);
					$db->delete("post_unlikes",$where);
					$data = array(
							"dislikes" => new Zend_Db_Expr("dislikes- 1")
					);
					$where = array(
							"id = ?" => $post_id
					);
					$db->update("posts",$data,$where);
					$select = $db->select()
						->from("posts")
						->where("id = $post_id");
					$data = $db->query($select)->fetchAll();
					foreach($data as $a){
						$likes = $a['likes'];
					}
				}
				$response = array(
						"status" => 1,
						"message" => "Dislike removed",
						"unlikes" => $likes
				);
				echo json_encode($response);
				return;
			}
			$data = array(
					"dislikes" => new Zend_Db_Expr("dislikes+1")
			);
			$where = array(
					"id = ?" => $post_id
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
				$likes = $a['dislikes'];
			}
			$data = array(
					"post_id" => $post_id,
					"user_id" => $user_id
			);
			$db->insert("post_unlikes",$data);
			$response = array(
					"status" => 1,
					"message" => "Disliked",
					"unlikes" => $likes
			);
			echo json_encode($response);
		}
		protected function session_authenticate(){
			$session = new Zend_Session_Namespace('login');
			$username = $session->username;
			if(empty($session->username)){
				$data = array(
						"status" => 0,
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