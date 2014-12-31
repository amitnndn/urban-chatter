<?php
	class Default_PostController extends Zend_Controller_Action {
		protected $_userid = 0;
		public function init(){
			//Default init function
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$data = $this->session_authenticate();
			$this->getResponse()
			->setHeader('Content-type','application/json');
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
		public function getPartAction(){
			$params = $this->_getAllParams();
			$id = $params['id'];
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("posts")
						 ->where("id = $id");
			$data = $db->query($select)->fetchAll();
			if(empty($data)){
				echo "No Data Present";
				return;
			}
			else{
				foreach($data as $a){
					print_r($a["id"]." ".$a["title"]);
				}
			}
		}
		public function getFullAction(){
			$params = $this->_getAllParams();
			$id = $params['id'];
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
			->from("posts")
			->where("id = $id");
			$data = $db->query($select)->fetchAll();
			if(empty($data)){
				$response = array(
					"message" => "No post present",
					"id" => $id
				);
				echo json_encode($response);
				return;
			}
			else{
				foreach($data as $a){
					echo json_encode($a);
				}
			}
		}
		public function getByauthorAction(){
			$params = $this->_getAllParams();
			$user_id = $params['user_id'];
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("posts")
						 ->where("user_id = $user_id");
			$data = $db->query($select)->fetchAll();
			if(empty($data)){
				echo "No Data Present";
				return;
			}
			else{
				foreach($data as $a){
					print_r($a);
					//Generate json response based on author
				}
			}
		}
		public function getBytypeAction(){
			$params = $this->_getAllParams();
			$tag = $params["tag"];
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("tags")
						 ->where("tag = $tag");
			$data = $db->query($select)->fetchAll();
			if(empty($data)){
				echo "No Post Present by this Tag";
				return;
			}
			else{
				foreach($data as $a){
					$post_id = $a["post_id"];
					$select = $db->select()
								 ->from("posts")
								 ->where("id = $post_id");
					$data1 = $db->query($select)->fetchAll();
					if(empty($data1)){
						continue;
					}
					else{
						foreach($data1 as $a1){
							print_r($a1);
							//Generate JSON response based on tag
						}
					}
				}
			}
		}
		public function createAction(){
			$data = $this->session_authenticate();
			$user_id = $data["userid"];
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$db = Zend_Db_Table::getDefaultAdapter();
			$user_id = 16;
			$title = $input->title;
			$content = $input->body;
			$tags = $input->tags;
			$data = array(
				"user_id" => $user_id,
				"title" => "$title",
				"content" => urldecode($content),
				"post_type" => 1237,
				"likes" => 0
			);
			try{
				$db->insert("posts",$data);
			}
			catch(Exception $exception){
				print_r($exception);return;
				switch(get_class($exception)){
					case 'Zend_Argument_Exception': $message = 'Argument Error.'; break;
					case 'Zend_Db_Statment_Exception': $message = 'Database Error.'; break;
					case 'default': $message = "Unknown Error."; break;
				}	
			}
			$last_insert_id = $db->lastInsertId();
			foreach($tags as $a){
				$data = array(
					"post_id" => $last_insert_id,
					"tag" => $a
				);
				try{
					$db->insert("tags",$data);
				}
				catch(Exception $exception){
					switch(get_class($exception)){
						case 'Zend_Argument_Exception': $message = 'Argument Error.'; break;
						case 'Zend_Db_Statment_Exception': $message = 'Database Error.'; break;
						case 'default': $message = "Unknown Error."; break;
					}
				}
			}
			$response = array(
					"message" => true,
					"post_id" => $last_insert_id,
					"user_id" => $user_id
			);
			echo json_encode($response);
		}
		public function deleteAction(){
			$params = $this->_getAllParams();
			$post_id = $params["id"];
			$db = Zend_Db_Table::getDefaultAdapter();
			try{
				$where = $db->quoteInto("id = ?", $post_id);
				$db->delete("posts",$where);
			}
			catch(Exception $exception){
				switch(get_class($exception)){
					case 'Zend_Argument_Exception': $message = 'Argument Error.'; break;
					case 'Zend_Db_Statment_Exception': $message = 'Database Error.'; break;
					case 'default': $message = "Unknown Error."; break;
				}
			}
			echo "Delete Successful";
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