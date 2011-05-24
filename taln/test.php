<?php
require_once("db.php");
require_once("NodeTools.php");
?>

<html>
<head></head>
<body> 
<?php
$db = getDB();
//var_dump($db->querySingle('SELECT * FROM user'));
//echo "<br /> ------------------<br />";
//print_r($db->querySingle('SELECT * FROM user', true));

/*
$res = $db->query('SELECT * FROM type_relation');
echo "<br /><br />";
	while ($x = $res->fetchArray()){
		echo $x['name'];
                echo "<br />";
	}
*/

//echo "<br /><br />";
//echo "**************";
$nt = new NodeTools();
//$wordEID = $nt->fetchRandomCentralEID();

//$nt->generateRandomGame();

//echo $nt->getPOS(35798);
//$nt->getWordCloud(35798);
//echo $nt->toString();

//echo "<br /><br />";
//echo "**************";

echo "<br />Part of Speech Experiment No. 1<br />";
$POS = $nt->getPOS(35798);
foreach($POS AS $k => $v){
    echo "POS value: ".$v."<br />";
}

echo "<br />Word Cloud Experiment No. 1<br />";
$cloudWords = $nt->getWordCloud(35798);
foreach($cloudWords AS $k => $v){
    foreach($cloudWords[$k] AS $k2 => $v2){
        echo "Relation No. ".$k.": ".$cloudWords[$k][$k2]."<br />";
    }
}

echo "<br />Experiment No. 2<br />";
$nt2 = new NodeTools();
$ndObject = $nt2->generateGame(35798);
echo $ndObject->toString();

?>
</body>
</html>