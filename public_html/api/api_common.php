<?php
    
$viewquery = <<< EOSQL
    CREATE TEMPORARY VIEW disp_temp AS
        SELECT country.iso3 AS code, country.name AS name,
            core.year AS year, 1e-6 * pop_person AS pop_mln, gdp_blnUSDMER,
            ppp2mer * gdp_blnUSDMER AS gdp_blnUSDPPP,
            (11.0/3.0) * fossil_CO2_MtC AS fossil_CO2_MtCO2,
            (11.0/3.0) * LULCF_MtC AS LULUCF_MtCO2,
            (11.0/3.0) * NonCO2_MtCe AS NonCO2_MtCO2e,
            (11.0/3.0) * vol_rdxn_MtC as vol_rdxn_MtCO2,
            (11.0/3.0) * gdrs.allocation_MtC AS gdrs_alloc_MtCO2,
            (11.0/3.0) * gdrs.r_MtC AS gdrs_r_MtCO2,
            (11.0/3.0) * a1_dom_rdxn_MtC AS a1_dom_rdxn_MtCO2,
			-1e-3 * net_export_ktCO2 as net_import_MtCO2,
            gdrs.c_blnUSDMER AS gdrs_c_blnUSDMER,
            gdrs.rci AS gdrs_rci, 1e-6 * gdrs.pop_above_dl AS gdrs_pop_mln_above_dl,
			1e-6 * gdrs.pop_above_lux AS gdrs_pop_mln_above_lux,
			(11.0/3.0) * gdrs.lux_emiss_MtC AS lux_emiss_MtCO2,
			(11.0/3.0) * gdrs.lux_emiss_applied_MtC AS lux_emiss_applied_MtCO2
        FROM country, core, gdrs
        WHERE country.iso3 = core.iso3 AND country.iso3 = gdrs.iso3
            AND gdrs.year = core.year AND gdrs.allocation_MtC IS NOT NULL;
EOSQL;
