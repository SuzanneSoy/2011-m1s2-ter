<?php>
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
