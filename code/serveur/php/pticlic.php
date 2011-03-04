<?php

function checkLogin($db, $user, $passwd) {
	$hashPasswd = md5($passwd);
	$loginIsOk = ($hashPasswd == $db->querySingle("SELECT hash_passwd FROM user WHERE login='".$user."';"));
	return $loginIsOk;
}

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

	fwrite($file,"\nErreur n° ".$errNum);
	fwrite($file," : ".$msg);
	if(!empty($other))	
		fwrite($file,"\n ".$other);
	fwrite($file,"\n  ".$dumpParameters);

	fclose($file);
}


/** Selectionne aléatoirement un noeud.
*/
function randomCenterNode($db)
{
	return $db->querySingle("select eid from random_center_node where rowid = (abs(random()) % (select max(rowid) from random_center_node))+1;");
}

function randomCloudNode($db)
{
	return $db->querySingle("select eid from random_cloud_node where rowid = (abs(random()) % (select max(rowid) from random_cloud_node))+1;");
}


// TODO : Yoann : peut-être faire une classe GameCreator avec les fonctions ci-dessous comme méthodes ?

/**
* @param cloudSize : Taille du nuage.
* @param centerEid : Identifiant du mot central.
* @param r1 Type de la relation 1.
* @param r2 Type de la relation 2.
*/
function cgBuildResultSets($db, $cloudSize, $centerEid, $r1, $r2)
{
	// 'w' => weight (poids), 'd' => difficulté, 's' => select
	// Le select doit ranvoyer trois colonnes :
	//   eid => l'eid du mot à mettre dans le nuage,
	//   r1 => la probabilité pour que le mot soit dans r1, entre -1 et 1 (négatif = ne devrait pas y être, positif = devrait y être à coup sûr, 0 = on sait pas).
	$typer1r2 = "type in ($r1, $r2)";
	$sources = array(
		// Voisins 1 saut du bon type (= relations déjà existantes)
		array('w'=>40, 'd'=>1, 's'=>"select end as eid, type = $r1 as r1, type = $r2 as r2, 0 as r0, 0 as trash from relation where start = $centerEid and $typer1r2 order by random();"),
		// Voisins 1 saut via r_associated (0), donc qu'on voudrait spécifier si possible.
		array('w'=>40, 'd'=>2, 's'=>"select end as eid, 0.25 as r1, 0.25 as r2, 0.5 as r0, 0 as trash from relation where start = $centerEid and type = 0 order by random();"),
		// Voisins 1 saut via les autres relations
		array('w'=>20, 'd'=>3, 's'=>"select end as eid, 0.1 as r1, 0.1 as r2, 0.8 as r0, 0 as trash from relation where start = $centerEid and type not in (0, $r1, $r2, 4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random();"),
		// Voisins 2 sauts, avec un mix de R1 et R2 pour les liens. Par ex [ A -R1-> B -R2-> C ] ou bien [ A -R2-> B -R2-> C ]
		// Version optimisée de : "select end as eid from relation where $typer1r2 and start in oneHopWithType order by random();"
		array('w'=>30, 'd'=>3, 's'=>"select B.end as eid, ((A.type = $r1) + (B.type = $r1)) / 3 as r1, ((A.type = $r2) + (B.type = $r2)) / 3 as r2, 1/6 as r0, 1/6 as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 1 saut r1/r2 + 1 saut synonyme
		// Version optimisée de : "select end as eid from relation where start in oneHopWithType and type = 5 order by random()";
		array('w'=>20, 'd'=>5, 's'=>"select B.end as eid, (A.type = $r1) * 0.75 as r1, (A.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.type = 5 order by random();"),
		// Version optimisée de : "select end as eid from relation where start in (select end from relation where start = $centerEid and type = 5) and $typer1r2 order by random();"
		array('w'=>20, 'd'=>6, 's'=>"select B.end as eid, (B.type = $r1) * 0.75 as r1, (B.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.type = 5 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 2 sauts (tous)
		// Version optimisée de : "select end as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from relation where start in (select end from relation where start = $centerEid and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001)) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random();"
		array('w'=>10, 'd'=>8, 's'=>"select x as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from (select x from (select X.eid + Y.dumb as x from (select B.end as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = $centerEid and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.start = A.end limit ".($cloudSize*4).") as X, (select 0 as dumb) as Y)) order by random();"),
		// Centre pointe vers X, M pointe vers X aussi, on prend M.
		// Version optimisée de : "select start as eid from relation where end in (select end from relation where start = $centerEid) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random();"
		// Ce n'est toujours pas ça… : "select eid from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = $centerEid and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit 1) order by random();"
		// Tordu, mais ça marche \o/ . En fait il faut empêcher l'optimiseur de ramener le random avant le limit (et l'optimiseur est malin… :)
		array('w'=>10, 'd'=>8, 's'=>"select x as eid, 0.1 as r1, 0.1 as r2, 0.2 as r0, 0.6 as trash from (select x from (select X.eid + Y.dumb as x from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = $centerEid and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit ".($cloudSize*4).") as X, (select 0 as dumb) as Y)) order by random();"),
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
				$sources[$k]['resultSet'][] = array('eid'=>randomCloudNode($db), 'r1'=>0, 'r2'=>0, 'r0'=>0, 'trash'=>1);
				$sources[$k]['rsSize']++;
			}
		}
	}

	return array($sources, $sumWeights);
}


