<?php

require_once("relations.php");
require_once("db.php");
require_once("ressources/errors.inc")

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

/**  Construit les sets de résultats qui serviront à la construction du nuage.
* @param cloudSize : Taille du nuage.
* @param centerEid : Identifiant du mot central.
* @param r1 Type de la relation 1.
* @param r2 Type de la relation 2.
*/
function cgBuildResultSets($cloudSize, $centerEid, $r1, $r2)
{
	$db = getDB();
	// 'w' => weight (poids), 'd' => difficulté, 's' => select
	// Le select doit ranvoyer trois colonnes :
	//   eid => l'eid du mot à mettre dans le nuage,
	//   r1 => la probabilité pour que le mot soit dans r1, entre -1 et 1 (négatif = ne devrait pas y être, positif = devrait y être à coup sûr, 0 = on sait pas).
	
	$sources = array(
		array('w'=>40, 'd'=>1, 's'=>sql1JumpGoodType($r1, $r2, $centerEid)),
		array('w'=>40, 'd'=>2, 's'=>sql1JumpViaRAssociated0($centerEid)),
		array('w'=>20, 'd'=>3.1, 's'=>sql1JumpViaOtherRelation($centerEid, $r1, $r2, $banned_types)),
		array('w'=>30, 'd'=>3.2, 's'=>sql2JumpWithMixR1R2ForLinks($r1, $r2, $centerEid)),
		array('w'=>20, 'd'=>5, 's'=>sql1JumpR1DivR2Plus1JumpSynonymOneHopWithType($r1, $r2, $centerEid)),
		array('w'=>20, 'd'=>6, 's'=>sql1JumpR1DivR2Plus1JumpSynonym($r1, $r2, $centerEid)),
		array('w'=>10, 'd'=>8, 's'=>sql2JumpAll($centerEid, $cloudSize)),
		array('w'=>10, 'd'=>8, 's'=>sqlXPointsToMMPointsToXTakeM($cloudSize)),
		'rand' => array('w'=>5, 'd'=>10, 's'=>false) // random. Le r1 et r2 de random sont juste en-dessous
	);

	$sumWeights = 0;
	
	foreach ($sources as $k => $x)
	{
		$sumWeights += $x['w'];
		$sources[$k]['rsPos'] = 0;
		$sources[$k]['rsSize'] = 0;
		
		if ($x['s'] !== false)
		{
			$sources[$k]['resultSet'] = array();
			$res = $db->query($x['s']);
			$i = 0;
			
			while ($i < 10 && $sources[$k]['resultSet'][] = $res->fetchArray())
			{
				$i++;
				$sources[$k]['rsSize']++;
			}
		} 
		else
		{
			$sources[$k]['resultSet'] = array();
			
			for ($i = 0; $i < 10; $i++)
			{
				$sources[$k]['resultSet'][] = array('eid'=>sqlGetRandomCloudNode(), 'r1'=>0, 'r2'=>0, 'r0'=>0, 'trash'=>1);
				$sources[$k]['rsSize']++;
			}
		}
	}

	return array($sources, $sumWeights);
}


/** Sélectionne aléatoirement deux relations.
* @return array : Tableau avec la relation 1 et la relation 2.
*/
function cgChooseRelations()
{
	$relations = array(5, 7, 9, 10);// /* Pas d'icônes pour celles-ci. */ 13, 14, 22);
	$r1 = rand(0,count($relations)-1);
	$r2 = rand(0,count($relations)-2);

	if ($r2 >= $r1)
		$r2++;

	$r1 = $relations[$r1];
	$r2 = $relations[$r2];

	return array($r1, $r2);
}

