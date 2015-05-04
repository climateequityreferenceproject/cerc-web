<?php
    require_once ('config.php');
    include("frameworks/frameworks.php");
    include("form_functions.php");
    
    $user_db = Framework::get_good_db();
    
    if (!$user_db) {
        $db_array = Framework::dup_master_db('calc', true);
        $master_db = $db_array['db'];
        $tmp_db = Framework::get_user_db($master_db);
        $shared_params = Framework::get_shared_params($tmp_db);
        unlink($tmp_db);
    } else {
        $shared_params = Framework::get_shared_params($user_db);
    }
    
    $advanced = true;
    
    echo select_num('cum_since_yr', $shared_params, "Cumulative since:");

