<?php
session_start();    

if(isset($_POST['loginid']) && !empty($_POST['loginid']))
	$user =  SQLite3::escapeString($_POST['loginid']);
if(isset($_POST['loginpswd']) && !empty($_POST['loginpswd']))
	$pswd = md5($_POST['loginpswd']);
if(isset($_GET['return']))
	$location = $_GET['return'];
else
	$location = "index.php";

if(isset($_GET['d']) && $_GET['d'] == "true") {
	session_destroy();
	header("location:index.php");
}

if(isset($user) && isset($pswd))
{
	$SQL_DBNAME = (dirname(__FILE__) . "/db");

	if (!$db = new SQlite3($SQL_DBNAME))
		mDie(1,"Erreur lors de l'ouverture de la base de données SQLite3");

	if($pswd == ($db->querySingle("SELECT hash_passwd FROM user WHERE login='$user';"))) {
		$_SESSION['userId'] = $user;
		
		header("location:".$location);
	}
	else
		$msg = "Mauvais nom d'utilisateur ou mot de passe";
}
else if(isset($user) or isset($pswd))
	$msg = "Veuillez remplir tous les champs";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>Titre</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<div class="menu">
			<?php include("ressources/menu.html"); ?>
		</div>
		<div class="content">
			<?php
				if(isset($_GET['return']) && $_GET['return'] == "download.php")
					echo '<p>Pour accéder à la page de téléchargement de l\'application vous devez être authentifié !</p>';
			?>
		    <p>Vous êtes déjà inscrit ? Authentifiez-vous :</p>
                    <?php
			if(isset($msg))
				echo '<span class="message warning">'.$msg.'</span>';
		    ?>
		    <form name="loginform" method="POST" action="login.php?return=<?php echo $location; ?>">
                        <table class="logintbl">
				<tr>
					<td>
						<label for="loginid"> Identifiant&nbsp;:</label> 
					</td>
					<td>
						<input name="loginid" type="text" /><br />
					</td>
				</tr>
                        	<tr>
					<td>
						<label for="loginpswd"> Mot de passe&nbsp;: </label>
					</td>
					<td>
						<input name="loginpswd" type="password" />
	                        	</td>
				</tr>
				<tr>
					<td>

					</td>
					<td>
						<input type="submit" name="loginsubmit" value="Valider" />
					</td>
				</tr>
			</table>
			</form>
			<p>Vous ne disposez pas encore de compte ? <a href="signup.php">Inscrivez vous dès maintenant</a>.</p>
			<h2>
		</div>

		<div class="footer">
			<?php include("ressources/footer.html"); ?>
		</div>
	</body>
 </html>
