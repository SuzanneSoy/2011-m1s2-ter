<?php
session_start();

if(!isset($_SESSION['userId']))
	header("location:login.php?return=download&showmsg=oth_login_download_nauth");

$dl = "ressources/pticlic-alpha-v0.2.apk"

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic sous Android™ - Version Alpha - Téléchargement</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />		
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>
		<div class="content">
			<?php include("ressources/showmsg.inc"); ?>
			<h2>Téléchargement de l'application</h2>			
			<span class="downloadarea"><a href="<?php echo $dl; ?>" id="downloadlink">Téléchargement</a></span>
			<h2>Installation de l'application</h2>
			<h3> A partir de votre téléphone </h3>
			<ul>
				<li><a href="<?php echo $dl; ?>">Téléchargez le fichier d'installation</a>.</li>
				<li>Vous avez besoin d'autoriser votre téléphone à accepter les sources inconnue. Pour cela, allez dans
					Préférence→Application. Cochez «Sources inconnues».</li>
				<li>Une fois téléchargé, cliquez sur le fichier dans la barre de notification d'Android™ et suivez les instructions
					d'installation. Vous devrez patienter quelques instants pendant l'installation.</li>
				<li>Une fois l'installation terminée, démarrez l'application.</li>
				<li>Suivez attentivement les instructions lors du premier démarrage de l'application.</li>
			</ul>
			<h3> A partir de votre ordinateur </h3>
			<ul>
				<li><a href="<?php echo $dl; ?>">Téléchargez le fichier d'installation</a>.</li>
				<li>Transférez ce fichier sur votre téléphone à l'aide de bluetooth, une clé usb ou autre.</li>
				<li>Depuis votre téléphone, trouvez sur votre carte mémoire l'application que vous venez de transférer.</li>
				<li>Cliquez sur l'application afin de l'installer sur votre téléphone</li>
				<li>Suivez attentivement les instructions lors du premier démarrage de l'application</li>
			</ul>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
