<?php
require_once("graphs/graph_core.php");
require_once("pledges/pledge_functions.php");
require_once("table_common.php");

function dec($num) {
    return max(0, 1 - floor(log10(abs($num))));
}

function nice_number($prefix, $num, $postfix) {
    if ($num < 0) {
        $retval = '<span class="num_negative">-';
    } else {
        $retval = '';
    }
    $retval .= $prefix;
    $retval .= number_format(abs($num), dec($num));
    $retval .= $postfix;
    if ($num < 0) {
        $retval .= '</span>';
    }
    return $retval;
}

// TODO: Replace "iso3" with the more generic "code"
function gdrs_country_report($dbfile, $country_name, $shared_params, $iso3 = NULL, $year = 2020) {
    $viewquery = get_common_table_query($dbfile);

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
        SUM(gdrs_r_MtCO2) AS r, SUM(gdrs_c_blnUSDMER) AS c, SUM(gdrs_rci) AS rci,
        SUM(fossil_CO2_MtCO2) AS fossil_CO2, SUM(LULUCF_MtCO2) AS LULUCF,
        SUM(NonCO2_MtCO2e) AS NonCO2
    FROM disp_temp WHERE year = $year;
EOSQL;

if (!is_country($iso3)) {
    foreach ($db->query("SELECT seq_no FROM tax_levels;") as $record) {
        $tax_string .= sprintf(', SUM(tax_pop_mln_below_%1$d) AS tax_pop_mln_below_%1$d', $record['seq_no']);
        $tax_string .= sprintf(', SUM(tax_income_dens_%1$d) AS tax_income_dens_%1$d', $record['seq_no']);
        $tax_string .= sprintf(', SUM(tax_revenue_dens_%1$d) AS tax_revenue_dens_%1$d', $record['seq_no']);
        $tax_string .= sprintf(', SUM(tax_pop_dens_%1$d) AS tax_pop_dens_%1$d', $record['seq_no']);
    }
$regionquery = <<< EOSQL
SELECT year, SUM(pop_mln) AS pop_mln, SUM(gdrs_pop_mln_above_dl) AS gdrs_pop_mln_above_dl,
        SUM(gdp_blnUSDMER) AS gdp_blnUSDMER, SUM(gdp_blnUSDPPP) AS gdp_blnUSDPPP,
        SUM(gdrs_alloc_MtCO2) AS gdrs_alloc_MtCO2,
        SUM(gdrs_r_MtCO2) AS gdrs_r_MtCO2, SUM(gdrs_c_blnUSDMER) AS gdrs_c_blnUSDMER,
        SUM(gdrs_rci) AS gdrs_rci, SUM(fossil_CO2_MtCO2) AS fossil_CO2_MtCO2,
        SUM(LULUCF_MtCO2) AS LULUCF_MtCO2, SUM(NonCO2_MtCO2e) AS NonCO2_MtCO2e
        $tax_string
    FROM disp_temp, flags WHERE
        flags.iso3 = disp_temp.iso3 AND
        (year=$year OR year=1990) AND
        flags.value = 1 AND
        flags.flag = "$iso3"
    GROUP BY year ORDER BY year;
EOSQL;
} else {
    $regionquery = null;
}

    $record = $db->query($worldquery)->fetchAll();
    $world_tot = $record[0]; // Only one record, but using "fetchAll" makes sure curser closed
    if (is_country($iso3)) {
        $record = $db->query('SELECT * FROM disp_temp WHERE (year=' . $year . ' OR year=1990) AND iso3="' . $iso3 . '" ORDER BY year;')->fetchAll();
    } else {
        $record = $db->query($regionquery)->fetchAll();;
    }
    $ctry_val_1990 = $record[0];
    $ctry_val = $record[1];
    $bau_1990 = $ctry_val_1990['fossil_CO2_MtCO2'];
    $bau = $ctry_val['fossil_CO2_MtCO2'];
    $world_bau = $world_tot['fossil_CO2'];
    $gases = "CO2";
    if ($shared_params['use_lulucf']['value']) {
        $bau_1990 += $ctry_val_1990['LULUCF_MtCO2'];
        $bau += $ctry_val['LULUCF_MtCO2'];
        $world_bau += $world_tot['LULUCF'];
    }
    if ($shared_params['use_nonco2']['value']) {
        $bau_1990 += $ctry_val_1990['NonCO2_MtCO2e'];
        $bau += $ctry_val['NonCO2_MtCO2e'];
        $world_bau += $world_tot['NonCO2'];
        $gases = "CO2e";
    }
    
$retval .= <<< EOHTML
<table cellspacing="2" cellpadding="2">
    <tbody>
EOHTML;
    
    // year Global mitigation obligation as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">Global mitigation requirement in " . $year . " as Mt" . $gases . " below BAU</td>";
    $val = $world_bau - $world_tot["gdrs_alloc"];
    $retval .= "<td>" . nice_number('', $val, '') . "</td>";
    $retval .= "</tr>";
    // Share of global RCI in year
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . $country_name . " share of global RCI in " . $year . "</td>";
    $val = 100.0 * $ctry_val["gdrs_rci"];
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // year National mitigation obligation
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">Mitigation obligation (= global mitigation requirement &#215; share of global RCI) in " . $year ." as</td>";
    $retval .= "<td></td>";
    $retval .= "</tr>";
    // National mitigation obligation as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Mt" . $gases . " below BAU</td>";
    $val = $bau - $ctry_val["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as a reduction target from 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Percentage of 1990 emissions</td>";
    $val = 100.0 * ($bau - $ctry_val["gdrs_alloc_MtCO2"])/$bau_1990;
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as reduction from 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Per capita as percentage of 1990 per capita emissions</td>";
    $val = 100.0 * (($bau - $ctry_val["gdrs_alloc_MtCO2"])/$ctry_val['pop_mln'])/($bau_1990/$ctry_val_1990['pop_mln']);
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as PC tax (collecting 1% of global GWP)
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Per capita tax (assuming global mitigation costs = 1% of global GWP)</td>";
    $val = 1000 * $world_tot['gdp_mer'] * 0.01 * $ctry_val["gdrs_rci"]/$ctry_val['pop_mln'];
    $retval .= "<td>" . nice_number('$', $val, '') . "</td>";
    $retval .= "</tr>";
    // GDRs allocation
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">Emissions allocation in " . $year ." as</td>";
    $retval .= "<td></td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as MtCO2e
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Mt" . $gases . "</td>";
    $val = $ctry_val["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . "</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent of 1990 emissions
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Percent of 1990 emissions</td>";
    $val = 100.0 * $ctry_val["gdrs_alloc_MtCO2"]/$bau_1990;
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent reduction of 1990 emissions 
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Percent reduction from 1990 emissions</td>";
    $val = 100.0 * (1 - $ctry_val["gdrs_alloc_MtCO2"]/$bau_1990);
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
//    // BAU emissions as percentage of 1990 emissions – projected to year
//    $retval .= "<tr>";
//    $retval .= "<td class=\"lj\">BAU emissions as percentage of 1990 emissions – projected to " . $year . "</td>";
//    $val = 100.0 * $bau/$bau_1990;
//    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
//    $retval .= "</tr>";
//    // Share of population above the development threshold – projected to year
//    $retval .= "<tr>";
//    $retval .= "<td class=\"lj\">Share of population above the development threshold – projected to " . $year . "</td>";
//    $val = 100.0 * $ctry_val["gdrs_pop_mln_above_dl"]/$ctry_val["pop_mln"];
//    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
//    $retval .= "</tr>";
//    // Share of global population – projected to year
//    $retval .= "<tr>";
//    $retval .= "<td class=\"lj\">Share of global population – projected to " . $year . "</td>";
//    $val = 100.0 * $ctry_val["pop_mln"]/$world_tot["pop"];
//    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
//    $retval .= "</tr>";
    
    
    /*
     * Tax table
     */
$retval .= <<< EOHTML
    </tbody>
</table>
<br />
<table cellspacing="2" cellpadding="2">
    <tbody>
    <thead>
    <tr>
        <th>Tax level<br/>(\$US/cap)</th>
        <th class="lj"></th>
        <th>Tax rate<br/>(% income)</th>
        <th>Population above<br/>tax level (% pop.)</th>
    </tr>
EOHTML;
    foreach ($db->query("SELECT seq_no, label, value FROM tax_levels;") as $record) {
        $retval .= '<tr>';
        if (!$record['value']) {
            $description = '(' . $record['label'] . ')';
            $val = $ctry_val['tax_income_dens_' . $record['seq_no']]/$ctry_val['tax_pop_dens_' . $record['seq_no']];
        } else {
            $description = '';
            $val = $record['value'];
        }
        $retval .= '<td>' . nice_number('', $val, '') . '</td>';
        $retval .= '<td class="lj">' . $description . '</td>';
        $val = 100 * $ctry_val['tax_revenue_dens_' . $record['seq_no']]/$ctry_val['tax_income_dens_' . $record['seq_no']];
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        $val = 100 * (1 - $ctry_val['tax_pop_mln_below_' . $record['seq_no']]/$ctry_val['pop_mln']);
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        $retval .= '</tr>';
    }
$retval .= <<< EOHTML
    </tbody>
</table>
EOHTML;
    
    /*
     * Pledge table
     */
$retval .= <<< EOHTML
    </tbody>
</table>
<br />
<table cellspacing="2" cellpadding="2">
    <tbody>
EOHTML;
    // International pledge
//    $intl_pledge = get_intl_pledge($iso3, $year);
//    if ($intl_pledge['intl_pledge'] !== 0) {
//        $retval .= '<tr><td class="lj" colspan="2">Pledged international support assuming ' . $intl_pledge['intl_price'] . ' USD/t' . $gases . '</td></tr>';
//        // Total
//        $retval .= "<tr>";
//        $retval .= "<td class=\"lj level2\">As Mt" . $gases . "</td>";
//        $val = $intl_pledge['intl_pledge'];
//        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
//        $retval .= "</tr>";
//        // Percent
//        $retval .= "<tr>";
//        $retval .= "<td class=\"lj level2\">As share of " . $year . " mitigation obligation</td>";
//        $val = 100 * $intl_pledge['intl_pledge']/($bau - $ctry_val["gdrs_alloc_MtCO2"]);
//        $retval .= "<td>" . nice_number('', $val, '') . "%</td>";
//        $retval .= "</tr>";
//    }
    $dom_pledges = get_processed_pledges($iso3, $shared_params, $dbfile);
    if ($dom_pledges['unconditional']) {
        $common_str = 'Unconditional pledged domestic action to ';
        $common_str .= $dom_pledges['unconditional']['pledge_info']['description'];
        $common_str .= ' by ' . $dom_pledges['unconditional']['year'];
        $retval .= '<tr><td class="lj" colspan="2">' . $common_str . '</td></tr>';
        // Total
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">As Mt" . $gases . "</td>";
        $val = $dom_pledges['unconditional']['pledge_info']['pledge'];
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        $retval .= "</tr>";
        // Percent
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">As share of " . $year . " mitigation obligation</td>";
        $val = 100 * $dom_pledges['unconditional']['pledge_info']['pledge']/($bau - $ctry_val["gdrs_alloc_MtCO2"]);
        $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
        $retval .= "</tr>";
    }
    if ($dom_pledges['conditional']) {
        $common_str = 'Conditional pledged domestic action to ';
        $common_str .= $dom_pledges['conditional']['pledge_info']['description'];
        $common_str .= ' by ' . $dom_pledges['conditional']['year'];
        $retval .= '<tr><td class="lj" colspan="2">' . $common_str . '</td></tr>';
        // Total
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">As Mt" . $gases . "</td>";
        $val = $dom_pledges['conditional']['pledge_info']['pledge'];
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        $retval .= "</tr>";
        // Percent
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">As share of " . $year . " mitigation obligation</td>";
        $val = 100 * $dom_pledges['conditional']['pledge_info']['pledge']/($bau - $ctry_val["gdrs_alloc_MtCO2"]);
        $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
        $retval .= "</tr>";
    }

    /*
     * Generate graphs
     */
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
    $query = 'SELECT year,';
    if (is_country($iso3)) {
        $query .=  ' gdrs_alloc_MtCO2, fossil_CO2_MtCO2, LULUCF_MtCO2,
        NonCO2_MtCO2e';
        $query .= ' FROM disp_temp WHERE iso3="' . $iso3 . '"';
    } else {
        $query .=  ' SUM(gdrs_alloc_MtCO2) AS gdrs_alloc_MtCO2,
        SUM(fossil_CO2_MtCO2) AS fossil_CO2_MtCO2,
        SUM(LULUCF_MtCO2) AS LULUCF_MtCO2,
        SUM(NonCO2_MtCO2e) AS NonCO2_MtCO2e';
       $query .= ' FROM disp_temp, flags WHERE';
        $query .= ' flags.value = 1 AND flags.iso3 = disp_temp.iso3 AND';
        $query .= ' flags.flag="' . $iso3 . '"';
    }
    $query .= ' AND year >= 1990 AND year <= 2030 GROUP BY year ORDER BY year;';
    
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
    
    $graph_width = 500;
    $graph_height = 312;
    $legend_height = 0;
    $graph = new Graph(array(
                    'width' => $graph_width,
                    'height' => $graph_height,
                    'legend_height' => $legend_height
                ), array(
                    'filename' => 'css/country_graphs.css',
                    'embed' => true
                )
                );
    // The TRUE means use the specified limits for the graph; the FALSE means don't format numbers
    $graph->set_xaxis(1990, 2030, "", "", TRUE, FALSE);
    $graph->set_yaxis($min, $max, "Mt" . $gases, "");
    $graph->add_series($bau_series, "bau");
    $graph->add_series($dulline_series, "physical");
    $graph->add_series($alloc_series, "gdrsalloc");
    if ($dom_pledges['conditional']) {
        $yr_ndx = $dom_pledges['conditional']['year'];
        $graph->add_glyph($yr_ndx,
                $bau_series[$yr_ndx] - $dom_pledges['conditional']['pledge_info']['pledge'],
                'cond-glyph',
                'circle', 10);
    }
    if ($dom_pledges['unconditional']) {
        $yr_ndx = $dom_pledges['unconditional']['year'];
        $graph->add_glyph($yr_ndx,
                $bau_series[$yr_ndx] - $dom_pledges['unconditional']['pledge_info']['pledge'],
                'uncond-glyph',
                'diamond', 12);
    }

    $maxgap = 0;
    $fund_others = false;
    for ($i = 1990; $i <= 2030; $i++ ) {
        if (abs($alloc_series[$i] - $dulline_series[$i]) > $maxgap) {
            $maxgap = abs($alloc_series[$i] - $dulline_series[$i]);
            $fund_others = $alloc_series[$i] < $dulline_series[$i];
        }
    }
    if ($fund_others) {
        $gap_color = NULL;
        $wedge_id = 'intloblig';
        $stripes = 'intlobligstripes';
    } else {
        $gap_color = '#6b87c3';
        $wedge_id = 'supportedmit';
        $stripes = NULL;
    }
    $graph_file = $graph->svgplot_wedges(array(
                        array(
                            'id' => 'mitoblig',
                            'between' => array('bau', 'gdrsalloc'),
                            'color' => '#8ebd7f',
                            'stripes' => NULL,
                            'opacity' => 0.8
                        ),
                        array(
                            'id' => $wedge_id,
                            'between' => array('physical', 'gdrsalloc'),
                            'color' => $gap_color,
                            'stripes' => $stripes,
                            'opacity' => 0.8
                        )
                    ), array(
                        'common_id' => 'historical',
                        'vertical_at' => $year
                    )
                    );

    $graph_file = "/tmp/" . basename($graph_file);
    
    $width_string = $graph_width . "px";
    $height_string = ($graph_height + $legend_height) . "px";
    
$retval .= <<< EOHTML
    </tbody>
</table>
<br />
<object data="$graph_file" type="image/svg+xml" style="width:$width_string; height:$height_string; border: 1px solid #CCC;">
    <p>No SVG support</p>
</object>
EOHTML;

$retval .= '<dl id="ctry_report_legend">';
    $retval .= '<dt class="key-bau"><span></span>Business as Usual</dt>';
    $retval .= '<dd>GHG emissions baselines (“BAU”) are based on projected emissions growth rates from McKinsey and Co\'s projections (Version 2.1) applied to the most current available annual emissions data (CO2 from fossil fuels from CDIAC\'s 2010 estimates); CO2 from land use is projected constant at 2005 levels and non-CO2 GHGs are a constant proportion relative to Fossil CO2 emissions at 2005 levels.</dd>';

    $retval .= '<dt class="key-gdrs"><span></span>GDRs "fair share" allocation</dt>';
    $retval .= '<dd>National allocation trajectory, as calculated by GDRs for ' . $ctry_val["country"] . ' using the specified pathways and parameters. The mitigation implied by this allocation can be either domestic or international &#8211; GDRs in itself says nothing about how or where it occurs.</dd>';
    
    $retval .= '<dt class="key-phys"><span></span>Domestic emissions</dt>';
    $retval .= '<dd>An example of an emissions trajectory for ' . $ctry_val["country"] . ' that is consistent with the specified pathways and parameters. ';
    $retval .= 'The actual domestic emissions trajectory would depend on the international cost and mitigation sharing that ' . $ctry_val["country"] . ' chooses to participate in. GDRs assigns each country a mitigation obligation. It does not specify how or where that obligation should be discharged.</dd>';

    $retval .= '<dt class="key-dom"><span></span>';
    if ($fund_others) {
        $retval .= 'Domestic-funded mitigation</dt>'; // if we decide to make a distinction, this one would be Domestic mitigation, with its own definition 
        $retval .= '<dd>Mitigation funded by ' . $ctry_val["country"] . ' and carried out within its own borders. The fraction of a country\'s mitigation obligation that is discharged domestically is not specified by GDRs, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.</dd>';
    } else {
        $retval .= 'Domestically-funded mitigation</dt>';
        $retval .= '<dd>Mitigation funded by ' . $ctry_val["country"] . ' and carried out within its own borders. The fraction of a country\'s mitigation obligation that is discharged domestically is not specified by GDRs, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.</dd>';
    }
    
    if ($fund_others) {
        $retval .= '<dt class="key-intl"><span></span>Mitigation funded in other countries</dt>';
        $retval .= '<dd>Mitigation funded by ' . $ctry_val["country"] . ' and carried out within other countries. The fraction of a country\'s mitigation obligation that is discharged in other countries is not specified by GDRs, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.</dd>';
        } else {
        $retval .= '<dt class="key-sup"><span></span>Mitigation funded by other countries</dt>';
        $retval .= '<dd>Mitigation funded other countries, but carried out within the borders of ' . $ctry_val["country"] . '. GDRs assigns the "credit" for this mitigation to the funder, but of course the terms of the mitigation would be as negotiated with the host country.</dd>';
    }
    $retval .= '<dt class="key-uncond"><span></span>Unconditional Pledge</dt>';
    $retval .= '<dd>Emissions consistent with ' . $ctry_val["country"] . '&#8217;s pledged emission reductions <em>not</em> conditional on other countries&#8217; actions.</dd>';

    $retval .= '<dt class="key-cond"><span></span>Conditional Pledge</dt>';
    $retval .= '<dd>Emissions consistent with ' . $ctry_val["country"] . '&#8217;s pledged emission reductions conditional on other countries&#8217; actions.</dd>';

    $retval .= '</dl>';

return $retval;
}