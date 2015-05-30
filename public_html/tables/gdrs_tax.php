<?php
require_once("table_common.php");

function insertspaces($in_string, $number_of_spaces, $space_string=" ") {
// inserts a specified $number_of_spaces (or of an alternative $space_string)
// between each of the character of $in_string
    $ret_string = "";
    $length = strlen($in_string);
    for ($i=0; $i< $length; $i++) {
        $ret_string .=  str_pad($in_string[$i], $number_of_spaces * strlen($space_string) + 1 , $space_string);
    }
    return $ret_string;
}

function gdrs_tax($dbfile, $year, $ep_start, $dec) {
    global $glossary;
    $viewquery = get_common_table_query();

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");


    // First, some parameters for later use
    $record = $db->query("SELECT int_val FROM params WHERE param_id='use_lulucf'")->fetchAll();
    $flag_use_lulucf = $record[0]["int_val"]; // Only one record, but using "fetchAll" makes sure curser closed
    $record = $db->query("SELECT int_val FROM params WHERE param_id='use_nonco2'")->fetchAll();
    $flag_use_nonco2 = $record[0]["int_val"];
    $record = $db->query("SELECT int_val FROM params WHERE param_id='usesequence'")->fetchAll();
    $flag_use_sequence = $record[0]["int_val"];
    if ($year >= $ep_start) {
        $record = $db->query("SELECT real_val FROM params WHERE param_id='billpercgwp_mit'")->fetchAll();
        $billfracgwp_mit = 0.01 * ($record[0]["real_val"]);
        $record = $db->query("SELECT real_val FROM params WHERE param_id='billpercgwp_adapt'")->fetchAll();
        $billfracgwp_adapt = 0.01 * ($record[0]["real_val"]);
        $billfracgwp = $billfracgwp_mit + $billfracgwp_adapt;
        $oblfactor = 1.0;
    } else {
        // If the emergency program hasn't started, then no obligations
        $billfracgwp_mit = 0.0;
        $billfracgwp_adapt = 0.0;
        $billfracgwp = 0.0;
        $oblfactor = 0.0;
    }
    
    // Start with the core SQL view
    $db->query($viewquery);
    
    // Total GWP, to get the "bill"
    $record = $db->query("SELECT SUM(gdp_blnUSDPPP) AS gdp_ppp, SUM(gdp_blnUSDMER) AS gdp_mer FROM disp_temp WHERE year = " . $year)->fetchAll();
    // $gwp_ppp = $record[0]["gdp_ppp"];
    $gwp_mer = $record[0]["gdp_mer"];

    // Make a new query for this table
$taxview = <<< EOSQL
CREATE TEMPORARY VIEW tax_temp AS
SELECT iso3, country, pop_mln as pop, gdp_blnUSDMER AS gdp_mer, gdp_blnUSDPPP AS gdp_ppp,
        max(0, fossil_CO2_MtCO2 + $flag_use_lulucf * LULUCF_MtCO2 +
        $flag_use_nonco2 * NonCO2_MtCO2e - gdrs_alloc_MtCO2 -
        $flag_use_sequence * vol_rdxn_MtCO2) as gdrs_oblig,
        gdrs_pop_mln_above_dl AS pop_above_dl
    FROM disp_temp WHERE year = $year;
EOSQL;
    $db->query($taxview);
    
$retval  = "<div>The cost figures in the table below are based on the user-provided assumptions about the ";
$retval .= $glossary->getLink('adapt_cost', false, _("cost of adaptation")) ." (" . number_format(100.00 * $billfracgwp_adapt, $dec) . "% of GWP) and the ";
$retval .= $glossary->getLink('mit_cost', false, _("incremental cost of mitigation")) ." (" . number_format(100.00 * $billfracgwp_mit, $dec) . "% of GWP), ";
$retval .= "which, based on these assumptions, are \$" . number_format($gwp_mer * $billfracgwp_adapt, 0) . "bn and \$" . number_format($gwp_mer * $billfracgwp_mit, 0) . "bn";
$retval .= " in " . $year . ", respectively (in 2010 \$US MER).</div>";
 
$mit_string = insertspaces("Mitigation", 4, "&nbsp;");
$adapt_string = insertspaces("Adaptation", 3, "&nbsp;");
$retval .= <<< EOHTML
<table cellspacing="0" cellpadding="0" class="tablesorter">
    <thead>
        <tr>
            <th rowspan="2" class="lj">Country or Group</th>
            <th rowspan="2">Fair share<br/>(% of total)</th>
            <th colspan="6" style="text-align: center;">$mit_string</th>
            <th colspan="4" style="text-align: center;">$adapt_string</th>
        </tr>
        <tr>
            <th>Fair share<br/>(billion 2010 \$US MER)</th>
            <th>Fair share<br/>(% GDP)</th>
            <th>Fair share per capita<br/>(2010 \$US MER/cap)</th>
            <th>Fair share per capita<br/>(t/cap)</th>
            <th>Fair share per<br/>person above development threshold<br/>(2010 \$US MER/cap)</th>
            <th>Fair share per<br/>person above development threshold<br/>(t/cap)</th>
        
            <th>Fair share<br/>(billion 2010 \$US MER)</th>
            <th>Fair share<br/>(% GDP)</th>
            <th>Fair share per capita<br/>(2010 \$US MER/cap)</th>
            <th>Fair share per<br/>person above development threshold<br/>(2010 \$US MER/cap)</th>        
        </tr>
        
    </thead>
    <tbody>
EOHTML;
    
$worldquery = <<< EOSQL
SELECT SUM(pop) AS pop, SUM(gdp_mer) AS gdp_mer, SUM(gdp_ppp) AS gdp_ppp,
        SUM(gdrs_oblig) as gdrs_oblig,
        SUM(pop_above_dl) AS pop_above_dl
    FROM tax_temp;
EOSQL;

    $record = $db->query($worldquery)->fetchAll();
    $world_tot = $record[0]; // Only one record, but using "fetchAll" makes sure curser closed
    $retval .= "<tr>";
    $retval .= '<td class="lj cr_item">( 1) World</td>';
    // Fair share % of total
    $retval .= "<td>" . number_format($oblfactor * 100.00, $dec) . "</td>";
    //1. MITIGATION 
    // Fair share bln USD MER
    $val = $billfracgwp_mit * $gwp_mer;
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    // Fair share % GDP
    $retval .= "<td>" . number_format(100.00 * $billfracgwp_mit, $dec) . "</td>";
    // Fair share per capita (in $/cap)
    $val = 1000.0 * $billfracgwp_mit * $gwp_mer/$world_tot["pop"];
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    // Fair share per capita (in t/cap)
    $val = $world_tot["gdrs_oblig"]/$world_tot["pop"];
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    // Fair share per person above dev threshold (in $/cap)
    $val = 1000.0 * $billfracgwp_mit * $gwp_mer/$world_tot["pop_above_dl"];
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    // Fair share per person above dev threshold (in t/cap)
    $val = $world_tot["gdrs_oblig"]/$world_tot["pop_above_dl"];
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    //2. ADAPTATION
    // Fair share bln USD MER
    $val = $billfracgwp_adapt * $gwp_mer;
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    // Fair share % GDP
    $retval .= "<td>" . number_format(100.00 * $billfracgwp_adapt, $dec) . "</td>";
    // Fair share per capita (in $/cap)
    $val = 1000.0 * $billfracgwp_adapt * $gwp_mer/$world_tot["pop"];
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    // Fair share per person above dev threshold (in $/cap)
    $val = 1000.0 * $billfracgwp_adapt * $gwp_mer/$world_tot["pop_above_dl"];
    $retval .= "<td>" . number_format($val, $dec) . "</td>";
    $retval .= "</tr>";
       
    $i = 2;
    foreach ($db->query('SELECT * FROM flag_names') as $flags) {
        $flagname = $flags["flag"];
        $longname = '(' . sprintf("%2d", $i) . ') ' . $flags["long_name"];
$regionquery = <<< EOSQL
SELECT SUM(pop) AS pop, SUM(gdp_mer) AS gdp_mer, SUM(gdp_ppp) AS gdp_ppp,
        SUM(gdrs_oblig) as gdrs_oblig,
        SUM(pop_above_dl) AS pop_above_dl
    FROM tax_temp, flags WHERE flags.iso3 = tax_temp.iso3 AND
        flags.value = 1 AND flags.flag = '$flagname';
EOSQL;
                
        foreach ($db->query($regionquery) as $record) {
            $retval .= "<tr>";
            $retval .= '<td class="lj cr_item">' . $longname . "</td>";
            // Fair share % of total
            $obl_frac = $record["gdrs_oblig"]/$world_tot["gdrs_oblig"];
            $val = 100.0 * $oblfactor * $obl_frac;
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            //1. MITIGATION 
            // Fair share bln USD MER
            $obl_mer = $billfracgwp_mit * $gwp_mer * $obl_frac;
            $retval .= "<td>" . number_format($obl_mer, $dec) . "</td>";
            // Fair share % GDP
            $val = 100.0 * $obl_mer/$record["gdp_mer"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            // Fair share per capita (in $/cap)
            $val = 1000.0 * $obl_mer/$record["pop"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            // Fair share per capita (in t/cap)
            $val = $record["gdrs_oblig"]/$record["pop"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";            
            // Fair share per person above dev threshold (in $/cap)
            $val = 1000.0 * $obl_mer/$record["pop_above_dl"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            // Fair share per person above dev threshold (in t/cap)
            $val = $record["gdrs_oblig"]/$record["pop_above_dl"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            //2. ADAPTATION
            // Fair share bln USD MER
            $obl_mer_adapt = $billfracgwp_adapt * $gwp_mer * $obl_frac;
            $retval .= "<td>" . number_format($obl_mer_adapt, $dec) . "</td>";
            // Fair share % GDP
            $val = 100.0 * $obl_mer_adapt/$record["gdp_mer"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            // Fair share per capita (in $/cap)
            $val = 1000.0 * $obl_mer_adapt/$record["pop"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            // Fair share per person above dev threshold (in $/cap)
            $val = 1000.0 * $obl_mer_adapt/$record["pop_above_dl"];
            $retval .= "<td>" . number_format($val, $dec) . "</td>";
            $retval .= "</tr>";
        }
        $i++;
    }

    foreach ($db->query("SELECT * FROM tax_temp ORDER BY country") as $record) {
        $retval .= "<tr>";
        $retval .= '<td class="lj cr_item">' . $record["country"] . "</td>";
        // Fair share % of total
        $obl_frac = $record["gdrs_oblig"]/$world_tot["gdrs_oblig"];
        $val = 100.0 * $oblfactor * $obl_frac;
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        //1. MITIGATION 
        // Fair share bln USD MER
        $obl_mer = $billfracgwp_mit * $gwp_mer * $obl_frac;
        $retval .= "<td>" . number_format($obl_mer, $dec) . "</td>";
        // Fair share % GDP
        $val = 100.0 * $obl_mer/$record["gdp_mer"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        // Fair share per capita (in $/cap)
        $val = 1000.0 * $obl_mer/$record["pop"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        // Fair share per capita (in t/cap)
        $val = $record["gdrs_oblig"]/$record["pop"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";            
        // Fair share per person above dev threshold (in $/cap)
        $val = 1000.0 * $obl_mer/$record["pop_above_dl"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        // Fair share per person above dev threshold (in t/cap)
        $val = $record["gdrs_oblig"]/$record["pop_above_dl"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        //2. ADAPTATION
        // Fair share bln USD MER
        $obl_mer_adapt = $billfracgwp_adapt * $gwp_mer * $obl_frac;
        $retval .= "<td>" . number_format($obl_mer_adapt, $dec) . "</td>";
        // Fair share % GDP
        $val = 100.0 * $obl_mer_adapt/$record["gdp_mer"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        // Fair share per capita (in $/cap)
        $val = 1000.0 * $obl_mer_adapt/$record["pop"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        // Fair share per person above dev threshold (in $/cap)
        $val = 1000.0 * $obl_mer_adapt/$record["pop_above_dl"];
        $retval .= "<td>" . number_format($val, $dec) . "</td>";
        $retval .= "</tr>";
    }
$retval .= <<< EOHTML
    </tbody>
</table>
EOHTML;
return $retval;
}