/**
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

/**
* @param cloudSize : Taille du nuage.
* @param sources Les sources.
* @param sumWeights La somme des poids.
* @return array : Tableau avec comme premier élément le nuage et comme second élément le total de difficulté.
*/
function cgBuildCloud($db, $centerEid, $cloudSize, $sources, $sumWeights)
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
		$cloud[$i] = array('pos'=>$i++, 'd'=>$sources['rand']['d'], 'eid'=>randomCloudNode($db), 'probaR1'=>$res['r1'], 'probaR2'=>$res['r2'], 'probaR0'=>$res['r0'], 'probaTrash'=>$res['trash']);
	}

	return array($cloud, $totalDifficulty);
}


/**
* @param centerEid : Identifiant du mot central.
* @param cloud : Le nuage.
* @param r1 : Le type de la relation 1.
* @param r2 : Le type de la relation 2.
* @param totalDifficulty : La difficulté totale.
*/
function cgInsert($db, $centerEid, $cloud, $r1, $r2, $totalDifficulty)
{
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
		$db->exec("INSERT INTO game_cloud(gid, num, difficulty, eid_word, totalWeight, probaR1, probaR2, probaR0, probaTrash)
			   VALUES ($gid, ".$c['pos'].", ".$c['d'].", ".$c['eid'].", 2, ".$c['probaR1'].", ".$c['probaR2'].", ".$c['probaR0'].", ".$c['probaTrash'].");");
		
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
function randomGameCore($db) {
	return $db->querySingle("select gid from game where gid = (abs(random()) % (select max(gid) from game))+1 or gid = (select max(gid) from game where gid > 0) order by gid limit 1;");
}

function randomGame($db)
{
	$gid = randomGameCore($db);
	if ($gid === null) {
		// TODO : séparer ces créations de parties dans une fonction qui détecte le mode toussa.
		for ($i = 0; $i < 100; $i++) {
			createGameCore($db, 10);
		}
		$gid = randomGameCore($db);
		if ($gid === null) {
			throw new Exception("Erreur lors de la récupération de la partie. Vérifiez qu'il y a au moins une partie.", 6);
		}
	}
	return $gid;
}

function formatWord($db, $word) {
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
	for ($depth = count($stack); $depth > 0; $depth--) {
		$res .= ')';
	}
	return $res;
}

/** Formate une partie en JSON en l'imprimant.
* @param gameId : L'identifiant d'une partie.
*/
function game2json($db, $user, $gameId)
{
	// TODO : planter si la requête suivante échoue pour quelque raison que ce soit.
	$db->exec("INSERT INTO played_game(pgid, gid, login, timestamp) VALUES (null, ".$gameId.", '$user', -1);");
	$pgid = $db->lastInsertRowID();
	
	// TODO Yoann : faire des tests d'erreur pour ces select ?
	$game = $db->query("select gid, (select name from node where eid = eid_central_word) as name_central_word, eid_central_word, relation_1, relation_2 from game where gid = ".$gameId.";");
	$game = $game->fetchArray();
	
	echo '{"gid":'.$gameId.',"pgid":'.$pgid.',"cat1":'.$game['relation_1'].',"cat2":'.$game['relation_2'].',"cat3":0,"cat4":-1,';
	echo '"center":{"id":'.$game['eid_central_word'].',"name":'.json_encode(''.formatWord($db, $game['name_central_word'])).'},';
	echo '"cloudsize":10,"cloud":['; // TODO ! compter dynamiquement.
	
	$res = $db->query("select eid_word,(select name from node where eid=eid_word) as name_word from game_cloud where gid = ".$gameId.";");
	$notfirst = false;
	
	while ($x = $res->fetchArray())
	{
		if ($notfirst) 
			echo ",";
		else
			$notfirst=true;

		echo '{"id":'.$x['eid_word'].',"name":'.json_encode("".formatWord($db, $x['name_word'])).'}';
	}

	echo "]}";
}


/** Création d'une partie.
*/
function createGame($nbParties, $mode)
{
	for ($i = 0; $i < $nbParties; $i++)
		createGameCore($db, 10);
}

/** Génère une partie (mode normal pour l'instant) pour une certaine taille de nuage.
* @param cloudSize : Taille du nuage.
*
* Est appelée par randomGame(), donc il faudra adapter quand on aura plusieurs modes, par exemple en ayant une fonction intermédiaire qui puisse être appelée par createGame et randomGame.
*/
function createGameCore($db, $cloudSize)
{
	// select random node
	$centerEid = randomCenterNode($db);

	$r1 = cgChooseRelations(); $r2 = $r1[1]; $r1 = $r1[0];
	$sources = cgBuildResultSets($db, $cloudSize, $centerEid, $r1, $r2); $sumWeights = $sources[1]; $sources = $sources[0];
	$cloud = cgBuildCloud($db, $centerEid, $cloudSize, $sources, $sumWeights); $totalDifficulty = $cloud[1]; $cloud = $cloud[0];
	cgInsert($db, $centerEid, $cloud, $r1, $r2, $totalDifficulty);
}

/** Récupération d'une partie.
*/
function getGame($db, $user, $nbGames, $mode)
{
	echo "[";

	for ($i=0; $i < $nbGames; $i)
	{
		game2json($db, $user, randomGame($db));

		if ((++$i) < $nbGames)
			echo ",";
	}
	
	echo "]";
}


/** Insertion des données d'une partie.
*/
function setGame($db, $user, $pgid, $gid, $num, $answers)
{
	if ('ok' !== $db->querySingle("SELECT 'ok' FROM played_game WHERE pgid = $pgid and $gid = $gid and login = '$user' and timestamp = -1;")) {
		throw new Exception("Cette partie n'est associée à votre nom d'utilisateur, ou bien vous l'avez déjà jouée.", 4);
	}
		
	$userReputation = $db->querySingle("SELECT score FROM user WHERE login='$user';");
	$userReputation = ($userReputation > 0) ? log($userReputation) : 0;
		
	$db->exec("begin transaction;");
	$db->exec("update played_game set timestamp = ".time()." where pgid = $pgid;");

	$r0 = 0;
	$trash = -1;
	$r1 = $db->querySingle("SELECT relation_1, relation_2 FROM game WHERE gid = $gid;", true);
	$r2 = $r1['relation_2'];
	$r1 = $r1['relation_1'];
	$res = $db->query("SELECT num, difficulty, totalWeight, probaR1, probaR2, probaR0, probaTrash FROM game_cloud WHERE gid = $gid;");
	
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
			default:     throw new Exception("Réponse invalide pour le mot $num.", 5);
		}
			
		$probas = array($row['probaR1']/$row['totalWeight'], $row['probaR2']/$row['totalWeight'], $row['probaR0']/$row['totalWeight'], $row['probaTrash']/$row['totalWeight']);
		// Calcul du score. Score = proba[réponse de l'utilisateur]*coeff1 - proba[autres reponses]*coeff2
		// score = - proba[autres reponses]*coeff2
		$score = -0.7 * (($probas[0] + $probas[1] + $probas[2] + $probas[3]) - $probas[$answer]);
		// ici, -0.7 <= score <= 0
		// score = proba[réponse de l'utilisateur]*coeff1 - proba[autres reponses]*coeff2
		$score += ($row['difficulty']/5) * $probas[$answer];
		// ici, -0.7 <= score <= 2
		// Adapter le score en fonction de la réputation de l'utilisateur (quand il est jeune, augmenter le score pour le motiver).
		$score += min(2 - max(0, ($userReputation / 4) - 1), 2);
		// ici, -0.7 <= score <= 4

		$db->exec("insert into played_game_cloud(pgid, gid, type, num, relation, weight, score) values($pgid, $gid, 1, $num, $r1, ".$userReputation.", ".$score.");");
		$db->exec("update game_cloud set $probaRx = $probaRx + ".max($userReputation,1)." where gid = $gid;");
		$db->exec("update game_cloud set totalWeight = totalWeight + ".max($userReputation,1)." where gid = $gid;");
		$db->exec("update user set score = score + ".$score." where login = '$user';");
	}

	$db->exec("commit;");
	// On renvoie une nouvelle partie pour garder le client toujours bien alimenté.
	echo "{\"score\":$score,\"newGame\":";
	game2json($db, $user, randomGame($db));
	echo "}";
}

?>