<?php
	session_start();
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic sous Android™ - Version Alpha - Accueil</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>
		<div class="content">
			<?php include("ressources/showmsg.inc"); ?>
			<h2>Jeu PtiClic - Téléchargement gratuit</h2>
			<p>
				Vous aimez les jeux de mots&nbsp;? Vous avez un smartphone sous Android&nbsp;?
				PtiClic est pour vous&nbsp;!
			</p>
			<p>
				Soyez parmi les tous premiers à <a href="download.php">télécharger cette 
				application gratuitement</a> en devenant Alpha-testeur.
				L'<a href="signup.php">inscription</a> est simple, il suffit de fournir
				une adresse mail, de créer un identifiant et vous pourrez commencer à jouer&nbsp;!
			</p>
			
			<h2>Le principe du jeu</h2>
			<p>
				Un mot central apparaît ainsi que quatre associations
				telles que "synonyme", "antonyme", "est une sorte de", "corbeille", … L'idée
				est de lier de nouveaux mots au mot central à l'aide des associations.
				Plus votre réponse est juste, plus vous gagnez de points.
				Attention, vous pouvez aussi perdre des points&nbsp;!
			</p>

			<h2>Le développement de l'application</h2>
			<p>
				La version alpha du jeu PtiClic sous Android est en cours de développement.
				Le projet s'inscrit dans le cadre d'un TER de Master en informatique
				à l'Université Montpellier II sous la direction de Mathieu LAFOURCADE. L'équipe
				de conception et de développement est composée de quatre étudiants&nbsp;: Bertrand BRUN, 
				Yoann BONAVERO, John CHARRON et Georges DUPERON.
			</p>
			<h2>Votre rôle en tant qu'Alpha-testeur</h2>
			<p>
				L'application étant en phase de développement et offerte gratuitement,
				nous serions reconnaissant si vous pouviez nous donner votre avis, vos suggestions, 
				vos idées. <a href="contact.php">Envoyez-nous un message&nbsp;!</a>
			</p>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
