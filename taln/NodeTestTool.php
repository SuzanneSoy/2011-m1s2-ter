<?php
// Ctrl + F : TODO to find where to start working...
/**
 *
 * TODO: analysis of relationships of relations  :
 * - which are sometimes together ?
 * - which are never together ?
 * - why ?
 * - are there other interesting combinations possible ?
 * - are some always included in others ? ex. "synonym" and "related to"
 * -
 *
 * IDEAS:
 *
 * - function or class that gives more info on clourds :
 * ->make sure the same word dosn't appear twice
 * ->make sure the main word does't appear in cloud
 * ->filter to make all other words nouns, adg, etc.
 * ->word count for percentage of words possible (if few words, small cloud ; if lots
 * of words, big cloud
 * ->
 */
require_once("db.php");

class NodeTestTool {

    //private static $typer1r2 = "type in ($r1, $r2)";
    //private static $banned_types = "4, 12, 36, 18, 29, 45, 46, 47, 48, 1000, 1001";

    private static $relations = array(0 => "r_associated",
        5 => "r_syn", 6 => "r_isa", 7 => "r_anto", 8 => "r_hypo", 9 => "r_haspart",
        10 => "r_holo", 13 => "r_agent", 15 => "r_lieu", 16 => "r_inst", 17 => "r_carac");

 /***********************GENERAL METHODS***********************************/

    public static function getWordFromEID($eid) {
        $db = getDB();
        $query = "SELECT name FROM node WHERE eid = $eid";
        $result = $db->querySingle($query, true);
        if($result != null) return $result['name'];
        else return null;
    }

    public static function getEIDFromWord($word) {
        $db = getDB();
        $query = "SELECT eid FROM node WHERE name = '$word'";
        $result = $db->querySingle($query, true);
        if($result != null) return $result['eid'];
        else return null;
    }

    public static function isEIDInDB($eid) {
        $db = getDB();
        $query = "SELECT eid FROM node WHERE eid = $eid";
        $result = $db->querySingle($query, true);
        if ($result != null)
            return true;
        return false;
    }

    public static function isWordInDB($word) {
        $db = getDB();
        $query = "SELECT eid FROM node WHERE name = '$word'";
        $result = $db->querySingle($query, true);
        if ($result != null)
            return true;
        return false;
    }

    public static function isCentralEIDinDB($centralEID) {
        $db = getDB();
        $query = "SELECT eid FROM random_center_node WHERE eid = $centralEID";
        $result = $db->querySingle($query, true);
        if ($result != null)
            return true;
        return false;
    }

    public static function isCentralWordInDB($word) {
        $centralEID = NodeTestTool::getEIDFromWord($word);
        return NodeTestTool::isCentralEIDinDB($centralEID);
    }

    public static function isCloudEIDInDB($cloudEID) {
        $db = getDB();
        $query = "SELECT eid FROM random_cloud_node WHERE eid = $cloudEID";
        $result = $db->querySingle($query, true);
        if ($result != null)
            return true;
        return false;
    }

    public static function isCloudWordInDB($cloudWord) {
        $db = getDB();
        $cloudEID = NodeTestTool::getEIDFromWord($cloudWord);
        return NodeTestTool::isCloudEIDinDB($cloudEID);
    }


    public static function getPOSsFromEID($eid) {
        $db = getDB();
        $query = "SELECT end FROM relation WHERE type = 4 AND start = $eid;";
        $res = $db->query($query);

        $adj = $adv = $nom = $ver = false;
        $s = "";

        while ($tuple = $res->fetchArray()) {
            $endEID = $tuple['end'];
            $query = "SELECT name FROM node WHERE eid = $endEID";
            $res2 = $db->querySingle($query, true);
            $POSline = $res2['name'];

            if (preg_match("/^Adj:/", $POSline)) {
                if ($adj == false) {
                    $s .= "Adj ";
                    $adj = true;
                }
            } else if (preg_match("/^Adv:/", $POSline)) {
                if ($adv == false) {
                    $s .= "Adv ";
                    $adv = true;
                }
            } else if (preg_match("/^Nom:/", $POSline)) {
                if ($nom == false) {
                    $s .= "Nom ";
                    $nom = true;
                }
            } else if (preg_match("/^Ver:/", $POSline)) {
                if ($ver == false) {
                    $s .= "Ver ";
                    $ver = true;
                }
            }
        }
        return $s;
    }

