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
			<h2>PtiClic : Affichage de parties</h2>
			<?php
				date_default_timezone_set('Europe/Paris');
				$SQL_DBNAME = (dirname(__FILE__) . "/db");
				
			?>
			<table>
				<thead>
					<tr>
						<th>Mot</th>
						<th><?php echo $r1 . " (" . $r1 . ")"; ?></th>
						<th><?php echo $r2 . " (" . $r2 . ")"; ?></th>
						<th>Idée associée (0)</th>
						<th>Poubelle (-1)</th>
					</tr>
			</table>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
