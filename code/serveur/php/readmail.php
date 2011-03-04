<?php
/* Cette page permet d'afficher les messages postés depuis le formulaire du site.
* Pour le moment l'identification se fait par le login : admin et le mot de passe : admin.
*/

// TODO Voir si on rajoute dans la base de donnée un champ permettant de distinguer un type d'utilisateurs particulier qui pourrait accéder au différentes pages de "gestion" comme celle-ci. Ou si on reste sur une compte unique d'administration.
	
session_start();
	
if(isset($_POST['login']))
	$login = $_POST['login'];
if(isset($_POST['passwd']))
	$pass = $_POST['passwd'];

if(isset($_GET['d']))
	session_destroy();

if(isset($login) && isset($pass) && $login == "admin" && $pass == "admin")
	$_SESSION['adminAuth'] = true;

function affiche_messages() {
	$fileName = "mails.txt";

	// Lecture et affichage du la totalité du fichier.
	readfile($fileName);
}

if(!isset($_SESSION['adminAuth']) || $_SESSION['adminAuth'] != true) {			// Affichage du formulaire d'authentification.
	echo '<form action="readmail.php" method="POST">';
	echo '<label for="login">login : </label><input type="text" name="login" /><br />';
	echo '<label for="passwd">mdp : </label><input type="password" name="passwd" /><br />';
	echo '<input type="submit" value="Suivant" />';
}
else {																			// Affichage des messages.
	header("Content-Type: text/plain");
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');

	affiche_messages();	
}

?>
