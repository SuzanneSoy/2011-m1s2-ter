<?php
// Requête : http://serveur/pticlic.php?action=getparties&nb=2&mode=normal&user=foo&passwd=bar

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

// initdb();

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
	// 'w' => weight (poids), 's' => select
	// TODO : comment mettre un poids sur random, sachant qu'il ne peut / devrait pas être dans ces select, mais plutôt un appel à random_node() ?
	$typer1r2 = "type in ($r1, $r2)";
	$sources = array(
		// Voisins 1 saut du bon type (= relations déjà existantes)
		array('w'=>10, 's'=>"select end as eid from relation where start = $centerEid and $typer1r2 order by random();"),
		// Voisins 1 saut via r_associated (0), donc qu'on voudrait spécifier si possible.
		array('w'=>10, 's'=>"select end as eid from relation where start = $centerEid and type = 0 order by random();"),
		// Voisins 1 saut via les autres relations
		array('w'=>10, 's'=>"select end as eid from relation where start = $centerEid and type not in (0, $r1, $r2) order by random();"),
		// Voisins 2 sauts, avec un mix de R1 et R2 pour les liens. Par ex [ A -R1-> B -R2-> C ] ou bien [ A -R2-> B -R2-> C ]
		// Version optimisée de : "select end as eid from relation where $typer1r2 and start in oneHopWithType order by random();"
		array('w'=>10, 's'=>"select B.end as eid from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 1 saut r1/r2 + 1 saut synonyme
		// Version optimisée de : "select end as eid from relation where start in oneHopWithType and type = 5 order by random();"
		array('w'=>10, 's'=>"select B.end as eid from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.type = 5 order by random();"),
		// Version optimisée de : "select end as eid from relation where start in (select end from relation where start = $centerEid and type = 5) and $typer1r2 order by random();"
		array('w'=>10, 's'=>"select B.end as eid from relation as A, relation as B where A.start = $centerEid and A.type = 5 and B.start = A.end and B.$typer1r2 order by random();"),
		// Voisins 2 sauts (tous)
		array('w'=>10, 's'=>"select end as eid from relation where start in (select end from relation where start = $centerEid) order by random();"),
		// Centre pointe vers X, M pointe vers X aussi, on prend M.
		// Version optimisée de : "select start as eid from relation where end in (select end from relation where start = $centerEid) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random();"
		// Ce n'est toujours pas ça… : "select eid from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = $centerEid and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit 1) order by random();"
		// Tordu, mais ça marche \o/ . En fait il faut empêcher l'optimiseur de ramener le random avant le limit (et l'optimiseur est malin… :)
		array('w'=>10, 's'=>"select x as eid from (select x from (select X.eid + Y.dumb as x from (select B.start as eid from relation as A, relation as B where A.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and A.start = 74860 and B.type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) and B.end = A.end limit $cloudSize) as X, (select 0 as dumb) as Y)) order by random();"),
		array('w'=>10, 's'=>false) // random
	);

	$sumWeights = 0;
	foreach ($sources as $k => $x) {
		$sumWeights += $x['w'];
		if ($x['s'] !== false) {
			$sources[$k]['resultSet'] = $db->query($x['s']);
		} else {
			$sources[$k]['resultSet'] = false;
		}
	}
	return array($sources, $sumWeights);
}

function cg_choose_relations() {
	$relations = array(5, 7, 9, 10);
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
	while ($i < $cloudSize && $nbFailed < 50) {
		// On choisit une source aléatoire en tennant compte des poids.
		$rands = rand(1,$sumWeights);
		$sumw = 0;
		$resultSet = false; // TODO : gérer l'erreur si ce n'est pas écrasé (-> random)
		foreach ($sources as $x) {
			$sumw += $x['w'];
			if ($rands < $sumw) {
				$resultSet = $x['resultSet'] ? $x['resultSet'] : false;
				break;
			}
		}
		if ($resultSet) {
			// TODO : vérifier qu'on peut fetch avant !
			if (!$res = $resultSet->fetchArray()) {
				$nbFailed++;
				continue;
			}
			$res = $res['eid'];
		} else {
			$res = random_node();
		}
		if (in_array($res, $cloud)) {
			$nbFailed++;
			continue;
		}
		$cloud[] = $res;
		$i++;
	}
	return $cloud;
}

function create_game($cloudSize) {
	global $db;
	// select random node
	$centerEid = random_node();
	$r1 = cg_choose_relations(); $r2 = $r1[1]; $r1 = $r1[0];
	$sources = cg_build_result_sets($cloudSize, $centerEid, $r1, $r2); $sumWeights = $sources[1]; $sources = $sources[0];
	$cloud = cg_build_cloud($cloudSize, $sources, $sumWeights);
	
	var_dump($cloud);
	exit;
	
	// select neighbors 1 hop
	if (!$difficulty_1 = $db->query()) { mDie(4,"Erreur dans la requête d1"); }

	
	// select neighbors 2 hops
	if (!$difficulty_2 = $db->query()) { mDie(4,"Erreur dans la requête d1"); }
	
	// select neighbors relative to the end (one hop start->end, one hop start<-end).
	if (!$difficulty_3 = $db->query()) { mDie(4,"Erreur dans la requête d1"); }
	
	// TODO : faire les select ci-dessous en les limitant à certaines relations.
	$db->exec("begin transaction;");
	$db->exec("insert into game(gid, eid_central_word, relation_1, relation_2, relation_3, relation_4, reference_played_game) values (null, ".$centerEid.", 1,2,3,4,null);");
	$gid = $db->lastInsertRowID();
	for ($i=0; $i < $cloudSize; $i++) {
		switch (rand(1,4)) {
			case 1:
				if ($eid = $difficulty_1->fetchArray()) { $eid=$eid['eid']; $difficulty=1; break; }
			case 2:
				if ($eid = $difficulty_2->fetchArray()) { $eid=$eid['eid']; $difficulty=2; break; }
			case 3:
				if ($eid = $difficulty_3->fetchArray()) { $eid=$eid['eid']; $difficulty=3; break; }
			case 4:
				$eid = random_node();
				$difficulty=4;
		}
		$db->exec("insert into game_cloud(gid, num, difficulty, eid_word) values(".$gid.", ".$i.", ".$difficulty.", ".$eid.");");
	}
	// TODO : insert into game_played une partie de référence.
	
	$db->exec("commit;");
}

create_game(10);
echo 'ok';

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