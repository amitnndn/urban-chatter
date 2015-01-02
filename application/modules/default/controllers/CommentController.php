<?php
	class Default_CommentController extends Zend_Controller_Action {
		public function init(){
			//Default init function
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
		public function getAction(){
			
		}
		public function createAction(){
			$session = $this->session_authenticate();
			$user_id = $session["userid"];
			$user_name = $session['username'];
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$db = Zend_Db_Table::getDefaultAdapter();
			$data = array(
				"user_id" => $user_id,
				"post_id" => $input->post_id,
				"title" => "Null",
				"content" => $input->content
			);
			try{
				$db->insert("comments",$data);
			}
			catch(Exception $exception){
				print_r($exception);
			}
			$last_insert_id = $db->lastInsertId();
			$response = array(
				"id" => $last_insert_id,
				"status" => 1,
				"message" => "Successfully Inserted",
				"html_content" => "<div class=\"comment\"><a class=\"delete_comment pull-right\" id='$last_insert_id'><i class=\"fa fa-close\"></i></a><div class=\"author\"><a id='$user_id'>$user_name</a></div><div class=\"comment_text\">$input->content</div></div>"
			);
			echo json_encode($response);			
		}
		public function deleteAction(){
			$session = $this->session_authenticate();
			$user_id = $session['userid'];
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$id = $input->id;
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("comments")
						 ->where("id = ".$id);
			$data = $db->query($select)->fetchAll();
			foreach($data as $a){
				$author_id = $a['user_id'];
			}
			if($user_id != $author_id){
				$response = array(
					"status" => 0,
					"message" => "User is not allowed to delete other's comments"
				);
				echo json_encode($response);
				return;
			}
			$where = array(
				"id = ?" => $id
			);
			try{
				$db->delete("comments",$where);
			}
			catch(Exception $exception){
				$response = array(
					"status" => 0,
					"message" => "Some error occured, please try after some time"
				);
				echo json_encode($response);
				return;
			}
			$response = array(
				"status" => 1,
				"message" => "Comment successfully deleted."
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