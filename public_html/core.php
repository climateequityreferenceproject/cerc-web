<?php
    include("frameworks/frameworks.php");
    
    // Check that we're current
    $up_to_date = FALSE;
    $ver_info = array();
    $ver_info['data_ver'] = Framework::get_data_ver();
    $ver_info['calc_ver'] = Framework::get_calc_ver();
    if (isset($_COOKIE['ver'])) {
       $last_ver = unserialize(stripslashes($_COOKIE['ver']));
       if ($last_ver['data_ver'] === $ver_info['data_ver'] && $last_ver['calc_ver'] === $ver_info['calc_ver']) {
           $up_to_date = TRUE;
       }
    }
    setcookie('ver',serialize($ver_info),time()+60*60*24*28);
    
    // Always using GDRs framework now
    $shared_params = Framework::get_shared_params();
    $fw = new Framework::$frameworks['gdrs']['class'];
    
    /*** Databases ************************************************************/
    // Create database filename if doesn't already exist
    $have_db = FALSE;
    if ($_POST['user_db'] && Framework::add_user_db_path($_POST['user_db'])) {
        $user_db = Framework::add_user_db_path($_POST['user_db']);
        $have_db = TRUE;
    } elseif (isset($_COOKIE['db']) && $up_to_date) {
        $user_db = realpath(unserialize(stripslashes($_COOKIE['db'])));
        $have_db = $user_db; // realpath returns FALSE on failure
    }
    
    if (!$have_db) {
        $db_array = Framework::dup_master_db('calc', TRUE);
        $master_db = $db_array['db'];
        if ($db_array['did_create']) {
            // Created a new one, so run it
            $fw->calculate($master_db, $shared_params, $fw->get_fw_params());
        }
        $user_db = Framework::get_user_db($master_db);
    }
    $fw_params = $fw->get_fw_params($user_db);
    setcookie('db',serialize(Framework::get_db_name($user_db)),time()+60*60*24*28);

    // If just asking for the db name (or to create a db) then that is all this script does
    if ($_POST['get_db'] || $_GET['get_db']) {
        echo $user_db;
        return;
    }
    // If not just getting db, then load table_generator and define get_usr_vals
    include("tables/table_generator.php");

    // Function to update parameters array with last user values, if any
    function get_usr_vals(&$array) {
        foreach($array as $key => $val) {
            if (isset($_POST[$key])) {
                $array[$key]['value'] = $_POST[$key];
            }
        }
        // Checkboxes are special - if not checked, they do not exist in $_POST, so check to make sure the form has been submitted
        if (isset($_POST['submit'])) {
            if ($array['use_lulucf'] && !$_POST['use_lulucf']) {
                $array['use_lulucf']['value'] = 0;
            }
            if ($array['use_nonco2'] && !$_POST['use_nonco2']) {
                $array['use_nonco2']['value'] = 0;
            }
            if ($array['use_sequencing'] && !$_POST['use_sequencing']) {
                $array['use_sequencing']['value'] = 0;
            }
            if ($array['do_luxcap'] && !$_POST['do_luxcap']) {
                $array['do_luxcap']['value'] = 0;
            }
            if ($array['interp_btwn_thresh'] && !$_POST['interp_btwn_thresh']) {
                $array['interp_btwn_thresh']['value'] = 0;
            }
        }
    }
    
    // Reload parameters--might be different from defaults
    if (!$_POST['reset']) {
        $shared_params = Framework::get_shared_params($user_db);
        $fw_params = $fw->get_fw_params($user_db);
    }

    /*** Display parameters ****************************************************/

    $basic_adv = array (
        basic => 'Basic',
        adv => 'Advanced');
    
    $advanced = false;
    // TODO: replace country_grp w JS to show/hide rows and columns, or HTML table filter
    $country_list = Framework::get_country_list($user_db);
    $display_params = array ('basic_adv' => array(
                                'value'=>'basic',
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>array(
                                    basic=>array('display_name'=>'Basic'),
                                    adv=>array('display_name'=>'Advanced')
                                )    
                            ),
                             'display_yr' => array(
                                'value'=>2020,
                                'advanced'=>false,
                                'min'=>1990,
                                'max'=>2030,
                                'step'=>1,
                                'list'=>NULL
                            ),
                             'display_ctry' => array(
                                'value'=>NULL,
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>NULL
                            ),
                             'decimal_pl' => array(
                                'value'=>2,
                                'advanced'=>false,
                                'min'=>0,
                                'max'=>6,
                                'step'=>1,
                                'list'=>NULL
                            ),
                             'country_grp' => array(
                                'value'=>'--All--',
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>NULL
                            ),
                             'framework' => array(
                                'value'=>'gdrs',
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>NULL
                            ),
                             'table_view' => array(
                                'value'=> null,
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>NULL
                            )
                        );
    
    if (!$_POST['reset']) {
        if (isset($_COOKIE['display_params']) && $up_to_date) {
            $display_params = unserialize(stripslashes($_COOKIE['display_params']));    
        }
        get_usr_vals($display_params);
    }
    setcookie('display_params',serialize($display_params),time()+60*60*24*365);
    
    /*** Calculator parameters *************************************************/
    // TODO: implement mid_rate - marginal nominal tax rate b/n dev and lux - as slider 0-100
    // TODO: implement tax_income_level - income level for nominal personal tax - as separate script?    

    
    // Redundant but convenient to have both
    $table_views = $fw->get_table_views();
    $display_params['table_view']['list'] = $fw->get_table_views();
    
    // A trick to get the first table defined in the view
    if (isset($_POST['forcesubmit']) || !isset($_POST['submit'])) {
        foreach ($table_views as $key => $val) {
            $display_params['table_view']['value'] = $key;
            break;
        }
        setcookie('display_params',serialize($display_params),time()+60*60*24*365);
    }
    
    if (!$_POST['reset']) {
        get_usr_vals($shared_params);
        get_usr_vals($fw_params);
    }
    
    $fw->calculate($user_db, $shared_params, $fw_params);
    
    // Use the most up-to-date parameter list: years might have changed
    $shared_params = Framework::get_shared_params($user_db);
    $fw_params = $fw->get_fw_params($user_db);
    
    /*** Cleanup ************************************************************/
    // Just to be sure, explicitly delete the object
    unset($fw);
    
    if ($_POST['ajax']) {
        // print_r($_POST);
        // echo("<br /><br />");
        // print_r($shared_params);
        // echo("<br /><br />");
        // print_r($fw_params);
        // echo("<br /><br />");
        // print_r($display_params);
        echo generate_table($display_params, $fw_params, $shared_params, $country_list, $table_views, $user_db);
    }