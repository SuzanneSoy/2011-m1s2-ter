<?php
require_once("ressources/strings.inc");
require_once("ressources/relations.inc");
require_once("ressources/backend.inc");
session_start();

if(!isset($_SESSION['userId']))
	header("location:login.php?return=createGame&showmsg=oth_login_createGame_nauth");
	
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic - Création de partie</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
		<script type="text/javascript" src="ressources/jquery-1.5.1.min.js" /></script>
		<script type="text/javascript" src="ressources/createGame.js" /></script>
<style type="text/css">
	#wordLines input{
		border : 2px solid grey;		
	}
	.status {
		visibility: hidden;
	}
	.valid .status, #center.valid .status {
		color: #20FF20;
		visibility: visible;
	}
	.invalid .status, #center.invalid .status, #center .status {
		color: #FF2020;
		visibility: visible;
	}
	
	.wordLinesTable {
		min-height : 20px;
		min-width : 20px;		
		border-collapse : collapse;
		border-spacing : 0px;
		margin-left : 30px;
	}
	
	.wordLinesTable td {
		padding : 6px;		
		padding-left : 10px;		
	}
	
	.wordLinesTable .lightLine {
		background-color : #F0F0D0;
	}
	
	.wordLinesTable td:first-child {
		text-align : right;
	}


	#center {		
		margin-left : 100px;
		margin-top : 20px;
		margin-bottom : 30px;
	}
	
	#center label {
		border-bottom : 1px solid grey;
		border-left : 1px solid grey;
		-moz-border-radius : 100%;
		padding-left : 10px;
	}

	
	#relations {
		margin-bottom : 20px;
		margin-top : 10px;
	}
	
	#relations label {
		margin-left : 40px;
		border-bottom : 1px solid grey;
		border-left : 1px solid grey;
		-moz-border-radius : 100%;
		padding-left : 10px;
		padding-right : 10px;
	}
	
	#button {
		margin-top : 30px;
		margin-left : 50px;
		margin-bottom : 40px;
	}
	
	#button input {
		margin-left : 10px;
		margin-right : 40px;
		padding : 4px;
		padding-left : 8px;
		padding-right : 8px;
		font-weight : bold;
	}
	
</style>
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>	
		<div class="content creategame">
			<h2>Création de parties</h2>
			<p>Cette page vous permet de créer des parties personalisées en indiquant les mots qui seront affiché pour un mot central.<br /><br />
			<div id="errorDiv" class="message warning" style="display:none;"></div>
			<div id="successDiv" class="message success" style="display:none;"></div>
			<a id="newCreationLink" style="display:none;" href="createGame.php">Créer une autre partie</a>
			
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
					<table>
						<thead> </thead>
						<tbody>					
							<tr class="wordLine">
								<td>
									<label for="word-"></label>
								</td>
								<td>
									<input value="" class="word" type="text" id="word-"/>
								</td>
								<td>
									<span class="status">●</span>
								</td>
								<td>
									<input type="checkbox" id="r1-"/><label class="r1 relationLabel" for="r1-">Blabla</label>
								</td>
								<td>
									<input type="checkbox" id="r2-"/><label class="r2 relationLabel" for="r2-">Blabla</label>
								</td>
								<td>
									<input type="checkbox" id="r3-"/><label class="r3 relationLabel" for="r3-">Blabla</label>
								</td>
								<td>
									<input type="checkbox" id="r4-"/><label class="r4 relationLabel" for="r4-">Blabla</label>
								</td>
							</tr>
						</tbody>
					</table>
				</div>
				
				<table class="wordLinesTable">
				<tr><td></td></tr>
				</table>
			</div>
			<div id="button"></div>
		</div>
		<div id="templates" style="display:none">
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
