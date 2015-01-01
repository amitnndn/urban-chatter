<?php
	class Default_ViewPostController extends Zend_Controller_Action {
		public function init(){
			//Default init function
		}
		public function indexAction(){
			$params = $this->_getAllParams();
			$id = $params['id'];
			$session = $this->session_authenticate();
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
				$this->view->json =  json_encode($response);
				return;
			}
			else{
				foreach($data as $a){
					$this->view->json = $a["id"];
					$select = $db->select()
								 ->from("users")
								 ->where("id = ".$a['user_id']);
					$this->view->select = $select;
					$data1 = $db->query($select)->fetchAll();
					foreach($data1 as $b){
						$username = $b['fname']." ".$b['lname'];
					}
					$select = $db->select()
								 ->from("tags")
								 ->where("post_id = ".$a['id']);
					$data1 = $db->query($select)->fetchAll();
					$i = 0;
					$tag_html = "";
					foreach($data1 as $b){
						$tag_id = $b['id'];
						$tag_name = $b['tag'];
						if($i == 0)
							$tag_html .= "<a class='tag_name' id='$tag_id'>$tag_name</a>";
						else{
							$tag_html .=", <a class='tag_name' id='$tag_id'>$tag_name</a>";
						}
						$i++;
					}
					$this->view->author = $username;
					$this->view->title = $a['title'];
					$this->view->content = $a['content'];
					$this->view->tags = $tag_html;
					$this->view->likes = $a['likes'];
					$this->view->dislikes = $a['dislikes'];
					if($session['status'] == 1){
						$select = $db->select()
									 ->from("likes")
									 ->where("post_id = ".$a['id']." AND user_id = ".$session['userid']);
						$this->view->loggedin = 1;
						$data2 = $db->query($select)->fetchAll();
						if(empty($data2)){
							$this->view->liked = 0;
						}
						else{
							$this->view->liked = 1;
						}
						$select = $db->select()
									 ->from("post_unlikes")
									 ->where("post_id = ".$a['id']." AND user_id = ".$session['userid']);
						$data2 = $db->query($select)->fetchAll();
						if(empty($data2)){
							$this->view->disliked = 0;
						}
						else{
							$this->view->disliked = 1;
						}
					}
					else{
						$this->view->loggedin = 0;
					}
					
				}
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