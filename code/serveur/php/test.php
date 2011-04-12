<?php
require_once("db.php");
require_once("ressources/sql.inc");

function test($fname) {
	$args = func_get_args();
	$expected = $args[count($args)-1];
	array_pop($args);
	array_shift($args);
	$result = call_user_func_array($fname, $args);
	if ($result == $expected)
		echo '<span style="color:green">Ok</span> ' . $fname . '<br/>';
	else
		echo '<span style="color:green">Failed</span> ' . $fname . '<br/>';
}

test("sqlGetPasswd", "foo", md5("bar"));
?>