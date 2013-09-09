<?php
if (isset($_GET['debug']) && $_GET['debug'] == 'yes') {
    ini_set('display_errors',1); 
    error_reporting(E_ALL);
}
require_once("../frameworks/frameworks.php");
require_once("table_common.php");

if (isset($_GET['allyears']) && $_GET['allyears'] == 'yes') {
    $all_years_condition_string = "";
} else {
    $all_years_condition_string = "AND combined.gdrs_alloc_MtCO2 IS NOT NULL";
}
$tax_string_gdrs = get_tax_string($_GET["db"]);
$tax_string_combined = get_tax_string($_GET["db"], FALSE);
$viewquery = <<< EOSQL
    CREATE TEMPORARY VIEW disp_temp AS
        SELECT combined.iso3 AS iso3, country.name AS country, year, pop_mln,
            gdp_blnUSDMER, gdp_blnUSDPPP, fossil_CO2_MtCO2, LULUCF_MtCO2, NonCO2_MtCO2e,
            vol_rdxn_MtCO2, gdrs_alloc_MtCO2, gdrs_r_MtCO2,
            a1_dom_rdxn_MtCO2, net_import_MtCO2, gdrs_c_blnUSDMER, gdrs_rci,
            gdrs_pop_mln_above_dl, gdrs_pop_mln_above_lux, lux_emiss_MtCO2,
            lux_emiss_applied_MtCO2, kyoto_gap_MtCO2
            $tax_string_combined
            FROM country,
            (SELECT core.iso3 AS iso3, core.year AS year,
            1e-6 * pop_person AS pop_mln, gdp_blnUSDMER,
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
			(11.0/3.0) * gdrs.lux_emiss_applied_MtC AS lux_emiss_applied_MtCO2,
                        (11.0/3.0) * gdrs.kyoto_gap_MtC AS kyoto_gap_MtCO2
                        $tax_string_gdrs
            FROM core LEFT JOIN gdrs ON core.year = gdrs.year AND core.iso3 = gdrs.iso3)
        AS combined WHERE country.iso3 = combined.iso3 $all_years_condition_string;
EOSQL;

$last_modified = Framework::get_db_time_string();

$database = 'sqlite:'.$_GET["db"];

$db = new PDO($database) OR die("<p>Can't open database</p>");
// Start with the core SQL view
if (!$db->query($viewquery)) {
    print_r($db->errorInfo());
}

$dlfile = "gdrs_all_output_" . time() . ".xls";
$tsfile = tempnam("/***REMOVED***/sessions/gdrs-db", "gdrs-tabsep-");

$fp = fopen($tsfile, "w");
if (!is_resource($fp))
{
    die("Cannot open $tsfile");
}

// Meta-data
fwrite($fp, "Greenhouse Development Rights Online Calculator (http://" . $_SERVER['HTTP_HOST'] . ")\n");
fwrite($fp, "Last modified " . $last_modified['master'] . "\n");
$record = $db->query("SELECT calc_version FROM meta")->fetchAll();
fwrite($fp, "Calculator version " . $record[0][0] . "\n");
$record = $db->query("SELECT data_version FROM meta")->fetchAll();
fwrite($fp, "Data version " . $record[0][0] . "\n");
foreach ($db->query("SELECT param_id, int_val, descr FROM params WHERE int_val IS NOT NULL") as $record) {
    fwrite($fp, $record["param_id"] . "\t" . $record["int_val"] . "\t".  $record["descr"] . "\n");
}
foreach ($db->query("SELECT param_id, real_val, descr FROM params WHERE real_val IS NOT NULL") as $record) {
    fwrite($fp, $record["param_id"] . "\t" . $record["real_val"] . "\t" . $record["descr"] . "\n");
}
fwrite($fp, "Tax table:\n");
fwrite($fp, "\t\"For income at tax, compute tax_income_dens_#/tax_pop_dens_#\"\n");
fwrite($fp, "\t\"For tax rate, compute tax_revenue_dens_#/tax_income_dens_#\"\n");
fwrite($fp, "\t\"For tax per capita, compute tax_revenue_dens_#/tax_pop_dens_#\"\n");
fwrite($fp, "\tSequence number\tLabel\n");
foreach ($db->query("SELECT seq_no, label FROM tax_levels;") as $record) {
    fwrite($fp, "\t" . $record['seq_no']. "\t\"" . $record['label'] . "\"\n");
}

// Country-level data
$query = $db->query("SELECT * FROM disp_temp ORDER BY country;");
if ($record = $query->fetch(PDO::FETCH_ASSOC)) {
    fwrite($fp, implode("\t", array_keys($record)) . "\n");
    do {
        fwrite($fp, implode("\t", $record) . "\n");
    } while ($record = $query->fetch(PDO::FETCH_ASSOC));
}

//
// Regions
//
// First, make query by grabbing all data columns (column 3 onward)
$region_sql = "SELECT year";
foreach (array_slice($db->query("PRAGMA table_info(disp_temp)")->fetchAll(PDO::FETCH_COLUMN, 1), 3) as $col) {
    $region_sql .= ", sum($col) AS $col";
}
$global_sql = $region_sql . " FROM disp_temp GROUP BY year;";
$region_sql .= " FROM disp_temp, flags WHERE flags.iso3 = disp_temp.iso3 AND ";
$region_sql .= "flags.value = 1 AND flags.flag = ? GROUP BY year;";

// Global
$row_start = "\tWorld\t";
foreach ($db->query($global_sql, PDO::FETCH_NUM) as $record) {
    fwrite($fp, $row_start . implode("\t", $record) . "\n");
}

// Regional
$region_query = $db->prepare($region_sql);
foreach ($db->query('SELECT * FROM flag_names') as $flags) {
    $longname = $flags["long_name"];
    $row_start = "\t$longname\t";
    $region_query->execute(array($flags["flag"]));
    foreach ($region_query->fetchAll(PDO::FETCH_NUM) as $record) {
        fwrite($fp, $row_start . implode("\t", $record) . "\n");
    }
}


fclose($fp);

header("Cache-Control: no-cache, must-revalidate");
header("Pragma: no-cache");
header("Content-type: text/tab-separated-values");
header("Content-Length: " . filesize($tsfile)); 
header("Content-Disposition: attachment; filename=\"" . $dlfile . "\"" );
header("Content-Description: PHP/INTERBASE Generated Data" );
readfile($tsfile);
?>