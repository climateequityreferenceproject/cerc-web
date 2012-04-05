<?php
// Use the API: over time, will make things more consistent
require_once "HTTP/Request.php";

function pledge_db_connect() {
    $db = mysql_connect('localhost', 'pledges', '***REMOVED***');
    if (!$db) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db("pledges", $db);
    
    return $db;
}

function pledge_query_db($query) {
    $db = pledge_db_connect();
    
    $result = mysql_query($query, $db);
    if (!$result) {
        mysql_close($db);
        die('Invalid query: ' . mysql_error());
    }
    
    mysql_close($db);
    
    return $result;
}

function pledge_nice_number($val) {
    if ($val > 0 && $val < 0.5) {
        $val_string = "<0.5";
    } elseif ($val < 0 && $val > -0.5) {
        $val_string = ">-0.5";
    } else {
        $val_string = number_format($val);
    }
    return $val_string;
}

// Returns pledge in MtCO2e, based on pledged dollar amount and carbon price
function get_intl_pledge($iso3, $year) {
    if (!$iso3 || !$year) {
        return array('intl_pledge' => NULL, 'intl_source' => NULL, 'intl_price' => NULL);
    }
    $sql = "SELECT iso3, pledge_mln_USD AS pledge, source FROM intl_pledge WHERE iso3='" . $iso3 . "'";
    $result = pledge_query_db($sql);
    $sources_array = array();
    $pledge_mlnUSD = 0;
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $pledge_mlnUSD += $row['pledge'];
        $sources_array[] = $row['source'];
    }
    $sources = join("; ", $sources_array);
    
    $sql = "SELECT c_price_USD_per_tCO2e AS price FROM carbon_price WHERE year='" . $year . "'";
    $result = pledge_query_db($sql);
    // Only one row
    $row = mysql_fetch_row($result);
    $price = $row[0];
    
    $pledge_MtCO2e = $pledge_mlnUSD/$price;
    
    return array('intl_pledge' => $pledge_MtCO2e, 'intl_source' => $sources, 'intl_price'=> $price);
}

// $iso3 is the three-letter code, $conditional is a boolean saying whether it's conditional or not
function get_min_target_year($code, $conditional) {
    // To protect against SQL injection, force conditional to be boolean
    $conditional_bool = $conditional ? 1 : 0;
    if (is_country($code)) {
        $ctryrgn_str = 'iso3="' . $code . '"';
    } else {
        $ctryrgn_str = 'region="' . $code . '"';
    }
    $sql = 'SELECT MIN(by_year) AS year FROM pledge WHERE conditional=' . $conditional_bool . ' AND ' . $ctryrgn_str . ' AND public = 1;';
    $result = pledge_query_db($sql);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    mysql_free_result($result);
    if (!$row) {
        return NULL;
    } else {
    return $row['year'];
}
}

function get_pledge_information($code, $conditional, $year) {
    // Protect against injection
    $conditional_bool = $conditional ? 1 : 0;
    $year_checked = intval($year);
    if (is_country($code)) {
        $ctryrgn_str = 'iso3="' . $code . '"';
    } else {
        $ctryrgn_str = 'region="' . $code . '"';
    }
    $sql = 'SELECT * FROM pledge WHERE conditional=' . $conditional_bool . ' AND ' . $ctryrgn_str . ' AND by_year=' . $year_checked . ';';
    $result = pledge_query_db($sql);
    $row = mysql_fetch_array($result, MYSQL_ASSOC);
    mysql_free_result($result);
    return $row;
}

function get_processed_pledges($iso3, $shared_params, $dbfile=NULL) {
    $retval = array();
    
    $pathway = $shared_params['emergency_path']['value'];
    
    if ($dbfile) {
        $db = basename($dbfile);
    } else {
        $db = NULL;
    }
    
    $year = get_min_target_year($iso3, true);
    if ($year) {
        $retval['conditional']['year'] = $year;
        $pledge_info = get_pledge_information($iso3, true, $year);
        $retval['conditional']['pledge_info'] = process_pledges($pledge_info, $pathway, $db);
    } else {
        $retval['conditional'] = NULL;
    }
    
    $year = get_min_target_year($iso3, false);
    if ($year) {
        $retval['unconditional']['year'] = $year;
        $retval['unconditional']['year'] = $year;
        $pledge_info = get_pledge_information($iso3, false, $year);
        $retval['unconditional']['pledge_info'] = process_pledges($pledge_info, $pathway, $db);
    } else {
        $retval['unconditional'] = NULL;
    }
    
    return $retval;
}

