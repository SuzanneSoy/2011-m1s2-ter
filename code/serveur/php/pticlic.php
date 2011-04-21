<?php

require_once("relations.php");
require_once("db.php");
require_once("ressources/errors.inc");

/* Les prototypes des fonctions :
* ===============================>
*   checkLogin($user, $passwd);
*   randomGame();
*   createGame($nbParties, $mode);
*
*   cgBuildResultSets($cloudSize, $centerEid, $r1, $r2);
*   cgChooseRelations();
*   cgBuildCloud($centerEid, $cloudSize, $sources, $sumWeights);
*   cgInsert($centerEid, $cloud, $r1, $r2, $totalDifficulty);
*   game2json($user, $gameId);
*   game2array($user, $gameId);
*   createGameCore($cloudSize);
*   getGame($user, $nbGames, $mode);
*   computeScore($probas, $difficulty, $answer, $userReputation);
*   computeUserReputation($score);
*   normalizeProbas($row);
*   setGame($user, $pgid, $gid, $answers);
*   get_game_relations();
	getGameRelationsJSON();
*   setGameGetScore($user, $pgid, $gid, $answers);
*   insertNode($node);
*   getNodeEid($node);
*/


/**  Vérifie la validité du couple nom d'utilisateur / mot de passe.
* @param user : Le nom d'utilisateur.
* @param passwd : Le mot de passe.
* @return boolean : true si OK sinon false.
*/
function checkLogin($user, $passwd)
{
	return md5($passwd) == sqlGetPasswd($user);
}

/** Création de plusieurs parties.
* @param nbParties : le nombre de parties à créer.
* @param mode : Le mode de jeu pour les parties à créer.
* @return gid : Le gid de la dernière partie générée.
*/
function createGame($nbParties)
{
	for ($i = 0; $i < $nbParties; $i++)
		$gid = createGameCore(10);
	return $gid;
}

/** Sélection aléatoire d'une partie de la base de données parmis les parties à jouer.
* @return gid : Identifiant de la partie selectionnée.
*/
function randomGame()
{
	$gid = sqlGetRandomGID();
	return ($gid !== null) ? $gid : createGame(100);
}

/* ========================================================================== */

/** Sélectionne aléatoirement deux relations.
* @return array : Tableau avec la relation 1 et la relation 2.
*/
function cgChooseRelations()
{
	$relations = get_game_relations();
	$r1 = rand(0,count($relations)-1);
	$r2 = rand(0,count($relations)-2);

	if ($r2 >= $r1)
		$r2++;

	return array($relations[$r1], $relations[$r2]);
}

/*
[
	{
		"gid":22,
		"pgid":512,
		"relations":[
			{"id":10,"name":"%mc fait partie de %mn"},
			{"id":9,"name":"%mn est une partie de %mc"},
			{"id":0,"name":"%mc est en rapport avec %mn"},
			{"id":-1,"name":"%mn n'est pas lié à %mc"}
		],
		"center":{"id":28282,"name":"transbahuter"},
		"cloud":[
				{"id":84632,"name":"camion"},
				{"id":61939,"name":"transbahutage"},
				{"id":104263,"name":"trimbaler"},
				{"id":44654,"name":"transporter"},
				{"id":38285,"name":"d\u00e9m\u00e9nageur"},
				{"id":43404,"name":"porter"},
				{"id":63192,"name":"transports"},
				{"id":130473,"name":"enthousiasmer"},
				{"id":90461,"name":"se trimbaler"},
				{"id":134609,"name":"baguenauder"}
		]
	}
]
*/

// TODO : Comment je nettoie le nuage ????%ejrqdguiosj
function resetCloud($eidCentralWord) {
	querySingle("delete from cloud where eid_central_word = ".$eidCentralWord.";");
	$cloud2 = sqlCloud2();
	foreach ($cloud2 as $word) {
		"insert into cloud values(…);";
	}
}

/* Pour créer un nuage :
 * Select tous les mots à 1 ou 2 (ou 3) de distance // sqlCloud1(), sqlCloud2()
 * // While sur les résultats
 *   Pour chacun, on cherche la chaîne de relations dans les triangles ou les path4. // select * from triangles where TA = $TA and TB = $TB;
 *   On en déduit l'existance ou non d'un lien pour chaque colonne (5, 7, 9, 10, 13, 14, 22), avec un certain poids // Boucle while sur les résultats
 *   insert into clouds(eidMotCentral, eidMotNuage, rel5, rel7, rel9, rel10, rel13, rel14, rel22) values(…);
 */