/** Génération d'un nuage pour un mot central.
* @param cloudSize : Taille du nuage.
* @param sources Les sources.
* @param sumWeights La somme des poids.
* @return array : Tableau avec comme premier élément le nuage et comme second élément le total de difficulté.
*/
function cgBuildCloud($centerEid, $cloudSize, $sources, $sumWeights)
{
	$db = getDB();
	// On boucle tant qu'il n'y a pas eu au moins 2 sources épuisées
	$cloud = array();
	$nbFailed = 0;
	$i = 0;
	$totalDifficulty = 0;
	
	while ($i < $cloudSize && $nbFailed < 10*$cloudSize)
	{
		// On choisit une source aléatoire en tennant compte des poids.
		$rands = rand(1,$sumWeights);
		$sumw = 0;
		if (!isset($sources['rand'])) {
			break;
		}
		$src = $sources['rand'];
		$srck = 'rand';

		// Sélection d'une source aléatoire
		foreach ($sources as $k => $x)
		{
			$sumw += $x['w'];
			
			if ($rands < $sumw)
			{
				$src = $x;
				$srck = $k;
				break;
			}
		}
		
		// Vérification si on est à la fin du ResultSet de cette source.
		if ($src['rsPos'] >= $src['rsSize'])
		{
			$nbFailed++;

			$sumWeights -= $src['w'];
			unset($sources[$srck]);

			continue;
		}
		
		// On récupère un résultat de cette source.
		$res = $src['resultSet'][$src['rsPos']];
		$sources[$srck]['rsPos']++;

		// On vérifie si le mot n'a pas déjà été sélectionné.
		$rejected = false;
		// Ne pas mettre le mot central dans le nuage.
		if ($res['eid'] == $centerEid) { continue; }
		$nodeName = sqlGetRawNodeName($res['eid']);
		if (substr($nodeName, 0, 2) == "::") { continue; }
		foreach ($cloud as $c) {
			if ($c['eid'] == $res['eid']) {
				$nbFailed++;
				$rejected = true;
				break;
			}
		}
		if ($rejected) { continue; }

		// position dans le nuage, difficulté, eid, probaR1, probaR2
		$totalDifficulty += $src['d'];
		$cloud[$i] = array('pos'=>$i++, 'd'=>$src['d'], 'eid'=>$res['eid'], 'probaR1'=>$res['r1'], 'probaR2'=>$res['r2'], 'probaR0'=>$res['r0'], 'probaTrash'=>$res['trash']);
	}

	$res = $sources['rand']['resultSet'][0];
	
	while ($i < $cloudSize)
	{
		$totalDifficulty += $sources['rand']['d'];
		$cloud[$i] = array('pos'=>$i++, 'd'=>$sources['rand']['d'], 'eid'=>sqlGetRandomCloudNode(), 'probaR1'=>$res['r1'], 'probaR2'=>$res['r2'], 'probaR0'=>$res['r0'], 'probaTrash'=>$res['trash']);
	}

	return array($cloud, $totalDifficulty);
}


