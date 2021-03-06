<?php

function db_connect() {
    $db_data = Constants::db_info();
    $db = mysqli_connect($db_data['host'], $db_data['user'], $db_data['pwd'], $db_data['dbname']);
    if (!$db) {
        die('Could not connect: ' . mysqli_connect_error());
    }
    return $db;
}

function query_db($query) {
    $db = db_connect();
    $result = mysqli_query($db, $query);
    if (!$result) {
        mysqli_close($db);
        die('Invalid query: ' . mysqli_error($db));
    }
    mysqli_close($db);
    return $result;
}

function remove_trailing_zeros($input) {
    $temp=explode(".",$input);
    $temp[1]=rtrim($temp[1],"0");
    $output = $temp[0];
    if (!empty($temp[1])) $output.='.'.$temp[1];
    return $output;
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

function get_public_value($edit_array) {
    if ($edit_array) {
        $retval = $edit_array['public'];
    } else {
        $retval = 1;
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

function get_value($edit_array, $entry) {
    if ($edit_array) {
        $retval = $edit_array[$entry];
    } else {
        $retval = '';
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
        $retval = 2030;
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
        $html .= '<option value="blank"></option>';
        $result = query_db("SELECT iso3, name FROM country ORDER BY name;");
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            if ($edit_array && $row['iso3']===$edit_array['iso3']) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $html .= sprintf('<option value="%s"%s>%s</option>', $row['iso3'], $selected, $row['name']);
        }
        mysqli_free_result($result);
$html .= <<<HTML
    </select><br />
    <input type="radio" name="country_or_region" value="region" $region_checked /><label for="region">Region: </label>
    <select name="region" id="region">
HTML;
        $result = query_db("SELECT region_code, name FROM region ORDER BY name;");
        while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
            if ($edit_array && $row['region_code']===$edit_array['region']) {
                $selected = ' selected="selected"';
            } else {
                $selected = '';
            }
            $html .= sprintf('<option value="%s"%s>%s</option>', $row['region_code'], $selected, $row['name']);  
        }
        mysqli_free_result($result);
$html .= <<<HTML
    </select><br />
HTML;

    return $html;
}
 
function check_for_new_regions() {
    // 1. get the currently known regions from the pledge database
    $pledge_db_regions = array ();
    $result = query_db("SELECT region_code, name FROM region ORDER BY name;");
    while ($row = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
        $pledge_db_regions[] = $row['region_code'] ;
    }
    
    // 2. get the regions currently used by the calculator 
    // let's check if we have been passed an API database to reuse through the form
    //$db = $_REQUEST['db'];
    $db = unserialize($_COOKIE['db']);

    $URL_calc_api = Constants::api_url(['public']) ;
    
    // check is this database still exists
    if (isset($db)) {
        $client = new \GuzzleHttp\Client();
        try {
             $response = $client->request('GET', $URL_calc_api . "?q=regions&db=" . $db)->getBody();
        } catch (Exception $e) {
             unset($db); // API returns a 410 error code when the DB doesn't exist anymore on the server, so let's forget it
        }
    }
    
    // if we don't have a database to reuse, we request a new copy from the calculator API
    if (!$db) {
        $client = new \GuzzleHttp\Client();
        try {
             $response = (array) json_decode($client->request('GET', $URL_calc_api . "?q=new_db", ['auth' => NULL])->getBody());
             $db = $response['db'];
        } catch (Exception $e) {
             throw $e;
        }
    }

    // now let's get the actual list of regions using this database copy
    $client = new \GuzzleHttp\Client();
    try {
         $response = $client->request('GET', $URL_calc_api . "?q=regions&db=" . $db)->getBody();
         $calc_regions = (array) json_decode($response);
    } catch (Exception $e) {
         throw $e;
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
 
    // we save the code of the database we have used as a one week cookie so it  
    // can be re-used, otherwise, every time the "region" drop down field is created, 
    // a new copy of the database is created.
    setcookie("db", serialize($db), time()+604800);
}
