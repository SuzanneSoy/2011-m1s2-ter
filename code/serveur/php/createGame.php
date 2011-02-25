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
<<<<<<< HEAD
			<?php
				if(isset($_POST['nbcloudwords']) && $_POST['nbcloudwords'] > 0)
					echo '<p>Remplissez le mot central ainsi que les différents mots du nuage pour réaliser un partie personalisée.<br />
						Une fois satisfait de votre partie cliquez sur "Enregistrer la partie"';
				else
					echo '<p>Cette page vous permet de créer des parties personalisées en indiquant les mots qui seront affiché pour un mot central.<br /><br />
						Veuillez entrer le nombre de mots composant le nuage dans le formulaire ci-dessous avant de continuer.</p>';
			?>
			<form action="createGame.php" method="POST">
				<table class="creategametbl">
					<?php
					if(!isset($_POST["nbcloudwords"])) {
						echo '<tr><td><label for="nbcloudwords"> Nombre de mots du nuage : </label></td>';
						echo '<td><input type="text" name="nbcloudwords" /></td></tr>';
						echo '<tr><td id="td2"></td><td><input type="submit" value="suivant" /></td></tr>';			
					}
					else {
						echo '<tr><td colspan="2"><label for="centralword">Mot central : </label><br /><br /></td>';
						echo '<td colspan="2" id="td2"><input type="text" name="centralword" /><br /><br /></td>';
				
						for($i = 0; $i < $_POST['nbcloudwords']; $i++) {
							if($i % 2 == 0) {
								echo '</tr><tr><td><label for="word'.$i.'">Mot '.($i+1).' : </label></td>';
								echo '<td id="td2"><input type="text" name="word'.$i.'" /></td>';
							}
							else {
								echo '<td><label for="word'.$i.'">Mot '.($i+1).' : </label></td>';
								echo '<td id="td2"><input type="text" name="word'.$i.'" /></td>';
							}
						}
				
						if($_POST['nbcloudwords'] % 2 != 0)
							echo '<td></td>';

						echo '</tr><tr><td colspan="2"></td><td colspan="2" id="td2"><input type="submit" value="Enregistrer la partie" /></td></tr>';
					}			
					?>
				</table>
			</form>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
