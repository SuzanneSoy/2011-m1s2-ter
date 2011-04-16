<?php

define('REL_SYNONYM', 5);

/* Les prototypes des fonctions :
 * ===============================>
 *   querySingle($query);
 *
 *   sqlGetPasswd($user);
 *   sqlGetRandomCenterNode();
 *   sqlGetRandomCloudNode();
 *   sqlGetForwardOne($origin, [$types]);
 *   sqlGetBackwardOne($origin, [$types]);
 *
 *   sourceOneForwardR1R2       ($centerEid, $r1, $r2);
 *   sourceOneForwardAssociated ($centerEid, $r1, $r2);
 *   sourceOneForwardAny        ($centerEid, $r1, $r2);
 *   sourceTwoForwardR1R2_R1R2  ($centerEid, $r1, $r2);
 *   sourceTwoForwardR1R2_Syn   ($centerEid, $r1, $r2);
 *   sourceTwoForwardSyn_R1R2   ($centerEid, $r1, $r2);
 *   sourceTwoForwardAny_Any    ($centerEid, $r1, $r2);
 *   sourceArrowheadAny_Any     ($centerEid, $r1, $r2);
 */

function querySingle($query, $wholeRow = false) {
	return getDB()->querySingle($query, $wholeRow);
}

/** Sélectionne le mot de passe d'un utilisateur.
 * @param user : Le nom d'utilisateur.
 * @return string : Le mot de passe.
 */
function sqlGetPasswd($user) {
	return querySingle("SELECT hash_passwd FROM user WHERE login='".SQLite3::escapeString($user)."';");
}

/** Selectionne aléatoirement l'eid d'un mot central.
 * @return eid : Identifiant d'un mot central, NULL en cas d'erreur.
 */
function sqlGetRandomCenterNode() {
	return querySingle("select eid from random_center_node where rowid = (abs(random()) % (select max(rowid) from random_center_node))+1;");
}

/** Selectionne aléatoirement un noeud d'un nuage.
 * @return eid : L'identifiant du noeud.
 */
function sqlGetRandomCloudNode() {
	return querySingle("select eid from random_cloud_node where rowid = (abs(random()) % (select max(rowid) from random_cloud_node))+1;");
}

/** Selectionne un voisin un saut en avant ou en arrière, avec une relation du type donné.
 * @param origin : L'identifiant du noeud de départ
 * @param types : Facultatif. Entier ou tableau d'entiers représentant les types de relations autorisés.
 * @param forward : Booléen indiquant s'il faut partir de origin (sinon, on arrive dessus).
 * @return eid : L'identifiant du noeud.
 */
function sqlGetForwardOne($origin, $types = null, $forward = true) {
	$dir_from = $forward ? 'start' : 'end';
	$dir_to = $forward ? 'end' : 'start';
	if (is_integer($types)) "= ".intval($types);
	elseif (is_array($types)) "in (".implode(',', array_map("intval", $types)).")";
	else $types = "not in (4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001)";
	// TODO : vérifier que c'est compatible avec l'affectation list($a,$b) = ...()
	return querySingle("select ".$dir_to.", type from relation where ".$dir_from." = ".intval($origin)." and type ".$types." order by random();", true);
}

/** Selectionne un voisin un saut en arrière, avec une relation du type donné.
 * @param origin : L'identifiant du noeud de départ
 * @param types : Facultatif. Entier ou tableau d'entiers représentant les types de relations autorisés.
 * @return eid : L'identifiant du noeud.
 */
function sqlGetBackwardOne($origin, $types = null) {
	return sqlGetForwardOne($origin, $types, false);
}

/* Voisins 1 saut du bon type (= relations déjà connues)
 * @return array : list($eid, $probaR1, $probaR2, $probaR0, $probaTrash)
 */
function sourceOneForwardR1R2($centerEid, $r1, $r2) {
	list($eid, $type) = sqlGetForwardOne($centerEid, array($r1, $r2));
	return array($eid, $type == $r1, $type == $r2, 0, 0);
}

/* Voisins 1 saut via r_associated (0), donc qu'on voudrait spécifier si possible.
 * @return array : list($eid, $probaR1, $probaR2, $probaR0, $probaTrash)
 */
function sourceOneForwardAssociated($centerEid, $r1, $r2) {
	list($eid, $type) = sqlGetForwardOne($centerEid, 0);
	return array($eid, 0.25, 0.25, 0.5, 0);
}

/* Voisins 1 saut relation quelconque.
 * @return array : list($eid, $probaR1, $probaR2, $probaR0, $probaTrash)
 */
function sourceOneForwardAny($centerEid, $r1, $r2) {
	// TODO : sauf relations 0, r1, r2, banned
	list($eid, $type) = sqlGetForwardOne($centerEid);
	return array($eid, 0.1, 0.1, 0.8, 0);
}

