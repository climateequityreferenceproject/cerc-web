<?php
include_once 'functions.php';

$edit_array = null;
if (isset($_POST['form']) && !isset($_POST['cancel'])) {
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
            // The following aren't fields in the database
            unset($new_values['country_or_region']);
            if (isset($new_values['replace'])) {
                $do_replace = true;
                $edit_id = $new_values['edit_id'];
                unset($new_values['replace']);
                unset($new_values['edit_id']);
            } else {
                $do_replace = false;
            }
            // Check boxes are odd--they just don't appear if unchecked
            foreach (array('conditional', 'include_nonco2', 'include_lulucf') as $checkbox) {
                if (array_key_exists($checkbox, $new_values)) {
                    $new_values[$checkbox] = 1;
                } else {
                    $new_values[$checkbox] = 0;
                }
            }
            foreach ($new_values as $key=>$value) {
                if ($value === null) {
                    $new_values[$key] = 'NULL';
                } elseif (!is_numeric($value)) {
                    $new_values[$key] = "'" . $value . "'";
                }
            }
            if ($do_replace) {
                $sql = "UPDATE pledge SET ";
                $colvals = array();
                foreach ($new_values as $key=>$value) {
                    $colvals[] = $key . '=' . $value . ' ';
                }
                $sql .= implode(",", $colvals);
                $sql .= 'WHERE id=' .$edit_id . ';';
            } else {
                $sql = "INSERT INTO pledge (";
                $sql .= implode(",", array_keys($new_values));
                $sql .= ") VALUE (";
                $sql .= implode(",", array_values($new_values));
                $sql .= ")";
            }
            if (!mysql_query($sql, $db)) {
                mysql_close($db);
                die('Invalid query: ' . mysql_error() . ' from SQL: ' . $sql);
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
                    case 'Edit':
                        $sql = "SELECT * FROM pledge WHERE id=" . $key;
                        $result = mysql_query($sql, $db);
                        $edit_array = mysql_fetch_array($result, MYSQL_ASSOC);
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