/** Insère la partie dans la base de données.
* @param centerEid : Identifiant du mot central.
* @param cloud : Le nuage.
* @param r1 : Le type de la relation 1.
* @param r2 : Le type de la relation 2.
* @param totalDifficulty : La difficulté totale.
* @return gid : le gid de la partie insérée.
*/
function cgInsert($centerEid, $cloud, $r1, $r2, $totalDifficulty)
{
	$db = getDB();
	// Insère dans la base une partie avec le mot central $centerEid, le nuage $cloud et les relations $r1 et $r2
	$db->exec("begin transaction;");
	$db->exec("INSERT INTO game(gid, eid_central_word, relation_1, relation_2, difficulty)
		   VALUES (null, $centerEid, $r1, $r2, $totalDifficulty);");
	$gid = $db->lastInsertRowID();
	
	$t = time();
	$db->exec("INSERT INTO played_game(pgid, gid, login, timestamp)
		   VALUES (null, $gid, null, $t);");
	$pgid1 = $db->lastInsertRowID();
	$db->exec("INSERT INTO played_game(pgid, gid, login, timestamp)
		   VALUES (null, $gid, null, $t);");
	$pgid2 = $db->lastInsertRowID();
	$db->exec("INSERT INTO played_game(pgid, gid, login, timestamp)
		   VALUES (null, $gid, null, $t);");
	$pgid0 = $db->lastInsertRowID();
	$db->exec("INSERT INTO played_game(pgid, gid, login, timestamp)
		   VALUES (null, $gid, null, $t);");
	$pgidT = $db->lastInsertRowID();

	// TODO : R0 et Trash + corrections
	foreach ($cloud as $c)
	{
		$totalWeight = $c['probaR1'] + $c['probaR2'] + $c['probaR0'] + $c['probaTrash'];
		$db->exec("INSERT INTO game_cloud(gid, num, difficulty, eid_word, totalWeight, probaR1, probaR2, probaR0, probaTrash)
			   VALUES ($gid, ".$c['pos'].", ".$c['d'].", ".$c['eid'].", $totalWeight, ".$c['probaR1'].", ".$c['probaR2'].", ".$c['probaR0'].", ".$c['probaTrash'].");");
		
		$db->exec("INSERT INTO played_game_cloud(pgid, gid, type, num, relation, weight, score)
			   VALUES ($pgid1, $gid, 0, ".$c['pos'].", $r1, ".$c['probaR1'].", 0);");

		$db->exec("INSERT INTO played_game_cloud(pgid, gid, type, num, relation, weight, score)
			   VALUES ($pgid2, $gid, 0, ".$c['pos'].", $r2, ".$c['probaR2'].", 0);");

		$db->exec("INSERT INTO played_game_cloud(pgid, gid, type, num, relation, weight, score)
			   VALUES ($pgid0, $gid, 0, ".$c['pos'].", 0, ".$c['probaR0'].", 0);");

		$db->exec("INSERT INTO played_game_cloud(pgid, gid, type, num, relation, weight, score)
			   VALUES ($pgidT, $gid, 0, ".$c['pos'].", -1, ".$c['probaTrash'].", 0);");
	}

	$db->exec("commit;");
	return $gid;
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

/** Formate une partie en JSON en l'imprimant.
* @param user : l'utilisateur.
* @param gameId : L'identifiant d'une partie.
*/
function game2json($user, $gameId)
{
	$db = getDB();
	// TODO : factoriser avec game2array() .
	// TODO : planter si la requête suivante échoue pour quelque raison que ce soit.
	$db->exec("INSERT INTO played_game(pgid, gid, login, timestamp) VALUES (null, ".$gameId.", '$user', -1);");
	$pgid = $db->lastInsertRowID();
	
	$game = sqlGetGameInfo($gameId);
	
	$retstr = "";
	$retstr .= '{"gid":'.$gameId.',"pgid":'.$pgid.',"cat1":'.$game['relation_1'].',"cat2":'.$game['relation_2'].',"cat3":0,"cat4":-1,';
	$retstr .= '"center":{"id":'.$game['eid_central_word'].',"name":'.json_encode(''.sqlGetNodeName($game['eid_central_word'])).'},';
	$retstr .= '"cloudsize":10,"cloud":['; // TODO ! compter dynamiquement.
	
	$res = $db->query(sqlGetCloudWords($gameId));
	$notfirst = false;
	
	while ($x = $res->fetchArray())
	{
		if ($notfirst) 
			$retstr .= ",";
		else
			$notfirst=true;

		$retstr .= '{"id":'.$x['eid_word'].',"name":'.json_encode("".sqlGetNodeName($x['eid_word'])).'}';
	}

	$retstr .= "]}";
	return $retstr;
}

/** Récupère une partie sous forme de tableau.
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
function get_game_relations()
{
		$reqlations = array();

		$relations[] = 5;
		$relations[] = 7;
		$relations[] = 9;
		$relations[] = 10;
		$relations[] = 14;
		$relations[] = 22;

		return $relations;
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
