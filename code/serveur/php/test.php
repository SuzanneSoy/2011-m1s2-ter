<!DOCTYPE html>
<html>
	<head>
		<title>PtiClic - Console de test</title>
		<meta charset="utf-8" />
	</head>
	<body>
		<h1>Console de test</h1>
<?php
require_once("db.php");
require_once("ressources/sql.inc");

function main_test() {
	$randomnode = sqlGetRandomCenterNode();
	$tests = array(
		"sqlGetPasswd" => array("user" => "foo"),
		"sqlGetRandomCenterNode" => array(),
		"sqlGetRandomCloudNode" => array(),
		"sqlGetForwardOne" => array("origin" => $randomnode),
		"sqlGetBackwardOne" => array("origin" => $randomnode),
		"sourceOneForwardR1R2" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sourceOneForwardAssociated" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sourceOneForwardAny" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sourceTwoForwardR1R2_R1R2" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sourceTwoForwardR1R2_Syn" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sourceTwoForwardSyn_R1R2" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sourceTwoForwardAny_Any" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sourceArrowheadAny_Any" => array("centerEid" => $randomnode, "r1" => 5, "r2" => 10),
		"sqlGetRawNodeName" => array("eid" => $randomnode),
	);

	if (isset($_GET["function"])) {
		$fn = $_GET["function"];
		$nbParams = intval($_GET["nbParams"]);
		$params = array();
		for ($i = 0; $i < $nbParams; $i++) {
			$params[$i] = $_GET["param".$i];
		}
		if (array_key_exists($fn, $tests) && $nbParams == count($tests["$fn"])) {
			$params_show = array();
			foreach ($params as $p) {
				$params_show[] = var_export($p, true);
			}
			echo "<p>".$fn."(".implode(", ", $params_show).") == ".var_export(call_user_func_array($fn, $params), true)."</p>";
		}
	}

	$t = 0;
	foreach ($tests as $fn => $params) {
		echo '	<form action="#" method="GET">';
		echo '		<input type="hidden" name="nbParams" value="'.count($params).'" />';
		echo '		<input type="hidden" name="nonce" value="'.microtime().'" />';
		$p = 0;
		foreach ($params as $name => $default) {
			echo '		<label for="test'.$t.'param'.$p.'">'.htmlspecialchars($name).' :</label>';
			echo '		<input type="text" id="test'.$t.'param'.$p.'" name="param'.$p.'" value="'.htmlspecialchars($default).'" />';
			$p++;
		}
		echo '		<input type="submit" name="function" value="'.$fn.'" />';
		echo '	</form>';
		$i++;
	}
}

main_test();
?>
	</body>
</html>
