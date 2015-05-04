<?php
// Use this to get URL via AJAX
require_once("frameworks/frameworks.php");
require_once('config.php');

$fw = new Framework::$frameworks['gdrs']['class'];
// Get a scorecard url
$query_string = $fw->get_params_as_query(Framework::get_good_db());

if (Framework::is_dev()) {
    $scorecard_url = $URL_sc_dev . '?' . $query_string;
} else {
    $scorecard_url = $URL_sc . '?' . $query_string;
}

echo $scorecard_url;

