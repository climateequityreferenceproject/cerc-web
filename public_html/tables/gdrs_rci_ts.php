<?php
require_once("table_common.php");

function gdrs_rci_ts($dbfile, $dec) {
    $viewquery = get_common_table_query();

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");
    
$retval = <<< EOHTML
<table cellspacing="0" cellpadding="0" class="tablesorter">
    <thead>
        <tr>
            <th> </th>
            <th colspan="5">percent of global total</th>
        </tr>        <tr>
            <th class="lj">Country or Group</th>
            <th>2010</th>
            <th>2015</th>
            <th>2020</th>
            <th>2025</th>
            <th>2030</th>
        </tr>
    </thead>
    <tbody>
EOHTML;

    // Start with the core SQL view
    $db->query($viewquery);

$worldquery = <<< EOSQL
SELECT year, SUM(gdrs_rci) AS rci
    FROM disp_temp WHERE year = 2010 OR year = 2015 OR
    year = 2020 OR year = 2025 OR year = 2030 GROUP BY year ORDER BY year;
EOSQL;

    # Out to be 100% in all years for world, but check...
    $retval .= "<tr>";
    $retval .= '<td class="lj cr_item">( 1) World</td>';
    foreach ($db->query($worldquery) as $record) {
        $retval .= "<td>" . number_format(100.00 * $record["rci"], $dec) . "</td>";
    }
    $retval .= "</tr>";
    
    $i = 2;
    foreach ($db->query('SELECT * FROM flag_names') as $flags) {
        $flagname = $flags["flag"];
        $longname = '(' . sprintf("%2d", $i) . ') ' . $flags["long_name"];
$regionquery = <<< EOSQL
SELECT year, SUM(gdrs_rci) AS rci
    FROM disp_temp, flags WHERE flags.iso3 = disp_temp.iso3 AND
        flags.value = 1 AND flags.flag = '$flagname' AND
        (year = 2010 OR year = 2015 OR year = 2020
        OR year = 2025 OR year = 2030) GROUP BY year ORDER BY year;
EOSQL;
                
        $retval .= "<tr>";
        $retval .= '<td class="lj cr_item">' . $longname . "</td>";
        foreach ($db->query($regionquery) as $record) {
            $retval .= "<td>" . number_format(100.00 * $record["rci"], $dec) . "</td>";
        }
        $retval .= "</tr>";
        $i++;
    }

$countryquery = <<< EOSQL
SELECT country, year, gdrs_rci
    FROM disp_temp WHERE
    year = 2010 OR year = 2015 OR year = 2020
    OR year = 2025 OR year = 2030 ORDER BY country, year;
EOSQL;
    $year = 2010;
    foreach ($db->query($countryquery) as $record) {
        if ($year == 2010) {
            $retval .= "<tr>";
            $retval .= '<td class="lj cr_item">' . $record["country"] . "</td>";
        }
        $retval .= "<td>" . number_format(100.00 * $record["gdrs_rci"], $dec) . "</td>";
        $year += 5;
        if ($year > 2030) {
            $retval .= "</tr>";
            $year = 2010;
        }
    }
$retval .= <<< EOHTML
    </tbody>
</table>
EOHTML;
return $retval;
}
