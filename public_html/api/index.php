<?php
    include("../frameworks/frameworks.php");
    /*** Databases ************************************************************/
    // Create database filename if doesn't already exist
    if ($_POST['db']) {
        $user_db = $_POST['db'];
    } else {
        $user_db = Framework::get_user_db();
    }

    // If just asking for the db name (or to create a db) then that is all this script does
    if ($_POST['get_db'] || $_GET['get_db']) {
        echo $user_db;
        return;
    }

    // Update parameters array with last user values, if any
    function get_usr_vals(&$array) {
        foreach($array as $key => $val) {
            if (isset($_POST[$key])) {
                $array[$key]['value'] = $_POST[$key];
            }
        }
    }
    
    /*** Calculator parameters *************************************************/
    $shared_params = Framework::get_shared_params();
    $fw = new Framework::$frameworks["gdrs"]['class'];
    $fw_params = $fw->get_fw_params();

    
    if (!$_POST['reset']) {
        if (isset($_COOKIE['api_shared_params'])) {
            $shared_params = unserialize(stripslashes($_COOKIE['api_shared_params']));
        }
        get_usr_vals($shared_params);
	
        if (isset($_COOKIE['api_fw_params'])) {
            $fw_params = unserialize(stripslashes($_COOKIE['api_fw_params']));	
        }
        get_usr_vals($fw_params);
    }
    setcookie('api_shared_params',serialize($shared_params),time()+60*60*24*365);
    setcookie('api_fw_params',serialize($fw_params),time()+60*60*24*365);

    $fw->calculate($user_db, $shared_params, $fw_params);

    /*** Cleanup ************************************************************/
    // Just to be sure, explicitly delete the object
    unset($fw);
    
    ####### TEST
    print_r($shared_params);
    echo "<br><br>";
    print_r($fw_params);
    