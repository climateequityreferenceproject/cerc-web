<?php
include("graphs/graph_core.php");

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
    $gases = "CO2";
    if ($shared_params['use_lulucf']['value']) {
        $bau_1990 += $ctry_val_1990['LULUCF_MtCO2'];
        $bau += $ctry_val['LULUCF_MtCO2'];
    }
    if ($shared_params['use_nonco2']['value']) {
        $bau_1990 += $ctry_val_1990['NonCO2_MtCO2e'];
        $bau += $ctry_val['NonCO2_MtCO2e'];
        $gases = "CO2e";
    }
    
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
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation as a reduction target below 1990 emissions</td>";
    $val = 100.0 * (1 - $ctry_val["gdrs_alloc_MtCO2"]/$bau_1990);
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation as Mt" . $gases . " below BAU</td>";
    $val = $bau - $ctry_val["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . number_format($val, dec($val)) . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as tCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation per capita as t" . $gases . " below BAU</td>";
    $val = ($bau - $ctry_val["gdrs_alloc_MtCO2"])/$ctry_val['pop_mln'];
    $retval .= "<td>" . number_format($val, dec($val)) . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as reduction from 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation per capita as reduction from 1990 per capita emissions</td>";
    $val = 100.0 * (1 - ($ctry_val["gdrs_alloc_MtCO2"]/$ctry_val['pop_mln'])/($bau_1990/$ctry_val_1990['pop_mln']));
    $retval .= "<td>" . number_format($val, dec($val)) . "%</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as PC tax (collecting 1% of global GWP)
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $year . " Mitigation obligation as per capita tax (assuming global mitigation costs = 1% of global GWP)</td>";
    $val = 1000 * $world_tot['gdp_mer'] * 0.01 * $ctry_val["gdrs_rci"]/$ctry_val['pop_mln'];
    $retval .= "<td>$" . number_format($val, dec($val)) . "</td>";
    $retval .= "</tr>";
    
$query = <<< EOSQL
SELECT year, SUM(gdrs_alloc_MtCO2) AS gdrs_alloc_MtCO2, SUM(fossil_CO2_MtCO2) AS fossil_CO2_MtCO2,
       SUM(LULUCF_MtCO2) AS LULUCF_MtCO2, SUM(NonCO2_MtCO2e) AS NonCO2_MtCO2e FROM disp_temp
       WHERE year >= 1990 AND year <= 2030 GROUP BY year ORDER BY year;
EOSQL;
    
    $global_bau_series = array();
    $global_alloc_series = array();
    foreach ($db->query($query) as $record) {
        $yr_ndx = $record['year'];
        $global_alloc_series[$yr_ndx] = $record['gdrs_alloc_MtCO2'];
        $global_bau_series[$yr_ndx] = $record['fossil_CO2_MtCO2'];
        if ($shared_params['use_lulucf']['value']) {
            $global_bau_series[$yr_ndx] += $record['LULUCF_MtCO2'];
        }
        if ($shared_params['use_nonco2']['value']) {
            $global_bau_series[$yr_ndx] += $record['NonCO2_MtCO2e'];
        }
    }
    $query = 'SELECT year, gdrs_alloc_MtCO2, fossil_CO2_MtCO2, LULUCF_MtCO2,
        NonCO2_MtCO2e FROM disp_temp WHERE year >= 1990 AND year <= 2030';
    $query .= ' AND iso3="' . $iso3 . '" ORDER BY year;';
    
    $bau_series = array();
    $alloc_series = array();
    $dulline_series = array();
    $min = 0;
    $max = 0;
    foreach ($db->query($query) as $record) {
        $yr_ndx = $record['year'];
        $alloc_series[$yr_ndx] = $record['gdrs_alloc_MtCO2'];
        $bau_series[$yr_ndx] = $record['fossil_CO2_MtCO2'];
        if ($shared_params['use_lulucf']['value']) {
            $bau_series[$yr_ndx] += $record['LULUCF_MtCO2'];
        }
        if ($shared_params['use_nonco2']['value']) {
            $bau_series[$yr_ndx] += $record['NonCO2_MtCO2e'];
        }
        $dulline_series[$yr_ndx] = $bau_series[$yr_ndx] * ($global_alloc_series[$yr_ndx]/$global_bau_series[$yr_ndx]);
        $min = min($min, $alloc_series[$yr_ndx]);
        $max = max($max, $bau_series[$yr_ndx]);
    }
    
    $graph = new Graph(500, 312);
    // The TRUE means use the specified limits for the graph; the FALSE means don't format numbers
    $graph->set_xaxis(1990, 2030, "", "", TRUE, FALSE);
    $graph->set_yaxis($min, $max, "Mt" . $gases, "");
    $graph->add_series($bau_series, "bau");
    $graph->add_series($dulline_series, "physical");
    $graph->add_series($alloc_series, "gdrs_alloc");
    $fund_others = $alloc_series[2030] < $dulline_series[2030];
    if ($fund_others) {
        $gap_color = NULL;
        $wedge_id = 'intl_oblig';
        $stripes = 'intl_oblig_stripes';
    } else {
        $gap_color = '#6b87c3';
        $wedge_id = 'supported_mit';
        $stripes = NULL;
    }
    $graph_file = $graph->svgplot_wedges(array(
                        array(
                            'id' => 'mit_oblig',
                            'between' => array('bau', 'gdrs_alloc'),
                            'color' => '#8ebd7f',
                            'stripes' => NULL,
                            'opacity' => 0.8
                        ),
                        array(
                            'id' => $wedge_id,
                            'between' => array('physical', 'gdrs_alloc'),
                            'color' => $gap_color,
                            'stripes' => $stripes,
                            'opacity' => 0.8
                        )
                    ), array(
                            'css' => array(
                                'filename' => 'css/country_graphs.css',
                                'embed' => false
                            ),
                            'common_id' => 'historical',
                            'vertical_at' => $year
                        )
                    );

    $graph_file = "/tmp/" . basename($graph_file);
    
$retval .= <<< EOHTML
    </tbody>
</table>
<br />
<object data="$graph_file" type="image/svg+xml" style="width:500px; height:312px; border: 1px solid #CCC;">
    <p>No SVG support</p>
</object>
EOHTML;

/* $retval .= '<ul id="ctry_report_legend">';
$retval .= '<li><img src="img/leg_clr_green.png" />&nbsp;';
if ($fund_others) {
    $retval .= 'Domestic mitigation</li>';
} else {
    $retval .= 'Domestically-funded mitigation</li>';
}
if ($fund_others) {
    $retval .= '<li><img src="img/leg_clr_ochre.png" />&nbsp;Mitigation in other countries</li>';
} else {
    $retval .= '<li><img src="img/leg_clr_blue.png" />&nbsp;Mitigation funded by other countries</li>';
}
$retval .= '</ul>';
 */
$retval .= '<p><em>We are working on the legend</em></p>';

return $retval;
}