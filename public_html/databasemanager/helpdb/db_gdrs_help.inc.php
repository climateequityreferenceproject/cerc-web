<?php
try
{
    if (strpos(dirname(__FILE__), "gd/gdrights.org")) {
        // pre-move calculator 
        $pdo = new PDO('mysql:host=localhost;dbname=calcsctext', 'calcsctext', '***REMOVED***');
    } else {
        // post-move calculator
        $pdo = new PDO('mysql:host=localhost;dbname=help_db', 'help_db', '***REMOVED***');
    }
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('SET NAMES "utf8"');
}
catch (PDOException $e)
{
    $error = 'Unable to connect to the database server.';
    include 'error.html.php';
    exit();
}