function createCloud($centerEID) {
	$cloud2 = sqlCloud2($centerEID);
	$guess = array();
	foreach ($cloud2 as &$word) {
		$node = intval($word["node"]);
		$res = queryMultiple("select TDeduction,round(weight/(0.0+total),3) as weight from guessTransitivity2 where TA = ".$word["TA"]." and TB = ".$word["TB"]);
		if (!isset($guess[$node])) $guess[$node] = array();
		foreach ($res as $r) {
			@$guess[$node][$r["TDeduction"]] += $r["weight"];
		}
	}
	foreach ($guess as $_node => $weights) {
		$q = "insert into clouds(eidCentralWord, eidCloudWord, rel5, rel7, rel9, rel10, rel13, rel14, rel22) values(".intval($centerEID).",".intval($_node);
		$q .= ",".(0+@$weights[5]);
		$q .= ",".(0+@$weights[7]);
		$q .= ",".(0+@$weights[9]);
		$q .= ",".(0+@$weights[10]);
		$q .= ",".(0+@$weights[13]);
		$q .= ",".(0+@$weights[14]);
		$q .= ",".(0+@$weights[22]);
		$q .= ");";
		querySingle("delete from clouds where eidCloudWord = ".intval($centerEID).";");
		querySingle($q);
	}
	return count($guess);
}

/* Pour créer une partie (motCentral, nbWords, relation1..4) :
 * On select nbWords rangées aléatoirement dans clouds avec le motCentral, et sur les colonnes des relations
 * insert into game(gid, creator, motCentral, nbWords, relation1..4)
 * insert into game_cloud(gid, eidMotNuage) select …random…;
 */

/* Pour insérer une partie (motCentral, relation1..4, eidMotsNuage) :
 * insert into game(gid, creator, motCentral, nbWords, relation1..4)
 * insert into game_cloud(gid, eidMotNuage) …;
 */

/* Pour afficher une partie(gid) :
 * On l'insère dans played_games
 * On select le cloud dans game_cloud
 * On select les infos dans game
 * On renvoie : {gid, pgid, relations : [{id, name}], center, cloud : [{id,name}]}
 */

/* Pour enregistrer les résultats d'un joueur :
 * On met à jour le timestamp dans played_game
 * On stocke les réponses dans played_game_cloud(pgid, cloudId, catAnswer);
 */

/** Récupère une partie sous forme de tableau associatif.
* @param db : descripteur de la bdd (obtenu avec getDB()).
* @param user : Login de l'utilisateur demandant la partie.
* @param gameId : L'identifiant d'une partie.
*/
function game2array($user, $gameId)
{
	$db = getDB();
	// TODO : planter si la requête suivante échoue pour quelque raison que ce soit.
	$db->exec("INSERT INTO played_game(pgid, gid, login, timestamp) VALUES (null, ".$gameId.", '$user', -1);");
	$pgid = $db->lastInsertRowID();
	
	// TODO Yoann : faire des tests d'erreur pour ces select ?
	$game = sqlGetGameInfo($gameId);

	$ret = array();
	$ret['gid'] = $gameId;
	$ret['pgid'] = $pgid;
	$ret['cat1'] = $game['relation_1'];
	$ret['cat2'] = $game['relation_2'];
	$ret['cat3'] = 0;
	$ret['cat4'] = -1;
	$ret['center'] = array('id' => $game['eid_central_word'], 'name' => sqlGetNodeName($game['eid_central_word']));
	$ret['cloud'] = array(); // TODO ! compter dynamiquement.
	
	$res = $db->query(sqlGetCloudInfo($gameId));
	
	while ($x = $res->fetchArray())
	{
		$ret['cloud'][$x['num']] = array(
			'id' => $x['eid_word'],
			'name' => sqlGetNodeName($x['eid_word']),
			'difficulty' => $x['difficulty'],
			'totalWeight' => $x['totalWeight'],
			'probaR1' => $x['probaR1'],
			'probaR2' => $x['probaR2'],
			'probaR0' => $x['probaR0'],
			'probaTrash' => $x['probaTrash'],
			'probas' => normalizeProbas($x)
		);
	}
	
	$ret['cloudsize'] = count($ret['cloud']);
	return $ret;
}


