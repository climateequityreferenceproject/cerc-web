<?php
include("config.php");

$db = basename($_REQUEST['db']);

$origin = $database_folder = pathinfo($core_db)['dirname'] . "/" . $db;
$destination = $user_db_store . "/" . $db;

if (file_exists($origin)) {
	if (!copy($origin, $destination)) {
    		echo "Failed to copy database '" . $db . "'... (though it does seem to exits... permissions problems perhaps?)";
	} else {
		echo "Seems to have worked. - Copied database '" . $db . "' to the user database folder. <br>\n";
		echo "You should now be able to use it in the calculator with the ?db=" . $db . " URL parameter switch.<br>\n";
		echo "Check the footer of the calculator output for the database version used for that output.";
	}
} else {
	echo "The requested database '" . $db . "' does not seem to exist in the database folder.";
}

?>
