<?php
include("api_common.php");

$database = 'sqlite:'.$user_db;

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
