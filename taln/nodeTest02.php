<?php
require_once("NodeTestTool.php");
/*
 * TODO: test each function entering bad data...
 */
$word = "maison";


$relAr1 = NodeTestTool::getRelations();
$relAr2 = NodeTestTool::getRelations();

foreach($relAr1 AS $k1 => $v1){
    foreach($relAr2 AS $k2 => $v2){
        echo "Mot : " . $word . " - " . $v1 . " - " . $v2 . " - " . "<br />";
        echo "Nuage : " . NodeTestTool::getWordCloudDistance2($word, $k1, $k2) . "<br /><br />";
    }
    echo "<br />";
}


?>
