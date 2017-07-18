<?php
    // -------------------------------------------------------------------
    //
    // Initialize Framework class-level static variables
    //
    // -------------------------------------------------------------------

    // The config file has likely been require'd before, but just in case
    require_once 'config.php';
    // Point to master database
    if (Framework::is_dev()) {
        Framework::$master_db = $core_db_dev;
    } else {
        Framework::$master_db = $core_db;
    }
    
    // Point to folder where user databases are stored
    Framework::$user_db_path = $user_db_store;
    
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
    if (Framework::is_dev()) {
        GreenhouseDevRights::$exec_path = $calc_engine_path_dev;
    } else {
        GreenhouseDevRights::$exec_path = $calc_engine_path;
    }
    GreenhouseDevRights::$param_log = $param_log_file_name;
