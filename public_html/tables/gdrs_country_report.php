<?php
require_once("graphs/graph_core.php");
require_once("pledges/pledge_functions.php");
require_once("table_common.php");
require_once("frameworks/frameworks.php");

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

function get_free_rider_adj($db, $iso3) {
    $retval = 0;
    
    $sql = "SELECT int_val FROM params WHERE param_id='use_kab'";
    $record = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    if ($record[0]['int_val']) {
    
$sql = <<<EOSQL
       SELECT commitment_percent FROM country LEFT OUTER JOIN
    (SELECT iso3, commitment_percent FROM kyoto_info, params WHERE
       param_id='kab_only_ratified' AND (int_val=0 OR ratified=1)) AS temp
       ON country.iso3 = temp.iso3 WHERE country.iso3='$iso3';
EOSQL;
        $record = $db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
        if ($record[0]['commitment_percent']) {
            $retval = 0.01 * max(0, 100 - $record[0]['commitment_percent']);
        }
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
    $i = 0;
    foreach ($year_list as $y) {
        $ctry_val[$y] = $record[$i];
        $bau[$y] = $ctry_val[$y]['fossil_CO2_MtCO2'] + 
            $use_nonco2 * $ctry_val[$y]['NonCO2_MtCO2e'] + 
            $use_lulucf * $ctry_val[$y]['LULUCF_MtCO2'];
        $i++;
    }
    $world_bau = $world_tot['fossil_CO2'] + 
        $use_nonco2 * $world_tot['NonCO2'] + 
        $use_lulucf * $world_tot['LULUCF'];
    
    $retval = '';
    
    /*
     * Generate graphs
     */
    $dom_pledges = get_processed_pledges($iso3, $shared_params, $dbfile);
    
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
    $glyph_id = 0;
    foreach ($dom_pledges['conditional'] as $pledge_year => $pledge_info) {
        $yr_ndx = $pledge_year;
        $graph->add_glyph($yr_ndx,
                $bau_series[$yr_ndx] - $pledge_info['pledge'],
                'cond-glyph', 'cond-glyph-' . $glyph_id++,
                'circle', 10);
    }
    foreach ($dom_pledges['unconditional'] as $pledge_year => $pledge_info) {
        $yr_ndx = $pledge_year;
        $graph->add_glyph($yr_ndx,
                $bau_series[$yr_ndx] - $pledge_info['pledge'],
                'uncond-glyph', 'uncond-glyph-' . $glyph_id++,
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
<object data="$graph_file" type="image/svg+xml" style="width:$width_string; height:$height_string; border: 1px solid #CCC;">
    <p>No SVG support</p>
</object>
EOHTML;
    
    $retval .= '<p id="toggle-key">' . _('Show graph key') . '</p>';
    $retval .= '<dl id="ctry_report_legend">';
    $retval .= '<dt class="key-bau"><span></span>' . _('Business as Usual') . '</dt>';
    $retval .= '<dd>' . _('GHG emissions baselines (“BAU”) are based on projected emissions growth rates from McKinsey and Co\'s projections (Version 2.1) applied to the most current available annual emissions data (CO<sub>2</sub> from fossil fuels from CDIAC\'s 2010 estimates); CO<sub>2</sub> from land use is projected constant at 2005 levels and non-CO<sub>2</sub> GHGs are a constant proportion relative to Fossil CO<sub>2</sub> emissions at 2005 levels.') . '</dd>';

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
        if (!empty($dom_pledges['unconditional'])) {
            $retval .= '<dt class="key-uncond"><span></span>' . _('Unconditional Pledge') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Emissions consistent with %s&#8217;s pledged emission reductions <em>not</em> conditional on other countries&#8217; actions.'), $country_name) . '</dd>';
        }
        if (!empty($dom_pledges['conditional'])) {
            $retval .= '<dt class="key-cond"><span></span>' . _('Conditional Pledge') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Emissions consistent with %s&#8217;s pledged emission reductions conditional on other countries&#8217; actions.'), $country_name) . '</dd>';
        }
    }
    $retval .= '</dl>';
    /*
     * Main table
     */
$retval .= <<< EOHTML
<br />
<table cellspacing="2" cellpadding="2">
<caption>Fair share table</caption>
    <tbody>
EOHTML;
    
    // BAU emissions
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%1$s business-as-usual emissions, projected to %2$d'), $country_name, $year) . "</td>";
    $val = $bau[$year];
    $retval .= '<td class="cj">&nbsp;</td>';
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // year Global mitigation obligation as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_("Global mitigation requirement below business-as-usual, projected to %d"), $year) . "</td>";
    $retval .= '<td class="cj">(A)</td>';
    $val = $world_bau - $world_tot["gdrs_alloc"];
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // Share of global RCI in year
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%1$s share of global Responsibility Capacity Index, projected to %2$d'), $country_name, $year) . "</td>";
    $retval .= '<td class="cj">(B)</td>';
    $val = 100.0 * $ctry_val[$year]["gdrs_rci"];
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // National mitigation obligation (group)
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%1$s mitigation obligation, projected to %2$d'), $country_name, $year) . "</td>";
    $retval .= '<td class="cj">(A &#215; B)</td>';
    $retval .= "<td>&nbsp;</td>";
    $retval .= "</tr>";
    // National mitigation obligation as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as tons below business-as-usual") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = $bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // National mitigation obligation as % below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as percent below business-as-usual") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 100 * (1 - $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[$year]);
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // Climate tax
    $climate_tax_link = '<a href="#tax-table">' . _('climate tax') . '</a>';
    $retval .= '<tr>';
    $retval .= "<td class=\"lj level2\">" . sprintf(_('as per-capita %1$s (assuming global mitigation costs = %2$s%% of global GWP)'), $climate_tax_link, nice_number('', $perc_gwp, '')) . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 1000 * $world_tot['gdp_mer'] * 0.01 * $perc_gwp * $ctry_val[$year]["gdrs_rci"]/$ctry_val[$year]['pop_mln'];
    $retval .= "<td>" . nice_number('$', $val, '') . "</td>";
    $retval .= "</tr>";
    // Blank line
    $retval .= "<tr class=\"blank\"><td colspan=\"3\">&nbsp;</td></tr>";
    // Emissions in 1990
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%s 1990 emissions'), $country_name) . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = $bau[1990];
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // GDRs allocation
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%1$s emissions allocation, projected to %2$d'), $country_name, $year) . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $retval .= '<td>&nbsp;</td>';
    $retval .= "</tr>";
    // GDRs 2020 allocation as MtCO2e
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as tons") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = $ctry_val[$year]["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent of 1990 emissions
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as percent of 1990 emissions") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 100.0 * $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[1990];
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent reduction of 1990 emissions 
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as percent below 1990 emissions") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 100.0 * (1 - $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[1990]);
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // Blank line
    $retval .= "<tr class=\"blank\"><td colspan=\"3\">&nbsp;</td></tr>";
    if (Framework::is_dev()) {
        $scorecard_url = 'http://www.gdrights.org/scorecard/';
    } else {
        $scorecard_url = 'http://www.gdrights.org/scorecard_dev/';
    }
    
    // Pledges
    $free_rider_adj = get_free_rider_adj($db, $iso3);

    $scorecard_link = '<a href="' . $scorecard_url . '">' . _('Climate Equity Scorecard') . '</a>';
    $condl_term = array('conditional' => _('conditional'), 'unconditional' => _('unconditional'));
    foreach (array('unconditional', 'conditional') as $condl) {
        $pledges = $dom_pledges[$condl];
        foreach ($pledges as $pledge_year => $pledge_info) {
            $mit_oblig = $bau[$pledge_year] - $ctry_val[$pledge_year]["gdrs_alloc_MtCO2"];
            $common_str = sprintf(_('%1$s %2$s pledge: %3$s by %4$d'),
                    $country_name,
                    $condl_term[$condl],
                    $pledge_info['description'],
                    $pledge_year);
            $retval .= '<tr><td class="lj" colspan="3">' . $common_str . '</td></tr>';
            // Total
            $retval .= "<tr>";
            $retval .= "<td class=\"lj level2\">as tons</td>";
            $retval .= '<td class="cj">&nbsp;</td>';
            $val = $pledge_info['pledge'];
            $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
            $retval .= "</tr>";
            // % below BAU
            $retval .= "<tr>";
            $retval .= "<td class=\"lj level2\">" . _('as percent below business-as-usual') . "</td>";
            $retval .= '<td class="cj">&nbsp;</td>';
            $val = 100 * $pledge_info['pledge']/$bau[$pledge_year];
            $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
            $retval .= "</tr>";
            // Score
            $retval .= "<tr>";
            $retval .= "<td class=\"lj level2\">" . sprintf(_('as %s-style score'), $scorecard_link) . "</td>";
            $retval .= '<td class="cj">&nbsp;</td>';
            $val = 100 * ($pledge_info['pledge'] - $mit_oblig)/($bau[$pledge_year] * (1 - $free_rider_adj));
            $retval .= "<td>" . nice_number('', $val, '') . "</td>";
            $retval .= "</tr>";
        }
    }
    
    // Close the table
    $retval .= '</tbody></table>';
    $retval .= '<br />';
    
    
    /*
     * Tax table
     */
    $cost_of_mitigation = 0.01 * $perc_gwp * $world_tot['gdp_mer']/($world_bau - $world_tot['gdrs_alloc']);
    
$retval .= <<< EOHTML
<a name="tax-table"></a>
<br />
<table cellspacing="2" cellpadding="2">
    <caption>Tax table</caption>
    <thead>
EOHTML;


$retval .= <<< EOHTML
    <tr>
        <th>Income level<br/>(\$US/cap)</th>
        <th>Income level<br/>(in PPP terms)</th>
        <th class="lj"></th>
        <th>&#8220Tax rate&#8221<br/>(% income)</th>
        <th>Population above<br/>tax level (% pop.)</th>
        <th>Per-capita obligation<br/>(\$US/cap)</th>
        <th>Per-capita obligation<br/>(kt$gases/cap)</th>
    </tr>
    </thead>
    <tbody>
EOHTML;
    foreach ($db->query("SELECT seq_no, label, value, ppp FROM tax_levels ORDER BY seq_no;") as $record) {
        $retval .= '<tr>';
        if (!$record['value']) {
            $description = '(' . $record['label'] . ')';
        } else {
            $description = '';
        }
        $val = $ctry_val[$year]['tax_income_mer_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_pop_dens_' . $record['seq_no']];
        $income_tmp = $val;
        $retval .= '<td>' . nice_number('', $val, '') . '</td>';
        $val = $ctry_val[$year]['tax_income_ppp_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_pop_dens_' . $record['seq_no']];
        $retval .= '<td>' . nice_number('', $val, '') . '</td>';
        $retval .= '<td class="lj">' . $description . '</td>';
        if ($record['ppp']) {
            $val = 100 * $ctry_val[$year]['tax_revenue_ppp_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_income_ppp_dens_' . $record['seq_no']];
        } else {
            $val = 100 * $ctry_val[$year]['tax_revenue_mer_dens_' . $record['seq_no']]/$ctry_val[$year]['tax_income_mer_dens_' . $record['seq_no']];
        }
        $per_cap_tax = 0.01 * $val * $income_tmp;
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        $val = 100 * (1 - $ctry_val[$year]['tax_pop_mln_below_' . $record['seq_no']]/$ctry_val[$year]['pop_mln']);
        $retval .= "<td>" . nice_number('', $val, '') . "</td>";
        // This was calculated above as tax (% income) * income
        $retval .= "<td>" . nice_number('', $per_cap_tax, '') . "</td>";
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
//    foreach ($dom_pledges['conditional'] as $pledge_year => $pledge_info) {
//        $common_str = 'Conditional pledged domestic action to ';
//        $common_str .= $pledge_info['description'];
//        $common_str .= ' by ' . $pledge_year;
//        $retval .= '<tr><td class="lj" colspan="2">' . $common_str . '</td></tr>';
//        // Total
//        $retval .= "<tr>";
//        $retval .= "<td class=\"lj level2\">As tons</td>";
//        $val = $pledge_info['pledge'];
//        $retval .= "<td>" . nice_number('', $val, '')  . ' Mt' . $gases . "</td>";
//        $retval .= "</tr>";
//        // Percent
//        $retval .= "<tr>";
//        $retval .= "<td class=\"lj level2\">As share of " . $pledge_year . " mitigation obligation</td>";
//        $val = 100 * $pledge_info['pledge']/($bau[$pledge_year] - $ctry_val[$pledge_year]["gdrs_alloc_MtCO2"]);
//        $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
//        $retval .= "</tr>";
//    }
    // Close the table
   
return $retval;
}