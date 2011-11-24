<?php
function gdrs_alloc($dbfile, $dec, $mode, $non_co2 = FALSE) {
    include("table_common.php");

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");
    
    if ($non_co2) {
        $gases = "CO2e";
    } else {
        $gases = "CO2";
    }
    $units = "Mt" . $gases;
    if ($mode === 'percap') {
        $units = "t" . $gases . "/cap";
    }
    
$retval = <<< EOHTML
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th> </th>
            <th colspan="5">$units</th>
        </tr>
        <tr>
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
SELECT year, SUM(gdrs_alloc_MtCO2) AS gdrs_alloc, SUM(pop_mln) AS pop
    FROM disp_temp WHERE year = 2010 OR year = 2015 OR
    year = 2020 OR year = 2025 OR year = 2030 GROUP BY year ORDER BY year;
EOSQL;

    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">( 1) World</td>";
    foreach ($db->query($worldquery) as $record) {
        switch ($mode) {
            case 'total':
                $val = $record["gdrs_alloc"];
                break;
            case 'percap':
                $val = $record["gdrs_alloc"]/$record["pop"];
                break;
            default:
                // This should never happen
                $val = -9999;
        }
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
    }
    $retval .= "</tr>";
    
    $i = 2;
    foreach ($db->query('SELECT * FROM flag_names') as $flags) {
        $flagname = $flags["flag"];
        $longname = '(' . sprintf("%2d", $i) . ') ' . $flags["long_name"];
$regionquery = <<< EOSQL
SELECT year, SUM(gdrs_alloc_MtCO2) AS gdrs_alloc, SUM(pop_mln) AS pop
    FROM disp_temp, flags WHERE flags.iso3 = disp_temp.iso3 AND
        flags.value = 1 AND flags.flag = '$flagname' AND
        (year = 2010 OR year = 2015 OR year = 2020
        OR year = 2025 OR year = 2030) GROUP BY year ORDER BY year;
EOSQL;
                
        $retval .= "<tr>";
        $retval .= "<td class=\"lj\">" . $longname . "</td>";
        foreach ($db->query($regionquery) as $record) {
            switch ($mode) {
                case 'total':
                    $val = $record["gdrs_alloc"];
                    break;
                case 'percap':
                    $val = $record["gdrs_alloc"]/$record["pop"];
                    break;
                default:
                    // This should never happen
                    $val = -9999;
            }
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
        }
        $retval .= "</tr>";
        $i++;
    }

$countryquery = <<< EOSQL
SELECT country, year, gdrs_alloc_MtCO2 as gdrs_alloc, pop_mln AS pop
    FROM disp_temp WHERE
    year = 2010 OR year = 2015 OR year = 2020
    OR year = 2025 OR year = 2030 ORDER BY country, year;
EOSQL;
    $year = 2010;
    foreach ($db->query($countryquery) as $record) {
        if ($year == 2010) {
            $retval .= "<tr>";
            $retval .= "<td class=\"lj\">" . $record["country"] . "</td>";
        }
        switch ($mode) {
            case 'total':
                $val = $record["gdrs_alloc"];
                break;
            case 'percap':
                $val = $record["gdrs_alloc"]/$record["pop"];
                break;
            default:
                // This should never happen
                $val = -9999;
        }
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
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
