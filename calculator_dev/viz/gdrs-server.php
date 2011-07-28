<?php
require_once 'MC/Google/Visualization.php';
$db = new PDO('sqlite:' . $_GET['db']);
$vis = new MC_Google_Visualization($db, 'sqlite');

$vis->addEntity('gdrs', array(
	'table' => 'gdrs g',
    'fields' => array(
        'name' => array('field' => 'c.name', 'type' => 'text', 'join' => 'country'),
		'year' => array('field' => 'g.year', 'type' => 'number'),
		'gdrs_alloc' => array('field' => '1e6 * (11.0/3.0) * allocation_MtC/core.pop_person', 'type' => 'number', 'join' => 'core'),
		'bau_pc' => array('field' => '1e6 * (11.0/3.0) * core.fossil_CO2_MtC/core.pop_person', 'type' => 'number', 'join' => 'core'),
		'gap' => array('field' => '1e6 * (11.0/3.0) * (core.fossil_CO2_MtC - allocation_MtC)/core.pop_person', 'type' => 'number', 'join' => 'core'),
		'rci' => array('field' => '100 * rci', 'type' => 'number'),
		'bau_foss' => array('field' => 'core.fossil_CO2_MtC', 'type' => 'number', 'join' => 'core'),
		'bau_lulcf' => array('field' => 'core.LULFC_CO2_MtC', 'type' => 'number', 'join' => 'core'),
		'bau_noco2' => array('field' => 'core.NonCO2_MtCe', 'type' => 'number', 'join' => 'core'),
		'pop' => array('field' => 'core.pop_person', 'type' => 'number', 'join' => 'core')
    ),
	'joins' => array(
		'core' => 'INNER JOIN core ON g.iso3=core.iso3 AND g.year=core.year',
		'country' => 'INNER JOIN country c ON g.iso3=c.iso3'
	)
));

$vis->handleRequest();
?>