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
function gdrs_country_report($dbfile, $country_name, $shared_params, $iso3, $year) {
    $year_list = get_pledge_years($iso3);
    $year_list[] = $year;
    $year_list[] = 1990;
    sort($year_list, SORT_NUMERIC);
    $year_list = array_unique($year_list, SORT_NUMERIC);
    $year_list_string = 'year=' . implode(' OR year=', $year_list);
    
    $world_code = Framework::get_world_code();
    
    $viewquery = get_common_table_query($dbfile);

    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");
    
    // Get value for percent of GWP as a convenience -- used in a couple of places
    $perc_gwp = $shared_params['percent_gwp']['value'];

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
    if ($iso3 === $world_code) {
        $flag_string = ' WHERE (' . $year_list_string . ')';
    } else {
        $flag_string = ', flags WHERE ';
        $flag_string .= 'flags.iso3 = disp_temp.iso3 AND ';
        $flag_string .= '(' . $year_list_string . ') AND ';
        $flag_string .= 'flags.value = 1 AND flags.flag = "' . $iso3 . '"';
    }
    $tax_string = '';
    foreach ($db->query("SELECT seq_no FROM tax_levels;") as $record) {
        $tax_string .= sprintf(', SUM(tax_pop_mln_below_%1$d) AS tax_pop_mln_below_%1$d', $record['seq_no']);
        $tax_string .= sprintf(', SUM(tax_income_mer_dens_%1$d) AS tax_income_mer_dens_%1$d', $record['seq_no']);
        $tax_string .= sprintf(', SUM(tax_income_ppp_dens_%1$d) AS tax_income_ppp_dens_%1$d', $record['seq_no']);
        $tax_string .= sprintf(', SUM(tax_revenue_mer_dens_%1$d) AS tax_revenue_mer_dens_%1$d', $record['seq_no']);
        $tax_string .= sprintf(', SUM(tax_revenue_ppp_dens_%1$d) AS tax_revenue_ppp_dens_%1$d', $record['seq_no']);
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
    FROM disp_temp$flag_string
    GROUP BY year ORDER BY year;
EOSQL;
} else {
    $regionquery = null;
}
    $record = $db->query($worldquery)->fetchAll();
    $world_tot = $record[0]; // Only one record, but using "fetchAll" makes sure curser closed
    if (is_country($iso3)) {
        $record = $db->query('SELECT * FROM disp_temp WHERE (' . $year_list_string . ') AND iso3="' . $iso3 . '" ORDER BY year;')->fetchAll();
    } else {
        $record = $db->query($regionquery)->fetchAll();;
    }
    
    $use_nonco2 = $shared_params['use_nonco2']['value'];
    $use_lulucf = $shared_params['use_lulucf']['value'];
    if ($use_nonco2) {
        $gases = "CO<sub>2</sub>e";
    } else {
        $gases = "CO<sub>2</sub>";
    }
    for ($i = 0; $i < count($year_list); $i++) {
        $y = $year_list[$i];
        $ctry_val[$y] = $record[$i];
        $bau[$y] = $ctry_val[$y]['fossil_CO2_MtCO2'] + 
            $use_nonco2 * $ctry_val[$y]['NonCO2_MtCO2e'] + 
            $use_lulucf * $ctry_val[$y]['LULUCF_MtCO2'];
    }
//    $bau_1990 = $ctry_val_1990['fossil_CO2_MtCO2'];
//    $bau = $ctry_val['fossil_CO2_MtCO2'];
    $world_bau = $world_tot['fossil_CO2'] + 
        $use_nonco2 * $world_tot['NonCO2'] + 
        $use_lulucf * $world_tot['LULUCF'];
    
$retval = <<< EOHTML
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
    $val = 100.0 * $ctry_val[$year]["gdrs_rci"];
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
    $val = $bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as a reduction target from 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Percentage of 1990 emissions</td>";
    $val = 100.0 * ($bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"])/$bau[1990];
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation per capita as reduction from 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Per capita as percentage of 1990 per capita emissions</td>";
    $val = 100.0 * (($bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"])/$ctry_val[$year]['pop_mln'])/($bau[1990]/$ctry_val[1990]['pop_mln']);
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // year Mitigation obligation as PC tax (collecting x% of global GWP)
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Per capita tax (assuming global mitigation costs = " . $perc_gwp . "% of global GWP)</td>";
    $val = 1000 * $world_tot['gdp_mer'] * 0.01 * $perc_gwp * $ctry_val[$year]["gdrs_rci"]/$ctry_val[$year]['pop_mln'];
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
    $val = $ctry_val[$year]["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . "</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent of 1990 emissions
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Percent of 1990 emissions</td>";
    $val = 100.0 * $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[1990];
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent reduction of 1990 emissions 
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">Percent reduction from 1990 emissions</td>";
    $val = 100.0 * (1 - $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[1990]);
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>"; 
    
    /*
     * Tax table
     */
    ;
    $cost_of_mitigation = 0.01 * $perc_gwp * $world_tot['gdp_mer']/($world_bau - $world_tot['gdrs_alloc']);
    
$retval .= <<< EOHTML
    </tbody>
</table>
<br />
<table cellspacing="2" cellpadding="2">
    <tbody>
    <thead>
    <tr>
        <th>Income level<br/>(\$US/cap)</th>
        <th>Income level<br/>(in PPP terms)</th>
        <th class="lj"></th>
        <th>&#8220Tax rate&#8221<br/>(% income)</th>
        <th>Population above<br/>tax level (% pop.)</th>
        <th>Per-capita obligation<br/>(kt$gases/cap)</th>
    </tr>
EOHTML;
    foreach ($db->query("SELECT seq_no, label, value, ppp FROM tax_levels ORDER BY seq_no;") as $record) {
        $retval .= '<tr>';
        if (!$record['value']) {
            $description = '(' . $record['label'] . ')';
        } else {
            $description = '';
        }
        $val = $ctry_val[$year]['tax_income_mer_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_pop_dens_' . $record['seq_no']];
        $retval .= '<td>' . nice_number('', $val, '') . '</td>';
        $val = $ctry_val[$year]['tax_income_ppp_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_pop_dens_' . $record['seq_no']];
        $retval .= '<td>' . nice_number('', $val, '') . '</td>';
        $retval .= '<td class="lj">' . $description . '</td>';
        if ($record['ppp']) {
            $val = 100 * $ctry_val[$year]['tax_revenue_ppp_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_income_ppp_dens_' . $record['seq_no']];
        } else {
            $val = 100 * $ctry_val[$year]['tax_revenue_mer_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_income_mer_dens_' . $record['seq_no']];
        }
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        $val = 100 * (1 - $ctry_val[$year]['tax_pop_mln_below_' . $record['seq_no']]/$ctry_val[$year]['pop_mln']);
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        $val = 0.001 * (1/$cost_of_mitigation) * $ctry_val[$year]['tax_revenue_mer_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_pop_dens_' . $record['seq_no']];        
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
        $val = 100 * $dom_pledges['unconditional']['pledge_info']['pledge']/($bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"]);
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
        $val = 100 * $dom_pledges['conditional']['pledge_info']['pledge']/($bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"]);
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
        $query .= ' FROM disp_temp WHERE iso3="' . $iso3 . '" AND';
    } else {
        $query .=  ' SUM(gdrs_alloc_MtCO2) AS gdrs_alloc_MtCO2,
        SUM(fossil_CO2_MtCO2) AS fossil_CO2_MtCO2,
        SUM(LULUCF_MtCO2) AS LULUCF_MtCO2,
        SUM(NonCO2_MtCO2e) AS NonCO2_MtCO2e';
        if ($iso3 === $world_code) {
            $query .= ' FROM disp_temp WHERE';
        } else {
            $query .= ' FROM disp_temp, flags WHERE';
            $query .= ' flags.value = 1 AND flags.iso3 = disp_temp.iso3 AND';
            $query .= ' flags.flag="' . $iso3 . '" AND';
        }
    }
    $query .= ' year >= 1990 AND year <= 2030 GROUP BY year ORDER BY year;';
    
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
    $retval .= '<dt class="key-bau"><span></span>' . _('Business as Usual') . '</dt>';
    $retval .= '<dd>' . _('GHG emissions baselines (“BAU”) are based on projected emissions growth rates from McKinsey and Co\'s projections (Version 2.1) applied to the most current available annual emissions data (CO2 from fossil fuels from CDIAC\'s 2010 estimates); CO2 from land use is projected constant at 2005 levels and non-CO2 GHGs are a constant proportion relative to Fossil CO2 emissions at 2005 levels.') . '</dd>';

    $retval .= '<dt class="key-gdrs"><span></span>' . _('GDRs "fair share" allocation') . '</dt>';
    $retval .= '<dd>' . sprintf(_('National allocation trajectory, as calculated by GDRs for %s using the specified pathways and parameters. The mitigation implied by this allocation can be either domestic or international &#8211; GDRs in itself says nothing about how or where it occurs.'), $country_name) . '</dd>';
    
    if ($iso3 != $world_code) {
        $retval .= '<dt class="key-phys"><span></span>' . _('Domestic emissions') . '</dt>';
        $retval .= '<dd>' . sprintf(_('An example of an emissions trajectory for %s that is consistent with the specified pathways and parameters.'), $country_name);
        $retval .= sprintf(_('The actual domestic emissions trajectory would depend on the international cost and mitigation sharing that %s chooses to participate in. GDRs assigns each country a mitigation obligation. It does not specify how or where that obligation should be discharged.'), $country_name) . '</dd>';

        $retval .= '<dt class="key-dom"><span></span>';
        if ($fund_others) {
            $retval .= _('Domestically-funded mitigation') . '</dt>'; // if we decide to make a distinction, this one would be Domestic mitigation, with its own definition 
            $retval .= '<dd>'. sprintf(_('Mitigation funded by %s and carried out within its own borders. The fraction of a country\'s mitigation obligation that is discharged domestically is not specified by GDRs, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.'), $country_name) . '</dd>';
        } else {
            $retval .= _('Domestically-funded mitigation') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Mitigation funded by %s and carried out within its own borders. The fraction of a country\'s mitigation obligation that is discharged domestically is not specified by GDRs, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.'), $country_name) . '</dd>';
        }

        if ($fund_others) {
            $retval .= '<dt class="key-intl"><span></span>' . _('Mitigation funded in other countries') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Mitigation funded by %s and carried out within other countries. The fraction of a country\'s mitigation obligation that is discharged in other countries is not specified by GDRs, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.'), $country_name) . '</dd>';
            } else {
            $retval .= '<dt class="key-sup"><span></span>' . _('Mitigation funded by other countries') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Mitigation funded other countries, but carried out within the borders of %s. GDRs assigns the "credit" for this mitigation to the funder, but of course the terms of the mitigation would be as negotiated with the host country.'), $country_name) . '</dd>';
        }
        $retval .= '<dt class="key-uncond"><span></span>' . _('Unconditional Pledge') . '</dt>';
        $retval .= '<dd>' . sprintf(_('Emissions consistent with %s&#8217;s pledged emission reductions <em>not</em> conditional on other countries&#8217; actions.'), $country_name) . '</dd>';

        $retval .= '<dt class="key-cond"><span></span>' . _('Conditional Pledge') . '</dt>';
        $retval .= '<dd>' . sprintf(_('Emissions consistent with %s&#8217;s pledged emission reductions conditional on other countries&#8217; actions.'), $country_name) . '</dd>';
    }
    $retval .= '</dl>';

return $retval;
}