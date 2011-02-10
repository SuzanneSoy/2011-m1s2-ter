<?php
// Requête : http://serveur/pticlic.php?action=getparties&nb=2&mode=normal&user=foo&passwd=bar
ob_start();

$email_admin = '';              // Adresse e-mail Administrateur.
$SQL_DBNAME = (dirname(__FILE__) . "/db");


/** Enregistre une erreur et quitte le programme.
* @param err : Numéro de l'erreur.
* @param msg : Description de l'erreur.
*/
function mDie($err,$msg)
{
	global $db;

	ob_end_clean();
	echo "{\"error\":".$err.",\"msg\":".json_encode("".$msg)."}";

	log_error($err,$msg);

	$db->close();
	exit;
}


/** Ecrit un rapport d'erreur dans un fichier.
* @param errNum : Numéro de l'erreur.
* @param msg : Description de l'erreur.
* @param [other] : (Optionnel) Complément d'information.
*/
function log_error($errNum, $msg, $other="")
{
	$file = fopen("./log.txt","a+");

	// Met en forme la chaine contenant les paramètres de la requête.
	$dump_parameters = str_replace("(\n","",print_r($_GET,true));
	$dump_parameters = str_replace(")\n","",$dump_parameters);

	fwrite($file,"\nErreur n° ".$errNum);
	fwrite($file," : ".$msg);
	if(!empty($other))	
		fwrite($file,"\n ".$other);
	fwrite($file,"\n  ".$dump_parameters);

	fclose($file);
}



if (!$db = new SQlite3($SQL_DBNAME))
	mDie(1,"Erreur lors de l'ouverture de la base de données SQLite3");


if(!isset($_GET['action']) || !isset($_GET['user']) || !isset($_GET['passwd']))
	mDie(2,"La requête est incomplète");

// Login
$action = $_GET['action'];
$user = SQLite3::escapeString($_GET['user']);
$hash_passwd = md5($_GET['passwd']);

if ($hash_passwd !== $db->querySingle("SELECT hash_passwd FROM user WHERE login='$user';"))
	mDie(3,"Utilisateur non enregistré ou mauvais mot de passe");


/** Selectionne aléatoirement un noeud.
*/
function random_node()
{
	global $db;

	return $db->querySingle("select eid from node where eid = (abs(random()) % (select max(eid) from node))+1 or eid = (select max(eid) from node where eid > 0) order by eid limit 1;");
}


// TODO : Yoann : peut-être faire une classe GameCreator avec les fonctions ci-dessous comme méthodes ?

/**
* @param cloudSize : Taille du nuage.
* @param centerEid : Identifiant du mot central.
* @param r1 Type de la relation 1.
* @param r2 Type de la relation 2.
*/
function cg_build_result_sets($cloudSize, $centerEid, $r1, $r2)
{
	global $db;

	// 'w' => weight (poids), 'd' => difficulté, 's' => select
	// Le select doit ranvoyer trois colonnes :
	//   eid => l'eid du mot à mettre dans le nuage,
	//   r1 => la probabilité pour que le mot soit dans r1, entre -1 et 1 (négatif = ne devrait pas y être, positif = devrait y être à coup sûr, 0 = on sait pas).
	// TODO : comment mettre un poids sur random, sachant qu'il ne peut / devrait pas être dans ces select, mais plutôt un appel à random_node() ?
	$typer1r2 = "type in ($r1, $r2)";
	$sources = array(
		// Voisins 1 saut du bon type (= relations déjà existantes)
		array('w'=>40, 'd'=>1, 's'=>"select end as eid, type = $r1 as r1, type = $r2 as r2, 0 as r0, 0 as trash from relation where start = $centerEid and $typer1r2 order by random();"),
		// Voisins 1 saut via r_associated (0), donc qu'on voudrait spécifier si possible.
		array('w'=>40, 'd'=>2, 's'=>"select end as eid, 0.25 as r1, 0.25 as r2, 0.5 as r0, 0 as trash from relation where start = $centerEid and type = 0 order by random();"),
		// Voisins 1 saut via les autres relations
		// TODO ! certains de ces select pourraient renvoyer des mots de types systèmes (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001), il faut les éliminer.
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
		array('w'=>10, 'd'=>7, 's'=>"select end as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from relation where start in (select end from relation where start = $centerEid and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001)) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random();"), // TODO : Georges : Optimiser.
		// Centre pointe vers X, M pointe vers X aussi, on prend M.
		// Version optimisée de : "select start as eid from relation where end in (select end from relation where start = $centerEid) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random();"
		// Ce n'est toujours pas ça… : "select eid from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = $centerEid and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit 1) order by random();"
		// Tordu, mais ça marche \o/ . En fait il faut empêcher l'optimiseur de ramener le random avant le limit (et l'optimiseur est malin… :)
		array('w'=>10, 'd'=>8, 's'=>"select x as eid, 0.1 as r1, 0.1 as r2, 0.2 as r0, 0.6 as trash from (select x from (select X.eid + Y.dumb as x from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = 74860 and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit $cloudSize) as X, (select 0 as dumb) as Y)) order by random();"),
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
				$sources[$k]['resultSet'][] = array('eid'=>random_node(), 'r1'=>0, 'r2'=>0, 'r0'=>0, 'trash'=>1);
				$sources[$k]['rsSize']++;
			}
		}
	}

	return array($sources, $sumWeights);
}


