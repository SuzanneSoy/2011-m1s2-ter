<!DOCTYPE html>
<html>
	<head>
		<title>Tests unitaires simplistes pour PtiClic</title>
		<style>
table {
	border-collapse: collapse;
}
table td {
	border: thin solid black;
	padding: 0.5em;
}
.fail {
	color: red;
}
.pass {
	color: green;
}
		</style>
	</head>
	<body>
		<h1>Résultat des tests :</h1>
		<table>
			<thead>
				<tr><th>État</th><th>Fonction</th><th>Résultat</th></tr>
			</thead>
			<tbody>
<?php
require_once("db.php");
require_once("ressources/sql.inc");

function test($fname, $expected) {
	$args = func_get_args();
	$expected = $args[count($args)-1];
	array_pop($args);
	array_shift($args);
	$result = call_user_func_array($fname, $args);

	$pass = "fail";
	if (is_callable($expected) && $expected($result)) $pass = "pass";
	elseif ($result == $expected) $pass = "pass";
	
	echo '<tr>';
	echo '<td class="'.$pass.'">'.$pass.'</td>';
	echo '<td>' . $fname . '</td>';
	echo '<td>' . var_export($result, true) . '</td>';
	echo '</tr>';
}

test("sqlGetPasswd", "foo", md5("bar"));
test("sqlGetRandomCenterNode", function($x){return is_int($x);});
test("sqlGetRandomCloudNode", function($x){return is_int($x);});
test("sqlGetForwardOne", sqlGetRandomCenterNode(), function($x){return is_int($x);});
test("sqlGetBackwardOne", sqlGetRandomCenterNode(), function($x){return is_int($x);});

?>
			</tbody>
		</table>
		<p>End of tests.</p>
	</body>
</html>
