<?php

function db_connect() {
    $db_data = Constants::db_info();
    $db = mysql_connect($db_data['host'], $db_data['user'], $db_data['pwd']);
    if (!$db) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db($db_data['dbname'], $db);
    return $db;
}

function query_db($query) {
    $db = db_connect();
    
    $result = mysql_query($query, $db);
    if (!$result) {
        mysql_close($db);
        die('Invalid query: ' . mysql_error());
    }
    
    mysql_close($db);
    
    return $result;
}

function option_number($start, $end, $step, $default = NULL) {
    for ($i = $start; $i <= $end; $i += $step) {
        $selected = "";
        if ($i == $default) {
            $selected = ' selected="selected"';
        }
        printf('<option value="%1$d"%2$s>%1$d</option>', $i, $selected);
    }
}

function get_conditional_value($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['conditional'];
    } else {
        $retval = 0;
    }
    return $retval;
}

function get_include_nonco2($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['include_nonco2'];
    } else {
        $retval = 1;
    }
    return $retval;
}

function get_include_lulucf($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['include_lulucf'];
    } else {
        $retval = 0;
    }
    return $retval;
}

function get_quantity_value($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['quantity'];
    } else {
        $retval = 'absolute';
    }
    return $retval;
}

function get_reduction_percent($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['reduction_percent'];
    } else {
        $retval = null;
    }
    return $retval;
}

function get_relto_value($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['rel_to'];
    } else {
        $retval = 'below';
    }
    return $retval;
}

function get_yearbau_value($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['year_or_bau'];
    } else {
        $retval = 'year';
    }
    return $retval;
}

function get_relto_year($edit_array) {
    if ($edit_array && $edit_array['year_or_bau']==='year') {
        $retval = $edit_array['rel_to_year'];
    } else {
        $retval = null;
    }
    return $retval;
}

function get_by_year($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['by_year'];
    } else {
        $retval = 2020;
    }
    return $retval;
}

function get_text($edit_array, $entry) {
    if ($edit_array) {
        $retval = $edit_array[$entry];
    } else {
        $retval = '';
    }
    return $retval;
}

function make_ctryregion_list($edit_array) {
    if ($edit_array && !$edit_array['iso3']) {
        // ISO3 will be null if not set, so if it isn't null it's a country
        $ctry_checked = '';
        $region_checked = ' checked="checked"';
    } else {
        $ctry_checked = 'checked="checked"';
        $region_checked = '';
    }
$html = <<<HTML
    <input type="radio" name="country_or_region" value="country" $ctry_checked /><label for="iso3">Country: </label>
    <select name="iso3" id="iso3">
HTML;
        $result = query_db("SELECT iso3, name FROM country ORDER BY name;");
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            if ($edit_array && $row['iso3']===$edit_array['iso3']) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $html .= sprintf('<option value="%s"%s>%s</option>', $row['iso3'], $selected, $row['name']);
        }
        mysql_free_result($result);
$html .= <<<HTML
    </select><br />
    <input type="radio" name="country_or_region" value="region" $region_checked /><label for="region">Region: </label>
    <select name="region" id="region">
HTML;
        $result = query_db("SELECT region_code, name FROM region ORDER BY name;");
        while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
            if ($edit_array && $row['region_code']===$edit_array['region']) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $html .= sprintf('<option value="%s"%s>%s</option>', $row['region_code'], $selected, $row['name']);  
        }
        mysql_free_result($result);
$html .= <<<HTML
    </select><br />
HTML;

    return $html;
}
 
function check_for_new_regions() {
    // 1. get the currently known regions from the pledge database
    $pledge_db_regions = array ();
    $result = query_db("SELECT region_code, name FROM region ORDER BY name;");
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $pledge_db_regions[] = $row['region_code'] ;  
    }
    
    // 2. get the regions currently used by the calculator 
    // let's check if we have been passed an API database to reuse through the form
    //$db = $_REQUEST['db'];
    $db = $_COOKIE['db'];
    
    
    // we don't really know where the calculator is accessed from, so we want to 
    // construct the API link to retain the current "domain name space"
    if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
        $URL_calc_api = 'http://' . $_SERVER['HTTP_X_FORWARDED_HOST'] . '/calculator/api/';
    } else {
        $URL_calc_api = 'http://' . $_SERVER['HTTP_HOST'] . '/calculator/api/';
    }

    // check is this database still exists
    if (isset($db)) {
        $req =& new HTTP_Request($URL_calc_api . "?q=regions&db=" . $db);
        $req->setMethod(HTTP_REQUEST_METHOD_GET);
        $req->sendRequest();
        $code = $req->getResponseCode();
        if ( $code == 410) {
            unset($db);
        }
    }
    
    // if we don't have a database to reuse, we request a new copy from the calculator API
    if (!$db) { 
        $req =& new HTTP_Request($URL_calc_api . "?q=new_db");
        $req->setMethod(HTTP_REQUEST_METHOD_GET);
        if (!PEAR::isError($req->sendRequest())) {
            $response = (array) json_decode($req->getResponseBody());
            $db = $response['db'];
        } else {
            throw new Exception($req->getMessage());
        }
    }

    // now let's get the actual list of regions using this database copy
    $req =& new HTTP_Request($URL_calc_api . "?q=regions&db=" . $db);
    $req->setMethod(HTTP_REQUEST_METHOD_GET);
    if (!PEAR::isError($req->sendRequest())) {
        // Note: json_decode returns arrays as StdClass, so have to cast
        $calc_regions = (array) json_decode($req->getResponseBody());
    } else {
        throw new Exception($req->getMessage());
    }
    
    // 3. go through the list and check if it's also part of the pledge
    // data base region list. If not, add it.
    foreach ($calc_regions as $region) {
        $reg = (array) $region;
        if (!(in_array ($reg['region_code'], $pledge_db_regions))) {
            echo ("<font color=\"red\"><b>Region " . $reg['name'] . " (" . $reg['region_code'] . ") is in the calculator database but not in the pledge database. It has been added.</b></font><br>\n");
            $sql = "INSERT INTO `region` (`region_code`, `name`) VALUES (\"" . $reg['region_code'] . "\",\"". $reg['name'] . "\");";
            $result = query_db($sql);
        }
    }
 
    // we save the code of the database we have used as a cookie so it can be 
    // re-used, otherwise, every time the "region" drop down field is created, 
    // a new copy of the database is created.
    setcookie("db", $db, time()+3600);
}

