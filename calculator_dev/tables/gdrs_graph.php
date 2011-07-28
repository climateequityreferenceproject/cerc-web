<?php
// region is currently ignored--just returns global total
function gdrs_graph($dbfile, $region) {
    include("graphs/graph_core.php");
    include("table_common.php");
    
    $database = 'sqlite:'.$dbfile;

    $db = new PDO($database) OR die("<p>Can't open database</p>");
    
    // Start with the core SQL view
    $db->exec($viewquery);
    
    // Get the filter for the emergency pathway
    $query = <<< EOSQL
    CREATE TEMPORARY VIEW __Source_Filter AS SELECT
        SUM(CASE WHEN param_id = "use_lulucf" THEN int_val ELSE 0 END) AS use_lulucf,
        SUM(CASE WHEN param_id = "use_nonco2" THEN int_val ELSE 0 END) AS use_nonco2
    FROM params;
EOSQL;

    $db->exec($query);
    
    $query = <<< EOSQL
    CREATE TEMPORARY VIEW paths AS SELECT
        disp_temp.iso3, disp_temp.year as year, baseline,
        baseline - vol_rdxn_MtCO2 AS vol_rdxn,
        baseline - vol_rdxn_MtCO2 - a1_dom_rdxn_MtCO2 AS dom_rdxn,
        gdrs_alloc_MtCO2 AS gdrs_alloc
    FROM disp_temp, (SELECT iso3, year, fossil_CO2_MtCO2 + use_lulucf * LULUCF_MtCO2 +
            use_nonco2 * NonCO2_MtCO2e AS baseline FROM disp_temp, __Source_Filter) AS baseline
    WHERE disp_temp.year >= 1990 AND disp_temp.year <= 2030
        AND baseline.year = disp_temp.year AND baseline.iso3 = disp_temp.iso3;
EOSQL;

    $db->exec($query);
    
    $query = <<< EOSQL
        SELECT year, SUM(gdrs_alloc) AS gdrs_alloc, SUM(dom_rdxn) AS dom_rdxn,
            SUM(vol_rdxn) AS vol_rdxn, SUM(baseline) AS baseline
        FROM paths
        GROUP BY year;
EOSQL;

    $alloc = array();
    $dom_rdxn = array();
    $vol_rdn = array();
    $baseline = array();
    
    $min = 0;
    $max = 0;
    foreach ($db->query($query) as $record) {
        $year = $record['year'];
        $alloc[$year] = $record['gdrs_alloc'];
        $dom_rdxn[$year] = $record['dom_rdxn'];
        $vol_rdxn[$year] = $record['vol_rdxn'];
        $baseline[$year] = $record['baseline'];
        
        $min = min($min, $alloc[$year]);
        $min = min($min, $dom_rdxn[$year]);
        $min = min($min, $vol_rdxn[$year]);
        $min = min($min, $baselin[$year]);
        
        $max = max($max, $alloc[$year]);
        $max = max($max, $dom_rdxn[$year]);
        $max = max($max, $vol_rdxn[$year]);
        $max = max($max, $baselin[$year]);
    }
    
    $graph = new Graph(600, 400);
    $graph->set_xaxis(1990, 2030, "years", "");
    $graph->set_yaxis($min, $max, "Emissions", "MtC");
    $graph->add_series($alloc);
    $graph->add_series($dom_rdxn);
    $graph->add_series($vol_rdxn);
    $graph->add_series($baseline);
    return $graph->svgplot_wedges(array("#900", "#090", "#009"));
}