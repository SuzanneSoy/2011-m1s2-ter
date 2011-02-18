<?php>
session_start();    
include("_head.php");
?>
	<body>
		<div class="menu">
			<?php include("ressources/menu.html"); ?>
		</div>
		<div class="content">
                    <p>Vous êtes déjà inscrit&nbsp;? Authentifiez-vous&nbsp;:</p>
                    <form name="loginform" method="post" action="loginaction.php">
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
					<td  colspan="2">
						<p> <input type="submit" name="loginsubmit" value="Valider" />
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
