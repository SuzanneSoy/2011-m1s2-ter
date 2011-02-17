<?php>
    include("_head.php");
?>
	<body>
		<div class="menu">
			<?php include("menu.html"); ?>	
		</div>
		<div class="content">
                    <p>Vous êtes déjà inscrit&nbsp;? Authentifiez-vous&nbsp;:</p>
                    <form name="loginform" method="post" action="loginaction.php">
                        <p> Identifiant&nbsp;: <input name="loginid" type="text" /></p>
                        <p> Mot de passe&nbsp;: <input name="loginpswd" type="password" /></p>
                        <p> <input type="submit" name="loginsubmit" value="Valider" />
                    </form>
		</div>

		<div class="footer">
			<?php include("footer.html"); ?>
		</div>
	</body>
        </html>