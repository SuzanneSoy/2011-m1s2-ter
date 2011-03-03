<?php
require_once("ressources/strings.inc");

function showmsg() {
	global $strings;

	if (isset($_GET['showmsg'])) {
		$msg = $_GET['showmsg'];
		$errmsg = false;
		
		if (array_key_exists($msg, $strings)) {
			$errmsg = preg_match('/^err_/', $msg);
			$okmsg = preg_match('/^ok_/', $msg);
			$msg = $strings[$msg];
		} else {
			$errmsg = true;
			$msg = $strings['err_index_invalid_msg'];
		}

		if($errmsg)
			echo '<p class="message warning">'.htmlspecialchars($msg).'</p>';
		else if($okmsg)
			echo '<p class="message success">'.htmlspecialchars($msg).'</p>';
		else
			echo '<p class="message other">'.htmlspecialchars($msg).'</p>';
	}
}

showmsg();

?>
