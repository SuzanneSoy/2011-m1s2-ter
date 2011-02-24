<?php
session_start();
$SQL_DBNAME = (dirname(__FILE__) . "/db");
    if (!$db = new SQlite3($SQL_DBNAME))
        mDie(1,"Erreur lors de l'ouverture de la base de données SQLite3");

$newpage = true;
if(!isset($msg)){
    $msg = array();
}
if(isset($_POST['signupemail'])){
    $newpage = false;
    $signupemail = $_POST['signupemail'];
    $pattern = "/^([a-zA-Z0-9])+([\.a-zA-Z0-9_-])*@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-]+)+/";
    
    if(trim($signupemail) == ""){
        $msg[] = "Veuillez renseigner le champ 'Saisir votre adresse mail'.";
        unset($_POST['signupemail']);
        $signupemail = "";
    }
    else if (!preg_match($pattern, $signupemail)){
        $msg[] = "Adresse mail invalide. Vous pouvez utiliser des lettres, des chiffres et
            les caractères spéciaux '-', '_' et '.'";
	$signupemail =  $_POST['signupemail'];
        unset($_POST['signupemail']);
        $signupemail = "";
    }
    else if ($db->querySingle("SELECT mail FROM user WHERE mail='$signupemail'") != null){
        $msg[] = "L'adresse mail saisie existe déjà ! Veuillez nous contacter si vous avez
            oublier votre identifiant et/ou votre mot de passe.";
        unset($_POST['signupemail']);
        $signupemail = "";
    }
}

if(isset($_POST['signupid'])){
    $newpage = false;
	$signupid = $_POST['signupid'];
        $pattern = "/^([a-zA-Z0-9])+([\.\-_][a-zA-Z0-9]*)*/";
    if(trim($signupid) == ""){
        $msg[] = "Veuillez renseigner le champ 'Choisir un identifiant'.\n";
        unset($_POST['signupid']);
        $signupid = "";
    }
    else if (!preg_match($pattern, $signupemail)){
        $msg[] = "Identifiant invalid. Vous pouvez utiliser des lettres, des chiffres et
            les caractères spéciaux '-', '_' et '.'\n";
	$signupid =  $_POST['signupid'];
        unset($_POST['signupid']);
        $signupid = "";
    }
    else if ($db->querySingle("SELECT login FROM user WHERE login='$signupid'") != null){
        $msg[] = "Identifiant déjà pris ! Veuillez choisir un autre identifiant.";
        unset($_POST['signupid']);
        $signupid = "";
    }
}

if(isset($_POST['signuppswd1'])){
    $newpage = false;
    $signuppswd1 = $_POST['signuppswd1'];
    if(trim($signuppswd1) == ""){
        $msg[] = "Veuillez renseigner le champ 'Mot de passe'.\n";
        unset($_POST['signuppswd1']);
        $signuppswd1 = "";
        unset($_POST['signuppswd2']);
        $signuppswd2 = "";
    }
    else  if(strlen($signuppswd1) < 5){
        $msg[] = "Mot de passe invalide. Votre mot de passe doit comporter au moins 5 caractères.\n";
        unset($_POST['signuppswd1']);
        $signuppswd1 = "";
        unset($_POST['signuppswd2']);
        $signuppswd2 = "";
    }
}

if(isset($_POST['signuppswd2'])){
    $newpage = false;
    $signuppswd2 = $_POST['signuppswd2'];
    if(trim($signuppswd2) == ""){
        $msg[] = "Veuillez renseigner le champ 'Resaisir le mot de passe'.\n";
        unset($_POST['signuppswd1']);
        $signuppswd1 = "";
        unset($_POST['signuppswd2']);
        $signuppswd2 = "";
    }
    if(strlen($signuppswd1 != $signuppswd2)){
        $msg[] = "Les deux mots de passe saisis ne sont pas identiques.\n";
        unset($_POST['signuppswd1']);
        $signuppswd1 = "";
        unset($_POST['signuppswd2']);
        $signuppswd2 = "";
    }
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic sous Android - Version Alpha - Inscription</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>


        <body>
		<div class="menu">
			<?php include("ressources/menu.html"); ?>
		</div>
		<div class="content">
                    <p>Vous n'êtes pas encore inscrit&nbsp;? Inscrivez-vous&nbsp;:</p>
                    <?php
			if(sizeof($msg) > 0){
				echo '<span class="message warning">'.
                            "<b>Saisie invalide. Les erreurs sont les suivantes : </b> <p>".
                            "<ul>";
                            foreach ($msg as $m) {
                                echo "<li>".$m;
                            }
                            echo "</ul>";
                        } 
                        else if($newpage == false){
                            $ok = ($db->query("INSERT INTO user(mail, login, hash_passwd, score) VALUES ('" . SQLite3::escapeString($signupemail)
                                   . "', '" . SQLite3::escapeString($signupid)
                                   . "', '" . SQLite3::escapeString(md5($signuppswd1))
                                   . "', 0);"));

                            if($ok == true)
                                echo "insertion worked!!!!!";
                            else
                                echo "insertion failed!!!";
                                    //header("location:".$location);
                           echo '<span class="message success">'."Inscription s'est déroulée avec succès !";
                           unset($_POST);
                           $newpage = true;
                        }

                            echo '</span>';
		    ?>
		    <form name="signupform" method="post" action="signup.php?return=<?php echo $location; ?>">
                        <table class="signuptbl">
				<tr>
					<td>
						<label for="signupemail">Saisir votre adresse mail&nbsp;:</label>
					</td>
					<td>
						<input name="signupemail" type="text"
                                                       <?php
                                                       if(isset($_POST['signupemail'])){
                                                           echo "value='$signupemail'";
                                                       }
                                                       ?>
                                                       /><br />
					</td>
				</tr>
                        	<tr>
					<td>
						<label for="signupid">Choisir un identifiant&nbsp;: </label>
					</td>
					<td>
						<input name="signupid" type="text"
                                                       <?php
                                                       if(isset($_POST['signupid'])){
                                                           echo "value='$signupid'";
                                                       }
                                                       ?>
                                                       />
	                        	</td>
                                </tr>
				<tr>
                                	<td>
						<label for="signuppswd1">Choisir un mot de passe&nbsp;: </label>
					</td>
					<td>
						<input name="signuppswd1" type="password" />
	                        	</td>
				</tr>
                  		<tr>
                                	<td>
						<label for="signuppswd2">Resaisir le mot de passe&nbsp;: </label>
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
		</div>

		<div class="footer">
			<?php include("ressources/footer.html"); ?>
		</div>
	</body>
 </html>
