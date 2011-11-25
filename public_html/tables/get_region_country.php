<?php

$database = 'sqlite:' . $_POST["user_db"];

$db = new PDO($database) OR die("<p>Can't open database</p>");

$allRegion[] = array(
    'name_S' => 'world',
    'name_L' => 'World'
);
foreach ($db->query('SELECT * FROM flag_names') as $flag) {
    $regionName = $flag['flag'];
    $regionLongName = $flag['long_name'];
    $allRegion[] = array(
        'name_S' => $regionName,
        'name_L' => $regionLongName
    );
    $queryGetRegion = <<< EOSQL
    SELECT country.iso3, country.name
    FROM country
    INNER JOIN flags ON country . iso3 = flags . iso3
    WHERE flag='$regionName' AND value=1 ORDER BY country.name
EOSQL;
    $countrys = '';
    foreach ($db->query($queryGetRegion) as $country) {
        $countrys[] = $country['iso3'];
    }
    $region[$regionName] = array(
        'country' => $countrys
    );
}


foreach ($db->query('SELECT * FROM country ORDER BY name') as $country) {
    $allCountry[] = array(
        'iso3' => $country['iso3'],
        'name' => $country['name']
    );
}
$regionCountryData['allRegion'] = $allRegion;
$regionCountryData['allCountry'] = $allCountry;
$regionCountryData['regionCountry'] = $region;

echo json_encode($regionCountryData);
?>
