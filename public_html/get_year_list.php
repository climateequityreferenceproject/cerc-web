<?php
    include("frameworks/frameworks.php");
    include("form_functions.php");
    
    $user_db = NULL;
    if ($_POST['user_db']) {
        $user_db = $_POST['user_db'];
    }
    if ($_GET['user_db']) {
        $user_db = $_GET['user_db'];
    }    
    
    $shared_params = Framework::get_shared_params($user_db);
    
    echo select_num('cum_since_yr', $shared_params, "Cumulative since:", $advanced);

