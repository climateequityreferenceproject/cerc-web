<?php
include("api_common.php");

$database = 'sqlite:'.$_GET["db"];

$db = new PDO($database) OR die("<p>Can't open database</p>");
// Start with the core SQL view
$db->query($viewquery);

$dlfile = "gdrs_all_output.xls";
$tsfile = tempnam("/***REMOVED***/sessions/gdrs-db", "gdrs-tabsep-");

$fp = fopen($tsfile, "w");
if (!is_resource($fp))
{
    die("Cannot open $tsfile");
}

// Meta-data
fwrite($fp, "Greenhouse Development Rights Online Calculator (http://" . $_SERVER['HTTP_HOST'] . ")\n");
$record = $db->query("SELECT modified FROM meta")->fetchAll();
fwrite($fp, "Last modified " . $record[0][0] . "\n");
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
fwrite($fp, "Thresholds and share of income counted toward capacity above threshold:\n");
foreach ($db->query("SELECT * FROM thresholds") as $record) {
    fwrite($fp, $record["income"] . "\t" . 100.0 * $record["rate"] . "%\n");
}

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