/**
* @return array : Tableau avec la relation 1 et la relation 2.
*/
function cg_choose_relations()
{
	$relations = array(5, 7, 9, 10, /* Pas d'icônes pour celles-ci. */ 13, 14, 22);
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
function cg_build_cloud($cloudSize, $sources, $sumWeights)
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
		foreach ($cloud as $c) {
			if ($c['eid'] == $res['eid']) {
				$nbFailed++;
				$rejected = true;
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
		$cloud[$i] = array('pos'=>$i++, 'd'=>$sources['rand']['d'], 'eid'=>random_node(), 'probaR1'=>$res['r1'], 'probaR2'=>$res['r2'], 'probaR0'=>$res['r0'], 'probaTrash'=>$res['trash']);
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
function cg_insert($centerEid, $cloud, $r1, $r2, $totalDifficulty)
{
	global $db;

	// Insère dans la base une partie avec le mot central $centerEid, le nuage $cloud et les relations $r1 et $r2
	$db->exec("begin transaction;");
	$db->exec("INSERT INTO game(gid, eid_central_word, relation_1, relation_2, difficulty)
		   VALUES (null, $centerEid, $r1, $r2, $totalDifficulty);");
	$gid = $db->lastInsertRowID();

	$db->exec("INSERT INTO played_game(pgid, gid, login, played)
		   VALUES (null, $gid, null, 1);");
	$pgid = $db->lastInsertRowID();

	foreach ($cloud as $c)
	{
		$db->exec("INSERT INTO game_cloud(gid, num, difficulty, eid_word, totalWeight, probaR1, probaR2, probaR0, probaTrash)
			   VALUES ($gid, ".$c['pos'].", ".$c['d'].", ".$c['eid'].", 2, ".$c['probaR1'].", ".$c['probaR2'].", ".$c['probaR0'].", ".$c['probaTrash'].");");
		
		$db->exec("INSERT INTO played_game_cloud(pgid, gid, type, num, relation, weight, score)
			   VALUES ($pgid, $gid, 0, ".$c['pos'].", $r1, ".$c['probaR1'].", 0);");

		$db->exec("INSERT INTO played_game_cloud(pgid, gid, type, num, relation, weight, score)
			   VALUES ($pgid, $gid, 0, ".$c['pos'].", $r1, ".$c['probaR1'].", 0);");
	}

	$db->exec("commit;");
}

/** Retourne un identifiant de partie aléatoire de la liste de parties jouables
* @return gid : Identifiant de partie.
*/
function randomGameCore() {
	global $db;
	return $db->querySingle("select gid from game where gid = (abs(random()) % (select max(gid) from game))+1 or gid = (select max(gid) from game where gid > 0) order by gid limit 1;");
}

function randomGame()
{
	$gid = randomGameCore();
	if ($gid === null) {
		// TODO : séparer ces créations de parties dans une fonction qui détecte le mode toussa.
		for ($i = 0; $i < 100; $i++) {
			createGameCore(10);
		}
		$gid = randomGameCore();
		if ($gid === null) {
			mDie(6, "Erreur lors de la récupération de la partie. Vérifiez qu'il y a au moins une partie.");
		}
	}
	return $gid;
}


/** Formate une partie en JSON en l'imprimant.
* @param game_id : L'identifiant d'une partie.
*/
function game2json($game_id)
{
	global $db, $user;
	
	// TODO : planter si la requête suivante échoue pour quelque raison que ce soit.
	$db->exec("INSERT INTO played_game(pgid, gid, login, played) VALUES (null, $game_id, '$user', 0);");
	$pgid = $db->lastInsertRowID();
	
	// TODO Yoann : faire des tests d'erreur pour ces select ?
	$game = $db->query("select gid, (select name from node where eid = eid_central_word) as name_central_word, eid_central_word, relation_1, relation_2 from game where gid = ".$game_id.";");
	$game = $game->fetchArray();
	
	echo '{"gid":'.$game_id.',"pgid":'.$pgid.',"cat1":'.$game['relation_1'].',"cat2":'.$game['relation_2'].',"cat3":0,"cat4":-1,';
	echo '"center":{"id":'.$game['eid_central_word'].',"name":'.json_encode(''.$game['name_central_word']).'},';
	echo '"cloudsize":10,"cloud":['; // TODO ! compter dynamiquement.
	
	$res = $db->query("select eid_word,(select name from node where eid=eid_word) as name_word from game_cloud where gid = ".$game_id.";");
	$notfirst = false;
	
	while ($x = $res->fetchArray())
	{
		if ($notfirst) 
			echo ",";
		else
			$notfirst=true;

		echo '{"id":'.$x['eid_word'].',"name":'.json_encode("".$x['name_word']).'}';
	}

	echo "]}";
}


/** Création d'une partie.
*/
function createGame()
{
	if (!isset($_GET['nb']) || !isset($_GET['mode']))
		mDie(2,"La requête est incomplète");

	$nbParties = intval($_GET['nb']);

	for ($i = 0; $i < $nbParties; $i++)
		createGameCore(10);
	
	echo '{"success":1}';
}

/** Génère une partie (mode normal pour l'instant) pour une certaine taille de nuage.
* @param cloudSize : Taille du nuage.
*
* Est appelée par randomGame(), donc il faudra adapter quand on aura plusieurs modes, par exemple en ayant une fonction intermédiaire qui puisse être appelée par createGame et randomGame.
*/
function createGameCore($cloudSize)
{
	global $db;

	// select random node
	$centerEid = random_node();

	$r1 = cg_choose_relations(); $r2 = $r1[1]; $r1 = $r1[0];
	$sources = cg_build_result_sets($cloudSize, $centerEid, $r1, $r2); $sumWeights = $sources[1]; $sources = $sources[0];
	$cloud = cg_build_cloud($cloudSize, $sources, $sumWeights); $totalDifficulty = $cloud[1]; $cloud = $cloud[0];
	cg_insert($centerEid, $cloud, $r1, $r2, $totalDifficulty);
}

/** Récupération d'une partie.
*/
function getGame()
{
	if(!isset($_GET['nb']) || !isset($_GET['mode']))
		mDie(2,"La requête est incomplète");

	$nbGames = intval($_GET['nb']);
	
	echo "[";

	for ($i=0; $i < $nbGames; $i)
	{
		game2json(randomGame());

		if ((++$i) < $nbGames)
			echo ",";
	}
	
	echo "]";
}


/** Insertion des données d'une partie.
*/
function setGame()
{
	global $db, $user;

	if (!isset($_GET['pgid']) || !isset($_GET['gid']))
		mDie(2,"La requête est incomplète");
	
	$pgid = intval($_GET['pgid']);
	$gid = intval($_GET['gid']);
		
	if ('t' !== $db->querySingle("SELECT 't' FROM played_game WHERE pgid = $pgid and $gid = $gid and played = 0 and login = '$user';"))
		mDie(4,"Cette partie n'est associée à votre nom d'utilisateur, ou bien vous l'avez déjà jouée.");
		
	$userReputation = $db->querySingle("SELECT score FROM user WHERE login='$user';");
	$userReputation = ($userReputation > 0) ? log($userReputation) : 0;
		
	$db->exec("begin transaction;");
	$db->exec("update played_game set played = 1 where pgid = $pgid;");

	$r0 = 0;
	$trash = -1;
	$r1 = $db->querySingle("SELECT relation_1, relation_2 FROM game WHERE gid = $gid;", true);
	$r2 = $r1['relation_2'];
	$r1 = $r1['relation_1'];
	$res = $db->query("SELECT num, difficulty, totalWeight, probaR1, probaR2, probaR0, probaTrash FROM game_cloud WHERE gid = $gid;");
	
	while ($row = $res->fetchArray())
	{
		$num = $row['num'];
		if (!isset($_GET[$num])) {
			mDie(5,"Cette requête set_partie ne donne pas de réponse (une relation) pour le mot numéro $num de la partie.");
		}
		$relanswer = intval($_GET[$num]);

		switch ($relanswer) 
		{
			case $r1:    $answer = 0; $probaRx = "probaR1"; break;
			case $r2:    $answer = 1; $probaRx = "probaR2"; break;
			case $r0:    $answer = 2; $probaRx = "probaR0"; break;
			case $trash: $answer = 3; $probaRx = "probaTrash"; break;
			default: mDie(5, "Réponse invalide pour le mot $num.");
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
	echo "{score:$score,newGame:";
	game2json(randomGame());
	echo "}";
}

/** La fonction principale.
* @param action : Un identifiant d'action.
*/
function main($action)
{	
	// Sinon tout est bon on effectu l'opération correspondant à la commande passée.
	// TODO : en production, utiliser : header("Content-Type: application/json; charset=utf-8");
	header("Content-Type: text/plain; charset=utf-8");
	
	if  ($action == 2)				// "Create partie"
		// Requête POST : http://serveur/pticlic.php?action=2&nb=2&mode=normal&user=foo&passwd=bar
		createGame();
	else if($action == 0)				// "Get partie"
		// Requête POST : http://serveur/pticlic.php?action=0&nb=2&mode=normal&user=foo&passwd=bar
		getGame();
	else if($action == 1)				// "Set partie"
		// Requête POST : http://serveur/pticlic.php?action=1&mode=normal&user=foo&passwd=bar&gid=1234&pgid=12357&0=0&1=-1&2=22&3=13&9=-1
		setGame();
	else
		mDie(2,"Commande inconnue");
}

main($action);

ob_end_flush();

?>