/** Génère une partie (mode normal pour l'instant) pour une certaine taille de nuage.
 * @param cloudSize : Taille du nuage.
 * @return gid : Le gid de la partie créée.
 */
function createGameCore($cloudSize)
{
	// select random node
	$centerEid = sqlGetEIDCenterNode();

	$r1 = cgChooseRelations(); $r2 = $r1[1]; $r1 = $r1[0];
	$sources = cgBuildResultSets($cloudSize, $centerEid, $r1, $r2); $sumWeights = $sources[1]; $sources = $sources[0];
	$cloud = cgBuildCloud($centerEid, $cloudSize, $sources, $sumWeights); $totalDifficulty = $cloud[1]; $cloud = $cloud[0];
	return cgInsert($centerEid, $cloud, $r1, $r2, $totalDifficulty);
}

/** Récupération d'une partie.
* @param user : L'identifiant de l'utilisateur.
* @param nbGames : Le nombre de parties à récupérer.
* @param mode : Le mode de jeu des parties à récupérer.
*/
function getGame($user, $nbGames, $mode)
{
	echo "[";

	for ($i=0; $i < $nbGames; $i)
	{
		echo game2json($user, randomGame());

		if ((++$i) < $nbGames)
			echo ",";
	}
	
	echo "]";
}


function computeScore($probas, $difficulty, $answer, $userReputation) {
	// Calcul du score. Score = proba[réponse de l'utilisateur]*coeff1 - proba[autres reponses]*coeff2 + bonus
	// score = - proba[autres reponses]*coeff2
	// On aura donc -5 <= score <= 0
	$score = -5 * (($probas[0] + $probas[1] + $probas[2] + $probas[3]) - $probas[$answer]);
	
	// score = proba[réponse de l'utilisateur]*coeff1 - proba[autres reponses]*coeff2
	// On aura donc -5 <= score <= 10
	$score += 10 * $probas[$answer];
	
	// On est indulgent si la réponse est 3 (poubelle) :
	if ($answer == 3 && $score < 0) {
		$score = $score / 2;
	}
	
	// Adapter le score en fonction de la réputation de l'utilisateur (quand il est jeune, augmenter le score pour le motiver).
	// On aura donc -5 <= score <= 15
	if ($score > 3) {
		$score += max(0, min(5, 5 - $userReputation));
	}
	
	return round($score);
}

/** Calcul de la réputation de l'utilisateur selon son score.
* @param score : Le score du joueur.
*/
function computeUserReputation($score) {
	return max(round(log($score/10)*100)/100, 0);
}

/** Formatage des probalitées dans un tableau.
* @param row : le vecteur de probabilités.
* @return array : Le vecteur de probabilités normalisé.
*/
function normalizeProbas($row) {
	return array($row['probaR1']/$row['totalWeight'], $row['probaR2']/$row['totalWeight'], $row['probaR0']/$row['totalWeight'], $row['probaTrash']/$row['totalWeight']);
}

