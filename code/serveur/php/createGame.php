<?php
require_once("ressources/strings.inc");
session_start();

$err = false;
$msg = "";

if(isset($_POST['nbcloudwords']))
	$nbword = $_POST['nbcloudwords'];

for($i = 0; $i < $nbword; $i++)
	if(!isset($_POST['word'.$i]) || empty($_POST['word'.$i])) {
		$err = true;
		$msg = $strings['err_creategame_fill_all'];
	}

if($err == false)
	$state = 0;
?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic Android - Création de partie</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>	
		<div class="content">
			<?php include("ressources/showmsg.inc"); ?>
			<form action="createGame.php" method="POST">
				<?php
					if(!isset($_POST["nbcloudwords"]))
					{
						echo '<input type="text" name="nbcloudwords" />';
						echo '<input type="submit" value="suivant" />';			
					}
					else
					{
						echo '<input type="text" name="centralword" />';
						
						for($i = 0; $i < $_POST['nbcloudwords']; $i++)
							echo '<input type="text" name="word'.$i.'" />';
						
						echo '<input type="submit" value="Enregistrer la partie" />';
					}
				?>
			</form>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
