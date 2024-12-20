<?php
// Use the API: over time, will make things more consistent
require_once realpath(__DIR__ . "/../inc/dependencies/autoload.php"); // loading guzzle http client
require_once "frameworks/frameworks.php";
require "config.php";

function pledge_db_connect() {
    global $pledge_db_config;
    $db = mysqli_connect($pledge_db_config['host'], $pledge_db_config['user'], $pledge_db_config['pwd'], $pledge_db_config['dbname']);
    if (!$db) {
        die('Could not connect: ' . mysqli_connect_error());
    }
    return $db;
}

function pledge_query_db($query) {
    $db = pledge_db_connect();

    $result = mysqli_query($db, $query);
    if (!$result) {
        mysqli_close($db);
        die('Invalid query: ' . mysqli_error($db));
    }
    mysqli_close($db);

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
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $pledge_mlnUSD += $row['pledge'];
        $sources_array[] = $row['source'];
    }
    $sources = join("; ", $sources_array);
    
    $sql = "SELECT c_price_USD_per_tCO2e AS price FROM carbon_price WHERE year='" . $year . "'";
    $result = pledge_query_db($sql);
    // Only one row
    $row = mysqli_fetch_row($result);
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
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
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
    $row = mysqli_fetch_array($result, MYSQLI_ASSOC);
    mysqli_free_result($result);
    return $row;
}

function get_pledge_years($code, $conditional=null) {
    if ($conditional !== null) {
        $conditional_bool = $conditional ? 1 : 0;
        $conditional_string = 'conditional=' . $conditional_bool . ' AND ';
    } else {
        $conditional_string = ''; 
    }
    if (is_country($code)) {
        $ctryrgn_str = 'iso3="' . $code . '"';
    } else {
        $ctryrgn_str = 'region="' . $code . '"';
    }
    $sql = 'SELECT by_year FROM pledge WHERE ' . $conditional_string . $ctryrgn_str . ';';
    $result = pledge_query_db($sql);
    $years = array();
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $years[] = $row['by_year'];
    }
    sort($years, SORT_NUMERIC);
    mysqli_free_result($result);
    return array_unique($years, SORT_NUMERIC);
}

function get_processed_pledges($iso3, $shared_params, $dbfile=NULL) {
    $retval = array();
    
    $pathway = $shared_params['emergency_path']['value'];
    
    if ($dbfile) {
        $db = basename($dbfile);
    } else {
        $db = NULL;
    }
    
    $pledge_years = get_pledge_years($iso3, true);
    $retval['conditional'] = array();
    if (count($pledge_years) > 0) {
        foreach ($pledge_years as $year) {
            $pledge_info = get_pledge_information($iso3, true, $year);
            if (($pledge_info['public']=='1') || (Framework::is_dev())) {
                $retval['conditional'][$year] = process_pledges($pledge_info, $pathway, $db);
            }
        }
    }
    
    $pledge_years = get_pledge_years($iso3, false);
    $retval['unconditional'] = array();
    if (count($pledge_years) > 0) {
        foreach ($pledge_years as $year) {
            $pledge_info = get_pledge_information($iso3, false, $year);
            if (($pledge_info['public']=='1') || (Framework::is_dev())) {
                $retval['unconditional'][$year] = process_pledges($pledge_info, $pathway, $db);
            }
        }
    }
    return $retval;
}

function is_country($code) {
    $db = pledge_db_connect();
    $sql = 'SELECT iso3 FROM country WHERE iso3="' . $code . '";';

    $result = mysqli_query($db, $sql);
    if (!$result) {
        mysqli_close($db);
        die('Invalid query: ' . mysqli_error($db));
    }
    mysqli_close($db);

    return mysqli_num_rows($result) > 0;
}

function remove_trailing_zeros($input) {
    $temp=explode(".",$input);
    $temp[1]=rtrim($temp[1],"0");
    $output = $temp[0];
    if (!empty($temp[1])) $output.='.'.$temp[1];
    return $output;
}

