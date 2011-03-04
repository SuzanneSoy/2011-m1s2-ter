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
				$r1 = 23;
				$r2 = 15;
				$textR1 = "Relation1";
				$textR2 = "Relation2";
			?>
			<table class="show-game">
				<caption>Mot central</caption>
				<thead>
					<tr>
						<th>Mot</th>
						<th><?php echo $textR1 . " (" . $r1 . ")"; ?></th>
						<th><?php echo $textR2 . " (" . $r2 . ")"; ?></th>
						<th>Idée associée (0)</th>
						<th>Poubelle (-1)</th>
					</tr>
				</thead>
				<tbody>
					<th>Foo</th>
					<td>0.8</td>
					<td>0.05</td>
					<td>0.1</td>
					<td>0.05</td>
				</tbody>
			</table>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
