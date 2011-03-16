<?php

require_once("db.php");

/* Les prototypes des fonctions :
* ===============================>
*   checkLogin($user, $passwd);
*   randomCenterNode();
*   randomCloudNode();
*   cgBuildResultSets($cloudSize, $centerEid, $r1, $r2);
*   cgChooseRelations();
*   cgBuildCloud($centerEid, $cloudSize, $sources, $sumWeights);
*   cgInsert($centerEid, $cloud, $r1, $r2, $totalDifficulty);
*   randomGameCore();
*   randomGame();
*   formatWord($word);
*   game2json($user, $gameId);
*   game2array($user, $gameId);
*   createGame($nbParties, $mode);
*   createGameCore($cloudSize);
*   getGame($user, $nbGames, $mode);
*   computeScore($probas, $difficulty, $answer, $userReputation);
*   computeUserReputation($score);
*   normalizeProbas($row);
*   setGame($user, $pgid, $gid, $answers);
*   get_game_relations();
*/


/**  Vérifie la validité du couple nom d'utilisateur / mot de passe.
* @param user : Le nom d'utilisateur.
* @param passwd : Le mot de passe.
* @return boolean : true si OK sinon false.
*/
function checkLogin($user, $passwd) {
	$db = getDB();
	$hashPasswd = md5($passwd);
	$loginIsOk = ($hashPasswd == $db->querySingle("SELECT hash_passwd FROM user WHERE login='".$user."';"));
	return $loginIsOk;
}

/** Selectionne aléatoirement l'eid d'un mot central.
* @return eid : Identifiant d'un mot central, NULL en cas d'erreur.
*/
function randomCenterNode()
{
	$db = getDB();
	return $db->querySingle("select eid from random_center_node where rowid = (abs(random()) % (select max(rowid) from random_center_node))+1;");
}

/** Selectionne aléatoirement un noeud d'un nuage.
* @return eid : L'identifiant du noeud.
*/
function randomCloudNode()
{
	$db = getDB();
	return $db->querySingle("select eid from random_cloud_node where rowid = (abs(random()) % (select max(rowid) from random_cloud_node))+1;");
}

/**
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
	$typer1r2 = "type in ($r1, $r2)";
	$banned_types = "4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001";
	
	$sources = array(
		// Voisins 1 saut du bon type (= relations déjà existantes)
		array('w'=>40, 'd'=>1, 's'=>"select end as eid, type = $r1 as r1, type = $r2 as r2, 0 as r0, 0 as trash from relation where start = $centerEid and $typer1r2 order by random();"),
		// Voisins 1 saut via r_associated (0), donc qu'on voudrait spécifier si possible.
		array('w'=>40, 'd'=>2, 's'=>"select end as eid, 0.25 as r1, 0.25 as r2, 0.5 as r0, 0 as trash from relation where start = $centerEid and type = 0 order by random();"),
		// Voisins 1 saut via les autres relations
		array('w'=>20, 'd'=>3.1, 's'=>"select end as eid, 0.1 as r1, 0.1 as r2, 0.8 as r0, 0 as trash from relation where start = $centerEid and type not in (0, $r1, $r2, $banned_types) order by random();"),
		// Voisins 2 sauts, avec un mix de R1 et R2 pour les liens. Par ex [ A -R1-> B -R2-> C ] ou bien [ A -R2-> B -R2-> C ]
		// Version optimisée de : "select end as eid from relation where $typer1r2 and start in oneHopWithType order by random();"
		array('w'=>30, 'd'=>3.2, 's'=>"select B.end as eid, ((A.type = $r1) + (B.type = $r1)) / 3. as r1, ((A.type = $r2) + (B.type = $r2)) / 3. as r2, 1/6. as r0, 1/6. as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 1 saut r1/r2 + 1 saut synonyme
		// Version optimisée de : "select end as eid from relation where start in oneHopWithType and type = 5 order by random()";
		array('w'=>20, 'd'=>5, 's'=>"select B.end as eid, (A.type = $r1) * 0.75 as r1, (A.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.type = 5 order by random();"),
		// Version optimisée de : "select end as eid from relation where start in (select end from relation where start = $centerEid and type = 5) and $typer1r2 order by random();"
		array('w'=>20, 'd'=>6, 's'=>"select B.end as eid, (B.type = $r1) * 0.75 as r1, (B.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.type = 5 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 2 sauts (tous)
		// Version optimisée de : "select end as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from relation where start in (select end from relation where start = $centerEid and type not in ($banned_types)) and type not in ($banned_types) order by random();"
		array('w'=>10, 'd'=>8, 's'=>"select x as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from (select x from (select X.eid + Y.dumb as x from (select B.end as eid from relation as A, relation as B where A.type not in ($banned_types) and A.start = $centerEid and B.type not in ($banned_types) and B.start = A.end limit ".($cloudSize*4).") as X, (select 0 as dumb) as Y)) order by random();"),
		// Centre pointe vers X, M pointe vers X aussi, on prend M.
		// Version optimisée de : "select start as eid from relation where end in (select end from relation where start = $centerEid) and type not in ($banned_types) order by random();"
		// Ce n'est toujours pas ça… : "select eid from (select B.start as eid from relation as A, relation as B where A.type not in ($banned_types) and A.start = $centerEid and B.type not in ($banned_types) and B.end = A.end limit 1) order by random();"
		// Tordu, mais ça marche \o/ . En fait il faut empêcher l'optimiseur de ramener le random avant le limit (et l'optimiseur est malin… :)
		array('w'=>10, 'd'=>8, 's'=>"select x as eid, 0.1 as r1, 0.1 as r2, 0.2 as r0, 0.6 as trash from (select x from (select X.eid + Y.dumb as x from (select B.start as eid from relation as A, relation as B where A.type not in ($banned_types) and A.start = $centerEid and B.type not in ($banned_types) and B.end = A.end limit ".($cloudSize*4).") as X, (select 0 as dumb) as Y)) order by random();"),
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
				$sources[$k]['resultSet'][] = array('eid'=>randomCloudNode(), 'r1'=>0, 'r2'=>0, 'r0'=>0, 'trash'=>1);
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
		$cloud[$i] = array('pos'=>$i++, 'd'=>$sources['rand']['d'], 'eid'=>randomCloudNode(), 'probaR1'=>$res['r1'], 'probaR2'=>$res['r2'], 'probaR0'=>$res['r0'], 'probaTrash'=>$res['trash']);
	}

	return array($cloud, $totalDifficulty);
}


/** Insère la partie dans la base de données.
* @param centerEid : Identifiant du mot central.
* @param cloud : Le nuage.
* @param r1 : Le type de la relation 1.
* @param r2 : Le type de la relation 2.
* @param totalDifficulty : La difficulté totale.
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
}

/** Retourne un identifiant de partie aléatoire de la liste de parties jouables
* @return gid : Identifiant de partie.
*/
function randomGameCore() {
	$db = getDB();
	return $db->querySingle("select gid from game where gid = (abs(random()) % (select max(gid) from game))+1 or gid = (select max(gid) from game where gid > 0) order by gid limit 1;");
}