    public static function getPOSsFromWord($word) {
        $eid = NodeTestTool::getEIDFromWord($word);
        $POSs = NodeTestTool::getPOSsFromEID($eid);
        return $POSs;
    }


    public static function getCloudEIDsFromCentralEIDAndRelNo($centralEID, $relNo) {
        if (NodeTestTool::isCentralEIDinDB($centralEID)) {
            $db = getDB();
            $query = "SELECT end FROM relation WHERE type = $relNo AND start = $centralEID";
            $res = $db->query($query);

            $s = "";

            while ($tuple = $res->fetchArray()) {
                $eid2 = $tuple['end'];
                $query = "SELECT eid FROM node WHERE eid = $eid2";
                $res2 = $db->querySingle($query, true);
                $s .= $res2['eid'] . ":";
            }
            if(strlen($s) > 0) $s = substr($s, 0, -1);
            return $s;
        }
        return null;
    }
  
    public static function getCloudWordsFromCentralEIDAndRelNo($centralEID, $relNo) {
        if (NodeTestTool::isCentralEIDInDB($centralEID)) {
            $db = getDB();
            $query = "SELECT end FROM relation WHERE type = $relNo AND start = $centralEID";
            $res = $db->query($query);

            $s = "";

            while ($tuple = $res->fetchArray()) {
                $eid2 = $tuple['end'];
                $query = "SELECT name FROM node WHERE eid = $eid2";
                $res2 = $db->querySingle($query, true);
                $s .= $res2['name'] . ":";
            }
            if(strlen($s) > 0) $s = substr($s, 0, -1);
            return $s;
        }
        return null;
    }

    public static function getCloudEIDsFromCentralWordAndRelNo($centralWord, $relNo) {
        $centralEID = NodeTestTool::getEIDFromWord($centralWord);
        if (NodeTestTool::isCentralEIDInDB($centralEID)) {
            return NodeTestTool::getCloudEIDsFromCentralEIDandRelNo($centralEID, $relNo);
        }
        return false;
    }

    public static function getCloudWordsFromCentralWordAndRelNo($centralWord, $relNo) {
        if (NodeTestTool::isCentralWordInDB($centralWord)) {
            $centralEID = NodeTestTool::getEIDFromWord($centralWord);
            return NodeTestTool::getCloudWordsFromCentralEIDAndRelNo($centralEID, $relNo);
        }
        return false;
    }

    public static function getAllCloudEIDsFromCentralEID($centralEID) {
        $s = "";
        foreach (NodeTestTool::$relations AS $relNo => $relName) {
            $cloud = NodeTestTool::getCloudEIDsFromCentralEIDAndRelNo($centralEID, $relNo);
            $s .= $relNo . "=>" . $relName . ": " . $cloud . "<br />";
        }
        return $s;
    }

    public static function getAllCloudWordsFromCentralEID($centralEID) {
        $s = "";
        foreach (NodeTestTool::$relations AS $relNo => $relName) {
            $cloud = NodeTestTool::getCloudWordsFromCentralEIDAndRelNo($centralEID, $relNo);
            $s .= $relNo . "=>" . $relName . ": " . $cloud . "<br />";
        }
        return $s;
    }

    public static function getAllCloudEIDsFromCentralWord($centralWord) {
        $s = "";
        foreach (NodeTestTool::$relations AS $relNo => $relName) {
            $cloud = NodeTestTool::getCloudEIDsFromCentralWordAndRelNo($centralWord, $relNo);
            $s .= $relNo . "=>" . $relName . ": " . $cloud . "<br />";
        }
        return $s;
    }

    public static function getAllCloudWordsFromCentralWord($centralWord) {
        $s = "";
        foreach (NodeTestTool::$relations AS $relNo => $relName) {
            $cloud = NodeTestTool::getCloudWordsFromCentralWordAndRelNo($centralWord, $relNo);
            $s .= $relNo . "=>" . $relName . ": " . $cloud . "<br />";
        }
        return $s;
    }


/*****************************RANDOM METHODS**************************/

    public static function getRandomCentralEID() {
        $db = getDB();
        $query = "SELECT eid FROM random_center_node WHERE rowid =
            (ABS(RANDOM()) % (SELECT MAX(rowid) FROM random_center_node)) + 1";
        $res = $db->querySingle($query, true);
        if ($res != null)
            return $res['eid'];
        else
            return null;
    }

