<?php
// Use this to get URL via AJAX
require_once("frameworks/frameworks.php");

$fw = new Framework::$frameworks['gdrs']['class'];
// Get a scorecard url
$query_string = $fw->get_params_as_query(Framework::get_good_db());

// not sure whether the functions of core.php are known at this point, 
// so instead of get_host_name() we just do this here directly
if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
} else {
    $host = $_SERVER['HTTP_HOST'];     
}
if (Framework::is_dev()) {
    $scorecard_url = 'http://' . $host . '/scorecard_dev/?' . $query_string;
} else {
    $scorecard_url = 'http://' . $host . '/scorecard/?' . $query_string;
}

echo $scorecard_url;