// Voisins 2 sauts, avec un mix de R1 et R2 pour les liens. Par ex [ A -R1-> B -R2-> C ] ou bien [ A -R2-> B -R2-> C ]
function sourceTwoForwardR1R2_R1R2($centerEid, $r1, $r2) {
	list($eid1, $type1) = sqlGetForwardOne($centerEid, array($r1, $r2));
	list($eid2, $type2) = sqlGetForwardOne($eid1, array($r1, $r2));
	return array($eid2, (($type1 == $r1) + ($type2 == $r1)) / 3, (($type1 == $r2) + ($type2 == $r2)) / 3, 1/6, 1/6);
}

// Voisins 1 saut r1/r2 + 1 saut synonyme
function sourceTwoForwardR1R2_Syn($centerEid, $r1, $r2) {
	list($eid1, $type1) = sqlGetForwardOne($centerEid, array($r1, $r2));
	list($eid2, $type2) = sqlGetForwardOne($eid1, constant('REL_SYNONYM'));
	return array($eid2, ($type1 == $r1) * 0.75, ($type1 == $r2) * 0.75, 0.5, 0.25);
}

// Voisins 1 saut synonyme + 1 saut r1/r2
function sourceTwoForwardSyn_R1R2($centerEid, $r1, $r2) {
	list($eid1, $type1) = sqlGetForwardOne($centerEid, constant('REL_SYNONYM'));
	list($eid2, $type2) = sqlGetForwardOne($eid1, array($r1, $r2));
	return array($eid2, ($type2 == $r1) * 0.75, ($type2 == $r2) * 0.75, 0.5, 0.25);
}

// Voisins 2 sauts (tous)
function sourceTwoForwardAny_Any($centerEid, $r1, $r2) {
	list($eid1, $type1) = sqlGetForwardOne($centerEid);
	list($eid2, $type2) = sqlGetForwardOne($eid1);
	return array($eid2, 0.1, 0.1, 0.3, 0.5);
}

// Centre pointe vers X, M pointe vers X aussi, on prend M.
function sourceArrowheadAny_Any($centerEid, $r1, $r2) {
	list($eid1, $type1) = sqlGetForwardOne($centerEid);
	list($eid2, $type2) = sqlGetBackwardOne($eid1);
	return array($eid2, 0.1, 0.1, 0.2, 0.6);
}

function sqlGetRawNodeName($eid) {
	return querySingle("select name from node where eid=".intval($eid).";");
}

/** Formatage des mots lorsqu'il y a des généralisations/spécifications par le symbole ">".
* @param word : Le mot que l'on veut reformater.
* @return word : le mot formaté.
*/
function sqlGetNodeName($eid) {
	$db = getDB();
	$res = "";
	$stack = array();
	$word = sqlGetRawNodeName($eid);

	while (($pos = strpos($word, ">")) !== false) {
		$res .= substr($word,0,$pos) . " (";
		$eid = intval(substr($word,$pos+1));
		if ($eid == 0) { errFollowingPointer($word);  }
		if (in_array($eid, $stack)) { errLoopDetected($word);  }
		if (count($stack) > 10) { errTooMuchRecursion($word);  }
		$stack[] = $eid;
		$word = sqlGetRawNodeName($eid);
	}

	$res .= $word;

	for ($depth = count($stack); $depth > 0; $depth--)
		$res .= ')';

	return $res;
}
													  
/** Retourne un identifiant de partie aléatoire de la liste de parties jouables
 * @return gid : Identifiant de partie.
 */
function sqlGetRandomGID() {
	return querySingle("select gid from game where gid = (abs(random()) % (select max(gid) from game))+1 or gid = (select max(gid) from game where gid > 0) order by gid limit 1;");
}

// TODO Yoann : faire des tests d'erreur pour ces select ?
function sqlGetGameInfo($gameId) {
	return querySingle("select gid, eid_central_word, eid_central_word, relation_1, relation_2 from game where gid = ".intval($gameId).";", true);
}

function sqlGetCloudWords($gameId) {
	return "select eid_word from game_cloud where gid = ".intval($gameId).";";
}

function sqlGetCloudInfo($gameId) {
	return "select eid_word, num, difficulty, totalWeight, probaR1, probaR2, probaR0, probaTrash from game_cloud where gid = ".intval($gameId).";";
}

function sqlGetUserScore($user) {
	return querySingle("SELECT score FROM user WHERE login='".sqlite3::escapestring($user)."';");
}

function sqlGetPlayedGameTime($pgid, $gid, $user) {
	return querySingle("SELECT timestamp FROM played_game WHERE pgid = ".intval($pgid)." and gid = ".intval($gid)." and login = '".sqlite3::escapestring($user)."';");
}

function sqlGameIsOK($pgid, $gid, $user) {
	return (-1) == sqlGetPlayedGameTime();
}

function sqlGetPlayedCloudScores($pgid, $gid) {
	return "SELECT num,score from played_game_cloud where pgid = ".intval($pgid)." and gid = ".intval($gid).";";
}
