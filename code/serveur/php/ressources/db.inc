<?php

$getDBSingleton = null;

function getDB() {
	global $getDBSingleton;
	
	if (!$getDBSingleton) {
		date_default_timezone_set('Europe/Paris');
		$SQL_DBNAME = (dirname(__FILE__) . "/../db"); // .. parce que db.inc est dans un sous-dossier.
		if (!$getDBSingleton = new SQlite3($SQL_DBNAME)) {
			throw new Exception("Erreur lors de l'ouverture de la base de données SQLite3", 1);
		}
	}
	return $getDBSingleton;
}

function closeDB() {
	global $getDBSingleton;

	if ($getDBSingleton) $getDBSingleton->close();
}

?>