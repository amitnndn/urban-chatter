<?php
	if($_SERVER['REQUEST_METHOD'] == 'GET')
		$key = $_GET['key'];
	else
		$key = "";
	if($key == "aadiamitsidda")
		phpinfo();
	else
		echo "Access Denied";
?>
