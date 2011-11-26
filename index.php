<?php
include("functions.php");

if ($_POST['form']) {
    $db = db_connect();
    switch ($_POST['form']) {
        case 'add':
            $new_values = array_slice($_POST, 1);
            if ($new_values['year_or_bau'] == 'bau') {
                $new_values['rel_to_year'] = NULL;
            }
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
                if (!is_numeric($value)) {
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
        case 'delete':
            foreach ($_POST as $key => $value) {
                if ($key !== 'form') {
                    $sql = "DELETE FROM pledge WHERE id=" . $key;
                    mysql_query($sql, $db);
                }
            }
            break;
        default:
            ;
    }
    mysql_close($db);
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>GDRs Pledges Database</title>
    </head>
    <body>
        <h1>GDRs pledges database entry form</h1>
        <form name="add" method="post">
            <input type="hidden" name="form" value="add"/>
            <!-- Country list-->
            <label for="iso3">Country: </label>
            <select name="iso3" id="iso3">
                <?php
                    $result = query_db("SELECT iso3, name FROM country ORDER BY name;");
                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        printf('<option value="%s">%s</option>', $row['iso3'], $row['name']);  
                    }
                    mysql_free_result($result);
                ?>
            </select>
            <br />
            <!-- Conditional/unconditional-->
            <input type="checkbox" name="conditional" id="conditional" value="0" />
            <label for="conditional"> Conditional</label>
            <br /><br />
            <table>
                <tr>
                    <td>Reduce</td>
                    <td>
                        <label><input type="radio" name="quantity" value="absolute" checked="checked"/> absolute emissions</label>
                        <br />
                        <label><input type="radio" name="quantity" value="intensity" /> intensity</label>
                    </td>
                    <td>to</td>
                    <td>
                        <select name="reduction_percent" id="reduction_percent">
                        <?php
                        option_number(1, 100, 1);
                        ?>
                        </select>%
                    </td>
                    <td>
                        <label><input type="radio" name="rel_to" value="below" checked="checked"/> below</label>
                        <br />
                        <label><input type="radio" name="rel_to" value="of" /> of</label>
                    </td>
                    <td>
                        <label><input type="radio" name="year_or_bau" value="year" checked="checked"/> value in</label>
                        <br />
                        <label><input type="radio" name="year_or_bau" value="bau" /> BAU</label>
                    </td>
                    <td>
                        <select name="rel_to_year" id="rel_to_year">
                        <?php
                        option_number(1990, 2010, 1);
                        ?>
                        </select>
                    </td>
                    <td>by</td>
                    <td>
                        <select name="by_year" id="by_year">
                        <?php
                        option_number(2010, 2050, 1, 2020);
                        ?>
                        </select>
                    </td>
                </tr>
            </table>
            <label>Source: <input type="text" name="source" maxlength="255" size="50"/></label><br/>
            <label>Details: <input type="text" name="details" maxlength="255" size="50"/></label>
            <br /><br />
            <input type="submit" value ="Add" />
            <br />
        </form>
        <form name="table" method="post">
            <input type="hidden" name="form" value="delete"/>
            <div id="table">
                <?php include("get_table.php"); ?>
            </div>
        </form>
    </body>
</html>