/** Sélection aléatoire d'une partie de la base de données parmis les parties à jouer.
* @return gid : Identifiant de la partie selectionnée.
*/
function randomGame()
{
	$gid = randomGameCore();

	if ($gid === null) {
		// TODO : séparer ces créations de parties dans une fonction qui détecte le mode toussa.
		for ($i = 0; $i < 100; $i++)
			createGameCore(10);

		$gid = randomGameCore();

		if ($gid === null)
			throw new Exception("Erreur lors de la récupération de la partie. Vérifiez qu'il y a au moins une partie.", 6);
	}
	return $gid;
}

/** Formatage des mots lorsqu'il y a des généralisations/spécifications par le symbole ">".
* @param word : Le mot que l'on veut reformater.
* @return word : le mot formaté.
*/
function formatWord($word) {
	$db = getDB();
	$res = "";
	$stack = array();

	while (($pos = strpos($word, ">")) !== false) {
		$res .= substr($word,0,$pos) . " (";
		$eid = intval(substr($word,$pos+1));
		if ($eid == 0) { throw new Exception("Erreur lors du suivi des pointeurs de spécialisation du mot $word.", 7); }
		if (in_array($eid, $stack)) { throw new Exception("Boucle rencontrée lors du suivi des pointeurs de spécialisation du mot $word.", 8); }
		if (count($stack) > 10) { throw new Exception("Trop de niveaux de récursions lors du suivi des pointeurs de spécialisation du mot $word.", 9); }
		$stack[] = $eid;
		$word = $db->querySingle("select name from node where eid = $eid");
	}

	$res .= $word;

	for ($depth = count($stack); $depth > 0; $depth--)
		$res .= ')';

	return $res;
}

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
	
	// TODO Yoann : faire des tests d'erreur pour ces select ?
	$game = $db->query("select gid, (select name from node where eid = eid_central_word) as name_central_word, eid_central_word, relation_1, relation_2 from game where gid = ".$gameId.";");
	$game = $game->fetchArray();
	
	$retstr = "";
	$retstr .= '{"gid":'.$gameId.',"pgid":'.$pgid.',"cat1":'.$game['relation_1'].',"cat2":'.$game['relation_2'].',"cat3":0,"cat4":-1,';
	$retstr .= '"center":{"id":'.$game['eid_central_word'].',"name":'.json_encode(''.formatWord($game['name_central_word'])).'},';
	$retstr .= '"cloudsize":10,"cloud":['; // TODO ! compter dynamiquement.
	
	$res = $db->query("select eid_word,(select name from node where eid=eid_word) as name_word from game_cloud where gid = ".$gameId.";");
	$notfirst = false;
	
	while ($x = $res->fetchArray())
	{
		if ($notfirst) 
			$retstr .= ",";
		else
			$notfirst=true;

		$retstr .= '{"id":'.$x['eid_word'].',"name":'.json_encode("".formatWord($x['name_word'])).'}';
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
	$game = $db->query("select gid, (select name from node where eid = eid_central_word) as name_central_word, eid_central_word, relation_1, relation_2 from game where gid = ".$gameId.";");
	$game = $game->fetchArray();

	$ret = array();
	$ret['gid'] = $gameId;
	$ret['pgid'] = $pgid;
	$ret['cat1'] = $game['relation_1'];
	$ret['cat2'] = $game['relation_2'];
	$ret['cat3'] = 0;
	$ret['cat4'] = -1;
	$ret['center'] = array('id' => $game['eid_central_word'], 'name' => formatWord($game['name_central_word']));
	$ret['cloud'] = array(); // TODO ! compter dynamiquement.
	
	$res = $db->query("select eid_word,(select name from node where eid=eid_word) as name_word, num, difficulty, totalWeight, probaR1, probaR2, probaR0, probaTrash from game_cloud where gid = ".$gameId.";");
	
	while ($x = $res->fetchArray())
	{
		$ret['cloud'][$x['num']] = array(
			'id' => $x['eid_word'],
			'name' => formatWord($x['name_word']),
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


/** Création d'un lot de parties suivant un mode donnée.
* @param nbParties : le nombre de parties à créer.
* @param mode : Le mode de jeu pour les parties à créer.
*/
function createGame($nbParties, $mode)
{
	for ($i = 0; $i < $nbParties; $i++)
		createGameCore(10);
}

/** Génère une partie (mode normal pour l'instant) pour une certaine taille de nuage.
* @param cloudSize : Taille du nuage.
*
* Est appelée par randomGame(), donc il faudra adapter quand on aura plusieurs modes, par exemple en ayant une fonction intermédiaire qui puisse être appelée par createGame et randomGame.
*/
function createGameCore($cloudSize)
{
	// select random node
	$centerEid = randomCenterNode();

	$r1 = cgChooseRelations(); $r2 = $r1[1]; $r1 = $r1[0];
	$sources = cgBuildResultSets($cloudSize, $centerEid, $r1, $r2); $sumWeights = $sources[1]; $sources = $sources[0];
	$cloud = cgBuildCloud($centerEid, $cloudSize, $sources, $sumWeights); $totalDifficulty = $cloud[1]; $cloud = $cloud[0];
	cgInsert($centerEid, $cloud, $r1, $r2, $totalDifficulty);
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
	if ('ok' !== $db->querySingle("SELECT 'ok' FROM played_game WHERE pgid = $pgid and $gid = $gid and login = '$user' and timestamp = -1;")) {
		throw new Exception("Cette partie n'est associée à votre nom d'utilisateur, ou bien vous l'avez déjà jouée.", 4);
	}
	
	$userReputation = computeUserReputation($db->querySingle("SELECT score FROM user WHERE login='".$user."';"));
	
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
	
	while ($row = $res->fetchArray())
	{
		$num = $row['num'];
		if (!isset($answers[$num])) {
			throw new Exception("Cette requête \"Set partie\" ne donne pas de réponse (une relation) pour le mot numéro $num de la partie.", 5);
		}
		$relanswer = intval($answers[$num]);

		switch ($relanswer) 
		{
			case $r1:    $answer = 0; $probaRx = "probaR1"; break;
			case $r2:    $answer = 1; $probaRx = "probaR2"; break;
			case $r0:    $answer = 2; $probaRx = "probaR0"; break;
			case $trash: $answer = 3; $probaRx = "probaTrash"; break;
			default:     throw new Exception("Réponse ($relanswer) invalide pour le mot $num. Les réponses possibles sont : $r1, $r2, $r0, $trash", 5);
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
	return $scores;
}

/** Fourni l'ensembles des relations pouvant apparaître dans le jeu.
* @return array : un tableau de realtions.
*/
function get_game_relations()
{
		$reqlations = array();
		$db = getDB();

		// TODO modifier la requête pour ne sélectionner que les relations pertinentes.
		$res = $db->query("SELECT num,extended_name
							FROM type_relation
							WHERE num=5 OR num=7 OR num=9
								OR num=10 OR num=13 OR num=14 OR num=22");
	
		while($r = $res->fetchArray())
			$relations[] = $r;

		return $relations;
}
?>
