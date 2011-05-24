<?php
require_once("NodeTestTool.php");

$word = "chemin";
$eid = 4455;
$relNo = 0;
echo '$word = ' . $word . "<br />";
echo '$eid = ' . $eid . ' ($word and $eid DO NOT correspond here)' . "<br />";
echo '$relNo = ' . $relNo . "<br />";
echo "<br />";

echo 'NodeTestTool::getPOSsFromEID($eid) : ';
echo NodeTestTool::getPOSsFromEID($eid);
echo "<br /><br />";

echo 'NodeTestTool::getPOSsFromWord($word) : ';
echo NodeTestTool::getPOSsFromWord($word);
echo "<br /><br />";

echo 'NodeTestTool::isEIDInDB($eid) : ';
echo NodeTestTool::isEIDInDB($eid);
echo "<br /><br />";

echo 'NodeTestTool::isWordInDB($word) : ';
echo NodeTestTool::isWordInDB($word);
echo "<br /><br />";

echo 'NodeTestTool::isCentralEIDinDB($eid) : ';
echo NodeTestTool::isCentralEIDinDB($eid);
echo "<br /><br />";

echo 'NodeTestTool::isCentralWordInDB($word) : ';
echo NodeTestTool::isCentralWordInDB($word);
echo "<br /><br />";

echo 'NodeTestTool::isCloudEIDInDB($eid) : ';
echo NodeTestTool::isCloudEIDInDB($eid);
echo "<br /><br />";

echo 'NodeTestTool::isCloudWordInDB($word) : ';
echo NodeTestTool::isCloudWordInDB($word);
echo "<br /><br />";

echo 'NodeTestTool::getRandomCentralEID() : ';
echo NodeTestTool::getRandomCentralEID();
echo "<br /><br />";

echo 'NodeTestTool::getRandomCentralWord() : ';
echo NodeTestTool::getRandomCentralWord();
echo "<br /><br />";

echo 'NodeTestTool::getCloudEIDsFromCentralEIDAndRelNo($eid, $relNo) : ';
echo NodeTestTool::getCloudEIDsFromCentralEIDAndRelNo($eid, $relNo);
echo "<br /><br />";

echo 'NodeTestTool::getCloudWordsFromCentralEIDAndRelNo($eid, $relNo) : ';
echo NodeTestTool::getCloudWordsFromCentralEIDAndRelNo($eid, $relNo);
echo "<br /><br />";

echo 'NodeTestTool::getCloudEIDsFromCentralWordAndRelNo($word, $relNo) : ';
echo NodeTestTool::getCloudEIDsFromCentralWordAndRelNo($word, $relNo);
echo "<br /><br />";

echo 'NodeTestTool::getCloudWordsFromCentralWordAndRelNo($word, $relNo) : ';
echo NodeTestTool::getCloudWordsFromCentralWordAndRelNo($word, $relNo);
echo "<br /><br />";

echo 'NodeTestTool::getAllCloudEIDsFromCentralEID($eid) : <br />';
echo NodeTestTool::getAllCloudEIDsFromCentralEID($eid);
echo "<br /><br />";

echo 'NodeTestTool::getAllCloudWordsFromCentralEID($eid) : <br />';
echo NodeTestTool::getAllCloudWordsFromCentralEID($eid);
echo "<br /><br />";

echo 'NodeTestTool::getAllCloudEIDsFromCentralWord($word) : <br />';
echo NodeTestTool::getAllCloudEIDsFromCentralWord($word);
echo "<br /><br />";

echo 'NodeTestTool::getAllCloudWordsFromCentralWord($word) : <br />';
echo NodeTestTool::getAllCloudWordsFromCentralWord($word);
echo "<br /><br />";

echo 'NodeTestTool::getAbsoluteRandomCloudEID() : ';
echo NodeTestTool::getAbsoluteRandomCloudEID();
echo "<br /><br />";

echo 'NodeTestTool::getAbsoluteRandomCloudWord() : ';
echo NodeTestTool::getAbsoluteRandomCloudWord();
echo "<br /><br />";

echo 'NodeTestTool::getRandomCloudEIDFromCentralEID($eid, $relNo) : ';
echo NodeTestTool::getRandomCloudEIDFromCentralEID($eid, $relNo);
echo "<br /><br />";

echo 'NodeTestTool::getRandomCloudWordFromCentralEID($eid, $relNo) : ';
echo NodeTestTool::getRandomCloudWordFromCentralEID($eid, $relNo);
echo "<br /><br />";

echo 'NodeTestTool::getRandomCloudEIDFromCentralWord($word, $relNo) : ';
echo NodeTestTool::getRandomCloudEIDFromCentralWord($word, $relNo);
echo "<br /><br />";

echo 'NodeTestTool::getRandomCloudWordFromCentralWord($word, $relNo) : ';
echo NodeTestTool::getRandomCloudWordFromCentralWord($word, $relNo);
echo "<br /><br />";

$word2 = "joli";

echo "Antonyms of an antonym of " . $word2 . ": ";
echo NodeTestTool::getWordCloudDistance2($word2, 5, 5);
echo "<br /><br />";
?>
