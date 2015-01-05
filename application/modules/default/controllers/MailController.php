<?php
	class Default_MailController extends Zend_Controller_Action {
		public function init(){
			//Default init function
			$this->_helper->layout->disableLayout();
			$this->_helper->viewRenderer->setNoRender(TRUE);
			$this->getResponse()
			->setHeader('Content-type','application/json');
		}
		public function welcomeMailAction(){
			$params = $this->_getAllParams();/* 
			$request = $this->getResponse();
			$rawBody =$request->getRawBody();
			$input = json_decode($rawBody); */
			$user_id = $params['id'];//$input->id
			$db = Zend_Db_Table::getDefaultAdapter();
			$select = $db->select()
						 ->from("users")
						 ->where("id = $user_id");
			$data = $db->query($select)->fetchAll();
			foreach($data as $a){
				$email = $a['email'];
				$username = $a['fname']." ".$a['lname'];
			}
			$path = array("http://".$_SERVER['HTTP_HOST']."/images/mailer_hdr.jpg","http://".$_SERVER['HTTP_HOST']."/images/button.jpg");
			try{
				$message = "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN'
							'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
							<html xmlns='http://www.w3.org/1999/xhtml'>
							<head>
							<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
							<title>Urban Chatter</title>
							</head>
							
							<body>
							    <table width='652' border='0' cellspacing='0' cellpadding='0'
							style='border: 1px solid #646464;' align='center'>
							        <tr>
							            <td><img src='".$path[0]."' width='650' height='100'
							style='display:block;' /></td>
							        </tr>
							        <tr>
							            <td style='padding:20px; font-family:Arial, Helvetica,
							sans-serif; font-size:12px; color:#646464; line-height:18px;
							text-align:justify;'>
							                <h1 style='font-size:24px; font-family:Arial,
							Helvetica, sans-serif; font-weight:normal; color:#0096d6;
							line-height:30px;'>Welcome to Urban Chatter, $username</h1>
							                <p>
							                   <span style='color:#FF692C; font-family:Arial,
							Helvetica, sans-serif; font-weight:bold;'>About Us:</span><br />
							Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean sit
							amet scelerisque nisi, ut vehicula urna. Pellentesque quis maximus
							felis, aliquam dignissim tellus. Maecenas sit amet aliquet enim.
							Nullam tincidunt est velit, non venenatis quam hendrerit nec. Vivamus
							ut mauris pretium, sodales odio et, vehicula ipsum. Aliquam posuere,
							lorem sit amet porta tincidunt, sapien elit finibus orci, nec
							dignissim magna nibh eget nisi. Mauris vel tincidunt nibh, ut mattis
							sem. Donec erat massa, ultricies finibus blandit a, malesuada non
							eros. In quis nisi id lacus feugiat consequat sit amet at orci.
							Vivamus tincidunt efficitur blandit. Morbi eu mi semper, interdum
							sapien a, malesuada sapien. Fusce rhoncus justo at diam elementum
							suscipit. Etiam euismod sit amet nulla ac venenatis. Nunc non
							vestibulum ante, ac laoreet elit. In justo orci, convallis eget massa
							et, facilisis pellentesque lorem. </p>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean sit
							amet scelerisque nisi, ut vehicula urna. Pellentesque quis maximus
							felis, aliquam dignissim tellus. Maecenas sit amet aliquet enim.
							Nullam tincidunt est velit, non venenatis quam hendrerit nec. Vivamus
							ut mauris pretium, sodales odio et, vehicula ipsum. Aliquam posuere,
							lorem sit amet porta tincidunt, sapien elit finibus orci, nec
							dignissim magna nibh eget nisi. Mauris vel tincidunt nibh, ut mattis
							sem. Donec erat massa, ultricies finibus blandit a, malesuada non
							eros. In quis nisi id lacus feugiat consequat sit amet at orci.
							Vivamus tincidunt efficitur blandit. Morbi eu mi semper, interdum
							sapien a, malesuada sapien. Fusce rhoncus justo at diam elementum
							suscipit. Etiam euismod sit amet nulla ac venenatis. Nunc non
							vestibulum ante, ac laoreet elit. In justo orci, convallis eget massa
							et, facilisis pellentesque lorem. </p>
							<table width='300' border='0' cellspacing='0' cellpadding='5' align='center'>
							  <tr>
							    <td width='62' style='font-family:Arial, Helvetica, sans-serif;
							font-size:12px; color:#646464; line-height:18px;'>Username</td>
							    <td width='218' style='font-family:Arial, Helvetica, sans-serif;
							font-size:12px; color:#646464;
							line-height:18px;'><strong>$email</strong></td>
							  </tr>
							</table>
							<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Aenean sit
							amet scelerisque nisi, ut vehicula urna. Pellentesque quis maximus
							felis, aliquam dignissim tellus. Maecenas sit amet aliquet enim.
							Nullam tincidunt est velit, non venenatis quam hendrerit nec. Vivamus
							ut mauris pretium, sodales odio et, vehicula ipsum. Aliquam posuere,
							lorem sit amet porta tincidunt, sapien elit finibus orci, nec
							dignissim magna nibh eget nisi. Mauris vel tincidunt nibh, ut mattis
							sem. Donec erat massa, ultricies finibus blandit a, malesuada non
							eros. In quis nisi id lacus feugiat consequat sit amet at orci.
							Vivamus tincidunt efficitur blandit. Morbi eu mi semper, interdum
							sapien a, malesuada sapien. Fusce rhoncus justo at diam elementum
							suscipit. Etiam euismod sit amet nulla ac venenatis. Nunc non
							vestibulum ante, ac laoreet elit. In justo orci, convallis eget massa
							et, facilisis pellentesque lorem. </p>
							<p><a href='' target='_blank'><img src='".$path[1]."' width='200'
							height='42' border='0' /></a></p>
							            </td>
							        </tr>
							    </table>
							</body>
							</html>";
				$mail = new Zend_Mail();
				$mail->addTo($email,$username)
					 ->setFrom("info@urbanchatter.in", "Urban Chatter")
					 ->setSubject("Welcome to urban chatter")
					 ->setBodyHtml($message)
					 ->send();
			}
			catch(Exception $exception){
				$response = array(
					"status" => 0,
					"message" => "Some error occured"
				);
				print_r($exception);
				echo json_encode($response);
				return;
			}
			$response = array(
				"status" => 1,
				"message" => "sent!"
			);
			echo json_encode($response);
		}
	}