function is_country($code)
{
    $db = pledge_db_connect();
    
    $sql = 'SELECT iso3 FROM country WHERE iso3="' . $code . '";';
    
    $result = mysql_query($sql, $db);
    if (!$result) {
        mysql_close($db);
        die('Invalid query: ' . mysql_error());
    }
    mysql_close($db);
    
    return mysql_num_rows($result) > 0;
}

function process_pledges($pledge_info, $pathway, $db) {
    $api_url = "http://gdrights.org/calculator_dev/api/";
    // First, get the parameter values used by the database
    $querystring = '?q=params';
    if ($db) {
        $querystring .= '&db=' . $db;
    }
    $req =& new HTTP_Request($api_url . $querystring);
    $req->setMethod(HTTP_REQUEST_METHOD_GET);
    if (!PEAR::isError($req->sendRequest())) {
         $params = (array) json_decode($req->getResponseBody());
    } else {
        $params = NULL;
    }
    
    $use_lulucf = (array) $params['use_lulucf'];
    $use_nonco2 = (array) $params['use_nonco2'];
    
    // Announce that we'd like to free up memory before reusing the variable
    unset($req);
    
    // Build up API query
    $req =& new HTTP_Request($api_url);
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    if ($pledge_info['rel_to_year']) {
        $years = $pledge_info['rel_to_year'] . "," . $pledge_info['by_year'];
    } else {
        $years = $pledge_info['by_year'];
    }
    $req->addPostData("years", $years);
    if (isset($pledge_info['iso3'])) {
        $ctryrgn = $pledge_info['iso3'];
    } elseif (isset($pledge_info['region'])) {
        $ctryrgn = $pledge_info['region'];
    } else {
        $ctryrgn = null;
    }
    $req->addPostData("countries", $ctryrgn);
    $req->addPostData("emergency_path", $pathway);
    if ($db) {
        $req->addPostData("db", $db);
    }
    if (!PEAR::isError($req->sendRequest())) {
         $response = json_decode($req->getResponseBody());
         // Oddly, the decode procedure seems to duplicate the first element, so get the tail:
         $response = array_slice($response, 1);
    } else {
        return NULL;
    }
    
    foreach ($response as $year_data_obj) {
        $year_data = (array) $year_data_obj;
        $year = $year_data['year'];
        $gdp[$year] = $year_data['gdp_blnUSDMER'];
        $bau[$year] = $year_data['fossil_CO2_MtCO2'];
        if ($use_lulucf['value']) {
            $bau[$year] += $year_data['LULUCF_MtCO2'];
        }
        if ($use_nonco2['value']) {
            $bau[$year] += $year_data['NonCO2_MtCO2e'];
        }
        $alloc[$year] = $year_data['gdrs_alloc_MtCO2'];
    }
    
    $gdrs_reduction = $bau[$pledge_info['by_year']] - $alloc[$pledge_info['by_year']];
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
    $description = 'reduce ';
    $by_factor = $pledge_info['reduction_percent'];
    switch ($pledge_info['quantity']) {
        case 'absolute':
            $description .= 'total emissions by ' . $by_factor . '% compared to ';
            if ($pledge_info['year_or_bau'] === 'bau') {
                $description .= 'business-as-usual';
                $pledged_reduction = (1 - $factor) * $bau[$pledge_info['by_year']];
            } else {
                $description .= $pledge_info['rel_to_year'];
                $pledged_reduction = $bau[$pledge_info['by_year']] - $factor * $bau[$pledge_info['rel_to_year']];
            }
            break;
        case 'intensity':
            $description .= 'emissions intensity by ' . $by_factor . '% compared to ';
            if ($pledge_info['year_or_bau'] === 'bau') {
                // This option actually makes no sense, but take care of it just in case:
                $description .= 'business-as-usual';
                $pledged_reduction = (1 - $factor) * $bau[$pledge_info['by_year']];
            } else {
                $description .= $pledge_info['rel_to_year'];
                $scaled_emiss = $gdp[$pledge_info['by_year']] * $bau[$pledge_info['rel_to_year']]/$gdp[$pledge_info['rel_to_year']];
                $pledged_reduction = $bau[$pledge_info['by_year']] - $factor * $scaled_emiss;
            }
            break;
        default:
            // Shouldn't reach here
    }
    
    return array('pledge' => $pledged_reduction, 'description' => $description);
}
