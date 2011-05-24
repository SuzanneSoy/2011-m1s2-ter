<?php

require_once("db.php");

class Game {

    private static $relationPhrases = array(
        0 => "%mc est en rapport avec %mn",
        5 => "%mc est un synonyme de %mn",
        6 => "%mc est une sorte de %mn",
        7 => "Un contraire de %mc est %mn",
        8 => "Un spécifique de %mc est %mn",
        9 => "%mn est une partie de %mc",
        10 => "%mc fait partie de %mn",
        13 => "Quoi/Qui pourrait %mc",
        15 => "Le lieu pour %mc est %mn",
        16 => "Un instrument pour %mc est %mn",
        17 => "Un caractéristique de %mc est %mn");
    private $db;
    private $centralWord;
    private $centralEID;
    private $centralPOSs;
    private $cloudEIDs;
    private $cloudWords;

    function __construct() {
        $this->db = getDB();
    }

    function setCentralWordWithEID($wordEID) {
        $this->centralEID = $wordEID;
    }

    function fetchRandomCentralEID() {
        $this->db = getDB();
        $query = "SELECT eid FROM random_center_node WHERE rowid
            = (abs(random()) % (SELECT max(rowid) FROM random_center_node))+1";
        $result = $this->db->querySingle($query, true);
        $this->centralEID = $result['eid'];
        $query = "SELECT name FROM node WHERE eid = $this->centralEID";
        $result = $this->db->querySingle($query, true);
        $this->centralWord = $result['name'];
        $this->centralPOSs = $this->getPOS($this->centralEID);
    }

    /**
     * Etant donné un mot passé en paramètre, on retourne le ou les POS.
     * Attention, parfois plusieurs réponses sont possibles : un mot
     * peut être à la fois un adjectif et un nom ou autre. Il y a quatre
     * POSs possible ici : adjectif, adverb, nom et verb qui sont représentées
     * par les strings 'adj', 'adv', 'nom' et 'verb' respectivement.
     * @param <type> $wordEID
     * @return string tableau de POS
     */
    function getPOS($wordEID) {
        $query = "SELECT end FROM relation WHERE type = 4 AND start = $wordEID;";
        $res = $this->db->query($query);

        $POSs = array();
        $cnt = 0;
        $adj = $adv = $nom = $ver = false;

        while ($tuple = $res->fetchArray()) {
            $endEID = $tuple['end'];
            $query = "SELECT name FROM node WHERE eid = $endEID";
            $res2 = $this->db->querySingle($query, true);
            $POSline = $res2['name'];

            if (preg_match("/^Adj:/", $POSline)) {
                if ($adj == false) {
                    $POSs[$cnt] = "Adj";
                    $cnt++;
                    $adj = true;
                }
            } else if (preg_match("/^Adv:/", $POSline)) {
                if ($adv == false) {
                    $POSs[$cnt] = "Adv";
                    $cnt++;
                    $adv = true;
                }
            } else if (preg_match("/^Nom:/", $POSline)) {
                if ($nom == false) {
                    $POSs[$cnt] = "Nom";
                    $cnt++;
                    $nom = true;
                }
            } else if (preg_match("/^Ver:/", $POSline)) {
                if ($ver == false) {
                    $POSs[$cnt] = "Ver";
                    $cnt++;
                    $ver = true;
                }
            }
        }
        $this->POSs = $POSs;
        return $POSs;
    }

    function getWordCloud($wordEID) {

//  TODO: Find a way to enumerate indices used (to skip some)
        for ($i = 5; $i < 10; $i++) {

            $query = "SELECT end FROM relation WHERE type = $i AND start = $wordEID";
            $res = $this->db->query($query);

            $this->cloudEIDs[$i] = array();
            $this->cloudWords[$i] = array();
            $cnt = 0;

            while ($tuple = $res->fetchArray()) {
                $eid = $this->cloudEIDs[$i][$cnt] = $tuple['end'];
                $query = "SELECT name FROM node WHERE eid = $eid";
                $res2 = $this->db->querySingle($query, true);
                $this->cloudWords[$i][$cnt] = $res2['name'];
                //echo "index " . $i . " | " . $cnt . ": ";
                //echo $this->cloudWords[$i][$cnt] . "<br />";
                $cnt++;
            }
        }
        return $this->cloudWords;
    }

    public function generateGame($wordEID) {
        $this->getPOS($wordEID);
        $this->getWordCloud($wordEID);
        return $this;
    }

    public function generateRandomGame() {
        $wordEID = $this->fetchRandomCentralEID();
        //$this->getPOS($wordEID);
        $this->getWordCloud($wordEID);
        echo $this->toString();
    }

    public function toString() {
        $s = "<dl>";
        //$s .= "<dt>Mot central</dt>" . "<dd>".$this->centralWord."</dd>";

        $s .= "<dt>POSs du mot central</dt>";
        echo "VARDULP : ";
        echo "<pre>";
        var_dump($this->centralPOSs);
        echo "</pre>";
        foreach ($this->centralPOSs AS $k => $v) {
            $s .= "<dd>" . $v . "</dd>";
        }
        foreach ($this->cloudWords AS $k1 => $v2) {
            echo "VARDUMP...V";
            var_dump($v) . "<br />";
            $s .= "<dt>Relation " . $v . "</dt>";
            foreach ($this->cloudWords[$v] AS $k2 => $v2) {
                echo "VARDUMP... K2";
                var_dump($k2) . "<br />";
                $s .= "<dd>" . $this->cloudWords[$k1][$k2] . "<dd>";
            }
        }
        echo "</dl>";
    }
}
?>