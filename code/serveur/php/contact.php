<?php
session_start();

$displayForm = true;

if(isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['message']))
	if(!empty($_POST['email']) && !empty($_POST['subject']) && !empty($_POST['message']))
	{
			$from = $_POST['email'];
			$subject = $_POST['subject'];
			$header = 'From: '.$from . "\r\n" .
					 'Reply-To: '.$from . "\r\n" .
					 'X-Mailer: PHP/' . phpversion();
			$dest = $EmailAddress;
			$message = str_replace("\r\n","\n",$_POST['message']);
			
			if(mail($dest,$subject,$message,$header))
			{
				$notif = "Votre email à été envoyé";
				$displayForm = false;
			}
			else
				$notif = "Une erreur s'est produite lors de l'envoi du message";
		}
	else
		$notif = "Veuillez remplir tout les champs";


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
			if(isset($notif))
				if($dspForm == true)
					echo '<span class="warning">'.$notif.'</span>';
				else
					echo '<span class="notifOK">'.$notif.'</span>';
			
			if($dspForm == true)
			{
				?>
				<form action="contact.php" method="POST">
					<table class="formTbl1">
						<tr>
							<td>
								<label for="email">Votre e-mail : </label>
							</td>
							<td>
								<input type="text" id="email" name="email" />
							</td>
						</tr>
						<tr>
							<td>
								<label for="sujet">Objet du mail : </label>
							</td>
							<td>
								<input type="text" id="sujet" name="sujet" />
							</td>
						</tr>
						<tr>
							<td>
								<label for="message">Votre message : </label>
							</td>
							<td>
								<textarea class="txMessage" id="message" name="message"></textarea>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<span class="btSubmit"><input type="submit" value="Envoyer le message" /></span>
							</td>
						</tr>
					</table>
				</form>
				<?php
			}
		</div>
		<div class="footer">
			<?php include("ressources/footer.html"); ?>
		</div>
	</body>
</html><?php

?>
