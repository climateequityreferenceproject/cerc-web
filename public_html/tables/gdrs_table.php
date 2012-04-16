<?php
function gdrs_table($dbfile, $year, $dec, $advanced) {
    include("table_common.php");

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");

    if ($advanced) {
$retval = <<< EOHTML
<table cellspacing="0" cellpadding="0" class="tablesorter">
    <thead>
        <tr>
            <th class="lj">Country or Group</th>
            <th>Population<br/>(million)</th>
            <th>Population<br/>(% of global)</th>
            <th>GDP<br/>(billion \$US)</th>
            <th>GDP<br/>(% of global)</th>
            <th>Income<br/>(\$US/cap)</th>
            <th>Income<br/>(\$US PPP/cap)</th>
            <th>Capacity<br/>(billion \$US)</th>
            <th>Capacity<br/>(% of global)</th>
            <th>Responsibility<br/>(% of global)</th>
            <th>RCI</th>
        </tr>
    </thead>
    <tbody>
EOHTML;
    } else {
$retval = <<< EOHTML
<table cellspacing="0" cellpadding="0">
    <thead>
        <tr>
            <th class="lj">Country or Group</th>
            <th>Population<br/>(% of global)</th>
            <th>GDP<br/>(% of global)</th>
            <th>Income<br/>(\$US PPP/cap)</th>
            <th>Capacity<br/>(% of global)</th>
            <th>Responsibility<br/>(% of global)</th>
            <th>RCI</th>
        </tr>
    </thead>
    <tbody>
EOHTML;
    }
    

    // Start with the core SQL view
    $db->query($viewquery);

$worldquery = <<< EOSQL
SELECT SUM(pop_mln) AS pop, SUM(gdp_blnUSDMER) AS gdp_mer,
        SUM(gdp_blnUSDPPP) AS gdp_ppp, SUM(gdrs_alloc_MtCO2) AS gdrs_alloc,
        SUM(gdrs_r_MtCO2) AS r, SUM(gdrs_c_blnUSDMER) AS c, SUM(gdrs_rci) AS rci
    FROM disp_temp WHERE year = $year;
EOSQL;

    $record = $db->query($worldquery)->fetchAll();
    $world_tot = $record[0]; // Only one record, but using "fetchAll" makes sure cursor closed
    $retval .= "<tr>";
    $retval .= '<td class="lj cr_item">( 1) World</td>';
    if ($advanced) {
        // Pop million
        $retval .= "<td>" . number_format($world_tot["pop"], $dec) . "</td>";
    }
    // Pop percent
    $retval .= "<td>" . number_format(100.00, $dec) . "</td>";
    if ($advanced) {
        // Total income (GWP/GDP MER)
        $retval .= "<td>" . number_format($world_tot["gdp_mer"], $dec) . "</td>";
    }
    // Income percent
    $retval .= "<td>" . number_format(100.00, $dec) . "</td>";
    if ($advanced) {
        // GDP MER per cap
        $val = 1000 * $world_tot["gdp_mer"]/$world_tot["pop"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
    }
    // GDP PPP per cap
    $val = 1000 * $world_tot["gdp_ppp"]/$world_tot["pop"];
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    if ($advanced) {
        // Total capacity (MER)
        $retval .= "<td>" . number_format($world_tot["c"], $dec) . "</td>";
    }
    // Capacity percent
    $retval .= "<td>" . number_format(100.00, $dec) . "</td>";
    // Resp percent
    $retval .= "<td>" . number_format(100.00, $dec) . "</td>";
    // RCI percent
    $retval .= "<td>" . number_format(100.00, $dec) . "</td>";
    $retval .= "</tr>";
    
    $i = 2;
    foreach ($db->query('SELECT * FROM flag_names') as $flags) {
        $flagname = $flags["flag"];
        $longname = '(' . sprintf("%2d", $i) . ') ' . $flags["long_name"];
$regionquery = <<< EOSQL
SELECT SUM(pop_mln) AS pop, SUM(gdp_blnUSDMER) AS gdp_mer,
        SUM(gdp_blnUSDPPP) AS gdp_ppp, SUM(gdrs_alloc_MtCO2) AS gdrs_alloc,
        SUM(gdrs_r_MtCO2) AS r, SUM(gdrs_c_blnUSDMER) AS c, SUM(gdrs_rci) AS rci
    FROM disp_temp, flags WHERE flags.iso3 = disp_temp.iso3 AND
        flags.value = 1 AND flags.flag = '$flagname' AND year = $year;
EOSQL;
                
        foreach ($db->query($regionquery) as $record) {
            $retval .= "<tr>";
            $retval .= '<td class="lj cr_item">' . $longname . "</td>";
            if ($advanced) {
                // Pop mln
                $retval .= "<td>" . number_format($record["pop"], $dec) . "</td>";
            }
            // Pop percent
            $val = 100.0 * $record["pop"]/$world_tot["pop"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            if ($advanced) {
                // Total income (GWP/GDP MER)
                $retval .= "<td>" . number_format($record["gdp_mer"], $dec) . "</td>";
            }
            // Income percent
            $val = 100.0 * $record["gdp_mer"]/$world_tot["gdp_mer"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            if ($advanced) {
                 // GDP MER per cap
                $val = 1000 * $record["gdp_mer"]/$record["pop"];
                $retval .= "<td>" . number_format($val, $dec) . "</td>";
            }
             // GDP PPP per cap
            $val = 1000 * $record["gdp_ppp"]/$record["pop"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            if ($advanced) {
                // Total capacity (MER)
                $retval .= "<td>" . number_format($record["c"], $dec) . "</td>";
            }
            // Capacity percent
            $val = 100.0 * $record["c"]/$world_tot["c"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            // Resp percent
            $val = 100.0 * $record["r"]/$world_tot["r"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            // RCI percent -- always scaled to sum to 1.0
            $retval .= "<td>" . number_format(100.0 * $record["rci"], $dec) . "</td>";
            $retval .= "</tr>";
        }
        $i++;
    }

    foreach ($db->query('SELECT * FROM disp_temp WHERE year=' . $year . ' ORDER BY country') as $record) {
        $retval .= "<tr>";
        $retval .= '<td class="lj cr_item">' . $record["country"] . "</td>";
        if ($advanced) {
            // Pop mln
            $retval .= "<td>" . number_format($record["pop_mln"], $dec) . "</td>";
        }
        // Pop percent
        $val = 100.0 * $record["pop_mln"]/$world_tot["pop"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        if ($advanced) {
            // Total income (GWP/GDP MER)
            $retval .= "<td>" . number_format($record["gdp_blnUSDMER"], $dec) . "</td>";
        }
        // Income percent
        $val = 100.0 * $record["gdp_blnUSDMER"]/$world_tot["gdp_mer"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        if ($advanced) {
            // GDP MER per cap
            $val = 1000 * $record["gdp_blnUSDMER"]/$record["pop_mln"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
        }
        // GDP PPP per cap
        $val = 1000 * $record["gdp_blnUSDPPP"]/$record["pop_mln"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        if ($advanced) {
            // Total capacity (MER)
            $retval .= "<td>" . number_format($record["gdrs_c_blnUSDMER"], $dec) . "</td>";
        }
        // Capacity percent
        $val = 100.0 * $record["gdrs_c_blnUSDMER"]/$world_tot["c"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        // Resp percent
        $val = 100.0 * $record["gdrs_r_MtCO2"]/$world_tot["r"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        // RCI percent -- always scaled to sum to 1.0
        $retval .= "<td>" . number_format(100.0 * $record["gdrs_rci"], $dec) . "</td>";
        $retval .= "</tr>";
    }
$retval .= <<< EOHTML
    </tbody>
</table>
EOHTML;
return $retval;
}
