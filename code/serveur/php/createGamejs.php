<?php
require_once("ressources/strings.inc");
require_once("relations.php");
require_once("server.php");
session_start();

$state = 0;
$err = false;
$msg = "";
$rels = array();

function getWords($nbwords)
{
	global $msg;
	global $err;
	$words = array();	

	for($i = 0; $i < $nbwords; $i++)
		if(!isset($_POST['word'.$i]) || empty($_POST['word'.$i])) {
			$err = true;
			$msg = $strings['err_creategame_fill_all'];
			return -1;
		}
		else
			$words[$i] = $_POST['word'.$i];

	return $words;
}

function getWordsAndResponses($nbwords)
{
	global $err;
	global $msg;
	$words = array();
	$respwords = array();
	
	$words = getWords($nbwords);

	if($words == -1)
		return -1;

	foreach($words as $key=>$w) {
		if(isset($_POST['rd'.$key])) {
			$respwords[$key] = array();
			$respwords[$key][0] = $words[$key];
			$respwords[$key][1] = $_POST['rd'.$key];
		}
		else
			return -1;
	}

	return $respwords;
}

function checked($name, $value) {
	if(isset($_POST[$name]) && $_POST[$name] == $value)
		return 'checked';
}

function probaOf($relation, $relation2) {
	if (($relation == "r1" && $relation2 == 0)
	|| ($relation == "r2" && $relation2 == 1)
	|| ($relation == "r0" && $relation2 == 2)
	|| ($relation == "trash" && $relation2 == 3))
		return 1;

	return 0;
}

if(isset($_POST['nbcloudwords'])) {
	$nbwords = $_POST['nbcloudwords'];

	if(!is_numeric($nbwords) || $nbwords <= 0) {
		$err = true;
		$msg = $strings['err_creategame_nbwords_value'];
	}
	else {
		$state = 1;
		$relations = get_game_relations();
	}
	
	if($state == 1 && isset($_POST['centralword']) && !empty($_POST['centralword'])) {
		if($_POST['relation1'] != $_POST['relation2']) {		
			$centralword = $_POST['centralword'];			
			$rels[0] = $stringRelations[$_POST['relation1']];
			$rels[1] = $stringRelations[$_POST['relation2']];
			$rels[2] = $stringRelations[0];
			$rels[3] = $stringRelations[-1];
			
			$words = getWords($nbwords);

			if($err != true)
				$state = 2;
			else {
				$err = true;
				$msg = $strings['err_creategame_cloud_fill_all'];
			}
				
		}
		else {
			$err = true;
			$msg = $strings['err_creategame_eq_relations'];
		}
	}
	elseif (isset($_POST['centralword']) && empty($_POST['centralword'])) {
		$err = true;
		$msg = $strings['err_creategame_cloud_fill_all'];
	}
	
	if($state == 2) {
		$respwords = getWordsAndResponses($nbwords);
		$r1 = $_POST['relation1'];
		$r2 = $_POST['relation2'];
		$cloud = array();
		$totalDifficulty = 0;
		$addedWords = 0;

		if($respwords != -1 && isset($_POST['tDifficulty'])) {
			if(is_numeric($totalDifficulty = $_POST['tDifficulty'])) {

				if(insertNode($centralword))
					$addedWords++;

				$centralword = getNodeEid($centralword);

				foreach($respwords as $key=>$rw) {
					$difficulty = $totalDifficulty / count($respwords);

					if(insertNode($respwords[$key][0]))
						$addedWords++;

					$cloud[$key] = array('pos'=>$key, 'd'=> $difficulty, 'eid'=>getNodeEid($respwords[$key][0]),
										'probaR1'=> probaOf("r1", $rw[1]),
										'probaR2'=> probaOf('r2', $rw[1]),
										'probaR0'=> probaOf('r0', $rw[1]),
										'probaTrash'=> probaOf('trash', $rw[1]));
				}
			}
			else {
				$err = true;
				$msg = $strings['err_creategame_isNumeric_tDifficulty'];
			}

			$state = 3;
			$msg = $strings['ok_creategame_game_create'];
		}

		cgInsert($centralword, $cloud, $r1, $r2, $totalDifficulty);
	}
	elseif($state == 2) {
		$err = true;
		$msg = $strings['err_creategame_fill_all'];
	}
}
else
	$err = true;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic Android - Création de partie</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
		<script type="text/javascript" src="ressources/jquery-1.5.1.min.js" /></script>
		<script type="text/javascript" src="ressources/createGame.js" /></script>
<style type="text/css">
	#wordLines input{
		border : 2px solid grey;		
	}
	.wordLine .status {
		visibility: hidden;
	}
	.wordLine.valid .status, #center.valid .status {
		color: green;
		visibility: visible;
	}
	.wordLine.invalid .status, #center.invalid .status, #center .status {
		color: red;
		visibility: visible;
	}
</style>
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>	
		<div class="content creategame">
			<h2>Création de parties</h2>
			<p>Cette page vous permet de créer des parties personalisées en indiquant les mots qui seront affiché pour un mot central.<br /><br />
			<div id="errorDiv" class="message warning" style="display:none;"></div>
			
			<div id="center">
				<label for="centralWord"> Le mot central : </label>
				<input type="text" id="centralWord" name="centralWord" />
				<span class="status">●</span>
			</div>
			<div id="relations">
				<label for="relation1">Relation 1</label>
				<select name="relation1" id="relation1">
				</select>
				<label for="relation2">Relation 2</label>
				<select name="relation2" id="relation2">
				</select>
			</div>
			<div id="wordLines">
				<div id="templates" style="display:none">
					<div class="wordLine" class="wordLine">
						<label for="word-"></label>
						<input type="text" id="word-"/>
						<span class="status">●</span>
						<input type="checkbox" id="r1-"/><label class="r1" for="r1-">Blabla</label>
						<input type="checkbox" id="r2-"/><label class="r2" for="r2-">Blabla</label>
						<input type="checkbox" id="r3-"/><label class="r3" for="r3-">Blabla</label>
						<input type="checkbox" id="r4-"/><label class="r4" for="r4-">Blabla</label>
					</div>
				</div>
			</div>
			<div id="button"></div>
		</div>
		<div id="templates" style="display:none">
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
