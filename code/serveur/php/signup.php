<?php
session_start();
require_once("ressources/strings.inc");
require_once("ressources/locations.inc");

$SQL_DBNAME = (dirname(__FILE__) . "/db");
	if (!$db = new SQlite3($SQL_DBNAME))
		die($strings['err_signup_dbopen']);

$location = get_location();

$newpage = true;
$msg = array();

if(isset($_POST['signupemail'])){
	$newpage = false;
	$signupemail = $_POST['signupemail'];
	// Regexp pour les adresses mail (incomplet, mais suffisant pour la plupart des adresses).
	// http://en.wikipedia.org/wiki/Email_address
	// N'implémente pas les "quotes" dans la partie locale (avant le @).
	$allowed_local = "[-a-zA-Z0-9!#\$%&'*+/^=?_`{|}~]"; /* Je ne sais pas trop si l'espace est autorisée. */
	$pattern_local = "$allowed_local(\\.?$allowed_local)*";
	$pattern_hostname_label = '[a-zA-Z0-9]([-a-zA-Z0-9]*[a-zA-Z0-9])?';
	$pattern_hostname = "$pattern_hostname_label(\\.$pattern_hostname_label)*";
	$pattern_ip = "([01]?[0-9]?[0-9]|2[0-4][0-9]|25[0-5])";
	$pattern_host = "($pattern_hostname|\\[$pattern_ip\\])";
	// Note : j'ai utilisé ";" comme délimiteur de regexp car il y a un slash dans $allowed_local, et je ne sais pas comment l'échapper là
	$pattern = ";^$pattern_local@$pattern_host\$;";
	
	if(trim($signupemail) == ""){
		$msg[] = $strings['err_signup_fill_mail'];
	}
	else if (!preg_match($pattern, $signupemail)){
		// TODO : ce message est erroné.
		$msg[] = $strings['err_signup_invalid_mail'];
	}
	else if ($db->querySingle("SELECT mail FROM user WHERE mail='$signupemail'") != null){
		$msg[] = $strings['err_signup_existing_mail'];
	}
}

if(isset($_POST['signupid'])){
	$newpage = false;
	$signupid = $_POST['signupid'];
	$pattern = "/^([a-zA-Z0-9])+([\.\-_][a-zA-Z0-9]*)*/";
	if(trim($signupid) == ""){
		$msg[] = $strings['err_signup_fill_login'];
	}
	else if (!preg_match($pattern, $signupid)){
		$msg[] = $strings['err_signup_invalid_login'];
		$signupid =	 $_POST['signupid'];
	}
	else if ($db->querySingle("SELECT login FROM user WHERE login='$signupid'") != null){
		$msg[] = $strings['err_signup_existing_login'];
	}
}

if(isset($_POST['signuppswd1'])){
	$newpage = false;
	$signuppswd1 = $_POST['signuppswd1'];
	if(trim($signuppswd1) == ""){
		$msg[] = $strings['err_signup_fill_passwd1'];
	}
	else  if(strlen($signuppswd1) < 5){
		$msg[] = $strings['err_signup_invalid_passwd1'];
	}
}

if(isset($_POST['signuppswd2'])){
	$newpage = false;
	$signuppswd2 = $_POST['signuppswd2'];
	if(trim($signuppswd2) == ""){
		$msg[] = $strings['err_signup_fill_passwd2'];
	}
	if(strlen($signuppswd1 != $signuppswd2)){
		$msg[] = $strings['err_signup_passwords_dont_match'];
	}
}

if(count($msg) == 0 && $newpage == false)
{
	$ok = ($db->query("INSERT INTO user(mail, login, hash_passwd, score, ugroup, cgCount) VALUES ('" . SQLite3::escapeString($signupemail)
		. "', '" . SQLite3::escapeString($signupid)
		. "', '" . SQLite3::escapeString(md5($signuppswd1))
		. "', 0, 1, 0);"));

	if($ok == true) {
		$_SESSION['userId'] = $signupid;
		return_to($location, "?show_msg=ok_signup_registered");
	} else {
		$msg[] = $strings['err_signup_dbinsert'];
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic sous Android™ - Version Alpha - S'inscrire</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>
		<div class="content">
			<h2>Inscription</h2>
			<?php include("ressources/showmsg.inc"); ?>
			<h3>Vous n'avez pas encore de compte ?</h3>
			<p>
				Inscrivez-vous pour accéder l'ensemble du site et faire parti des alpha-testeurs ! <br />
				Vous pourrez ainsi télécharger l'application la tester et nous faire part de vos remarques afin de l'améliorer.<br />
			</p>
			<p>
				Pour vous inscrire maintenant veuillez remplir le formulaire qui suit :
			</p>
			<?php
				if(count($msg) > 0)
				{
					echo '<div class="message warning">'.
						 '<p><b>Saisie invalide. Les erreurs sont les suivantes : </b></p>'.
						 '<ul>';
					foreach ($msg as $m) {
						echo "<li>".htmlspecialchars($m)."</li>";
					}
					echo '</ul>';
					echo '</div>';
				}
			?>
			<form name="signupform" method="post" action="signup.php?return=<?php echo $location; ?>">
				<table>
					<tr>
						<td>
							<label for="signupemail">Saisissez votre adresse mail&nbsp;:</label>
						</td>
						<td>
							<input name="signupemail" type="text"
								<?php
									if(isset($_POST['signupemail'])){
										echo " value='$signupemail'";
									}
								?>
							/>
						</td>
					</tr>
					<tr>
						<td>
							<label for="signupid">Choisissez un identifiant&nbsp;: </label>
						</td>
						<td>
							<input name="signupid" type="text"
								<?php
									if(isset($_POST['signupid'])){
										echo " value='$signupid'";
									}
								?>
							/>
						</td>
					</tr>
					<tr>
						<td>
							<label for="signuppswd1">Choisissez un mot de passe&nbsp;: </label>
						</td>
						<td>
							<input name="signuppswd1" type="password" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="signuppswd2">Resaisissez le mot de passe&nbsp;: </label>
						</td>
						<td>
							<input name="signuppswd2" type="password" />
						</td>
					</tr>
					<tr>
						<td>
							
						</td>					
						<td>
							<input type="submit" name="signupsubmit" value="Valider" />
						</td>
					</tr>
				</table>
			</form>
			<h3>Vous êtes déjà inscrit&nbsp;? <a href="login.php?return=<?php echo $location; ?>">Connectez-vous&nbsp;!</a></h3>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
