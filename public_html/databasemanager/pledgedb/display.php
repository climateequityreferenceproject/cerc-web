<?php

require_once "config/config.php";
include_once 'functions.php';

function db_get_country_table($min_by_year = NULL, $max_by_year = NULL) {
    $db = db_connect();
    $yearwhere = "";
    if (isset($min_by_year)) { $yearwhere = " AND pledge.by_year >= " . $min_by_year . " "; }
    if (isset($max_by_year)) { $yearwhere .= " AND pledge.by_year <= " . $max_by_year . " "; }

    $query = "SELECT public, id, country.iso3 AS iso3, name, conditional, quantity, reduction_percent, ";
    $query .= "rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details ";
    $query .= "FROM country, pledge WHERE country.iso3 = pledge.iso3 " . $yearwhere ;
    $query .= "ORDER BY name, by_year, conditional;";

    $result = mysql_query($query, $db);
    mysql_close($db);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    } else {
        return $result;
    }
}    

$result = db_get_country_table($_REQUEST['min_year'], $_REQUEST['max_year']);
$data = array();
while($row = mysql_fetch_assoc($result))
{
   $data[] = $row;
}
 
$colNames = array_keys(reset($data));
$ret = "";

// header row
$ret .= (isset($_REQUEST['dl'])) ? "" : "<table border='1'>\n<tr>\n<th>";
foreach($colNames as $colName) {
    $ret .= $colName . ((isset($_REQUEST['dl'])) ? "\t" : "</th>\n<th>");
}
$ret .= (isset($_REQUEST['dl'])) ? "\n" : "</th>\n</tr>\n";

//print the rows
foreach($data as $row) {
$ret .= (isset($_REQUEST['dl'])) ? "" : "<tr>\n<td>";
    foreach($colNames as $colName) {
        $cell = $row[$colName];
        $cell = str_replace(chr(13) , " " , $cell);
        $cell = str_replace(chr(10) , " " , $cell);
        $ret .= $cell . ((isset($_REQUEST['dl'])) ? "\t" : "</td>\n<td>");
    }
    $ret .= (isset($_REQUEST['dl'])) ? "\n" : "</td>\n</tr>\n";
}
$ret .= (isset($_REQUEST['dl'])) ? "" : "</table>";

if (isset($_REQUEST['dl'])) {
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/tab-separated-values");
    header("Content-Length: " . filesize($ret)); 
    header("Content-Disposition: attachment; filename=\"pledges.xls\"" );
    header("Content-Description: PHP/INTERBASE Generated Data" );
    echo ($ret);
} else {
    echo '<a href="display.php?dl=1">Download as Exel file</a><br/><br/>';
    echo ($ret);
}