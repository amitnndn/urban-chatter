<?php
	class Default_EditPostController extends Zend_Controller_Action {
		public function init(){
			//Default init funciton
			
		}
		public function indexAction(){
			$params = $this->_getAllParams();
			$session = $this->session_authenticate();
			$user_id = $session['userid'];
			$post_id = $params['id'];
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("posts")
						 ->where("id = $post_id");
			$data = $db->query($select)->fetchAll();
			if(empty($data)){
				$response = array(
					"status" => 0,
					"message" => "Page not present"
				);
				$this->view->response = $response;
			}
			else{
				foreach($data as $a){
					if($user_id != $a['user_id']){
						$response = array(
							"status" => 0,
							"message" => "Permission Denied"
						);
						$this->view->response = $response;
					}
					else{
						$this->view->content = $a['content'];
						$this->view->title = $a['title'];
						$select = $db->select()
									 ->from('tags')
									 ->where("post_id = $post_id");
						$data1 = $db->query($select)->fetchAll();
						$tags = array();
						foreach($data1 as $b){
							$tag = array(
								"content" => $b['tag']
							);
							array_push($tags,$tag);
						}
						$this->view->tags = $tags;
					}
				}
			}
		}
		public function getTagsAction(){
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$post_id = $input->post_id;
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("tags")
						 ->where("post_id = $post_id");
			$data = $db->query($select)->fetchAll();
			$tags = array();
			foreach($data as $a){
				$tag = $a['tag'];
				array_push($tags,$tag);
			}
			echo json_encode($tags);
			return;
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