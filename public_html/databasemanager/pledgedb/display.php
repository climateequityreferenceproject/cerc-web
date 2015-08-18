<?php

require_once "config/config.php";
include_once 'functions.php';
include_once('api_functions.php');

function db_get_country_table($min_by_year = NULL, $max_by_year = NULL, $region = FALSE) {
    $db = db_connect();
    $yearwhere = "";
    if ($min_by_year!==0) { $yearwhere .= " AND pledge.by_year >= " . $min_by_year . " "; }
    if ($max_by_year!==0) { $yearwhere .= " AND pledge.by_year <= " . $max_by_year . " "; }

    if ($region)  {
        $query = "SELECT public, id, region AS iso3, name, conditional, quantity, reduction_percent, ";
        $query .= "rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details, ";
        $query .= "target_Mt, target_Mt_CO2, target_Mt_nonCO2, target_Mt_LULUCF ";
        $query .= "FROM region, pledge WHERE region.region_code = pledge.region " . $yearwhere ;
        $query .= "ORDER BY name, by_year, conditional;";    
    } else {
        $query = "SELECT public, id, country.iso3 AS iso3, name, conditional, quantity, reduction_percent, ";
        $query .= "rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details, ";
        $query .= "target_Mt, target_Mt_CO2, target_Mt_nonCO2, target_Mt_LULUCF ";
        $query .= "FROM country, pledge WHERE country.iso3 = pledge.iso3 " . $yearwhere ;
        $query .= "ORDER BY name, by_year, conditional;";
    }

    $result = mysql_query($query, $db);
    mysql_close($db);
    if (!$result) {
        die('Invalid query(b): ' . mysql_error() . " (" . $query . ")");
    } else {
        return $result;
    }
}    

function db_get_pledge_years($min_by_year = NULL, $max_by_year = NULL) {
    $db = db_connect();
    $yearwhere = "(1=1)";
    if ($min_by_year!==0) { $yearwhere .= " AND pledge.by_year >= " . $min_by_year . " "; }
    if ($max_by_year!==0) { $yearwhere .= " AND pledge.by_year <= " . $max_by_year . " "; }

    $query = "SELECT by_year FROM pledge WHERE " . $yearwhere . "ORDER BY by_year;";
    $result = mysql_query($query, $db);
    mysql_close($db);
    if (!$result) { die('Invalid query(a): ' . mysql_error() . " (" . $query . ")"); } 
    $years = array();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $years[] = $row['by_year'];
    }
    sort($years, SORT_NUMERIC);
    mysql_free_result($result);
    return array_unique($years, SORT_NUMERIC);
}    

function db_get_pledge_countries($pledge_years = NULL, $regions=FALSE) {
    $db = db_connect();
    $region_or_country = $regions ? "region" : "iso3";
    $yearwhere = "";
    foreach($pledge_years as $pledge_year) {
        $yearwhere .= (strlen($yearwhere)>0) ? "OR " : "";
        $yearwhere .= "pledge.by_year = " . $pledge_year . " "; 
    }
    $query = "SELECT " . $region_or_country . " FROM pledge WHERE " . $yearwhere . "ORDER BY " . $region_or_country . ";";
    $result = mysql_query($query, $db);
    mysql_close($db);
    if (!$result) { die('Invalid query(c): ' . mysql_error() . " (" . $query . ")"); } 
    $countries = array();
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $countries[] = $row[$region_or_country];
    }
    sort($countries, SORT_STRING);
    mysql_free_result($result);
    return array_unique($countries, SORT_STRING);
}    

// get pledges from the pledge database 
$data = array();
$result = db_get_country_table(intval($_REQUEST['min_year']), intval($_REQUEST['max_year']));
while($row = mysql_fetch_assoc($result)) { $data[$row['id']] = $row; }
//get pledges for regions, too
$result = db_get_country_table(intval($_REQUEST['min_year']), intval($_REQUEST['max_year']), TRUE);
while($row = mysql_fetch_assoc($result)) { $data[$row['id']] = $row; }

// get the BAU values for the relevant years from the core database
$pledge_years = db_get_pledge_years(intval($_REQUEST['min_year']), intval($_REQUEST['max_year']));
// $parms1['years'] = trim(implode(",",$pledge_years),",");
// turns out I need baseyear data, too, so just getting BAUs for pledge years ain't good enough
for ($x = 1990; $x <= max($pledge_years); $x++) { $pledge_years2[] = $x; }
$parms1['years'] = trim(implode(",",$pledge_years2),",");
$parms1['countries'] = trim(implode(",",db_get_pledge_countries($pledge_years)),",");
$pledge_regions = db_get_pledge_countries($pledge_years,TRUE); 
if (count($pledge_regions)>0) { $parms1['countries'] .= ((strlen($parms1['countries'])>0) ? "," : "") . trim(implode(",",$pledge_regions),","); }
if (isset($_COOKIE['db'])) { 
    $db = $_COOKIE['db']; 
} else { 
    $db = get_new_API_DB(); 
    setcookie("db", $db, time()+604800);    // cookies must be sent before any output from your script
}
$data_list = get_data($parms1, $db);
$bau = array();
$keep_these_codes = array ("fossil_CO2_MtCO2","LULUCF_MtCO2","NonCO2_MtCO2e","gdp_blnUSDMER");
foreach ($data_list as $entry) {
    $temp = (array) $entry;
    foreach ($temp as $key => $value) {
        if (in_array($key,$keep_these_codes)) { $bau[$temp['code']][$temp['year']][$key] = floatval($value); }
    }
}

