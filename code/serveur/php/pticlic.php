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

function create_game($cloudSize) {
	global $db;
	// select random node
	$centerEid = random_node();
	$relation_1 = 5;
	$relation_2 = 7;
	//$relation_3 = 9;
	//$relation_4 = 10;

	// select neighbors 1 hop
	if (!$difficulty_1 = $db->query("select end as eid from relation where start = 42 and type in (".$relation_1.",".$relation_2.") order by random() limit " . $cloudSize . ";")) { mDie(4,"Erreur dans la requête d1"); }

	
	// select neighbors 2 hops
	if (!$difficulty_2 = $db->query("select end as eid from relation where start in (select end from relation where start = 42) order by random() limit " . $cloudSize . ";")) { mDie(4,"Erreur dans la requête d1"); }
	
	// select neighbors relative to the end (one hop start->end, one hop start<-end).
	if (!$difficulty_3 = $db->query("select start as eid from relation where end in (select end from relation where start = 42) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) order by random() limit " . $cloudSize . ";")) { mDie(4,"Erreur dans la requête d1"); }
	
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
echo "ok";

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