<?php
require_once("NodeTools.php");


$node = NodeTools::getRandomCenterEID();
echo $node;
echo "<br />";




?>
<html>
<form name="input" action="whiteboard.php" method="get">
Mot central : <input type="text" name="word" />
<input type="submit" value="Submit" />
</form>

<?php

if(isset($_GET['word'])){
    $word = $_GET['word'];
    unset($_GET['word']);
    $wordInDB = NodeTools::isWordInDB($word);
    if(!$wordInDB) echo "<p>ERREUR : le mot '$word' n'est pas dans la base de données !</p>";
    else echo "<p>Le mot '$word' est bien dans la base de données !</p>";
    echo "Central word: " . $_GET['word']."<br />";
    //echo "Parts of speech: "
}

$myEID = 4743;
$myWord = "chat";
$word = NodeTools::getWordFromEID($myEID);
echo $word . "<br />";
$eid = NodeTools::getEIDFromWord($myWord);
echo $eid. "<br />";
$pos = NodeTools::getPOSsFromEID($myEID);
echo $pos . "<br />";
$cloud = NodeTools::getCloudFromEID($myEID, 0);
echo $cloud . "<br />";
$allClouds = NodeTools::getAllCloudsFromEID($myEID);
echo $allClouds . "<br />";
$allClouds2 = NodeTools::getAllCloudsFromWord($myWord);
echo $allClouds2 . "<br />";

?>