function process_pledges($pledge_info, $pathway, $db) {
    global $URL_calc_api, $URL_calc_api_dev, $dev_calc_creds;
    if (Framework::is_dev()) {
        $api_url = $URL_calc_api_dev;
    } else {
        $api_url = $URL_calc_api ;
    }
    // First, get the parameter values used by the database
    $querystring = '?q=params';
    if ($db) {
        $querystring .= '&db=' . $db;
    }
    $auth = NULL;
    if (Framework::is_dev()) { $auth = array($dev_calc_creds['user'], $dev_calc_creds['pass']); }
    $client = new \GuzzleHttp\Client();
    try {
         $response = $client->request('GET', $api_url . $querystring, ['auth' => $auth]);
         $params = (array) json_decode($response->getBody());
    } catch (Exception $e) {
         echo $e->getMessage();
         $params = NULL;
    }
    
    $use_lulucf = (array) $params['use_lulucf'];
    $use_nonco2 = (array) $params['use_nonco2'];
    
    // Announce that we'd like to free up memory before reusing the variable
    unset($client);

    // Build up API query
    if ($pledge_info['rel_to_year']) {
        $years = $pledge_info['rel_to_year'] . "," . $pledge_info['by_year'];
        $numitems = 2;
    } else {
        $years = $pledge_info['by_year'];
        $numitems = 1;
    }
    $POST_params['years'] = $years;
    if (isset($pledge_info['iso3'])) {
        $ctryrgn = $pledge_info['iso3'];
    } elseif (isset($pledge_info['region'])) {
        $ctryrgn = $pledge_info['region'];
    } else {
        $ctryrgn = null;
    }
    $POST_params['countries'] = $ctryrgn;
    $POST_params['emergency_path'] = $pathway;
    if ($db) {
        $POST_params['db'] = $db;
    }
    $auth = NULL;
    if (Framework::is_dev()) { $auth = array($dev_calc_creds['user'], $dev_calc_creds['pass']); }
    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->request('POST', $api_url, ['form_params' => $POST_params, 'auth' => $auth, 'allow_redirects' => ['strict' => true]]);
        $response = (array) json_decode($response->getBody());
        // Oddly, the decode procedure sometimes seems to duplicate the first element.
        if (count($response) > $numitems) {
           $response = array_slice($response, 1);
        }
    } catch (Exception $e) {
        echo $e->getMessage();
        return NULL;
    }
    
    foreach ($response as $year_data_obj) {
        $year_data = (array) $year_data_obj;
        $year = $year_data['year'];
        $gdp[$year] = $year_data['gdp_blnUSDMER'];
        $bau[$year] = $year_data['fossil_CO2_MtCO2'];
        if ($use_lulucf['value'] && $pledge_info['include_lulucf']) {
            $bau[$year] += $year_data['LULUCF_MtCO2'];
        }
        if ($use_nonco2['value'] && $pledge_info['include_nonco2']) {
            $bau[$year] += $year_data['NonCO2_MtCO2e'];
        }
    }
    
    switch ($pledge_info['rel_to']) {
        case 'below':
            $factor = 1 - $pledge_info['reduction_percent']/100.0;
            $reduce_text_1 = 'by ';
            $reduce_text_2 = '% compared to ';
            break;
        case 'of':
            $factor = $pledge_info['reduction_percent']/100.0;
            $reduce_text_1 = 'to ';
            $reduce_text_2 = '% of ';
            break;
        default:
            // Shouldn't get here
    }
    $description = '';
    $by_factor = remove_trailing_zeros($pledge_info['reduction_percent']);
    switch ($pledge_info['quantity']) {
        case 'absolute':
            $description .= 'reduce total emissions ' . $reduce_text_1 . $by_factor . $reduce_text_2;
            if ($pledge_info['year_or_bau'] === 'bau') {
                $description .= 'baseline';
                $pledged_reduction = (1 - $factor) * $bau[$pledge_info['by_year']];
            } else {
                $description .= $pledge_info['rel_to_year'];
                $pledged_reduction = $bau[$pledge_info['by_year']] - $factor * $bau[$pledge_info['rel_to_year']];
            }
            break;
        case 'intensity':
            $description .= 'reduce emissions intensity ' . $reduce_text_1 . $by_factor . $reduce_text_2;
            if ($pledge_info['year_or_bau'] === 'bau') {
                // This option actually makes no sense, but take care of it just in case:
                $description .= 'baseline';
                $pledged_reduction = (1 - $factor) * $bau[$pledge_info['by_year']];
            } else {
                $description .= $pledge_info['rel_to_year'];
                $scaled_emiss = $gdp[$pledge_info['by_year']] * $bau[$pledge_info['rel_to_year']]/$gdp[$pledge_info['rel_to_year']];
                $pledged_reduction = $bau[$pledge_info['by_year']] - $factor * $scaled_emiss;
            }
            break;
        case 'target_Mt':
            $description .= ($bau[$pledge_info['by_year']] > $pledge_info['target_Mt']) ? "reduce " : "limit ";
            $description .= 'total emissions to ' . remove_trailing_zeros($pledge_info['target_Mt']) . 'Mt';
            $pledged_reduction = $bau[$pledge_info['by_year']] - $pledge_info['target_Mt'];
            break;
        default:
            // Shouldn't reach here
    }
    // CH: check if there is any additional data in the pledge database
    // those are stored in the 'caveat' field and follow this JSON syntax:
    // {"description_override":"Text of the Override"}
    // 
    $output_array = array ();
    preg_match("/{.*}/", $pledge_info['caveat'], $output_array);
    // this if loop gets entered if there is any JSON encoded data in the
    // caveat field, so deal with all possible cases accordingly
    if (isset($output_array[0])) { 
        $output = json_decode($output_array[0], TRUE);
        if (isset($output['description_override'])) { $description = $output['description_override']; }
        if (isset($output['help_label']) || isset($output['help_title']) || isset($output['help_text'])) {
            $helptext = "<a onclick='display_popup(\"" . $output['help_title'] . "\",\"". $output['help_text'] . "\")'>" . $output['help_label'] . "</a>";
        }
        if ((isset($output['unconditional'])) || (isset($output['conditional']))) {
            $condl_override = (isset($output['unconditional'])) ? 'unconditional' : 'conditional';
        }
    }
    $retvar = array('pledge' => $pledged_reduction, 'description' => $description);
    if (isset($helptext)) { $retvar['helptext'] = $helptext; }
    if (isset($condl_override)) { $retvar['conditionality_override'] = $condl_override; }
    if (isset($output['pledge_qualifier'])) { $retvar['pledge_qualifier']=" (" . $output['pledge_qualifier'] . ")"; }
    return $retvar;
}
