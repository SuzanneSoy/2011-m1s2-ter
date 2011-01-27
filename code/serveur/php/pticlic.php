<?php
require_once("./config/config.php");

if(!isset($_POST['cmd']) || !isset($_POST['psd']) || !isset($_POST['passwd']))
	mDie(1,"La requête est incomplète");
	
$cmd = secure($_POST['cmd']);
$psd = secure($_POST['psd']);
$passwd = md5($_POST['passwd']);

$req = "SELECT passwd FROM member WHERE pseudo='$psd'";

$sql = sqlConnect();
$resp = mysql_query($req);

if(mysql_num_rows($res) < 1)
	mDie(2,"Utilisateur non enregistré");
	
$data = mysql_fetch_array($resp);

mysql_close($sql);

if(strcmp($data['passwd'],$passwd) != 0)
	mDie(3,"Nom d'utilisateur ou mot de passe incorrect");
	

function random_node() {
	return mysql("select eid from node where eid = (abs(random()) % (select max(eid) from node))+1 or eid = (select max(eid) from node where eid > 0) order by eid desc limit 1;");
}

function create_game($cloud_size) {
	// select random node
	$eid_center=random_node();

	// select neighbors 1 hop
	$niveau1=mysql("select end from relation where start = 42 limit $taille_nuage;");
	
	// select neighbors 2 hops
	$niveau2=mysql("select * from relation where start in (select end from relation where start = 42) limit $taille_nuage;");
	
	// select neighbors relative to the end (one hop start->end, one hop start<-end).
	$niveau3=mysql("select * from relation where end in (select end from relation where start = 42) and type not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001) limit $taille_nuage;");

	// pour compléter si nécessaire :
	// select random words
	$niveau4=array();
	for ($i=0; $i < $cloud_size; $i++) {
		$niveau4[$i] = ???
	}

	// start transaction;
	// insert into game $eid_center
	// insert into game_cloud [$cloud_size mots choisis dans $niveau1, $niveau2, $niveau3, $niveau4]
	// insert into game_played une partie de référence.
	// commit;
}


// Sinon tout est bon on effectu l'opération correspondant à la commande passée.
if($cmd == 0)						// "Get partie" 
{
	// Requête sql de création de partie.
	$req = "...";
	
	$sql = sqlConnect();
	$resp = mysql_query($req);
	
	if(mysql_num_rows($resp) == 0)
		echo mysql_error();
	else
	{
		$sequence = "...";
		echo $sequence;
	}
	
	mysql_close($sql);
}
else if($cmd == 1)					// "Set partie"
{
	// Requête sql d'ajout d'informations (et calcul de résultat).
	$req = "...";
	
	$sql = sqlConnect();
	$resp = mysql_query($req);
	
	if(mysql_num_rows($resp) == 0)
		echo mysql_error();
	else
	{
		$sequence = "...";
		echo $sequence;
	}
	
	mysql_close($sql);
}
else if($cmd == 2)
{

}
else if($cmd == 3)
{

}
else if($cmd == 4)
{

}
else
	die("Commande inconnue");
	
?>