<?php
$configfile = dirname(__FILE__). "/../../../config.php";
if (is_readable($configfile)) { include($configfile); } else { die("Cannot read config.php file. If this is a new installation, locate the config.php.new file, enter the required information, and rename if config.php."); }
try {
    $pdo = new PDO('mysql:host='.$help_db_config['host'].';dbname='.$help_db_config['dbname'], $help_db_config['user'], $help_db_config['pwd']);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET NAMES "utf8"');
} catch (PDOException $e) {
    $error = 'Unable to connect to the database server.<br>';
    $error .= $e->getMessage();
    include 'error.html.php';
    exit();
}
