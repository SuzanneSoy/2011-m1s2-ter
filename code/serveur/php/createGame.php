<?php
session_start();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic Android - Création de partie</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<div class="menu">
			<?php include("ressources/menu.html"); ?>	
		</div>
		<div class="content">
			<form action="createGame.php" method="POST">
			</form>
		</div>
		<div class="footer">
			<?php include("ressources/footer.html"); ?>
		</div>
	</body>
</html>
