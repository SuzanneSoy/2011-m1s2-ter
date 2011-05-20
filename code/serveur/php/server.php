<?php
session_start();
require_once("ressources/backend.inc");
require_once("ressources/db.inc");

/** Ecrit un rapport d'erreur dans un fichier.
* @param errNum : Numéro de l'erreur.
* @param msg : Description de l'erreur.
* @param [other] : (Optionnel) Complément d'information.
*/
function logError($errNum, $msg, $other="")
{
	$file = fopen("./log.txt","a+");

	// Met en forme la chaine contenant les paramètres de la requête.
	$dumpParameters = str_replace("(\n","",print_r($_GET,true));
	$dumpParameters = str_replace(")\n","",$dumpParameters);

	// Ecriture dans le fichier.
	fwrite($file,"\nErreur n° ".$errNum);
	fwrite($file," : ".$msg);
	if(!empty($other))	
		fwrite($file,"\n ".$other);
	fwrite($file,"\n  ".$dumpParameters);

	fclose($file);
}

/** La fonction principale.
* @param action : Un identifiant d'action.
*/
function main()
{
	$loginIsOk = false;
	$user = 'nobody';
	if(!isset($_GET['action'])) {
		throw new Exception("La requête est incomplète.", 2);
	}
	if(isset($_GET['user']) && isset($_GET['passwd'])) {
		unset($_SESSION['userId']);
		$user = SQLite3::escapeString($_GET['user']);
		$loginIsOk = connect($user, $_GET['passwd']);
		if ($loginIsOk) {
			$_SESSION['userId'] = $user;
		} else {
			throw new Exception("Utilisateur non enregistré ou mauvais mot de passe.", 3);
		}
	} elseif(isset($_SESSION['userId'])) {
		$user = $_SESSION['userId'];
		$loginIsOk = true;
	}
	
	$action = $_GET['action'];
	
	if ($action != 3 && (!$loginIsOk))
		throw new Exception("Vous n'êtes pas connecté.", 10);

	if ($action == 3) {
		echo json_encode(
			Array(
				"loginOk" => !!$loginIsOk,
				"whoami" => "".$user
			)
		);
		return;
	}
	
	if  ($action == 2) {                // "Create partie"
		// Requête POST : http://serveur/server.php?action=2&nb=2&mode=normal&user=foo&passwd=bar
		if (!isset($_GET['nb']) || !isset($_GET['mode']))
			throw new Exception("La requête est incomplète", 2);

		createGame(intval($_GET['nb']), $_GET['mode']);
		echo '{"success":1}';
	}
	else if($action == 0) {           // "Get partie"
		// Requête POST : http://serveur/server.php?action=0&user=foo&passwd=bar
		echo game2json($user, isset($_GET['pgid']) ? $_GET['pgid'] : null);
	}
	else if($action == 1) {				// "Set partie"
		// Requête POST : http://serveur/server.php?action=1&mode=normal&user=foo&passwd=bar&gid=1234&pgid=12357&0=0&1=-1&2=22&3=13&9=-1
		if (!isset($_GET['pgid']) || !isset($_GET['gid']) || !isset($_GET['answers']))
			throw new Exception("La requête est incomplète", 2);

		setGameGetScore($user, $_GET['pgid'], $_GET['gid'], $_GET['answers']);
	}
	else if($action == 4) {           // CheckWord
		if (!isset($_GET['word']))
			throw new Exception("La requête est incomplète", 2);

		if(wordExist($_GET['word']))
			echo JSON_encode(true);
		else
			echo JSON_encode(false);
	}
	else if($action == 5) {           	// Get relations (JSON)
		echo getGameRelations();
	}
	else if($action == 6) {
		if (!isset($_GET['game']))
			throw new Exception("La requête est incomplète", 2);
		
		decodeAndInsertGame($user,$_GET['game']);
	}
	elseif ($action == 7) {         	// Get user prefs
		userPrefs($user);
	}
	elseif ($action == 8) {         	// Set user pref
		if (!isset($_GET['key']) || !isset($_GET['value']))
			throw new Exception("La requête est incomplète", 2);
			
		setUserPref($user, $_GET['key'], $_GET['value']);
		userPrefs($user);
	}
	else {
		throw new Exception("Commande inconnue", 2);
	}
}

function server() {
	if(isset($_GET['callback'])) {
		echo $_GET['callback'].'(';
		header("Content-Type: application/javascript; charset=utf-8");
	} else {
		header("Content-Type: application/json; charset=utf-8");
	}
	
	ob_start();
	
	try {
		main();
		ob_end_flush();
	} catch (Exception $e) {
		ob_end_clean();
		$code = $e->getCode();
		$msg = $e->getMessage();
		if ($code != 10 && $code != 3) $msg = "Erreur ".$code." : " . $msg;
		echo json_encode(
			Array(
				"error" => $code,
				"msg" => $msg,
				"isError" => true
			)
		);
		
		logError($e->getCode(), $e->getMessage(), date("c"));
		closeDB();
	}
	
	if(isset($_GET['callback']))
		echo ')';
}

server();

?>
