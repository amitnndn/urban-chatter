<?php
	class Default_MyProfileController extends Zend_Controller_Action {
		public function init(){
			//Default init function
			$session = $this->session_authenticate();
			if($session['status'] == 0){
				$response = array(
					"status" => 0,
					"message" => "User not logged in."
				);
				echo json_encode($response);
				exit;
			}
		}
		public function indexAction(){
			$session = $this->session_authenticate();
			$user_id = $session['userid'];
			$db = Zend_Db_Table::getDefaultAdapter();
			if(!($session['userid'] == $user_id)){
				$response = array(
					"status" => 0,
					"message" => "Permisssion Denied"
				);
				$this->view->response = $response;
				return;
			}
			else{
				$response = array(
					"status" => 1
				);
				$this->view->response = $response;
				$select = $db->select()
							 ->from("users")
							 ->where("id = $user_id");
				$data = $db->query($select)->fetchAll();
				foreach($data as $a){
					$fname = $a['fname'];
					$lname = $a['lname'];
					$email = $a['email'];
				}
				$this->view->fname = $fname;
				$this->view->lname = $lname;
				$this->view->email = $email;
				$select = $db->select()
							 ->from("posts")
							 ->where("user_id = $user_id");
				$data1 = $db->query($select)->fetchAll();
				$posts = array();
				foreach($data1 as $b){
					$post = array(
						"id" => $b['id'],
						"title" => $b['title'],
						"content" => $b['content'],
						"likes" => $b['likes'],
						"dislikes" => $b['dislikes']
					);
					array_push($posts,$post);
				}
				$this->view->posts = $posts;
			}
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