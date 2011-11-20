<?php
include("functions.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>GDRs Pledges Database</title>
    </head>
    <body>
        <h1>GDRs pledges database entry form</h1>
        <form>
            <!-- Country list-->
            <label for="country">Country: </label>
            <select name="country" id="country">
                <?php
                    $result = query_db("SELECT iso3, name FROM country ORDER BY name;");
                    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
                        printf('<option value="%s">%s</option>', $row['iso3'], $row['name']);  
                    }
                    mysql_free_result($result);
                ?>
            </select>
            <br />.
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
                        option_number(1, 100, 1, $default = NULL);
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
                    <td>YEAR</td>
                    <td>by</td>
                    <td>YEAR</td>
                </tr>
            </table>
            <input type="submit" value ="Add" />
        </form>
        <div id="table"></div>
    </body>
</html>
