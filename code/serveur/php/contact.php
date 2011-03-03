<?php
require_once("ressources/strings.inc");
session_start();

$displayForm = true;
$emailaddress = "";
$mailfile = "mails.txt";
$msg = null;

function writemail($filename,$email,$subject,$message)
{
	$file = fopen($filename,"a+");
	
	if($file != -1) {
		fprintf($file,"%s\n%s\n%s\n\n",$email,$subject,$message);
	}
	else
		die($strings['err_contact_open_mailfile']);

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
			$msg = $strings['ok_msg_sent'];
			$displayForm = false;
		}
		else
			$msg = "Une erreur s'est produite lors de l'envoi du message";*/
		$msg = $strings['ok_msg_sent'];
	}
	else
		$msg = $strings['err_contact_fill_all'];


?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="fr" lang="fr">
	<head>
		<title>PtiClic sous Android™ - Version Alpha - Contact</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />		
		<link rel="stylesheet" href="ressources/simple.css" />
	</head>
	<body>
		<?php include("ressources/menu.inc"); ?>
		<div class="content contact">
			<?php include("ressources/showmsg.inc"); ?>

			<h2>Contact</h2>

			<?php			
				if($msg != null)
					if($displayForm == true)
						echo '<span class="message warning">'.htmlspecialchars($msg).'</span>';
					else
						echo '<span class="message success">'.htmlspecialchars($msg).'</span>';
				
				if($displayForm == true)
				{ // Fin sous le <form> ci-dessous
			?>
			<p>
				Vous souhaitez signaler un défaut dans l'application, ou bien vous avez des remarques, des suggestions ?<br />
				Faites-nous-en part en nous envoyant un message&nbsp;:
			</p>
			<form action="contact.php" method="POST">
				<table>
					<tr>
						<td>
							<label for="email">Votre mail&nbsp;: </label>
						</td>
						<td>
							<input type="text" id="email" name="email" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="subject">Objet&nbsp;: </label>
						</td>
						<td>
							<input type="text" id="subject" name="subject" />
						</td>
					</tr>
					<tr>
						<td>
							<label for="message">Message&nbsp;: </label>
						</td>
						<td>
							<textarea class="txMessage" id="message" name="message"></textarea>
						</td>
					</tr>
					<tr>
						<td>
							
						</td>							
						<td>
							<span class="btSubmit"><input type="submit" value="Envoyer le message" /></span>
						</td>
					</tr>
				</table>
			</form>
			<?php
				} // Fin de if($displayForm == true)
			?>
		</div>
		<?php include("ressources/footer.inc"); ?>
	</body>
</html>
