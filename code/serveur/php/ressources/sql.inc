$typer1r2 = "type in ($r1, $r2)";
$banned_types = "4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001";

function sqlGetPassword($user) {
	return "SELECT hash_passwd FROM user WHERE login='".$user."';";
}

function sqlGetEIDCenterNode() {
	return "select eid from random_center_node where rowid = (abs(random()) % (select max(rowid) from random_center_node))+1;";
}

function sqlGetEIRCloudNode() {
	return "select eid from random_cloud_node where rowid = (abs(random()) % (select max(rowid) from random_cloud_node))+1;";
}

// Voisins 1 saut du bon type (= relations déjà existantes)
function sql1JumpGoodType($r1, $r2, $centerEid) {
	global $typer1r2;
	
	return "select end as eid, type = $r1 as r1, type = $r2 as r2, 0 as r0, 0 as trash from relation where start = $centerEid and $typer1r2 order by random();";
}

// Voisins 1 saut via r_associated (0), donc qu'on voudrait spécifier si possible.
function sql1JumpViaRAssociated0($centerEid) {
	return "select end as eid, 0.25 as r1, 0.25 as r2, 0.5 as r0, 0 as trash from relation where start = $centerEid and type = 0 order by random();";
}

// Voisins 1 saut via les autres relations
function sql1JumpViaOtherRelation($centerEid, $r1, $r2) {
	global $banned_types;
	
	return "select end as eid, 0.1 as r1, 0.1 as r2, 0.8 as r0, 0 as trash from relation where start = $centerEid and type not in (0, $r1, $r2, $banned_types) order by random();";
}

// Voisins 2 sauts, avec un mix de R1 et R2 pour les liens. Par ex [ A -R1-> B -R2-> C ] ou bien [ A -R2-> B -R2-> C ]
// Version optimisée de : "select end as eid from relation where $typer1r2 and start in oneHopWithType order by random();"
function sql2JumpWithMixR1R2ForLinks($r1, $r2, $centerEid) {
	global $typer1r2;

	return "select B.end as eid, ((A.type = $r1) + (B.type = $r1)) / 3. as r1, ((A.type = $r2) + (B.type = $r2)) / 3. as r2, 1/6. as r0, 1/6. as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.$typer1r2 order by random();";
}

// Voisins 1 saut r1/r2 + 1 saut synonyme
// Version optimisée de : "select end as eid from relation where start in oneHopWithType and type = 5 order by random()";
function sql1JumpR1DivR2Plus1JumpSynonymOneHopWithType($r1, $r2, $centerEid) {
	global $typer1r2;

	return "select B.end as eid, (A.type = $r1) * 0.75 as r1, (A.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.$typer1r2 and B.start = A.end and B.type = 5 order by random();";
}

// Version optimisée de : "select end as eid from relation where start in (select end from relation where start = $centerEid and type = 5) and $typer1r2 order by random();"
function sql1JumpR1DivR2Plus1JumpSynonym($r1, $r2, $centerEid) {
	global $typer1r2;
	
	return "select B.end as eid, (B.type = $r1) * 0.75 as r1, (B.type = $r2) * 0.75 as r2, 0.25 as r0, 0 as trash from relation as A, relation as B where A.start = $centerEid and A.type = 5 and B.start = A.end and B.$typer1r2 order by random();";
}

// Voisins 2 sauts (tous)
// Version optimisée de : "select end as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from relation where start in (select end from relation where start = $centerEid and type not in ($banned_types)) and type not in ($banned_types) order by random();"
function sql2JumpAll($centerEid, $cloudSize) {
	global $banned_types;
	
	return "select x as eid, 0.1 as r1, 0.1 as r2, 0.3 as r0, 0.5 as trash from (select x from (select X.eid + Y.dumb as x from (select B.end as eid from relation as A, relation as B where A.type not in ($banned_types) and A.start = $centerEid and B.type not in ($banned_types) and B.start = A.end limit ".($cloudSize*4).") as X, (select 0 as dumb) as Y)) order by random();";
}

// Centre pointe vers X, M pointe vers X aussi, on prend M.
// Version optimisée de : "select start as eid from relation where end in (select end from relation where start = $centerEid) and type not in ($banned_types) order by random();"
// Ce n'est toujours pas ça… : "select eid from (select B.start as eid from relation as A, relation as B where A.type not in ($banned_types) and A.start = $centerEid and B.type not in ($banned_types) and B.end = A.end limit 1) order by random();"
// Tordu, mais ça marche \o/ . En fait il faut empêcher l'optimiseur de ramener le random avant le limit (et l'optimiseur est malin… :)
function sqlXPointsToMMPointsToXTakeM($cloudSize) {
	global $banned_types;
	
	return "select x as eid, 0.1 as r1, 0.1 as r2, 0.2 as r0, 0.6 as trash from (select x from (select X.eid + Y.dumb as x from (select B.start as eid from relation as A, relation as B where A.type not in ($banned_types) and A.start = $centerEid and B.type not in ($banned_types) and B.end = A.end limit ".($cloudSize*4).") as X, (select 0 as dumb) as Y)) order by random();";
}

function sqlGetNameFromNode($res) {
	return "select name from node where eid=".$res['eid'].";";
}

function sqlGetGidFromGame() {
	return "select gid from game where gid = (abs(random()) % (select max(gid) from game))+1 or gid = (select max(gid) from game where gid > 0) order by gid limit 1;";
}

function sqlGetNameFromNodeWithEid($eid) {
	return "select name from node where eid = $eid";
}

// TODO Yoann : faire des tests d'erreur pour ces select ?
function sqlGetGamesForId($gameId) {
	return "select gid, (select name from node where eid = eid_central_word) as name_central_word, eid_central_word, relation_1, relation_2 from game where gid = ".$gameId.";";
}

function sqlGetWordEidAndName($gameId) {
	return "select eid_word,(select name from node where eid=eid_word) as name_word from game_cloud where gid = ".$gameId.";";
}

function sqlGetInformationAboutGame($gameId) {
	return "select eid_word,(select name from node where eid=eid_word) as name_word, num, difficulty, totalWeight, probaR1, probaR2, probaR0, probaTrash from game_cloud where gid = ".$gameId.";";
}

function sqlGameIsOK($pgid, $gid, $user) {
	return "SELECT 'ok' FROM played_game WHERE pgid = $pgid and $gid = $gid and login = '$user' and timestamp = -1;";
}

function sqlGetScoreForUser($user) {
	return "SELECT score FROM user WHERE login='".$user."';";
}

function sqlGetPlayedGameTime($pgid, $gid, $user) {
	return "SELECT timestamp FROM played_game WHERE pgid = $pgid and $gid = $gid and login = '$user';";
}

function sqlGetNumAndScoreFromGame($pgid, $gid) {
	return "SELECT num,score from played_game_cloud where pgid = $pgid and gid = $gid;";
}

function sqlGetEidFromNode($node) {
	return "SELECT eid FROM node WHERE name='".SQLite3::escapeString($node)."'";
}