<?php
session_start();
include("_head.php");

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
}


if(isset($_POST['signupid'])){
    $newpage = false;
	$signupid = $_POST['signupid'];
        $pattern = "/^([a-zA-Z0-9])+([\.\-_][a-zA-Z0-9]*)*/";
    if(trim($signupid) == ""){
        $msg[] = "Veuillez renseigner le champ 'Choisir un identifiant'.\n";
        unset($_POST['signupid']);
        $signid = "";
    }
    else if (!preg_match($pattern, $signupemail)){
        $msg[] = "Identifiant invalid. Vous pouvez utiliser des lettres, des chiffres et
            les caractères spéciaux '-', '_' et '.'\n";
	$signupemail =  $_POST['signupemail'];
        unset($_POST['signupid']);
        $signid = "";
    }
}

if(isset($_POST['signuppswd1'])){
    $newpage = false;
    $signuppswd1 = $_POST['signuppswd1'];
    if(trim($signuppswd1) == ""){
        $msg[] = "Veuillez renseigner le champ 'Mot de passe'.\n";
        unset($_POST['signuppswd1']);
        $signid = "";
        unset($_POST['signuppswd2']);
        $signid = "";
    }
    else  if(strlen($signuppswd1) < 8){
        $msg[] = "Mot de passe invalide. Votre mot de passe doit comporter au moins 8 caractères.\n";
        unset($_POST['signuppswd1']);
        $signid = "";
        unset($_POST['signuppswd2']);
        $signid = "";
    }
}

if(isset($_POST['signuppswd2'])){
    $newpage = false;
    $signuppswd2 = $_POST['signuppswd2'];
    if(trim($signuppswd2) == ""){
        $msg[] = "Veuillez renseigner le champ 'Resaisir le mot de passe'.\n";
        unset($_POST['signuppswd1']);
        $signid = "";
        unset($_POST['signuppswd2']);
        $signid = "";
    }
    if(strlen($signuppswd1 != $signuppswd2)){
        $msg[] = "Les deux mots de passe saisis ne sont pas identiques.\n";
            unset($_POST['signuppswd1']);
        $signid = "";
        unset($_POST['signuppswd2']);
        $signid = "";
    }
}

echo var_dump($msg);

if(isset($_GET['return']))
	$location = $_GET['return'];
else
	$location = "contact.php";


if(isset($_GET['d']) && $_GET['d'] == "true") {
	session_destroy();
	header("location:index.php");
}



/*
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

*/
?>
	<body>
		<div class="menu">
			<?php include("ressources/menu.html"); ?>
		</div>
		<div class="content">
                    <p>Vous n'êtes pas encore inscrit&nbsp;? Inscrivez-vous&nbsp;:</p>
                    <?php
			if(sizeof($msg) > 0){
				echo '<span class="warning">'.
                            "<b>Saisie invalide. Les erreurs sont les suivantes : </b> <p>".
                            "<ul>";
                            foreach ($msg as $m) {
                                echo "<li>".$m;
                            }
                            echo "</ul>";
                        } 
                        else if($newpage == false){
                                                // On mets les données dans la bd...
                           echo '<span class="warning">'."Inscription déroulée avec succès !";
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
				<tr>
                                	<td>
						<label for="signuppswd1">Choisir un mot de passe&nbsp;: </label>
					</td>
					<td>
						<input name="signuppswd1" type="password"
                                                       <?php
                                                       if(isset($_POST['signuppswd1'])){
                                                           echo "value='signuppswd1'";
                                                       }
                                                       ?>
                                                       />
	                        	</td>
				</tr>
                  		<tr>
                                		<td>
						<label for="signuppswd2">Resaisir le mot de passe&nbsp;: </label>
					</td>
					<td>
						<input name="signuppswd2" type="password"
                                                       <?php
                                                       if(isset($_POST['signuppswd2'])){
                                                           echo "value='signuppswd2'";
                                                       }
                                                       ?>
                                                       />
	                        	</td>
				</tr>
				<tr>
					<td  colspan="2">
						<p> <input type="submit" name="signupsubmit" value="Valider" />
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





<?
/*
 * php

    include("_head.php");
    include("ressources/FormValidator.php");
    $fv = new FormValidator("post");
?>
	<body>
		<div class="menu">
			<?php include("ressources/menu.html"); ?>
		</div>
                <p>Vous n'êtes pas encore inscrit&nbsp;? Inscrivez-vous&nbsp;:
                <form name="signupform" method="post" >

                    <?php
                    
                    $error_email1 = "Tapez votre adresse mail.";
                    $error_email2 = "L'adresse mail que vous avez fournie
                        n'est pas valide. Veuillez saisir votre adresse mail.";
                    if($fv->isEmpty("signupemail", $error_email1)){
                        echo "<p>$error_email1</p>";
                    }
                    if(!$fv->isSafeValidEmail("signupemail", $error_email2)){
                        echo "<p>$error_email2</p>";
                    }
                    ?>

                    <p>Tapez votre adresse mail&nbsp;: <input name="signupemail" type="text" /></p>

                    <?php
                    $error_id1 = "Choisissez un identifiant";
                    $error_id2 = "Votre identifant peut se composer de nombres, de lettres
                        et des caractères '-', '_' et '.'. Veuillez resaisir un identifiant";
                    if($fv->isEmpty("signupid", $error_id1))
                        echo "<p>$error_id1</p>";
                    if(!$fv->isSafeAlphaNumeric("signupid", $error_id2))
                        echo "<p>$error_id2</p>";
                    ?>
                    <p>Choisir un identifiant&nbsp;: <input name="signupid" type="text" /></p>
                        <!-- TODO: Tester pour voie si l'identifiant n'est pas déjà pris -->
        
                    <?php
                    $error_pswd1_1 = "Choisissez un mot de passe";
                    $error_pswd1_2 = "Votre mot de passe peut se composer de nombres, de lettres
                        et des caractères '-', '_' et '.'. Veuillez resaisir un identifiant";
                    if($fv->isEmpty("signuppswd1", $error_pswd1_1))
                            echo "<p>$error_pswd1_1</p>";
                    if(!$fv->isSafeAlphaNumeric("signuppswd1", $error_pswd1_2))
                            echo "<p>$error_pswd1_2</p>";
                    if(!$fv->getSimpleValue("signuppswd1") != $fv->getSimpleValue("signuppswd2"))
                        echo "<p> Les deux mots de passe que vous avez saisis ne sont pas identiques</p>";
                    ?>
                    <p>Choisir un mot de passe&nbsp;: <input name="signuppswd1" type="password" /></p>

                    
                    
                    <p>Retapez le mot de passe&nbsp;: <input name="signuppswd2" type="password" /></p>
                    <p> <input type="submit" name="signupsubmit" value="Valider" />
                </form>
		</div>




		<div class="footer">
			<?php include("ressources/footer.html"); ?>
		</div>
	</body>
        </html>
*/