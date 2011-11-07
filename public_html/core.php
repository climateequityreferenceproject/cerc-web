<?php
    include("frameworks/frameworks.php");
    
    // Always using GDRs framework now
    $shared_params = Framework::get_shared_params();
    $fw = new Framework::$frameworks['gdrs']['class'];
    $fw_params = $fw->get_fw_params();
    
    /*** Databases ************************************************************/
    // Create database filename if doesn't already exist
    if ($_POST['user_db']) {
        $user_db = $_POST['user_db'];
    } else {
        $db_array = Framework::dup_master_db('calc', $create);
        $master_db = $db_array['db'];
        if ($db_array['did_create']) {
            // Created a new one, so run it
            $fw->calculate($master_db, $shared_params, $fw_params);
        }
        $user_db = Framework::get_user_db($master_db);
    }

    // If just asking for the db name (or to create a db) then that is all this script does
    if ($_POST['get_db'] || $_GET['get_db']) {
        echo $user_db;
        return;
    }

    include("tables/table_generator.php");

    // Update parameters array with last user values, if any
    function get_usr_vals(&$array) {
        foreach($array as $key => $val) {
            if (isset($_POST[$key])) {
                $array[$key]['value'] = $_POST[$key];
            }
        }
        // Checkboxes are special - if not checked, they do not exist in $_POST, so check to make sure the form has been submitted
        if (isset($_POST['submit'])) {
            if ($array['use_sequencing'] && !$_POST['use_sequencing']) {
                $array['use_sequencing']['value'] = 0;
            }
            if ($array['use_lulucf'] && !$_POST['use_lulucf']) {
                $array['use_lulucf']['value'] = 0;
            }
            if ($array['use_nonco2'] && !$_POST['use_nonco2']) {
                $array['use_nonco2']['value'] = 0;
            }
            if ($array['do_luxcap'] && !$_POST['do_luxcap']) {
                $array['do_luxcap']['value'] = 0;
            }
        }
    }
    
    /*** Display parameters ****************************************************/

    $basic_adv = array (
        basic => 'Basic',
        adv => 'Advanced');
    
    $advanced = false;

    // TODO: implement output of $basic_adv inputs from multi-dim array
    //$basic_adv = array ('basic' => array('display_name'=>'Basic', 'checked'=>false), 
    //                    'adv' => array('display_name'=>'Advanced', 'checked'=>true));
    
    // TODO: implement function to replace this literal array of option names, to get pathway names from master database
    $emergency_paths = array( array('display_name'=>"750 GtCO2 cumulative"), array('display_name'=>"1000 GtCO2 cumulative") );
    
    // TODO: replace country_grp w JS to show/hide rows and columns, or HTML table filter
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
        if (isset($_COOKIE['display_params'])) {
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
        if (isset($_COOKIE['shared_params'])) {
            $shared_params = unserialize(stripslashes($_COOKIE['shared_params']));    
        }
        get_usr_vals($shared_params);
        
        if (isset($_COOKIE['fw_params'])) {
            $fw_params = unserialize(stripslashes($_COOKIE['fw_params']));    
        }
        get_usr_vals($fw_params);
    }
    
    if ($shared_params['cum_since_yr']['value'] < $shared_params['cum_since_yr']['min']) {
        $shared_params['cum_since_yr']['value'] = $shared_params['cum_since_yr']['min'];
    }
    
    setcookie('shared_params',serialize($shared_params),time()+60*60*24*365);
    setcookie('fw_params',serialize($fw_params),time()+60*60*24*365);
    
    $fw->calculate($user_db, $shared_params, $fw_params);
    
    // Check that we've got the correct years, after calculating
    $year_range = Framework::get_year_range($user_db);
    $shared_params['cum_since_yr']['min'] = $year_range['min_year'];

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
        echo generate_table($display_params, $fw_params, $shared_params, $table_views, $user_db);
    }