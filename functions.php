<?php
function query_db($query) {
    $db = mysql_connect('localhost', 'pledges', '***REMOVED***');
    if (!$db) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db("pledges", $db);
    
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

