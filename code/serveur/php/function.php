<?php

/**Ce fichier définit un certain nombre de fonctions utiles */

// Connexion à la base de données.
function sqlConnect()
{
	global $sql_serveur, $sql_login, $sql_pass, $sql_bdd;
	//connexion au serveur
	$linkid = @mysql_connect($sql_serveur,$sql_login,$sql_pass) or die ("Erreur lors de la connection au serveur MySQL !");
	//selection de la base
	@mysql_select_db($sql_bdd,$linkid) or die("Impossible de selectionner la base de données\n<br>\nVoici l'erreur renvoyée par le serveur MySQL :\n<br>\n".mysql_error());
	
	return $linkid;
}

function secure($string)
{
	if(ctype_digit($string))
	{
		$string = intval($string);
	}
	else
	{
		$string = mysql_real_escape_string($string);
		$string = addcslashes($string, '%_');
	}
	
	return $string;
}

// TODO Yoann : fonction qui échappe les "
function escape_json_string($str) {
	return $str;
}

function mDie($err,$msg)
{
	echo "{ error:\"".escape_json_string($err)."\", msg:\"".escape_json_string($msg)."\"}";
	exit(1);
}

function writeRequest($request)
{
	
}
?>