<?php
session_start();

$displayForm = true;
$emailaddress = "";
$mailfile = "mails.txt";

function writemail($filename,$email,$subject,$message)
{
	$file = fopen($filename,"a+");
	
	if($file != -1) {
		fprintf($file,"%s\n%s\n%s\n\n",$email,$subject,$message);
	}
	else
		die("Erreur lors de l'ouverture du fichier d'enregistrement de mails");

	fclose($file);
}


if(isset($_POST['email']) && isset($_POST['subject']) && isset($_POST['message']))
	if(!empty($_POST['email']) && !empty($_POST['subject']) && !empty($_POST['message']))
	{
			$from = $_POST['email'];
			$subject = $_POST['subject'];
			$header = 'From: '.$from . "\r\n" .
					 'Reply-To: '.$from . "\r\n" .
					 'X-Mailer: PHP/' . phpversion();
			$dest = $emailaddress;
			$message = str_replace("\r\n","\n",$_POST['message']);
			
			writemail($mailfile,$from,$subject,$message);

			/*if(mail($dest,$subject,$message,$header))
			{
				$notif = "Votre email à été envoyé";
				$displayForm = false;
			}
			else
				$notif = "Une erreur s'est produite lors de l'envoi du message";*/

			$notif = "Votre email à été envoyé";
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
			<?php			
			if(isset($notif))
				if($displayForm == true)
					echo '<span class="message warning">'.$notif.'</span>';
				else
					echo '<span class="message success">'.$notif.'</span>';
			
			if($displayForm == true)
			{
				?>
				<form action="contact.php" method="POST">
					<table class="contacttbl">
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
								<label for="subject">Objet du mail : </label>
							</td>
							<td>
								<input type="text" id="subject" name="subject" />
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
			?>
		</div>
		<div class="footer">
			<?php include("ressources/footer.html"); ?>
		</div>
	</body>
</html><?php

?>
