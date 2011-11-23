<?php

function dec($num) {
    return max(0, 1 - floor(log10(abs($num))));
}

function gdrs_country_report($dbfile, $shared_params, $iso3 = NULL, $year = 2020) {
    include("table_common.php");

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");
    
    if (!$iso3) {
        $iso3 = $countries[0]['iso3'];
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
    $world_tot = $record[0]; // Only one record, but using "fetchAll" makes sure curser closed
    $record = $db->query('SELECT * FROM disp_temp WHERE (year=' . $year . ' OR year=1990) AND iso3="' . $iso3 . '" ORDER BY year;')->fetchAll();
    $ctry_val_1990 = $record[0];
    $ctry_val = $record[1];
    $bau_1990 = $ctry_val_1990['fossil_CO2_MtCO2'];
    $bau = $ctry_val['fossil_CO2_MtCO2'];
    if ($shared_params['use_lulucf']['value']) {
        $bau_1990 += $ctry_val_1990['LULUCF_MtCO2'];
        $bau += $ctry_val['LULUCF_MtCO2'];
    }
    if ($shared_params['use_nonco2']['value']) {
        $bau_1990 += $ctry_val_1990['NonCO2_MtCO2e'];
        $bau += $ctry_val['NonCO2_MtCO2e'];
    }
        
    $retval .= "<h1>" . $ctry_val['country'] . "</h1>";
    
$retval .= <<< EOHTML
<table cellspacing="2" cellpadding="2">
    <tbody>
EOHTML;
    
    // BAU emissions as percentage of 1990 emissions – projected to year
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">BAU emissions as percentage of 1990 emissions – projected to " . $year . "</td>";
    $val = 100.0 * $bau/$bau_1990;
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // Share of population above the development threshold – projected to year
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">Share of population above the development threshold – projected to " . $year . "</td>";
    $val = 100.0 * $ctry_val["gdrs_pop_mln_above_dl"]/$ctry_val["pop_mln"];
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // Share of global population – projected to year
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">Share of global population – projected to " . $year . "</td>";
    $val = 100.0 * $ctry_val["pop_mln"]/$world_tot["pop"];
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // Share of global RCI in year
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">Share of global RCI in " . $year . "</td>";
    $val = 100.0 * $ctry_val["gdrs_rci"];
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as a reduction target from 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation as a reduction target from 1990</td>";
    $val = 100.0 * (1 - $ctry_val["gdrs_alloc_MtCO2"]/$bau_1990);
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation as MtCO2e below BAU</td>";
    $val = $bau - $ctry_val["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . number_format($val, dec($val)) . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as tCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation per capita as tCO2e below BAU</td>";
    $val = ($bau - $ctry_val["gdrs_alloc_MtCO2"])/$ctry_val['pop_mln'];
    $retval .= "<td>" . number_format($val, dec($val)) . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as reduction from 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation as per capita as reduction from 1990</td>";
    $val = 100.0 * (1 - ($ctry_val["gdrs_alloc_MtCO2"]/$ctry_val['pop_mln'])/($bau_1990/$ctry_val_1990['pop_mln']));
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as PC tax (collecting 1% of global GWP)
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation as PC tax (collecting 1% of global GWP)</td>";
    $val = 1000 * $world_tot['gdp_mer'] * 0.01 * $ctry_val["gdrs_rci"]/$ctry_val['pop_mln'];
    $retval .= "<td>$" . number_format($val, dec($val)) . "</td>";
    $retval .= "</tr>";
        
$retval .= <<< EOHTML
    </tbody>
</table>
EOHTML;
return $retval;
}