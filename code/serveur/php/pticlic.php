<?php
// Requête : http://serveur/pticlic.php?action=getparties&nb=2&mode=normal&user=foo&passwd=bar

$do_initdb = false;
$email_admin = '';              // Adresse e-mail Administrateur.

$SQL_DBNAME = (dirname(__FILE__) . "/db");

function mDie($err,$msg)
{
	echo "{ error:".json_encode("".$err).", msg:".json_encode("".$msg)."}";
	exit;
}

if (!$db = new SQLite3('db')) {
	mDie(1,"Erreur lors de l'ouverture de la base de données SQLite3");
}

function initdb() {
	global $db;
	$db->exec("insert into user(login, mail, hash_passwd) values('foo', 'foo@isp.com', '".md5('bar')."');");
}

if ($do_initdb) initdb();

if(!isset($_GET['action']) || !isset($_GET['user']) || !isset($_GET['passwd']))
	mDie(2,"La requête est incomplète");

// Login
$action = $_GET['action'];
$user = $_GET['user'];
$hash_passwd = md5($_GET['passwd']);
if ($hash_passwd !== $db->querySingle("SELECT hash_passwd FROM user WHERE login='".SQLite3::escapeString($user)."';"))
	mDie(3,"Utilisateur non enregistré ou mauvais mot de passe");

function random_node() {
	global $db;
	return $db->querySingle("select eid from node where eid = (abs(random()) % (select max(eid) from node))+1 or eid = (select max(eid) from node where eid > 0) order by eid limit 1;");
}

// TODO : Yoann : peut-être faire une classe create_game avec les fonctions ci-dessous comme méthodes ?

