<?php
session_start();

if(!isset($_SESSION['userId']))
	header("location:login.php?return=download.php");

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic Android - Téléchargement</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />		
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<div class="menu">
			<?php include("ressources/menu.html"); ?>
		</div>
		<div class="content">
			<h2>Téléchargez la dernière version :</h2>
			> <a href="ressources/pticlic.apk" id="downloadlink">Télécharger</a>
			<h2>Installation de l'application :</h2>
			<h3> A partir du téléphone </h3>
			<ul>			
				<li> Téléchargez l'application en cliquant sur le lien ci-dessus.</li>
				<li> Un fois téléchargée Android vous demande si vous voulez l'installée, selectionnez oui.</li>
				<li> Patientez quelques instant. Une fois l'installation terminée démarrez l'application.</li>
				<li> Suivez les instructions pour le premier démarrage de l'application</li>
			</ul>
			<h3> A partir d'un ordinateur </h3>
			<ul>			
				<li> Téléchargez sur votre ordinateur l'application en cliquant sur le lien ci-dessus. </li>
				<li> Transférez le fichier ainsi téléchargé sur votre téléphone (par bluetooth, usb...)</li>
				<li> Depuis votre téléphone allez cherchez sur votre carte mémoire l'application ainsi transférée.</li>
				<li> Cliquez sur l'application et installé là sur le téléphone.</li>
				<li> Démarrez l'application et suivez les instructions pour le premier démarrage.</li>
			</ul>
		</div>
		<div class="footer">
			<?php include("ressources/footer.html"); ?>
		</div>
	</body>
</html>
