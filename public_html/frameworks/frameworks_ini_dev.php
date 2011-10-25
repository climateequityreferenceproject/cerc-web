<?php
    // -------------------------------------------------------------------
    //
    // Initialize Framework class-level static variables
    //
    // -------------------------------------------------------------------
    // Point to master database
    Framework::$master_db = "/***REMOVED***/databases/gdrs_core.sql3";
    
    // Point to folder where user databases are stored
    Framework::$user_db_path = "/***REMOVED***/sessions/gdrs-db";
    
    
    // -------------------------------------------------------------------
    //
    // Read in all framework files
    //
    // -------------------------------------------------------------------
    $fw_file = "framework.php";
    $scriptdir = dirname(__FILE__);
    $currdir = getcwd();
    if (chdir($scriptdir)) {
        $dhandle = opendir($scriptdir);
        if ($dhandle) {
            while (($dirname = readdir($dhandle)) !== false) {
                if (is_dir($dirname) && $dirname != '.' && $dirname != '..') {
                    $fname = "$dirname/$fw_file";
                    if (file_exists($fname)) {
                        include($fname);
                    }
                }
            }
        }
    }
    chdir($currdir);
    
    // -------------------------------------------------------------------
    //
    // Initialize individual framework-specific static variables
    //
    // -------------------------------------------------------------------
    // For GDRs, set path to the calculator
    GreenhouseDevRights::$exec_path = "/***REMOVED***/gdrscode/gdrsengine-dev/bin/gdrsengine";
