<?php
    // -------------------------------------------------------------------
    //
    // Initialize Framework class-level static variables
    //
    // -------------------------------------------------------------------
    // Point to master database
    Framework::$master_db = "/***REMOVED***/databases/gdrs_core.sql3";
    
    // Point to folder where use databases are stored
    Framework::$user_db_path = "/***REMOVED***/sessions/gdrs-db";
    
    
    // -------------------------------------------------------------------
    //
    // Read in all framework files
    //
    // -------------------------------------------------------------------
    $fw_file = "framework.php";
    $dhandle = opendir('frameworks');
    if ($dhandle) {
        while (($dirname = readdir($dhandle)) !== false) {
            if (is_dir('frameworks/' . $dirname ) && $dirname != '.' && $dirname != '..') {
                $fname = "frameworks/$dirname/$fw_file";
                if (file_exists($fname)) {
                    include($fname);
                }
            }
        }
    }
    
    // -------------------------------------------------------------------
    //
    // Initialize individual framework-specific static variables
    //
    // -------------------------------------------------------------------
    // For GDRs, set path to the calculator
    GreenhouseDevRights::$exec_path = "/***REMOVED***/gdrscode/gdrsengine-dev/bin/gdrsengine";
