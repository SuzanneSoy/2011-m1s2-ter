<?php
session_start();
require_once("ressources/strings.inc");
require_once("ressources/locations.inc");

$msg = null;

if(isset($_POST['loginid']) && !empty($_POST['loginid']))
	$user =	 SQLite3::escapeString($_POST['loginid']);
if(isset($_POST['loginpswd']) && !empty($_POST['loginpswd']))
	$pswd = md5($_POST['loginpswd']);

$location = getlocation();

if(isset($_GET['d']) && $_GET['d'] == "true") {
	session_destroy();
	return_to($location, "?show_msg=ok_login_disconnect");
}

if(isset($user) && isset($pswd))
{
	$SQL_DBNAME = (dirname(__FILE__) . "/db");

	if (!$db = new SQlite3($SQL_DBNAME))
		die($strings['err_login_dbopen']);

	if($pswd == ($db->querySingle("SELECT hash_passwd FROM user WHERE login='$user';"))) {
		$_SESSION['userId'] = $user; // Le login se fait aussi dans signup.
		
		return_to($location);
	}
	else
		$msg = $strings['err_login_bad_user_pass'];
}
else if(isset($user) or isset($pswd))
	$msg = $strings['err_login_fill_all'];

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic sous Android™ - Version Alpha - Se connecter</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>
		<div class="content">
			<h2>Connexion</h2>
			<?php include("ressources/showmsg.inc"); ?>
			<h3>Vous êtes déjà inscrit&nbsp;? Authentifiez-vous.</h3>
			<?php
				if($msg !== null)
					echo '<p class="message warning">'.htmlspecialchars($msg).'</p>';
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
			<h3>Vous ne disposez pas encore d'un compte&nbsp;? <a href="signup.php?return=<?php echo $location; ?>">Inscrivez-vous</a> dès maintenant !</h3>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
