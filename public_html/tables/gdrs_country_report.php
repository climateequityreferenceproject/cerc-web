<?php
require_once("config.php");
require_once("graphs/graph_core.php");
require_once("pledges/pledge_functions.php");
require_once("table_common.php");
require_once("frameworks/frameworks.php");

function dec($num) {
    return max(0, 1 - floor(log10(abs($num))));
}

function nice_number($prefix, $num, $postfix, $decimal = NULL) {
    if (abs($num) < 1.0e-7) {
        $num = 0;
    }
    if ($num < 0) {
        $retval = '<span class="num_negative">-';
    } else {
        $retval = '';
    }
    $retval .= $prefix;
    if (is_numeric($decimal)) {
        $dec = $decimal;
    } else {
        $dec = dec($num);
    }
    $retval .= number_format(abs($num), $dec);
    $retval .= $postfix;
    if ($num < 0) {
        $retval .= '</span>';
    }
    return $retval;
}

function is_country_gdrsdb($db, $code)
{
    $record = $db->query('SELECT iso3 FROM country WHERE iso3="' . $code . '";')->fetchAll();
    return count($record) > 0;
}

function get_kyoto_commitment($db, $iso3) {
    $retval = null;

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
            $retval = 0.01 * $record[0]['commitment_percent'];
        }
    }

    return $retval;
}

