<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Word
 *
 * @author John CHARRON
 */
class Word {
    private $word;
    private $eid;

    function __construct($word, $eid){
        $this->word = $word;
        $this->eid = $eid;
    }
}
?>
