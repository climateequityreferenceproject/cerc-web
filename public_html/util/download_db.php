<?php
require_once("../frameworks/frameworks.php");

$file_path = Framework::add_user_db_path($_REQUEST['db']);

if ($file_path) {
    $path_parts = pathinfo($file_path);
    $file_name  = $path_parts['basename'];
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-type: application/octet-stream");
    header("Content-Length: " . filesize($file_path)); 
    header("Content-Disposition: attachment; filename=\"" . $file_name . ".sqlite3\"" );
    header("Content-Description: PHP/INTERBASE Generated Data" );
    readfile($file_path);
}

?>