<?php

$location_list['download'] = "download.php";
$location_list['index'] = "index.php";
$location_list['contact'] = "contact.php";
$location_list['createGame'] = "createGame.php";
$location_list['login'] = "login.php";
$location_list['signup'] = "signup.php";
$location_list['createGame'] = 'createGame.php';

function return_to($location, $params = "") {
	global $location_list;

	if (array_key_exists($location,$location_list))
		header("location:".$location_list[$location].$params);
	else
		header("location:index.php?showmsg=err_index_redirect");
}

function get_location() {
	if(isset($_GET['return']) && preg_match('/^[a-zA-Z0-9_]*$/', $_GET['return']))
		return $_GET['return'];
	else
		return "index";
}

?>