    public static function getRandomCentralWord() {
        $eid = NodeTestTool::getRandomCentralEID();
        return NodeTestTool::getWordFromEID($eid);
    }

    public static function getAbsoluteRandomCloudEID() {
        $db = getDB();
        $query = "SELECT eid FROM random_cloud_node WHERE rowid =
            (ABS(RANDOM()) % (SELECT MAX(rowid) FROM random_center_node)) + 1";
        $res = $db->querySingle($query, true);
        if ($res != null)
            return $res['eid'];
        else
            return null;
    }

    public static function getAbsoluteRandomCloudWord() {
        $eid = NodeTestTool::getAbsoluteRandomCloudEID();
        return NodeTestTool::getWordFromEID($eid);
    }

    public static function getRandomCloudEIDFromCentralEID($centralEID, $relNo){
        $s = NodeTestTool::getCloudEIDsFromCentralEIDAndRelNo($centralEID, $relNo);
        $ar = preg_split("/:/", $s);
        if(count($ar) > 0) return $ar[rand(0, count($ar)-1)];
        return null;
    }

    public static function getRandomCloudWordFromCentralEID($centralEID, $relNo){
        $eid = NodeTestTool::getRandomCloudEIDFromCentralEID($centralEID, $relNo);
        if($eid != null) return NodeTestTool::getWordFromEID($eid);
        return null;
    }

    public static function getRandomCloudEIDFromCentralWord($centralWord, $relNo){
        $centralEID = NodeTestTool::getEIDFromWord($centralWord);
        if($centralEID != null) return NodeTestTool::getRandomCloudEIDFromCentralEID($centralEID, $relNo);
        return null;
    }

    public static function getRandomCloudWordFromCentralWord($centralWord, $relNo){
        $centralEID = NodeTestTool::getEIDFromWord($centralWord);
        if($centralEID != null) return NodeTestTool::getRandomCloudWordFromCentralEID($centralEID, $relNo);
        return null;
    }

    public static function getWordCloudDistance2($centralWord, $relNoDist1, $relNoDist2){
        $randomCloudWord = NodeTestTool::getRandomCloudWordFromCentralWord($centralWord, $relNoDist1);
        echo "Mot intermédiaire : " . $randomCloudWord . "<br />";
        if(strlen($randomCloudWord) > 0)
            return NodeTestTool::getCloudWordsFromCentralWordAndRelNo($randomCloudWord, $relNoDist2);
        return null;
        }

    /*
     * In the implementation of the function getWordCloudDistance2, 8 different
     * functions could be made corresponding to returning a word cloud or an
     * eid cloud, the naming would have to be changed to reflect the different
     * parameters and return values...
     */

    //TODO: FROM HERE DOWN ISN4T IN nodeTest01.php START FROM HERE !!

    //getRandomCloudFromCentralEID($centralEID, $numWords)
    //getRandomCloudFromCentralWord($centralWord, $numWords)

    
    /*     * *******************************WRONG OR DISTORTED CLOUD WORDS********* */

    public static function getRandomCloudEIDofCloudEID($couldEID, $relNum) {
        
    }

    public static function getRandomCloudWordOfCloudWord($cloudWord, $relNum) {

    }

    /*************************ACCESSEURS************************/

    public static function getRelations(){
        return NodeTestTool::$relations;
    }



    /*     * ***********************************GD****************************** */

    /**
     * (gd) Voisins 1 saut du bon type (= relations déjà existantes)
     * @global <type> $typer1r2
     * @param <type> $r1
     * @param <type> $r2
     * @param <type> $centerEID
     */
    public static function gd1JumpGoodType($r1, $r2, $centerEID) {
        global $typer1r2;
        $db = getDB();
        $query = "SELECT end AS eid, type = $r1 as r1, type = $r2 as r2, 
        0 as r0, 0 as trash FROM relation WHERE start = $centerEID AND
        $typer1r2 order by random()";
        $result = $db->querySingle($query, true); //??
        return $result['end']; //??
    }