// calculate targets in Mt
foreach ($data as $pledge_info) {
    switch ($pledge_info['rel_to']) {
        case 'below':
            $factor = 1 - $pledge_info['reduction_percent']/100.0;
            break;
        case 'of':
            $factor = $pledge_info['reduction_percent']/100.0;
            break;
        default:
            // Shouldn't get here
    }
    $baseline  = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['fossil_CO2_MtCO2'];
    $baseline += $bau[$pledge_info['iso3']][$pledge_info['by_year']]['LULUCF_MtCO2']     * intval($pledge_info['include_lulucf']);
    $baseline += $bau[$pledge_info['iso3']][$pledge_info['by_year']]['NonCO2_MtCO2e']    * intval($pledge_info['include_nonco2']);
    $baseyear  = $bau[$pledge_info['iso3']][$pledge_info['rel_to_year']]['fossil_CO2_MtCO2'];
    $baseyear += $bau[$pledge_info['iso3']][$pledge_info['rel_to_year']]['LULUCF_MtCO2']     * intval($pledge_info['include_lulucf']);
    $baseyear += $bau[$pledge_info['iso3']][$pledge_info['rel_to_year']]['NonCO2_MtCO2e']    * intval($pledge_info['include_nonco2']);
    switch ($pledge_info['quantity']) {
        case 'absolute':
            if ($pledge_info['year_or_bau'] === 'bau') {
                $pledged_reduction = (1 - $factor) * $baseline;
            } else {
                $pledged_reduction = $baseline - $factor * $baseyear;
            }
            break;
        case 'intensity':
            if ($pledge_info['year_or_bau'] === 'bau') {
                // Erik: This option actually makes no sense, but take care of it just in case:
                $pledged_reduction = (1 - $factor) * $baseline;
            } else {
                $gdp['by_year'] = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['gdp_blnUSDMER'];
                $gdp['rel_to_year'] = $bau[$pledge_info['iso3']][$pledge_info['rel_to_year']]['gdp_blnUSDMER'];
                $scaled_emiss = $gdp['by_year'] * $baseyear / $gdp['rel_to_year'];
                $pledged_reduction = $baseline - $factor * $scaled_emiss;
            }
            break;
        case 'target_Mt':
            $pledged_reduction = $baseline - $pledge_info['target_Mt'];
            break;
        default:
            // Shouldn't reach here
    }
    if ((floatval($pledge_info['target_Mt_CO2'])!=0) && (floatval($pledge_info['target_Mt_LULUCF'])!=0) && (floatval($pledge_info['target_Mt_nonCO2'])!=0)) {
        $co2 = $pledge_info['target_Mt_CO2'];
        $lulucf = $pledge_info['target_Mt_LULUCF'];
        $nonco2 = $pledge_info['target_Mt_nonCO2'];
    } else {
        $co2 = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['fossil_CO2_MtCO2'];
        $lulucf = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['LULUCF_MtCO2'];
        $nonco2 = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['NonCO2_MtCO2e'];
    }
    $data[$pledge_info['id']]['BAU_Mt_CO2'] =  $bau[$pledge_info['iso3']][$pledge_info['by_year']]['fossil_CO2_MtCO2'];
    $data[$pledge_info['id']]['BAU_Mt_LULUCF'] =  $bau[$pledge_info['iso3']][$pledge_info['by_year']]['LULUCF_MtCO2'];
    $data[$pledge_info['id']]['BAU_Mt_nonCO2'] =  $bau[$pledge_info['iso3']][$pledge_info['by_year']]['NonCO2_MtCO2e'];
    $calcd_target_Mt_Total = $baseline - $pledged_reduction;
    $calcd_target_Mt_CO2   = $calcd_target_Mt_Total * $co2/($co2+(intval($pledge_info['include_nonco2'])*$nonco2)+(intval($pledge_info['include_lulucf'])*$lulucf));
    $data[$pledge_info['id']]['calcd_target_Mt_Total'] = $calcd_target_Mt_Total;
    $data[$pledge_info['id']]['calcd_target_Mt_CO2']   = $calcd_target_Mt_CO2;
    $data[$pledge_info['id']]['calcd_target_Mt_LULUCF']= $lulucf/$co2 * $calcd_target_Mt_CO2;
    $data[$pledge_info['id']]['calcd_target_Mt_nonCO2']= $nonco2/$co2 * $calcd_target_Mt_CO2;
    $data[$pledge_info['id']]['pledged_reduction']=$pledged_reduction;
}
 
$colNames = array_keys(reset($data));
$ret = "";

// header row
if (!(isset($_REQUEST['dl']))) {
    $ret .= '<html><head>';
    $ret .= '<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>';
    $ret .= '<script type="text/javascript" src="js/jquery.floatThead.min.js?v=1.2.12"></script>';
    $ret .= '<link rel="stylesheet" type="text/css" href="css/pledges.css" />';
    $ret .= '</head><body>';
    $ret .= '<a href="display.php?dl=1&min_year=' .$_REQUEST['min_year'] . '&max_year=' .$_REQUEST['max_year'] . '">Download as Exel file</a><br/><br/>';
    $ret .= "<table border='1' class='table pledges'>\n<thead>\n<tr>\n<th>";
}
foreach($colNames as $colName) {
    $ret .= $colName . ((isset($_REQUEST['dl'])) ? "\t" : "</th>\n<th>");
}
$ret .= (isset($_REQUEST['dl'])) ? "\n" : "</th>\n</tr>\n</thead>\n";

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
    echo ("<---END OF DATA--->");
    
} else {
    echo ($ret);
    echo ("<---END OF DATA--->");
    echo "<script>";
    echo "    $(document).ready(function(){";
    echo "        $('table.pledges').floatThead({useAbsolutePositioning: false});";
    echo "    });";
    echo "</script>";
    echo "</body>";
    echo "</html>";    
}