// TODO: Replace "iso3" with the more generic "code"
function gdrs_country_report($dbfile, $country_name, $shared_params, $display_params, $year) {
    global $host_name, $main_domain_host;
    global $URL_sc, $URL_sc_dev, $URL_calc, $svg_tmp_dir;
    global $glossary;
    $iso3 = $display_params['display_ctry']['value'];
    $year_list = get_pledge_years($iso3);
    $year_list[] = $year;
    $year_list[] = 1990;
    $year_list[] = intval($display_params['reference_yr']['value']);
    $year_list[] = 2012;
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

if (!is_country_gdrsdb($db,$iso3)) {
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
    if (is_country_gdrsdb($db,$iso3)) {
        $record = $db->query('SELECT * FROM disp_temp WHERE (' . $year_list_string . ') AND iso3="' . $iso3 . '" ORDER BY year;')->fetchAll();
    } else {
        $record = $db->query($regionquery)->fetchAll();
    }

    $use_nonco2 = (int) $shared_params['use_nonco2']['value'];
    $use_lulucf = (int) $shared_params['use_lulucf']['value'];
    $gases = "CO<sub>2</sub>";
    $gases_svg = 'CO<tspan dy="3" font-size="10">2</tspan>';
    if ($use_nonco2) {
        $gases .= "e";
        $gases_svg .= '<tspan dy="-3">e</tspan>';
    }
    if (!($use_lulucf)) {
        // $gases .= " (excl. LULUCF)"; // looks weird in the table
        $gases_svg .= ' (excl. LULUCF)';
    }
    $i = 0;
    foreach ($year_list as $y) {
        $ctry_val[$y] = $record[$i];
        $pop[$y] = $ctry_val[$y]['pop_mln'];
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
    $num_pledges = count($dom_pledges, COUNT_RECURSIVE) - count($dom_pledges, COUNT_NORMAL);

$query = <<< EOSQL
SELECT year, SUM(gdrs_alloc_MtCO2) AS gdrs_alloc_MtCO2, SUM(fossil_CO2_MtCO2) AS fossil_CO2_MtCO2,
       SUM(LULUCF_MtCO2) AS LULUCF_MtCO2, SUM(NonCO2_MtCO2e) AS NonCO2_MtCO2e FROM disp_temp
       WHERE year >= 1990 AND year <= 2030 GROUP BY year ORDER BY year;
EOSQL;

    $global_bau_series = array();
    $global_alloc_series = array();
    foreach ($db->query($query) as $record) {
        $yr_ndx = $record['year'];
        $global_bau['fossil'][$yr_ndx] = $record['fossil_CO2_MtCO2'];
        $global_bau['lulucf'][$yr_ndx] = $record['LULUCF_MtCO2'];
        $global_bau['nonco2'][$yr_ndx] = $record['NonCO2_MtCO2e'];
        $global_bau_series[$yr_ndx] = $global_bau['fossil'][$yr_ndx] + ($use_lulucf * $global_bau['lulucf'][$yr_ndx]) + ($use_nonco2 * $global_bau['nonco2'][$yr_ndx]);
        $global_alloc_series[$yr_ndx] = $record['gdrs_alloc_MtCO2'];
    }
    $query = 'SELECT year,';
    if (is_country_gdrsdb($db,$iso3)) {
        $query .=  ' gdrs_alloc_MtCO2, fossil_CO2_MtCO2, LULUCF_MtCO2,
        NonCO2_MtCO2e, gdrs_rci';
        $query .= ' FROM disp_temp WHERE iso3="' . $iso3 . '" AND';
    } else {
        $query .=  ' SUM(gdrs_alloc_MtCO2) AS gdrs_alloc_MtCO2,
        SUM(fossil_CO2_MtCO2) AS fossil_CO2_MtCO2,
        SUM(LULUCF_MtCO2) AS LULUCF_MtCO2,
        SUM(NonCO2_MtCO2e) AS NonCO2_MtCO2e,
        SUM(gdrs_rci) AS gdrs_rci';
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
    $bau_data = array();
    $alloc_series = array();
    $dulline_series = array();
    $min = 0;
    $max = 0;
    foreach ($db->query($query) as $record) {
        $yr_ndx = $record['year'];
        $bau_series[$yr_ndx] = $record['fossil_CO2_MtCO2'] + ($use_lulucf * $record['LULUCF_MtCO2']) + ($use_nonco2 * $record['NonCO2_MtCO2e']);
        $bau_data['fossil_CO2_MtCO2'][$yr_ndx] = $record['fossil_CO2_MtCO2'];
        $bau_data['LULUCF_MtCO2'][$yr_ndx] = $record['LULUCF_MtCO2'];
        $bau_data['NonCO2_MtCO2e'][$yr_ndx] = $record['NonCO2_MtCO2e'];
        $alloc_series[$yr_ndx] = $record['gdrs_alloc_MtCO2'];

        $dulline_series[$yr_ndx] = $bau_series[$yr_ndx] * ($global_alloc_series[$yr_ndx]/$global_bau_series[$yr_ndx]);
        $min = min($min, $alloc_series[$yr_ndx]);
        $max = max($max, $bau_series[$yr_ndx]);
    }
//   var_dump($bau_series,$global_alloc_series, $global_bau_series , $dulline_series);die();
    // check if we need to change the chart scale to fit all pledges on the chart
    foreach (array('conditional', 'unconditional') as $condl) {
        $pledges = $dom_pledges[$condl];
        foreach ($pledges as $pledge_year => $pledge_info) {
            $min = min($min, $bau_series[$pledge_info['by_year']] - $pledge_info['pledge']);
            $max = max($max, $bau_series[$pledge_info['by_year']] - $pledge_info['pledge']);
        }
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
    $graph->set_yaxis($min, $max, "Mt" . $gases_svg, "");
    $graph->add_series($bau_series, "bau", "bau");
    if (Framework::is_dev() || Framework::user_is_developer()) {
        $graph->add_series($bau_data['fossil_CO2_MtCO2'], "fossil_CO2_MtCO2", "bau_details_fossil");
        $graph->add_series($bau_data['LULUCF_MtCO2'], "LULUCF_MtCO2", "bau_details_lulucf");
        $graph->add_series($bau_data['NonCO2_MtCO2e'], "NonCO2_MtCO2e", "bau_details_nonco2");
    }
    $graph->add_series($dulline_series, "physical", "physical");
    $graph->add_series($alloc_series, "alloc",  "alloc");
    $glyph_id = 0;
    foreach (array('conditional', 'unconditional') as $condl) {
        $pledges = $dom_pledges[$condl];
        foreach ($pledges as $pledge_year => $pledge_info) {
            $yr_ndx = $pledge_year;
            $conditionality = isset($pledge_info['conditionality_override']) ? $pledge_info['conditionality_override'] : $condl;
            if ($conditionality=="conditional") {
                $graph->add_glyph($yr_ndx,
                        $bau_series[$yr_ndx] - $pledge_info['pledge'],
                        'cond-glyph', 'cond-glyph-' . $glyph_id++,
                        'circle', 10);
            }
            if ($conditionality=="unconditional") {
                $graph->add_glyph($yr_ndx,
                        $bau_series[$yr_ndx] - $pledge_info['pledge'],
                        'uncond-glyph', 'uncond-glyph-' . $glyph_id++,
                        'diamond', 12);
            }
        }
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
    if (!(Framework::is_dev() || (Framework::user_is_developer()))) {
        // in tooltips, only shows greenlines for devs - we need to decide whether showing
        // numbers reifies the greenline?
        $ignore_for_tooltips = array("physical");
    }
//    $graph_file = $graph->svgplot_wedges(array(    // old start of the plot command, to write to svg file - would also have "code_output" as false
    // getting svg source code and dumping it directly into the page to make it css-able
    $retval .= $graph->svgplot_wedges(array(
                        array(
                            'id' => 'mitoblig',
                            'between' => array('bau', 'alloc'),
                            'color' => '#8ebd7f',
                            'stripes' => NULL,
                            'opacity' => 0.8,
                            'css_class' => 'mitoblig'
                        ),
                        array(
                            'id' => $wedge_id,
                            'between' => array('physical', 'alloc'),
                            'color' => $gap_color,
                            'stripes' => $stripes,
                            'opacity' => 0.8
                        )
                    ), array(
                        'common_id' => 'historical',
                        'ignore_for_common' => array('natl_bau', 'fossil_CO2_MtCO2', 'LULUCF_MtCO2', 'NonCO2_MtCO2e'),
                        'vertical_at' => $year,
                        'show_data_tooltips' => (Framework::is_dev() || (Framework::user_is_developer())),
                        'ignore_for_tooltips' => $ignore_for_tooltips,
                        'code_output' => true
                    )
                    );

// below, old code that constructs an object to display the external svg image
// file instead of dumping svg code onto the page
//    $graph_file = "/tmp/" . basename($graph_file);
//    $width_string = $graph_width . "px";
//    $height_string = ($graph_height + $legend_height) . "px";
//$retval .= <<< EOHTML
//<object data="$graph_file" type="image/svg+xml" style="width:$width_string; height:$height_string; border: 1px solid #CCC;">
//    <p>No SVG support</p>
//</object>
//EOHTML;

    $retval .= '<div id="ctry_report_legend_ctr" style="position:relative;">';
    $retval .= '<p id="toggle-key">' . _('Show graph key') . '</p>';
    $retval .= '<dl id="ctry_report_legend">';
    $retval .= '<dt class="key-bau"><span></span>' . _('Baseline Emissions') . '</dt>';
    $retval .= '<dd>' . _('GHG emissions baselines (these are <strong>*not*</strong> business-as-usual pathways) are calculated as counter-factual ' . $glossary->getLink('gloss_bau', false, _('non-policy baselines')) . '. The method applies recent improvements of carbon intensity to GDP forecast estimates. GDP estimates based on national data from the IMF&#39;s Worls Economic Outlook (WEO)for the next 5 years and then on regional data from IPCC (Fifth Assessment Report) through 2030. See <a href="http://' . $main_domain_host . '/calculator-information/gdp-and-emissions-baselines/">Definition, sourcing, and updating of the emissions baselines</a> for details.') . '</dd>';

    $retval .= '<dt class="key-gdrs"><span></span>' . _('"Fair share" allocation') . '</dt>';
    $retval .= '<dd>' . sprintf(_('National allocation trajectory, as calculated for %s using the specified pathways and parameters.
        The mitigation implied by this allocation can be either domestic or international &#8211; The Climate Equity Reference Project effort-sharing framework says nothing about how or where it occurs.'), $country_name) . '</dd>';

    if ($iso3 != $world_code) {
        $retval .= '<dt class="key-phys"><span></span>' . _('Domestic emissions') . '</dt>';
        $retval .= '<dd>' . sprintf(_('An example domestic emissions pathway for %s, one that’s consistent with the selected parameters. '), $country_name);
        $retval .= sprintf(_('This pathway does not describe the national fair share. Rather it is shown as a guide to thought. In this example, domestic emissions (the dotted green line) decline (relative to national BAU) at the same rate that global emissions decline below the global BAU.  In the real world, a national domestic emissions trajectory will depend on the cost of domestic mitigation relative to the cost of mitigation in other countries, and on its chosen participation in international mechanisms for providing or receiving financial and technological support for mitigation.'), $country_name) . '</dd>';

        $retval .= '<dt class="key-dom"><span></span>';
        if ($fund_others) {
            $retval .= _('Domestically-funded mitigation') . '</dt>'; // if we decide to make a distinction, this one would be Domestic mitigation, with its own definition
            $retval .= '<dd>'. sprintf(_('Mitigation funded by %s and carried out within its own borders. The fraction of a country\'s mitigation fair share that is discharged domestically is not specified by the CERP effort-sharing framework, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.'), $country_name) . '</dd>';
        } else {
            $retval .= _('Domestically-funded mitigation') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Mitigation funded by %s and carried out within its own borders. The fraction of a country\'s mitigation fair share that is discharged domestically is not specified by the CERP effort-sharing framework, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.'), $country_name) . '</dd>';
        }

        if ($fund_others) {
            $retval .= '<dt class="key-intl"><span></span>' . _('Mitigation funded in other countries') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Mitigation funded by %s and carried out within other countries. The fraction of a country\'s mitigation fair share that is discharged in other countries is not specified by the CERP effort-sharing framework, but is rather a result of the international cost and mitigation sharing arrangements that it chooses to participate in.'), $country_name) . '</dd>';
            } else {
            $retval .= '<dt class="key-sup"><span></span>' . _('Mitigation funded by other countries') . '</dt>';
            $retval .= '<dd>' . sprintf(_('Mitigation funded other countries, but carried out within the borders of %s. The CERP effort-sharing framework currently assigns the "credit" for this mitigation to the funder, but of course the terms of the mitigation would be as negotiated with the host country.'), $country_name) . '</dd>';
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
    $retval .= '<p style="position:absolute; left:125px; top:1px">';
    $fw = new Framework::$frameworks['gdrs']['class'];
    $query_string = $fw->get_params_as_query($dbfile) . '&dataversion=' . $fw->get_data_ver() . '&iso3=' . $iso3;
    unset($fw);
    $retval .= '<a href="' . $URL_calc . '?' . $query_string . '">Shareable Link to this view</a>';
    if ((Framework::is_dev()) || (Framework::user_is_developer())) { $retval .= '&nbsp;&nbsp;&nbsp;<a href="' . $URL_calc_dev . '?' . $query_string . '">Link (dev version)</a>'; }
    $retval .= '</p>';
    $retval .= '</div>';
    /*
     * Main table
     */
    if ($num_pledges > 0) {
        $caption = _("Fair shares and pledges");
    } else {
        $caption = _("Fair shares");
    }
$retval .= <<< EOHTML
<br />
<table cellspacing="2" cellpadding="2">
<caption>$caption</caption>
    <tbody>
EOHTML;

    // BAU emissions
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%1$s baseline emissions, projected to %2$d'), $country_name, $year) . "</td>";
    $val = $bau[$year];
    $retval .= '<td class="cj">&nbsp;</td>';
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    if (Framework::is_dev() || Framework::user_is_developer()) {
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">" . sprintf(_('%1$s fossil CO<sub>2</sub> emissions, projected to %2$d'), $country_name, $year) . "</td>";
        $val = $bau_data['fossil_CO2_MtCO2'][$year];
        $retval .= '<td class="cj">&nbsp;</td>';
        $retval .= "<td>" . nice_number('', $val, '') . ' Mt CO<sub>2</sub></td>';
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">" . sprintf(_('%1$s LULUCF emissions, projected to %2$d'), $country_name, $year) . "</td>";
        $val = $bau_data['LULUCF_MtCO2'][$year];
        $retval .= '<td class="cj">&nbsp;</td>';
        $retval .= "<td>" . nice_number('', $val, '') . ' Mt CO<sub>2</sub></td>';
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">" . sprintf(_('%1$s non-CO<sub>2</sub> emissions, projected to %2$d'), $country_name, $year) . "</td>";
        $val = $bau_data['NonCO2_MtCO2e'][$year];
        $retval .= '<td class="cj">&nbsp;</td>';
        $retval .= "<td>" . nice_number('', $val, '') . ' Mt CO<sub>2</sub>e</td>';
        $retval .= "</tr>";
        $retval .= "<tr>";
        $retval .= "<td class=\"lj level2\">" . sprintf(_('%1$s population, in %2$d'), $country_name, $year) . "</td>";
        $val = $pop[$year]*1000000;
        $retval .= '<td class="cj">&nbsp;</td>';
        $retval .= "<td>" . nice_number('', $val, '') . '</td>';
        $retval .= "</tr>";

    }

    // year Global mitigation fair share as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_("Global mitigation requirement below global baseline, projected to %d"), $year) . "</td>";
    $retval .= '<td class="cj">(A)</td>';
    $val = $world_bau - $world_tot["gdrs_alloc"];
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // Share of global RCI in year
    $retval .= "<tr>";
    if ($shared_params['use_mit_lag']['value']) {
        $retval .= "<td class=\"lj\">" . sprintf(_('%1$s share of global Responsibility Capacity Index in %2$s to %3$d period'), $country_name, strftime("%Y"), $year) . "</td>";
    } else {
        $retval .= "<td class=\"lj\">" . sprintf(_('%1$s share of global Responsibility Capacity Index, projected to %2$d'), $country_name, $year) . "</td>";
    }
    $retval .= '<td class="cj">(B)</td>';
    $val = 100.0 * $ctry_val[$year]["gdrs_rci"];
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // National mitigation fair share (group)
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%1$s mitigation fair share, projected to %2$d'), $country_name, $year) . "</td>";
    $retval .= '<td class="cj">(A &#215; B)</td>';
    $retval .= "<td>&nbsp;</td>";
    $retval .= "</tr>";
    // National mitigation fair share as MtCO2e below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as tonnes below baseline") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = $bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // National mitigation fair share as tCO2e/capita below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as tonnes per capita below baseline") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = ($bau[$year] - $ctry_val[$year]["gdrs_alloc_MtCO2"])/$pop[$year];
    $retval .= "<td>" . nice_number('', $val, '', 1) . ' t' . $gases . "/cap</td>";
    $retval .= "</tr>";
    // National mitigation fair share as % below BAU
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as percent below baseline") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 100 * (1 - $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[$year]);
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // Financial expression (formerly known as "climate tax")
    // $climate_tax_link = '<a href="#tax-table">' . _('climate tax') . '</a>';
    $retval .= '<tr>';
    $retval .= "<td class=\"lj\">Averagge per capita fair share of global costs, expressed in financial terms</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $retval .= '<td class="cj">&nbsp;</td>';
    $retval .= '</tr>';
    // 1. Mitigation
    $retval .= '<tr>';
    $retval .= "<td class=\"lj level2\">" . sprintf(_('Mitigation costs (assuming %1$s = %2$s%% of GWP)'), $glossary->getLink('mit_cost', false, _('incremental global mitigation costs')), nice_number('', $shared_params['percent_gwp_MITIGATION']['value'], '')) . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 1000 * $world_tot['gdp_mer'] * 0.01 * $shared_params['percent_gwp_MITIGATION']['value'] * $ctry_val[$year]["gdrs_rci"]/$ctry_val[$year]['pop_mln'];
    $retval .= "<td>" . nice_number('$', $val, '') . "</td>";
    $retval .= "</tr>";
    // 2. Adaptation
    $retval .= '<tr>';
    $retval .= "<td class=\"lj level2\">" . sprintf(_('Adaptation cost (assuming %1$s = %2$s%% of GWP)'), $glossary->getLink('adapt_cost', false, _('global adaptation costs')), nice_number('', $shared_params['percent_gwp_ADAPTATION']['value'], '')) . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 1000 * $world_tot['gdp_mer'] * 0.01 * $shared_params['percent_gwp_ADAPTATION']['value'] * $ctry_val[$year]["gdrs_rci"]/$ctry_val[$year]['pop_mln'];
    $retval .= "<td>" . nice_number('$', $val, '') . "</td>";
    $retval .= "</tr>";
    // Blank line
    $retval .= "<tr class=\"blank\"><td colspan=\"3\">&nbsp;</td></tr>";
    // Emissions in Baseyear
    $retval .= "<tr>";
    $retval .= "<td class=\"lj\">" . sprintf(_('%1s %2$d emissions'), $country_name, $display_params['reference_yr']['value']) . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = $bau[intval($display_params['reference_yr']['value'])];
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
    $retval .= "<td class=\"lj level2\">" . _("as tonnes") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = $ctry_val[$year]["gdrs_alloc_MtCO2"];
    $retval .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
    $retval .= "</tr>";
    // GDRs allocation as tCO2e/capita
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . _("as tonnes per capita") . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = $ctry_val[$year]["gdrs_alloc_MtCO2"]/$pop[$year];
    $retval .= "<td>" . nice_number('', $val, '', 1) . ' t' . $gases . "/cap</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent of baseyear emissions
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . sprintf(_('as percent of %1$d emissions'), $display_params['reference_yr']['value']) . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $val = 100.0 * $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[intval($display_params['reference_yr']['value'])];
    $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
    $retval .= "</tr>";
    // GDRs 2020 allocation as percent reduction of baseyear emissions
    $val = 100.0 * (1 - $ctry_val[$year]["gdrs_alloc_MtCO2"]/$bau[intval($display_params['reference_yr']['value'])]);
    if ($val >= 0) {
        $string = sprintf(_('as percent below %1$d emissions'), $display_params['reference_yr']['value']);
    } else {
        $string = sprintf(_('as percent above %1$d emissions'), $display_params['reference_yr']['value']);
    }
    $retval .= "<tr>";
    $retval .= "<td class=\"lj level2\">" . $string . "</td>";
    $retval .= '<td class="cj">&nbsp;</td>';
    $retval .= "<td>" . nice_number('', abs($val), '%') . "</td>";
    $retval .= "</tr>";
    // Blank line
    $retval .= "<tr class=\"blank\"><td colspan=\"3\">&nbsp;</td></tr>";
    if (Framework::is_dev()) {
        $scorecard_url = $URL_sc_dev;
    } else {
        $scorecard_url = $URL_sc;
    }

    // Pledges
//    $free_rider_adj = get_kyoto_commitment($db, $iso3);
//    if (is_null($free_rider_adj)) {
//        $free_rider_adj = 0;
//    } else {
//        $free_rider_adj *= $bau[1990]/$bau[2012];
//        if ($free_rider_adj > 1) {
//            $free_rider_adj = 0;
//        } else {
//            $free_rider_adj = 1 - $free_rider_adj;
//        }
//    }


    $condl_term = array('conditional' => _('conditional'), 'unconditional' => _('unconditional'));
    $condl_code['unconditional'] = '0';
    $condl_code['conditional'] = '1';
    $pledge_table_output = array();
    foreach (array('unconditional', 'conditional') as $condl) {
        $pledges = $dom_pledges[$condl];
        foreach ($pledges as $pledge_year => $pledge_info) {
            $mit_oblig = $bau[$pledge_year] - $ctry_val[$pledge_year]["gdrs_alloc_MtCO2"];
            $conditionality = isset($pledge_info['conditionality_override']) ? $pledge_info['conditionality_override'] : $condl;
            $common_str = sprintf(_('%1$s %2$s pledge%3$s: %4$s by %5$d %6$s'),
                    $country_name,
                    $condl_term[$conditionality],
                    (isset($pledge_info['pledge_qualifier']) ? $pledge_info['pledge_qualifier'] : ""),
                    (isset($pledge_info['description']) ? $pledge_info['description'] : ""),
                    $pledge_year,
                    (isset($pledge_info['helptext']) ? $pledge_info['helptext'] : ""));
            $ouput_idx = $pledge_year * 10 + (($conditionality == "unconditional") ? 5 : 0); // need to use $condl here so conditionality override doesn't overwrite output
            while ((isset($pledge_table_output[$ouput_idx])) && (strlen($pledge_table_output[$ouput_idx])>0)) { $ouput_idx = $ouput_idx - 1; }

            $pledge_table_output[$ouput_idx] = '<tr><td class="lj" colspan="3">' . $common_str . '</td></tr>';
            if (Framework::is_dev() || Framework::user_is_developer()) {
                // pledge target breakdown in Mt
//                $pledge_table_output[$ouput_idx] .= "<tr>";
//                $pledge_table_output[$ouput_idx] .= "<td class=\"lj level2\">... target emissions for fossil CO2</td>";
//                $pledge_table_output[$ouput_idx] .= '<td class="cj">&nbsp;</td>';
//                $val = $pledge_info['pledge'];
//                $pledge_table_output[$ouput_idx] .= "<td>" . nice_number('', $val, '') . ' Mt</td>';
//                $pledge_table_output[$ouput_idx] .= "</tr>";

            }
            // Total
            $pledge_table_output[$ouput_idx] .= "<tr>";
            $pledge_table_output[$ouput_idx] .= "<td class=\"lj level2\">in tonnes below baseline</td>";
            $pledge_table_output[$ouput_idx] .= '<td class="cj">&nbsp;</td>';
            $val = $pledge_info['pledge'];
            $pledge_table_output[$ouput_idx] .= "<td>" . nice_number('', $val, '') . ' Mt' . $gases . "</td>";
            $pledge_table_output[$ouput_idx] .= "</tr>";
            // Per capita
            $pledge_table_output[$ouput_idx] .= "<tr>";
            $pledge_table_output[$ouput_idx] .= "<td class=\"lj level2\">in tonnes per capita below baseline</td>";
            $pledge_table_output[$ouput_idx] .= '<td class="cj">&nbsp;</td>';
            $val = $pledge_info['pledge']/$pop[$pledge_year];
            $pledge_table_output[$ouput_idx] .= "<td>" . nice_number('', $val, '', 1) . ' t' . $gases . "/cap</td>";
            $pledge_table_output[$ouput_idx] .= "</tr>";
            // % below BAU
            $pledge_table_output[$ouput_idx] .= "<tr>";
            $pledge_table_output[$ouput_idx] .= "<td class=\"lj level2\">" . _('as percent below baseline') . "</td>";
            $pledge_table_output[$ouput_idx] .= '<td class="cj">&nbsp;</td>';
            $val = 100 * $pledge_info['pledge']/$bau[$pledge_year];
            $pledge_table_output[$ouput_idx] .= "<td>" . nice_number('', $val, '%') . "</td>";
            $pledge_table_output[$ouput_idx] .= "</tr>";
            // Mitigation shortfall/exceedance
            $val = ($pledge_info['pledge'] - $mit_oblig)/$pop[$pledge_year];
            if ($val <0) {
                $pledge_table_output[$ouput_idx] .= "<tr>";
                $pledge_table_output[$ouput_idx] .= "<td class=\"lj level2\">Amount by which this pledge falls short of mitigation fair share</td>";
                $pledge_table_output[$ouput_idx] .= '<td class="cj">&nbsp;</td>';
                $pledge_table_output[$ouput_idx] .= "<td>" . nice_number('<span class="num_negative">', abs($val), '</span>', 1) . ' t' . $gases . '/cap' . "</td>";
                $pledge_table_output[$ouput_idx] .= "</tr>";
            } else {
                $pledge_table_output[$ouput_idx] .= "<tr>";
                $pledge_table_output[$ouput_idx] .= "<td class=\"lj level2\">Amount by which this pledge exceeds the mitigation fair share</td>";
                $pledge_table_output[$ouput_idx] .= '<td class="cj">&nbsp;</td>';
                $pledge_table_output[$ouput_idx] .= "<td>" . nice_number('<span class="num_pos_green">', $val, '</span>', 1) . ' t' . $gases . '/cap' . "</td>";
                $pledge_table_output[$ouput_idx] .= "</tr>";
            }
        }
    }
    krsort($pledge_table_output);
    foreach ($pledge_table_output as $value) {
        $retval .= $value;
    }

    // Close the table
    $retval .= '</tbody></table>';
    $retval .= '<br />';


    // Tax table - only display it when explicitly wanted
    if (isset($_REQUEST['show_tax_tables'])) {
        // CH: even though the varibale name is $cost_of_mitigation, I think it
        // calculates the total of mitigation and adaptation cost
        $cost_of_mitigation = 0.01 * $perc_gwp * $world_tot['gdp_mer']/($world_bau - $world_tot['gdrs_alloc']);

        $retval .= <<< EOHTML
        <a name="tax-table"></a>
        <br />
        <table cellspacing="2" cellpadding="2">
          <caption>Tax table (illustrative, assuming global mitigation and adaptation costs as currently specified)</caption>
          <thead>
EOHTML;
        $retval .= <<< EOHTML
            <tr>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th>&nbsp;</th>
                <th style="border: none;">&nbsp;</th>
                <th colspan="2" style="text-align:center">Annual per-capita fair share</th>
            </tr>
            <tr>
                <th>Income level<br/>(2010 \$US MER/cap)</th>
                <th>Income level<br/>(2005 \$US PPP/cap)</th>
                <th class="lj"></th>
                <th>&#8220Tax rate&#8221<br/>(% income)</th>
                <th>Population above<br/>tax level (% pop.)</th>
                <th style="border: none;">&nbsp;</th>
                <th><br/>as 2010 \$US MER/cap</th>
                <th><br/>as t$gases/cap</th>
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
                $retval .= '<td style="border: none;">&nbsp;</th>';
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
        }
// Tax table ENDS

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
//        $retval .= "<td class=\"lj level2\">As share of " . $pledge_year . " mitigation fair share</td>";
//        $val = 100 * $pledge_info['pledge']/($bau[$pledge_year] - $ctry_val[$pledge_year]["gdrs_alloc_MtCO2"]);
//        $retval .= "<td>" . nice_number('', $val, '%') . "</td>";
//        $retval .= "</tr>";
//    }
    // Close the table
return $retval;
}
