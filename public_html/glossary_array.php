<?php
/**
 * glossary_array.php
 * 
 * PHP Version 5
 *
 * @package GDRsScorecard
 * @copyright 2011-2012 EcoEquity and Stockholm Environment Institute
 * @license All rights reserved
 * @link http://www.gdrights.org/
 */

require_once "help/HWTHelp/HWTHelp.php";

if (isset($_GET['id'])) {
    $glossary = new HWTHelp('def_link', 'glossary.php', 'calc_gloss');
    
    echo $glossary->getJSON($_GET['id']);
}