    /**
     * (gd) Voisins 1 saut via r_associated (0), donc qu'on voudrait spécifier
     * si possible.
     * @param <type> $centerEID
     */
    public static function gd1JumpViaRAssociated0($centerEID) {
        $db = getDB();
        $query = "SELECT end AS eid, 0.25 AS r1, 0.25 AS r2, 0.5 AS r0,
        0 AS trash FROM relation WHERE start = $centerEid AND
        type = 0 order by random()";
        $result = $db->querySingle($query, true); //??
        return $result['end']; //??
    }

    /**
     * (gd) Voisins 1 saut via les autres relations
     * @global  $banned_types
     * @param <type> $centerEid
     * @param <type> $r1
     * @param <type> $r2
     * @return <type> 
     */
    public static function gd1JumpViaOtherRelation($centerEid, $r1, $r2) {
        global $banned_types;
        $query = "SELECT end AS eid, 0.1 AS r1, 0.1 AS r2, 0.8 AS r0, 0 AS trash
        FROM relation WHERE start = $centerEid AND type NOT IN
        (0, $r1, $r2, $banned_types) ORDER BY random()";
        $result = $db->querySingle($query, true); //??
        return $result['end']; //??
    }

    /**
     * (gd) Voisins 2 sauts, avec un mix de R1 et R2 pour les liens. Par ex
     * [ A -R1-> B -R2-> C ] ou bien [ A -R2-> B -R2-> C ]
     * Version optimisée de : "SELECT end AS eid FROM relation WHERE $typer1r2
     * AND start IN oneHopWithType ORDER BY random();"
     * @global  $typer1r2
     * @param <type> $r1
     * @param <type> $r2
     * @param <type> $centerEid
     * @return <type>
     */
    public static function gd2JumpWithMixR1R2ForLinks($r1, $r2, $centerEid) {
        global $typer1r2;
        $db = getDB();
        $query = "SELECT B.end AS eid, ((A.type = $r1) + (B.type = $r1)) / 3.
        AS r1, ((A.type = $r2) + (B.type = $r2)) / 3. AS r2, 1/6. AS r0, 1/6.
        AS trash FROM relation AS A, relation AS B WHERE A.start = $centerEid
        AND A.$typer1r2 AND B.start = A.end AND B.$typer1r2 ORDER BY random()";
        $result = $db->querySingle($query, true); //??
        return $result['end']; //??
    }

    /**
     * (gd) Voisins 1 saut r1/r2 + 1 saut synonyme
     * Version optimisée de : "SELECT end AS eid FROM relation WHERE start IN
     * oneHopWithType AND type = 5 order by random()";
     * @global  $typer1r2
     * @param <type> $r1
     * @param <type> $r2
     * @param <type> $centerEid
     * @return <type>
     */
    public static function gd1JumpR1DivR2Plus1JumpSynonymOneHopWithType($r1, $r2, $centerEid) {
        global $typer1r2;
        $db = getDB();
        $query = "SELECT B.end AS eid, (A.type = $r1) * 0.75 as r1,
        (A.type = $r2) * 0.75 AS r2, 0.25 AS r0, 0 AS trash FROM relation AS A,
        relation AS B WHERE A.start = $centerEid AND A.$typer1r2 AND B.start = A.end
        AND B.type = 5 ORDER BY random()";
        //$result = $db->querySingle($query, true); //??
        //return $result['end']; //??
    }

    /**
     * Version optimisée de : "SELECT end AS eid FROM relation WHERE start IN
     * (SELECT end FROM relation WHERE start = $centerEid AND type = 5) AND $typer1r2
     * ORDER BY random();"
     * @global  $typer1r2
     * @param <type> $r1
     * @param <type> $r2
     * @param <type> $centerEid
     * @return <type>
     */
    public static function gd1JumpR1DivR2Plus1JumpSynonym($r1, $r2, $centerEid) {
        global $typer1r2;
        $db = getDB();
        $query = "SELECT B.end AS eid, (B.type = $r1) * 0.75 as r1,
        (B.type = $r2) * 0.75 AS r2, 0.25 AS r0, 0 AS trash FROM relation AS A,
        relation AS B WHERE A.start = $centerEid AND A.type = 5 AND B.start = A.end
        AND B.$typer1r2 ORDER BY random()";
        //$result = $db->querySingle($query, true); //??
        //return $result['end']; //??
    }

