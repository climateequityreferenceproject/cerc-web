<?php
function percap_alloc($dbfile, $dec) {

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");
    
$retval = <<< EOHTML
<table>
    <thead>
        <tr>
            <th> </th>
            <th colspan="5">MtCO<sub>2</sub></th>
        </tr>
        <tr>
            <th class="lj">Country or<br/>Group</th>
            <th>2010</th>
            <th>2015</th>
            <th>2020</th>
            <th>2025</th>
            <th>2030</th>
        </tr>
    </thead>
    <tbody>
EOHTML;

$worldquery = <<< EOSQL
SELECT year, (11.0/3.0) * SUM(allocation_MtC) AS alloc_MtCO2
    FROM fw_percap WHERE year = 2010 OR year = 2015 OR
    year = 2020 OR year = 2025 OR year = 2030 GROUP BY year ORDER BY year;
EOSQL;

    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">( 1) World</td>";
    foreach ($db->query($worldquery) as $record) {
        $retval .= "<td>" . number_format($record["alloc_MtCO2"], $dec) . "</td>";
    }
    $retval .= "</tr>";
    
    $i = 2;
    foreach ($db->query('SELECT * FROM flag_names') as $flags) {
        $flagname = $flags["flag"];
        $longname = '(' . sprintf("%2d", $i) . ') ' . $flags["long_name"];
$regionquery = <<< EOSQL
SELECT year, (11.0/3.0) * SUM(allocation_MtC) AS alloc_MtCO2
    FROM fw_percap, flags WHERE flags.iso3 = fw_percap.iso3 AND
        flags.value = 1 AND flags.flag = '$flagname' AND
        (year = 2010 OR year = 2015 OR year = 2020
        OR year = 2025 OR year = 2030) GROUP BY year ORDER BY year;
EOSQL;
                
        $retval .= "<tr>";
        $retval .= "<td class=\"lj\">" . $longname . "</td>";
        foreach ($db->query($regionquery) as $record) {
            $retval .= "<td>" . number_format($record["alloc_MtCO2"], $dec) . "</td>";
        }
        $retval .= "</tr>";
        $i++;
    }

$countryquery = <<< EOSQL
SELECT name as country, year, (11.0/3.0) * allocation_MtC AS alloc_MtCO2
    FROM fw_percap, country WHERE
    (year = 2010 OR year = 2015 OR year = 2020
    OR year = 2025 OR year = 2030) AND country.iso3 = fw_percap.iso3
    ORDER BY country, year;
EOSQL;
    $year = 2010;
    foreach ($db->query($countryquery) as $record) {
        if ($year == 2010) {
            $retval .= "<tr>";
            $retval .= "<td class=\"lj\">" . $record["country"] . "</td>";
        }
        $retval .= "<td>" . number_format($record["alloc_MtCO2"], $dec) . "</td>";
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