/** Insertion des données d'une partie joué par un utilisateur.
* @param user : L'identifiant de l'utilisateur ayant joué à la partie.
* @param pgid : L'identifiant de la partie jouée.
* @param gid : L'identifiant du jeu auquel la partie appartient.
* @return score : Le score réalisé par le joueur.
*/
function setGame($user, $pgid, $gid, $answers)
{
	$db = getDB();
	if (sqlGameIsOK($pgid, $gid, $user)) {
		return getGameScores($user, $pgid, $gid);
	}
	
	$userReputation = computeUserReputation(sqlGetUserScore($user));
	
	$db->exec("begin transaction;");
	$db->exec("update played_game set timestamp = ".time()." where pgid = $pgid;");

	$r0 = 0;
	$trash = -1;
	$r1 = $db->querySingle("SELECT relation_1, relation_2 FROM game WHERE gid = $gid;", true);
	$r2 = $r1['relation_2'];
	$r1 = $r1['relation_1'];
	$res = $db->query("SELECT num, difficulty, totalWeight, probaR1, probaR2, probaR0, probaTrash FROM game_cloud WHERE gid = $gid;");
	$gameScore = 0;
	$scores = array();
	$nbScores = 0;
	
	while ($row = $res->fetchArray())
	{
		$num = intval($row['num']);
		$nbScores++;
		if (!isset($answers[$num])) {
			errSetPartiNoRelation($num);
		}
		$relanswer = intval($answers[$num]);

		switch ($relanswer) 
		{
			case $r1:    $answer = 0; $probaRx = "probaR1"; break;
			case $r2:    $answer = 1; $probaRx = "probaR2"; break;
			case $r0:    $answer = 2; $probaRx = "probaR0"; break;
			case $trash: $answer = 3; $probaRx = "probaTrash"; break;
			default:     errAnswerInvalidForWord($r1, $r2, $r0, $trash);
		}
			
		$wordScore = computeScore(normalizeProbas($row), $row['difficulty'], $answer, $userReputation);
		$gameScore += $wordScore;
		$scores[$num] = $wordScore;
		
		$db->exec("insert into played_game_cloud(pgid, gid, type, num, relation, weight, score) values($pgid, $gid, 1, $num, $r1, ".$userReputation.", ".$wordScore.");");
		$db->exec("update game_cloud set $probaRx = $probaRx + ".max(min($userReputation/2,5),0)." where gid = $gid and num = $num;");
		$db->exec("update game_cloud set totalWeight = totalWeight + ".max(min($userReputation/2,5),0)." where gid = $gid and num = $num;");
	}
	$db->exec("update user set score = score + ".$gameScore." where login = '$user';");

	$db->exec("commit;");
	$scores['total'] = $gameScore;
	$scores['nb'] = $nbScores;
	$scores['alreadyPlayed'] = 'false';
	return $scores;
}

function getGameScores($user, $pgid, $gid) {
	$db = getDB();
	$timestamp = sqlGetPlayedGameTime($pgid, $gid, $user);
	if ($timestamp == -1) {
		errGameNeverPlayed();
	} else if ($timestamp == null) {
		errGameNotAssociatedWithUser();
	}
	
	$gameScore = 0;
	$scores = array();
	$nbScores = 0;
	$res = $db->query(sqlGetPlayedCloudScores($pgid, $gid));
	while ($row = $res->fetchArray())
	{
		$nbScores++;
		$gameScore += $row['score'];
		$scores[$row['num']] = $row['score'];
	}
	$scores['total'] = $gameScore;
	$scores['nb'] = $nbScores;
	$scores['alreadyPlayed'] = 'true';
	return $scores;
}

/** Fourni l'ensembles des relations pouvant apparaître dans le jeu.
* @return array : un tableau de realtions.
*/
function get_game_relations() {
	return array(5, 7, 9, 10, 13, 14, 22); /* Pas d'icônes pour celles-ci : 13, 14, 22 */
}

function getGameRelationsJSON() {
	$json = "{";
	global $stringRelations;
	
	foreach($stringRelations as $id=>$description)
		if($id == -1)
			$json .= '"'.$id.'":"'.$description.'"';
		else
			$json .= ',"'.$id.'":"'.$description.'"';
			
	$json .= '}';
	
	return $json;
}

function setGameGetScore($user, $pgid, $gid, $answers) {
	$scores = setGame($user, intval($pgid), intval($gid), $answers);
	// On renvoie une nouvelle partie pour garder le client toujours bien alimenté.
	echo '{"scoreTotal":'.$scores['total'];
	echo ',"alreadyPlayed":'.$scores['alreadyPlayed'];
	echo ',"scores":[';
	for ($i = 0; $i < $scores['nb']; $i++) {
		if ($i != 0) echo ',';
		echo $scores[$i];
	}
	echo "],\"newGame\":";
	echo json_encode("".game2json($user, randomGame()));
	echo "}";
}

/** Insère dans la base de données le noeud si il n'existe pas encore.
* @param node : le noeud à ajouter.
*/
function insertNode($node) {
	$db = getDB();
	
	if($db->querySingle(sqlGetEidFromNode($node)) == null) {
		$db->exec("INSERT INTO node(name,type,weight) VALUES('".SQLite3::escapeString($node)."',1,50);");
		return true;
	}

	return false;
}


/** retourne l'eid d'un mot.
* @param node : le mot dont on veut obtenir l'eid.
*/

function getNodeEid($node) {
	$db = getDB();

	return $db->querySingle(sqlGetEidFromNode($node));
}

function wordExist($node) {
	$db = getDB();

	return $db->querySingle("SELECT eid FROM node WHERE name='".SQLite3::escapeString($node)."';") ? true : false;
}
?>
