<?php
include_once 'functions.php';
       
$edit_array = null;
if (isset($_POST['form']) && !isset($_POST['cancel'])) {
    $db = db_connect();
    switch ($_POST['form']) {
        case 'add':
            $new_values = array_slice($_POST, 1);  /// this is pretty unsafe (SQL injection) but since this entry form is for internal use only; I won't bother sanitizing
            if ($new_values['year_or_bau'] == 'bau') {
                $new_values['rel_to_year'] = null;
            }
            if ($new_values['country_or_region'] == 'country') {
                $new_values['region'] = null;
            } else {
                $new_values['iso3'] = null;
            }
            if ($new_values['quantity'] == 'target_Mt') {
                $new_values['reduction_percent'] = null;
                $new_values['rel_to'] = null;
                $new_values['year_or_bau'] = null;
                $new_values['rel_to_year'] = null;
            } else {
                $new_values['target_Mt'] = null;                
            }
            // save empty textboxes as NULL not 0
            foreach (array('reduction_percent', 'target_Mt', 'target_Mt_CO2', 'target_Mt_nonCO2', 'target_Mt_LULUCF') as $textbox) {
                if (strlen($new_values[$textbox])==0) { $new_values[$textbox] = NULL; }
            }
            // construct the JSON data array for the caveat field
            $json = "";
            foreach ($new_values as $key=>$val) { 
                 if (!(strpos($key, "caveat_")===false)) {
                     if (strlen($val)>0) {
                        $json .= (strlen($json)==0) ? "\n\n" . "{" : ", ";
                        $json .= '"' . str_replace("caveat_", "", $key) . '":';
                        $json .= '"' . str_replace("'","&#39;",str_replace('"','&quot;',trim($val))) . '"';
                     }
                     unset($new_values[$key]);
                 }
            }
            $json .= (strlen($json)>0) ? "}" : "";
            $new_values['caveat'] = trim($new_values['caveat'] . $json);

            // The following aren't fields in the database
            unset($new_values['country_or_region']);
            unset($new_values['db']);
            if (isset($new_values['replace'])) {
                $do_replace = true;
                $edit_id = $new_values['edit_id'];
                unset($new_values['replace']);
                unset($new_values['edit_id']);
            } else {
                $do_replace = false;
            }
            // Check boxes are odd--they just don't appear if unchecked
            foreach (array('conditional', 'public', 'include_nonco2', 'include_lulucf') as $checkbox) {
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
                    // Double up on single quotes if needed; replace any sequence of single quotes, to make
                    // sure that we don't just keep adding doubled single quotes
                    $new_values[$key] = "'" . preg_replace("/'+/", "''", $value) . "'";
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
            if (!mysqli_query($db, $sql)) {
                mysqli_close($db);
                die('Invalid query: ' . mysqli_error($db) . ' from SQL: ' . $sql);
            }
            break;
        case 'table':
            foreach ($_POST as $key => $value) {
                switch ($value) {
                    case 'Delete':
                        $sql = "DELETE FROM pledge WHERE id=" . $key;
                        mysqli_query($db, $sql);
                        break;
                    case 'Publish':
                        $sql = "UPDATE pledge SET public = 1 WHERE id=" . $key;
                        mysqli_query($db, $sql);
                        break;
                    case 'Hide':
                        $sql = "UPDATE pledge SET public = 0 WHERE id=" . $key;
                        mysqli_query($db, $sql);
                        break;
                    case 'Edit':
                        $sql = "SELECT * FROM pledge WHERE id=" . $key;
                        $result = mysqli_query($db, $sql);
                        $edit_array = mysqli_fetch_array($result, MYSQLI_ASSOC);
                        break;
                    default:
                        break;
                }
            }
            break;
        default:
            break;
    }
    mysqli_close($db);
}
?>