    /**
     * // Voisins 2 sauts (tous)
     * Version optimisée de : "SELECT end AS eid, 0.1 AS r1, 0.1 AS r2, 0.3 AS r0,
     * 0.5 AS trash FROM relation WHERE start IN (SELECT end FROM relation
     * WHERE start = $centerEid AND type NOT IN ($banned_types)) AND type NOT IN
     * ($banned_types) ORDER BY random();"
     * @global $banned_types $banned_types
     * @param <type> $centerEid
     * @param <type> $cloudSize
     * @return <type>
     */
    public static function gd2JumpAll($centerEid, $cloudSize) {
        global $banned_types;
        $db = getDB();
        $query = "SELECT x AS eid, 0.1 AS r1, 0.1 AS r2, 0.3 AS r0, 0.5 AS trash
        FROM (SELECT x FROM (SELECT X.eid + Y.dumb AS x FROM (SELECT B.end AS eid
        FROM relation AS A, relation AS B WHERE A.type NOT IN ($banned_types)
        AND A.start = $centerEid AND B.type NOT IN ($banned_types) AND
        B.start = A.end LIMIT " . ($cloudSize * 4) . ") AS X, (SELECT 0 AS dumb)
            AS  Y)) ORDER BY random()";
        //$result = $db->querySingle($query, true); //??
        //return $result['end']; //??
    }

    /**
     * (gd) Centre pointe vers X, M pointe vers X aussi, on prend M.
     * Version optimisée de : "select start as eid from relation where end in (select end from relation where start = $centerEid) and type not in ($banned_types) order by random();"
     * Ce n'est toujours pas ça… : "select eid from (select B.start as eid from relation as A, relation as B where A.type not in ($banned_types) and A.start = $centerEid and B.type not in ($banned_types) and B.end = A.end limit 1) order by random();"
     * Tordu, mais ça marche \o/ . En fait il faut empêcher l'optimiseur de ramener le random avant le limit (et l'optimiseur est malin… :)
     * @global <type> $banned_types
     * @param <type> $cloudSize
     */
    public static function gdXPointsToMMPointsToXTakeM($cloudSize) {
        global $banned_types;
        $db = getDB();
        $query = "SELECT x as eid, 0.1 as r1, 0.1 as r2, 0.2 as r0, 0.6 as trash
        FROM (SELECT x from (SELECT X.eid + Y.dumb AS x FROM  (SELECT
        B.start AS eid FROM relation AS A, relation AS B WHERE A.type NOT IN
        ($banned_types) AND A.start = $centerEid AND B.type NOT IN ($banned_types)
        AND B.end = A.end limit " . ($cloudSize * 4) . ") AS X, (SELECT 0 AS dumb) AS Y))
        ORDER BY random()";
        //$result = $db->querySingle($query, true); //??
        //return $result['end']; //??
    }

    public static function gdGetGidFromGame() {
        return "select gid from game where gid = (abs(random()) % (select max(gid) from game))+1 or gid = (select max(gid) from game where gid > 0) order by gid limit 1;";
    }

// TODO Yoann : faire des tests d'erreur pour ces select ?
    public static function sqlGetGamesForId($gameId) {
        return "select gid, (select name from node where eid = eid_central_word) as name_central_word, eid_central_word, relation_1, relation_2 from game where gid = " . $gameId . ";";
    }

    public static function sqlGetWordEidAndName($gameId) {
        return "select eid_word,(select name from node where eid=eid_word) as name_word from game_cloud where gid = " . $gameId . ";";
    }

    public static function sqlGetInformationAboutGame($gameId) {
        return "select eid_word,(select name from node where eid=eid_word) as name_word, num, difficulty, totalWeight, probaR1, probaR2, probaR0, probaTrash from game_cloud where gid = " . $gameId . ";";
    }

    public static function sqlGameIsOK($pgid, $gid, $user) {
        return "SELECT 'ok' FROM played_game WHERE pgid = $pgid and $gid = $gid and login = '$user' and timestamp = -1;";
    }

    public static function sqlGetScoreForUser($user) {
        return "SELECT score FROM user WHERE login='" . $user . "';";
    }

    public static function sqlGetPlayedGameTime($pgid, $gid, $user) {
        return "SELECT timestamp FROM played_game WHERE pgid = $pgid and $gid = $gid and login = '$user';";
    }

    public static function sqlGetNumAndScoreFromGame($pgid, $gid) {
        return "SELECT num,score from played_game_cloud where pgid = $pgid and gid = $gid;";
    }
}

?>