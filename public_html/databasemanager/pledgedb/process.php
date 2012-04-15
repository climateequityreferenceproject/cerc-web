<?php
include_once 'functions.php';

if ($_POST['form']) {
    $db = db_connect();
    switch ($_POST['form']) {
        case 'add':
            $new_values = array_slice($_POST, 1);
            if ($new_values['year_or_bau'] == 'bau') {
                $new_values['rel_to_year'] = null;
            }
            if ($new_values['country_or_region'] == 'country') {
                $new_values['region'] = null;
            } else {
                $new_values['iso3'] = null;
            }
            unset($new_values['country_or_region']); // This isn't a field in the database
            // Check boxes are odd--they just don't appear if unchecked
            if (array_key_exists('conditional', $new_values)) {
                $new_values['conditional'] = 1;
            } else {
                $new_values['conditional'] = 0;
            }
            $sql = "INSERT INTO pledge (";
            $sql .= implode(",", array_keys($new_values));
            $sql .= ") VALUE (";
            $values = array_values($new_values);
            $mod_values = array();
            foreach ($values as $value) {
                if ($value === null) {
                    $value = 'NULL';
                } elseif (!is_numeric($value)) {
                    $value = "'" . $value . "'";
                }
                $mod_values[] = $value;
            }
            $sql .= implode(",", $mod_values);
            $sql .= ")";
            if (!mysql_query($sql, $db)) {
                mysql_close($db);
                die('Invalid query: ' . mysql_error());
            }
            break;
        case 'table':
            foreach ($_POST as $key => $value) {
                switch ($value) {
                    case 'Delete':
                        $sql = "DELETE FROM pledge WHERE id=" . $key;
                        mysql_query($sql, $db);
                        break;
                    case 'Publish':
                        $sql = "UPDATE pledge SET public = 1 WHERE id=" . $key;
                        mysql_query($sql, $db);
                        break;
                    case 'Hide':
                        $sql = "UPDATE pledge SET public = 0 WHERE id=" . $key;
                        mysql_query($sql, $db);
                        break;
                    default:
                        ;
                }
            }
            break;
        default:
            ;
    }
    mysql_close($db);
}
?>
