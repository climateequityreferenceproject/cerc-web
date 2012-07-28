<?php

function get_common_table_query($dbfile = null) {
    $CtoCO2 = 11.0/3.0;
    
    $tax_string = '';
    if ($dbfile) {
        $tax_string = get_tax_string($dbfile);
    }

$viewquery = <<< EOSQL
    CREATE TEMPORARY VIEW disp_temp AS
        SELECT country.iso3 AS iso3, country.name AS country,
            core.year AS year, 1e-6 * pop_person AS pop_mln, gdp_blnUSDMER,
            ppp2mer * gdp_blnUSDMER AS gdp_blnUSDPPP,
            $CtoCO2 * fossil_CO2_MtC AS fossil_CO2_MtCO2,
            $CtoCO2 * LULCF_MtC AS LULUCF_MtCO2,
            $CtoCO2 * NonCO2_MtCe AS NonCO2_MtCO2e,
            $CtoCO2 * vol_rdxn_MtC as vol_rdxn_MtCO2,
            $CtoCO2 * gdrs.allocation_MtC AS gdrs_alloc_MtCO2,
            $CtoCO2 * gdrs.r_MtC AS gdrs_r_MtCO2,
            $CtoCO2 * a1_dom_rdxn_MtC AS a1_dom_rdxn_MtCO2,
            -1e-3 * net_export_ktCO2 as net_import_MtCO2,
            gdrs.c_blnUSDMER AS gdrs_c_blnUSDMER,
            gdrs.rci AS gdrs_rci,
            1e-6 * gdrs.pop_above_dl AS gdrs_pop_mln_above_dl,
            1e-6 * gdrs.pop_above_lux AS gdrs_pop_mln_above_lux,
            $CtoCO2 * gdrs.lux_emiss_MtC AS lux_emiss_MtCO2,
            $CtoCO2 * gdrs.lux_emiss_applied_MtC AS lux_emiss_applied_MtCO2
            $tax_string
        FROM country, core, gdrs
        WHERE country.iso3 = core.iso3 AND country.iso3 = gdrs.iso3
            AND gdrs.year = core.year AND gdrs.allocation_MtC IS NOT NULL;
EOSQL;

    return $viewquery;
}

function get_tax_string($dbfile, $from_gdrs = TRUE) {
    $database = 'sqlite:'.$dbfile;
    $db = new PDO($database) OR die("<p>Can't open database '" . $dbfile . "'</p>");
    $tax_string = '';
    foreach ($db->query("SELECT seq_no FROM tax_levels;") as $record) {
        if ($from_gdrs) {
            $tax_string .= sprintf(', 1e-6 * tax_pop_below_%1$d AS tax_pop_mln_below_%1$d', $record['seq_no']);
        } else {
            $tax_string .= sprintf(', tax_pop_mln_below_%d', $record['seq_no']);
        }
        $tax_string .= sprintf(', tax_income_mer_dens_%d', $record['seq_no']);
        $tax_string .= sprintf(', tax_income_ppp_dens_%d', $record['seq_no']);
        $tax_string .= sprintf(', tax_revenue_mer_dens_%d', $record['seq_no']);
        $tax_string .= sprintf(', tax_revenue_ppp_dens_%d', $record['seq_no']);
        $tax_string .= sprintf(', tax_pop_dens_%d', $record['seq_no']);
    }
    return $tax_string;
}
