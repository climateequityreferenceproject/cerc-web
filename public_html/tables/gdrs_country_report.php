<?php
include("../frameworks/frameworks.php");

// TODO: Link into whole infrastructure
// TODO: Allow user to choose country from a dropdown on this view & update dynamically
// TODO: Adjust decimal points to match values
// Example call:
//   http://www.gdrights.org/calculator_dev/tables/gdrs_country_report.php?country=USA&db=/***REMOVED***/sessions/gdrs-db/fw-sql3-adlY39

$can_run = TRUE;

if (isset($_GET['country'])) {
    $iso3 = $_GET['country'];
} else {
    $can_run = FALSE;
}

if (isset($_GET['year'])) {
    $year = $_GET['year'];
} else {
    $year = 2020;
}

if (isset($_GET['db'])) {
    $user_db = $_GET['db'];
} else {
    $can_run = FALSE;
}


function gdrs_country_report($dbfile, $year, $iso3) {
    include("table_common.php");

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");
    
    $shared_params = Framework::get_shared_params($dbfile);


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
    
    $retval = "<h1>" . $ctry_val['country'] . "</h1>";
    
$retval .= <<< EOHTML
<table cellspacing="2" cellpadding="2">
    <thead>
        <tr>
            <th class="lj">Variable</th>
            <th>Value</th>
        </tr>
    </thead>
    <tbody>
EOHTML;
    
    // BAU emissions as percentage of 1990 emissions – projected to year
    $retval .= "<tr>";
    $retval .= "<td>BAU emissions as percentage of 1990 emissions – projected to " . $year . "</td>";
    $retval .= "<td>" . number_format(100.0 * $bau/$bau_1990, 0) . "%</td>";
    $retval .= "</tr>";
    // Share of population above the development threshold – projected to year
    $retval .= "<tr>";
    $retval .= "<td>Share of population above the development threshold – projected to " . $year . "</td>";
    $retval .= "<td>" . number_format(100.0 * $ctry_val["gdrs_pop_mln_above_dl"]/$ctry_val["pop_mln"], 0) . "%</td>";
    $retval .= "</tr>";
    // Share of global population – projected to year
    $retval .= "<tr>";
    $retval .= "<td>Share of global population – projected to " . $year . "</td>";
    $retval .= "<td>" . number_format(100.0 * $ctry_val["pop_mln"]/$world_tot["pop"], 2) . "%</td>";
    $retval .= "</tr>";
    // Share of global RCI in year
    $retval .= "<tr>";
    $retval .= "<td>Share of global RCI in " . $year . "</td>";
    $retval .= "<td>" . number_format(100.0 * $ctry_val["gdrs_rci"], 3) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as a reduction target from 1990
    $retval .= "<tr>";
    $retval .= "<td>" . $year . " Mitigation obligation as a reduction target from 1990</td>";
    $retval .= "<td>" . number_format(100.0 * (1 - $ctry_val["gdrs_alloc_MtCO2"]/$bau_1990), 1) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td>" . $year . " Mitigation obligation as MtCO2e below BAU</td>";
    $retval .= "<td>" . number_format($bau - $ctry_val["gdrs_alloc_MtCO2"], 1) . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as tCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td>" . $year . " Mitigation obligation per capita as tCO2e below BAU</td>";
    $retval .= "<td>" . number_format(($bau - $ctry_val["gdrs_alloc_MtCO2"])/$ctry_val['pop_mln'], 1) . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as reduction from 1990
    $retval .= "<tr>";
    $retval .= "<td>" . $year . " Mitigation obligation as per capita as reduction from 1990</td>";
    $retval .= "<td>" . number_format(100.0 * (1 - ($ctry_val["gdrs_alloc_MtCO2"]/$ctry_val['pop_mln'])/($bau_1990/$ctry_val_1990['pop_mln'])), 1) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as PC tax (collecting 1% of global GWP)
    $retval .= "<tr>";
    $retval .= "<td>" . $year . " Mitigation obligation as PC tax (collecting 1% of global GWP)</td>";
    $retval .= "<td>\$" . number_format(1000 * $world_tot['gdp_mer'] * 0.01 * $ctry_val["gdrs_rci"]/$ctry_val['pop_mln'], 0) . "</td>";
    $retval .= "</tr>";
        
$retval .= <<< EOHTML
    </tbody>
</table>
EOHTML;
return $retval;
}


if ($can_run) {
    echo gdrs_country_report($user_db, $year, $iso3);
} else {
    echo "<p>Insufficient information to run</p>";
}