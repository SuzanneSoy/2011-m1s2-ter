<?php
require_once("ressources/strings.inc");
session_start();

$state = 0;
$err = false;
$msg = "";

if(isset($_POST['nbcloudwords'])) {
	$nbwords = $_POST['nbcloudwords'];

	if(!is_numeric($nbwords) || $nbwords <= 0) {
		$err = true;
		$msg = $strings['err_creategame_nbwords_value'];
	}
	else
		$state = 1;
	
	if($state == 1) {
		for($i = 0; $i < $nbwords; $i++)
			if(!isset($_POST['word'.$i]) || empty($_POST['word'.$i])) {
				$err = true;
				$msg = $strings['err_creategame_fill_all'];
				break;
			}
	}
}
else
	$err = true;

if($err == false)
	$state = 1;

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic Android - Création de partie</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>	
		<div class="content creategame">
			<h2>Création de parties</h2>
			<?php
				if(isset($_POST['nbcloudwords']) && $_POST['nbcloudwords'] > 0)
					echo '<p>Remplissez le mot central ainsi que les différents mots du nuage pour réaliser un partie personalisée.<br />
						Une fois satisfait de votre partie cliquez sur "Enregistrer la partie"';
				else
					echo '<p>Cette page vous permet de créer des parties personalisées en indiquant les mots qui seront affiché pour un mot central.<br /><br />
						Veuillez entrer le nombre de mots composant le nuage dans le formulaire ci-dessous avant de continuer.</p><br />';
			?>
			<form action="createGame.php" method="POST">
					<?php
					if($err == true && $msg != "")
						echo '<span class="message warning">'.$msg.'</span>';
					else if ($msg != "")
						echo '<span class="message success">'.$msg.'</span>';

					if($state == 0) {
						echo '<table>';
						echo '<tr><td><label for="nbcloudwords"> Nombre de mots du nuage : </label></td>';
						echo '<td><input type="text" name="nbcloudwords" /></td></tr>';
						echo '<tr><td></td><td><input type="submit" value="suivant" /></td></tr>';			
					}
					else {
						echo '<table class="wordsform">';
						echo '<input type="hidden" name="nbcloudwords" value="'.$nbwords.'" />';
						echo '<tr><td colspan="2"><label for="centralword">Mot central : </label><br /><br /></td>';
						echo '<td colspan="2" class="inputcell"><input type="text" name="centralword" /><br /><br /></td>';
				
						for($i = 0; $i < $nbwords; $i++) {
							if($i % 2 == 0) {
								echo '</tr><tr><td><label for="word'.$i.'">Mot '.($i+1).' : </label></td>';
								echo '<td class="inputcell"><input type="text" name="word'.$i.'" /></td>';
							}
							else {
								echo '<td><label for="word'.$i.'">Mot '.($i+1).' : </label></td>';
								echo '<td class="inputcell"><input type="text" name="word'.$i.'" /></td>';
							}
						}
				
						if($nbwords % 2 != 0)
							echo '<td></td>';

						echo '</tr><tr><td colspan="2"></td><td colspan="2" class="td2"><input type="submit" value="Enregistrer la partie" /></td></tr>';
					}			
					?>
				</table>
			</form>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
