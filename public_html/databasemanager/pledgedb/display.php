<?php

require_once "config/config.php";
include_once 'functions.php';
include_once 'api_functions.php';

function db_get_country_table($min_by_year = NULL, $max_by_year = NULL, $region = FALSE, $public = NULL) {
    $db = db_connect();
    $where = "";
    if (isset($min_by_year)) { $where .= " AND pledge.by_year >= " . $min_by_year . " "; }
    if (isset($max_by_year)) { $where .= " AND pledge.by_year <= " . $max_by_year . " "; }
    if (isset($public)) { $where .= " AND pledge.public = " . $public . " "; }

    if ($region)  {
        $query = "SELECT public, id, region AS iso3, name, conditional, quantity, reduction_percent, ";
        $query .= "rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details, ";
        $query .= "target_Mt, target_Mt_CO2, target_Mt_nonCO2, target_Mt_LULUCF ";
        $query .= "FROM region, pledge WHERE region.region_code = pledge.region " . $where ;
        $query .= "ORDER BY name, by_year, conditional;";    
    } else {
        $query = "SELECT public, id, country.iso3 AS iso3, name, conditional, quantity, reduction_percent, ";
        $query .= "rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details, ";
        $query .= "target_Mt, target_Mt_CO2, target_Mt_nonCO2, target_Mt_LULUCF ";
        $query .= "FROM country, pledge WHERE country.iso3 = pledge.iso3 " . $where ;
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
    $where = "(1=1)";
    if (isset($min_by_year)) { $where .= " AND pledge.by_year >= " . $min_by_year . " "; }
    if (isset($max_by_year)) { $where .= " AND pledge.by_year <= " . $max_by_year . " "; }

    $query = "SELECT by_year FROM pledge WHERE " . $where . "ORDER BY by_year;";
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
    $query = "SELECT " . $region_or_country . " FROM pledge " . ((strlen($yearwhere)>0) ? "WHERE " . $yearwhere : "") . "ORDER BY " . $region_or_country . ";";
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
$min_year = (isset($_REQUEST['min_year']) & (strlen($_REQUEST['min_year'])>0)) ? intval($_REQUEST['min_year']) : NULL;
$max_year = (isset($_REQUEST['max_year']) & (strlen($_REQUEST['max_year'])>0)) ? intval($_REQUEST['max_year']) : NULL;
$public = isset($_REQUEST['public']) ? ((($_REQUEST['public']=='0') || ($_REQUEST['public']=='no')) ? 0 : 1) : NULL;
$api_params['dev'] = (($_REQUEST['dev']=='1') || ($_REQUEST['dev']=='yes') || ($_REQUEST['dev']=='dev')) ? true : false;
$data = array();
$result = db_get_country_table($min_year, $max_year, FALSE, $public);
while($row = mysql_fetch_assoc($result)) { $data[$row['id']] = $row; }
//get pledges for regions, too
$result = db_get_country_table($min_year, $max_year, TRUE, $public);
while($row = mysql_fetch_assoc($result)) { $data[$row['id']] = $row; }

// get the BAU values for the relevant years from the core database
$pledge_years = db_get_pledge_years($min_year, $max_year);
// $parms1['years'] = trim(implode(",",$pledge_years),",");
// turns out I need baseyear data, too, so just getting BAUs for pledge years ain't good enough
for ($x = 1990; $x <= max($pledge_years); $x++) { $pledge_years2[] = $x; }
$parms1['years'] = trim(implode(",",$pledge_years2),",");
$parms1['countries'] = trim(implode(",",db_get_pledge_countries($pledge_years)),",");
$pledge_regions = db_get_pledge_countries($pledge_years,TRUE); 
if (count($pledge_regions)>0) { $parms1['countries'] .= ((strlen($parms1['countries'])>0) ? "," : "") . trim(implode(",",$pledge_regions),","); }
$db = unserialize($_COOKIE['db']); 
if (!(exists_API_DB($db, $api_params))) {
    $db = get_new_API_DB($api_params);
    setcookie("db", serialize($db), time()+604800);    // cookies must be sent before any output from your script
}
$data_list = get_data($parms1, $db, $api_params);
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
            break;
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
            if ((floatval($pledge_info['target_Mt_CO2'])!=0) && (floatval($pledge_info['target_Mt_LULUCF'])!=0) && (floatval($pledge_info['target_Mt_nonCO2'])!=0)) {
                $pledged_reduction = $baseline - $pledge_info['target_Mt_CO2'] - (intval($pledge_info['include_nonco2']) * floatval($pledge_info['target_Mt_nonCO2'])) - (intval($pledge_info['include_lulucf']) * floatval($pledge_info['target_Mt_LULUCF']));                
            } else {
                $pledged_reduction = $baseline - $pledge_info['target_Mt'];
            }
            break;
        default:
            break;
    }
    // add BAU info to output data array
    $data[$pledge_info['id']]['BAU_Mt_CO2']    =  $bau[$pledge_info['iso3']][$pledge_info['by_year']]['fossil_CO2_MtCO2'];
    $data[$pledge_info['id']]['BAU_Mt_LULUCF'] =  $bau[$pledge_info['iso3']][$pledge_info['by_year']]['LULUCF_MtCO2'];
    $data[$pledge_info['id']]['BAU_Mt_nonCO2'] =  $bau[$pledge_info['iso3']][$pledge_info['by_year']]['NonCO2_MtCO2e'];
    // calculate (and add to output data array) the breakdown of the pledge into source categories
    if ((floatval($pledge_info['target_Mt_CO2'])!=0) && (floatval($pledge_info['target_Mt_LULUCF'])!=0) && (floatval($pledge_info['target_Mt_nonCO2'])!=0)) {
        // in case a specific breakdown was provided, we use it to proportionally calculate the 
        // ratio of sources within what we calculate to be the total target
        $co2 = $pledge_info['target_Mt_CO2'];
        $lulucf = $pledge_info['target_Mt_LULUCF'];
        $nonco2 = $pledge_info['target_Mt_nonCO2'];
        $calcd_target_Mt_Total = $baseline - $pledged_reduction;
        $calcd_target_Mt_CO2   = $calcd_target_Mt_Total * $co2/($co2+(intval($pledge_info['include_nonco2'])*$nonco2)+(intval($pledge_info['include_lulucf'])*$lulucf));
//        $data[$pledge_info['id']]['calcd_target_Mt_Total'] = $calcd_target_Mt_Total;
        $data[$pledge_info['id']]['obsolete'] = "obsolete";  // this was a source of confusion since it was unclear whether it contained all sectors or just those coverd by the pledge. keeping this in here to maintain structure of xls download
        $data[$pledge_info['id']]['calcd_target_Mt_CO2']   = $calcd_target_Mt_CO2;
        $data[$pledge_info['id']]['calcd_target_Mt_LULUCF']= $lulucf/$co2 * $calcd_target_Mt_CO2;
        $data[$pledge_info['id']]['calcd_target_Mt_nonCO2']= $nonco2/$co2 * $calcd_target_Mt_CO2;
    } else {
        // default method: 
        // source categories that are included in the pledge are broken down in proportion to their share of the target year BAU
        // source categories that are not included are set to their absolute Mt BAU level
        $co2 = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['fossil_CO2_MtCO2']; // BAU CO2 in target year
        $lulucf = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['LULUCF_MtCO2'];  // BAU LULUCF CO2 in target year
        $nonco2 = $bau[$pledge_info['iso3']][$pledge_info['by_year']]['NonCO2_MtCO2e']; // BAU non-CO2 in target year
        $calcd_target_Mt_Total = $baseline - $pledged_reduction;
//        $data[$pledge_info['id']]['calcd_target_Mt_Total'] = $calcd_target_Mt_Total;
        $data[$pledge_info['id']]['obsolete'] = "obsolete";  // this was a source of confusion since it was unclear whether it contained all sectors or just those coverd by the pledge. keeping this in here to maintain structure of xls download
        $data[$pledge_info['id']]['calcd_target_Mt_CO2']   = $calcd_target_Mt_Total * $co2 / $baseline; // remember, $baseline only includes the sources specified in the pledge
        $data[$pledge_info['id']]['calcd_target_Mt_LULUCF']= ($pledge_info['include_lulucf'] == 1 ) ? ($calcd_target_Mt_Total * $lulucf / $baseline) : $lulucf;
        $data[$pledge_info['id']]['calcd_target_Mt_nonCO2']= ($pledge_info['include_nonco2'] == 1 ) ? ($calcd_target_Mt_Total * $nonco2 / $baseline) : $nonco2;
    }
    $data[$pledge_info['id']]['pledged_reduction_all_sectors']=$pledged_reduction;
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
$ret .= "<---END OF DATA--->";

if (isset($_REQUEST['dl'])) {
    $filename = isset($_REQUEST['filename']) ? $_REQUEST['filename'] : "pledges.xls";
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: text/tab-separated-values");
    header("Content-Length: " . strlen($ret)); 
    header("Content-Disposition: attachment; filename=\"" . $filename . "\"" );
    header("Content-Description: PHP/INTERBASE Generated Data" );
    echo ($ret);
    
} else {
    echo ($ret);
    echo "<script>";
    echo "    $(document).ready(function(){";
    echo "        $('table.pledges').floatThead({useAbsolutePositioning: false});";
    echo "    });";
    echo "</script>";
    echo "</body>";
    echo "</html>";    
}
