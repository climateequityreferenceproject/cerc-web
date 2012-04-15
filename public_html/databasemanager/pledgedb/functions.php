<?php

function db_connect() {
    if (strpos($_SERVER['REQUEST_URI'],"dev") !== FALSE) {
        $dbname = 'pledges-dev';
    } else {
        $dbname = 'pledges';
    }
    $db = mysql_connect('localhost', $dbname, '***REMOVED***');
    if (!$db) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db($dbname, $db);
    
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

