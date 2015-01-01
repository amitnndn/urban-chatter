<?php
	class Default_ViewPostController extends Zend_Controller_Action {
		public function init(){
			//Default init function
		}
		public function indexAction(){
			$params = $this->_getAllParams();
			$id = $params['id'];
			$a = $this->session_authenticate();
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
					$this->view->username = $username;
					$this->view->title = $a['title'];
					$this->view->content = $a['content'];
					$this->view->tags = $tag_html;
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