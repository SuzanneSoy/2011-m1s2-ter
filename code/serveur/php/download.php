<?php
session_start();

if(!isset($_SESSION['userId']))
	header("location:login.php?return=download.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
	<title>PtiClic sous Android, version Alpha - Téléchargement</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />		
	<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<div class="menu">
			<?php include("ressources/menu.inc"); ?>
		</div>
		<div class="content">
			<span class="downloadarea"><a href="ressources/pticlic.apk" id="downloadlink">Télécharger</a></span>
			<h2>Installation de l'application</h2>
			<h3> A partir de votre téléphone </h3>
			<ul>
			    <li> <a href="ressources/pticlic.apk" />Téléchargez le fichier d'installation</a></li>
			    <li>Une fois téléchargé, cliquez sur le fichier dans la barre de notification d'Android et suivez
			    les instructions d'installation. Vous devrez patientez quelques instant pendant l'installation.</li>
				<li>Une fois l'installation terminée, démarrez l'application</li>
				<li> Suivez attentivement les instructions lors du premier démarrage de l'application</li>
			</ul>
			<h3> A partir de votre ordinateur </h3>
			<ul>
			    <li><a href="ressources/pticlic.apk">Téléchargez le fichier d'installation</a> </li>
			    <li>Transférez ce fichier sur votre téléphone à l'aide de bluetooth, une clé usb ou autre</li>
			    <li>Depuis votre téléphone, retrouvez sur votre carte mémoire l'application que vous
			    venez de transférer</li>
			    <li>Cliquez sur l'application afin de l'installer sur votre téléphone</li>
			    <li>Suivez attentivement les instructions lors du premier démarrage de l'application</li>
			</ul>
		</div>
		<div class="footer">
			<?php include("ressources/footer.inc"); ?>
		</div>
	</body>
</html>
