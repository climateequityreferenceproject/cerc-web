<?php
include("api_common.php");
// getting rid of legacy GDRs stuff in field names, unless API version 1.0 is specified
if (!(filter_input(INPUT_POST, 'v', FILTER_SANITIZE_URL)=="1.0")) {
    global $excel_download_header_rename;
    foreach ($excel_download_header_rename as $old=>$new ) {
        $viewquery = str_replace($old, $new, $viewquery);
    }
}

$database = 'sqlite:'.$user_db;

$db = new PDO($database) OR die("<p>Can't open database</p>");
// Start with the core SQL view
$db->query($viewquery);

$data_array = array();

$query = $db->query("SELECT * FROM disp_temp WHERE " . $yearquery . " AND " . $countryquery . " ORDER BY name;");
if ($record = $query->fetch(PDO::FETCH_ASSOC)) {
    $data_array[] = $record;
    do {
        $data_array[] = $record;
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
$global_sql = $region_sql . " FROM disp_temp WHERE " . $yearquery . " GROUP BY year;";
$region_sql .= " FROM disp_temp, flags WHERE flags.iso3 = disp_temp.code AND ";
$region_sql .= "flags.value = 1 AND flags.flag = ? AND " . $yearquery . " GROUP BY year;";

// Global
$world_code = Framework::get_world_code();
$world_name = Framework::get_world_name();
if (in_array($world_code, $countries)) {
    $row_start = array('code' => $world_code, 'name' => $world_name);
    foreach ($db->query($global_sql, PDO::FETCH_ASSOC) as $record) {
        $data_array[] = array_merge($row_start, $record);
    }
}

// Regional
$region_query = $db->prepare($region_sql);
foreach ($db->query('SELECT * FROM flag_names') as $flags) {
    $longname = $flags["long_name"];
    $shortname = $flags["flag"];
    if (in_array($shortname, $countries)) {
        $row_start = array('code' => $shortname, 'name' => $longname);
        $region_query->execute(array($shortname));
        foreach ($region_query->fetchAll(PDO::FETCH_ASSOC) as $record) {
            $data_array[] = array_merge($row_start, $record);
        }
    }
}