function cg_build_result_sets($cloudSize, $centerEid, $r1, $r2) {
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
		array('w'=>20, 'd'=>3, 's'=>"select end as eid, 0.1 as r1, 0.1 as r2, 0.8 as r0, 0 as trash from relation where start = $centerEid and type not in (0, $r1, $r2) order by random();"),
		// Voisins 2 sauts, avec un mix de R1 et R2 pour les liens. Par ex [ A -R1-> B -R2-> C ] ou bien [ A -R2-> B -R2-> C ]
		// Version optimisée de : "select end as eid from relation where $typer1r2 and start in oneHopWithType order by random();"
		array('w'=>30, 'd'=>3, 's'=>"select B.end as eid, ((A.type = $r1) + (B.type = $r1)) / 3 as r1, ((A.type = $r2) + (B.type = $r2)) / 3 as r2, 1/6 as r0, 1/6 as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 1 saut r1/r2 + 1 saut synonyme
		// Version optimisée de : "select end as eid from relation where start in oneHopWithType and type = 5 order by random();"
		array('w'=>20, 'd'=>5, 's'=>"select B.end as eid, (A.type = $r1) * 0.75 as r1, (A.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.type = 5 order by random();"),
		// Version optimisée de : "select end as eid from relation where start in (select end from relation where start = $centerEid and type = 5) and $typer1r2 order by random();"
		array('w'=>20, 'd'=>6, 's'=>"select B.end as eid, (B.type = $r1) * 0.75 as r1, (B.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.type = 5 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 2 sauts (tous)
		array('w'=>10, 'd'=>7, 's'=>"select end as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from relation where start in (select end from relation where start = $centerEid) order by random();"),
		// Centre pointe vers X, M pointe vers X aussi, on prend M.
		// Version optimisée de : "select start as eid from relation where end in (select end from relation where start = $centerEid) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random();"
		// Ce n'est toujours pas ça… : "select eid from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = $centerEid and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit 1) order by random();"
		// Tordu, mais ça marche \o/ . En fait il faut empêcher l'optimiseur de ramener le random avant le limit (et l'optimiseur est malin… :)
		array('w'=>10, 'd'=>8, 's'=>"select x as eid, 0.1 as r1, 0.1 as r2, 0.2 as r0, 0.6 as trash from (select x from (select X.eid + Y.dumb as x from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = 74860 and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit $cloudSize) as X, (select 0 as dumb) as Y)) order by random();"),
		'rand' => array('w'=>5, 'd'=>10, 's'=>false) // random. Le r1 et r2 de random sont juste en-dessous
	);

	$sumWeights = 0;
	foreach ($sources as $k => $x) {
		$sumWeights += $x['w'];
		$sources[$k]['rsPos'] = 0;
		$sources[$k]['rsSize'] = 0;
		if ($x['s'] !== false) {
			$sources[$k]['resultSet'] = array();
			$res = $db->query($x['s']);
			$i = 0;
			while ($i < 10 && $sources[$k]['resultSet'][] = $res->fetchArray()) {
				$i++;
				$sources[$k]['rsSize']++;
			}
		} else {
			$sources[$k]['resultSet'] = array();
			for ($i = 0; $i < 10; $i++) {
				$sources[$k]['resultSet'][] = array('eid'=>random_node(), 'r1'=>0, 'r2'=>0, 'r0'=>0, 'trash'=>1);
				$sources[$k]['rsSize']++;
			}
		}
	}
	return array($sources, $sumWeights);
}

function cg_choose_relations() {
	$relations = array(5, 7, 9, 10, /* Pas d'icônes pour celles-ci. */ 13, 14, 22);
	$r1 = rand(0,count($relations)-1);
	$r2 = rand(0,count($relations)-2);
	if ($r2 >= $r1) $r2++;
	$r1 = $relations[$r1];
	$r2 = $relations[$r2];
	return array($r1, $r2);
}

function cg_build_cloud($cloudSize, $sources, $sumWeights) {
	// On boucle tant qu'il n'y a pas eu au moins 2 sources épuisées
	$cloud = array();
	$nbFailed = 0;
	$i = 0;
	while ($i < $cloudSize && $nbFailed < 5*$cloudSize) {
		// On choisit une source aléatoire en tennant compte des poids.
		$rands = rand(1,$sumWeights);
		$sumw = 0;
		$src = $sources['rand'];
		$srck = null;
		foreach ($sources as $k => $x) {
			$sumw += $x['w'];
			if ($rands < $sumw) {
				$src = $x;
				$srck = $k;
				break;
			}
		}
		if ($src['rsPos'] >= $src['rsSize']) {
			$nbFailed++;
			if ($srck !== null) {
				$sumWeights -= $src['w'];
				unset($sources[$srck]);
			}
			continue;
		}
		$res = $src['resultSet'][$src['rsPos']++];
		if (in_array($res['eid'], $cloud)) {
			$nbFailed++;
			continue;
		}
		// position dans le nuage, difficulté, eid, probaR1, probaR2
		$cloud[$i] = array('pos'=>$i++, 'd'=>$src['d'], 'eid'=>$res['eid'], 'probaR1'=>$res['r1'], 'probaR2'=>$res['r2'], 'probaR0'=>$res['r0'], 'probaTrash'=>$res['trash']);
	}
	$res = $sources['rand']['resultSet'][0];
	while ($i < $cloudSize) {
		$cloud[$i] = array('pos'=>$i++, 'd'=>$sources['rand']['d'], 'eid'=>random_node(), 'probaR1'=>$res['r1'], 'probaR2'=>$res['r2'], 'probaR0'=>$res['r0'], 'probaTrash'=>$res['trash']);
	}
	return $cloud;
}

function cg_insert($centerEid, $cloud, $r1, $r2) {
	// Insère dans la base une partie avec le mot central $centerEid, le nuage $cloud et les relations $r1 et $r2
	global $db;
	$db->exec("begin transaction;");
	$db->exec("insert into game(gid, eid_central_word, relation_1, relation_2) values (null, $centerEid, $r1, $r2);");
	$gid = $db->lastInsertRowID();
	$db->exec("insert into played_game(pgid, gid, login) values (null, $gid, null);");
	$pgid = $db->lastInsertRowID();
	foreach ($cloud as $c) {
	    $db->exec("insert into game_cloud(gid, num, difficulty, eid_word, totalWeight, probaR1, probaR2, probaR0, probaTrash) values($gid, ".$c['pos'].", ".$c['d'].", ".$c['eid'].", 2, ".$c['probaR1'].", ".$c['probaR2'].", ".$c['probaR0'].", ".$c['probaTrash'].");");
		$db->exec("insert into played_game_cloud(pgid, gid, type, num, relation, weight, score) values($pgid, $gid, 0, ".$c['pos'].", $r1, ".$c['probaR1'].", 0);");
		$db->exec("insert into played_game_cloud(pgid, gid, type, num, relation, weight, score) values($pgid, $gid, 0, ".$c['pos'].", $r1, ".$c['probaR1'].", 0);");
	}
	$db->exec("commit;");
}

function create_game($cloudSize) {
	global $db;
	// select random node
	$centerEid = random_node();
	$r1 = cg_choose_relations(); $r2 = $r1[1]; $r1 = $r1[0];
	$sources = cg_build_result_sets($cloudSize, $centerEid, $r1, $r2); $sumWeights = $sources[1]; $sources = $sources[0];
	$cloud = cg_build_cloud($cloudSize, $sources, $sumWeights);
	cg_insert($centerEid, $cloud, $r1, $r2);
}

create_game(10);
echo "ok\n";
exit;

// // Sinon tout est bon on effectu l'opération correspondant à la commande passée.
// if($action == 0)						// "Get partie" 
// {
// 	// Requête sql de création de partie.
// 	$req = "...";
	
// 	$sql = sqlConnect();
// 	$resp = mysql_query($req);
	
// 	if(mysql_num_rows($resp) == 0)
// 		echo mysql_error();
// 	else
// 	{
// 		$sequence = "...";
// 		echo $sequence;
// 	}
	
// 	mysql_close($sql);
// }
// else if($action == 1)					// "Set partie"
// {
// 	// Requête sql d'ajout d'informations (et calcul de résultat).
// 	$req = "...";
	
// 	$sql = sqlConnect();
// 	$resp = mysql_query($req);
	
// 	if(mysql_num_rows($resp) == 0)
// 		echo mysql_error();
// 	else
// 	{
// 		$sequence = "...";
// 		echo $sequence;
// 	}
	
// 	mysql_close($sql);
// }
// else if($action == 2)
// {

// }
// else if($action == 3)
// {

// }
// else if($action == 4)
// {

// }
// else
// 	die("Commande inconnue");
	
?>
