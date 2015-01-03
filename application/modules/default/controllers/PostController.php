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
		public function getHomepageAction(){
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("users")
						 ->order(array('id DESC'))
						 ->limit(2);
			$data = $db->query($select)->fetchAll();
			$posts = array(
					"status" => 1,
					"featured" => array(),
					"new" => array(),
					"recent" => array()
			);
			$recent_new_user = array();
			$posts = array();
			$temp_post = array();
			$featured_post = array();
			foreach($data as $a){
				$author_name = $a['fname']." ".$a['lname'];
				$author_id = $a['id'];
				$select = $db->select()
							 ->from("posts")
							 ->where("user_id = ".$a['id']);
				try{
					$data1 = $db->query($select)->fetchAll();
				}
				catch(Exception $exception){
					echo $exception;					
				}
				if(empty($data1)){
					continue;
				}
				foreach($data1 as $b){
					array_push($recent_new_user,$b['id']);
					$title = $b['title'];
					$description = $b['content'];
					$post_id = $b['id'];
					$likes = $b['likes'];
					$unlikes = $b['dislikes'];
				}
				$post = array(
					"title" => $title,
					"description" => $description,
					"url" => $post_id,
					"author_id" => $author_id,
					"likes" => $likes,
					"unlikes" => $unlikes
				);
				if($post_id == 29){
					array_push($featured_post,$post);
				}
				else{
					array_push($temp_post,$post);
				}
			}
			$posts["new"] = $temp_post;
			$posts["featured"] = $featured_post;
			$select = $db->select()
						 ->from("posts")
						 ->order(array('id DESC'))
						 ->limit(10);
			$data = $db->query($select)->fetchAll();
			$recent_id = array();
			$temp_post = array();
			foreach($data as $a){
				if(in_array($a['id'],$recent_new_user)){
					continue;
				}
				$title = $a['title'];
				if($a['user_id'] == null){
					continue;
				}
				$select = $db->select()
							 ->from("users")
							 ->where("id = ".$a['user_id']);
				try{
					$data1 = $db->query($select)->fetchAll();
				}
				catch(Exception $exception){
					echo $select;
					echo $exception;
				}
				foreach($data1 as $b){
					$author_name = $b['fname']." ".$b['lname'];
				}
				$description = $a['content'];
				$post_id = $a['id'];
				$likes = $a['likes'];
				$unlikes = $a['dislikes'];
				$post = array(
					"title" => $title,
					"author" => $author_name,
					"author_id" => $a['user_id'],
					"description" => $description,
					"post_id" => $post_id,
					"likes" => $likes,
					"unlikes" => $unlikes
				);
				array_push($temp_post,$post);
			}
			$posts["recent"] = $temp_post;
			$posts["status"] = 1;
			echo json_encode($posts);
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
			$session = $this->session_authenticate();
			$username = $session["username"];
			$user_id = $session["userid"];
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
			$tag_html = "";
			$i=0;
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
				$tag_id = $db->lastInsertId();
				if($i == 0)
					$tag_html .= "<a class='tag_name' id='$tag_id'>$a</a>";
				else{
					$tag_html .=", <a class='tag_name' id='$tag_id'>$a</a>";
				}
				$i++;
			}
			$session = $this->session_authenticate();
			$user_name = $session["username"];
			$response = array(
					"status" => true,
					"message" => "Blog has been successfully created",
					"url" => "/view-post/?id=$last_insert_id",
					"post_id" => $last_insert_id,
					"user_id" => $user_id
					/* "html_content" => "<div id='post_create'><h1 class='post_title'>$title</h1><a class='username' href='>".$user_name."</a><div class='blog_post'>".urldecode($content)."</div><div class='tags'>$$tag_html</div></div>",
					"tag_html" => "<div class='tags'>$tag_html</div>" */
			);
			echo json_encode($response);
		}
		public function deleteAction(){
			$session = $this->session_authenticate();
			$user_id = $session['userid'];
			$params = $this->_getAllParams();
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$post_id = $input->id;
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("posts")
						 ->where("id = $post_id");
			$data = $db->query($select)->fetchAll();
			foreach($data as $a){
				$author_id = $a['user_id'];
			}
			if($user_id != $author_id){
				$response = array(
					"status" => 0,
					"message" => "Cannot delete, User is not the author"
				);
				echo json_encode($response);
				return;
			}
			try{
				$where = array(
					"id = ?" => $post_id
				);
				$db->delete("posts",$where);
			}
			catch(Exception $exception){
				print_r($exception);
				switch(get_class($exception)){
					case 'Zend_Argument_Exception': $message = 'Argument Error.'; break;
					case 'Zend_Db_Statment_Exception': $message = 'Database Error.'; break;
					case 'default': $message = "Unknown Error."; break;
				}
				$response = array(
					"status" => 0,
					"message" => $exception
				);
				echo json_encode($response);
				return;
			}
			$response = array(
				"status" => 1,
				"message" => "Post Successfully Deleted"
			);
			echo json_encode($response);
		}
		public function updateAction(){
			$session = $this->session_authenticate();
			$userid = $session['userid'];
			$request = $this->getRequest();
			$rawBody = $request->getRawBody();
			$input = json_decode($rawBody);
			$db = Zend_Db_Table::getDefaultAdapter();
			$title = $input->title;
			$content = urldecode($input->body);
			$post_id = $input->post_id;
			$tags = $input->tags;
			$data = array(
				"content" => $content,
				"title" =>$title
			);
			$where = array(
				"id = ?" => $post_id
			);
			try{
				$db->update("posts",$data,$where);
			}	
			catch(Exception $exception){
				$response = array(
					"status" => 0,
					"message" => "Some error occured in Updating. Please try after some time."
				);
				echo json_encode($data);
				return;
			}
			$where = array(
				"post_id = ?" => $post_id
			);
			try{
				$db->delete("tags",$where);
			}
			catch(Exception $exception){
				$response = array(
					"status" => 0,
					"message" => "Some error occured in Deleting Tags. Please try after some time"
				);
				echo json_encode($response);
				return;
			}
			foreach($tags as $a){
				$data = array(
					"post_id" => $post_id,
					"tag" => $a
				);
				try{
					$db->insert("tags",$data);
				}
				catch(Exception $exception){
					$response = array(
						"status" => 0,
						"message" => "Some error occured in Inserting Tags. Please try after some time"
					);
					echo json_encode($response);
					return;
				}
			}
			$response = array(
				"status" => 1,
				"message" => "Post Successfully Updated",
				"url" => "/view-post/?id=$post_id"
			);
			echo json_encode($response);
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