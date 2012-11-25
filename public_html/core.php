<?php
    require_once('i18n.php');

    include_once("help/HWTHelp/HWTHelp.php");
    require_once("frameworks/frameworks.php");
    require_once("tables/table_generator.php");
    
    $glossary = new HWTHelp('def_link', 'glossary.php', 'calc_gloss');
    
    if ((isset($_GET['copydb']) && $_GET['copydb'] === "yes") || (isset($_POST['copydb']) && $_POST['copydb'] === "yes")) {
        $copydb = true;
    } else {
        $copydb = false;
    }
    
    // Generic cookie array
    $cookie_info=array();
    $cookie_info['time'] = time()+60*60*24*28;
    $cookie_info['server'] = preg_replace("/^\.|www\./","",$_SERVER['HTTP_HOST']);
    
    // Always using GDRs framework now
    $shared_params = Framework::get_shared_params();
    $fw = new Framework::$frameworks['gdrs']['class'];
    
    /*** Databases ************************************************************/
    
    if (isset($_POST['reset'])) {
        $user_db = null;
    } else {
        $user_db = Framework::get_good_db();
    }
    
    $up_to_date = true;
    if (!$user_db) {
        if (!isset($_POST['reset'])) {
            $up_to_date = false;
        }
        $db_array = Framework::dup_master_db('calc', TRUE);
        $master_db = $db_array['db'];
        if ($db_array['did_create']) {
            // Created a new one, so run it
            $fw->calculate($master_db, $shared_params, $fw->get_fw_params());
        }
        $user_db = Framework::get_user_db($master_db);
    } else if ($copydb) {
        // This will make a copy of the user_db
        $user_db = Framework::get_user_db($user_db);
    }
    $fw_params = $fw->get_fw_params($user_db);
    setcookie('db',serialize(Framework::get_db_name($user_db)),$cookie_info['time'],"",$cookie_info['server']);

    if ((isset($_GET['getdb']) && $_GET['getdb'] === "yes") || (isset($_POST['getdb']) && $_POST['getdb'] === "yes")) {
        echo $user_db;
        exit;
    }

    // Function to update parameters array with last user values, if any
    function get_usr_vals(&$array) {
        foreach(array_keys($array) as $key) {
            if (isset($_POST[$key])) {
                $array[$key]['value'] = $_POST[$key];
            } elseif (isset($_POST['submit']) && Framework::is_bool($key, $array)) {
                // This is a checkbox: if not checked, it does not exist in $_POST
                $array[$key]['value'] = 0;
            }
        }
    }
    
    // Reload parameters--might be different from defaults
    $shared_params = Framework::get_shared_params($user_db);
    $fw_params = $fw->get_fw_params($user_db);
    
    /*** Display parameters ****************************************************/

    $basic_adv = array (
        'basic' => 'Basic',
        'adv' => 'Advanced');
    
    $advanced = false;
    // TODO: replace country_grp w JS to show/hide rows and columns, or HTML table filter
    $country_list = Framework::get_country_list($user_db);
    $region_list = Framework::get_region_list(null, $user_db); // Don't specify a country
    $display_params = array ('basic_adv' => array(
                                'value'=>'basic',
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>array(
                                    'basic'=>array('display_name'=>'Basic'),
                                    'adv'=>array('display_name'=>'Advanced')
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
    
    if (!isset($_POST['reset'])) {
        if (isset($_COOKIE['display_params']) && $up_to_date) {
            $display_params = unserialize(stripslashes($_COOKIE['display_params']));    
        }
        get_usr_vals($display_params);
    }
    setcookie('display_params',serialize($display_params),$cookie_info['time'],"",$cookie_info['server']);
    
    // Redundant but convenient to have both
    $table_views = $fw->get_table_views();
    $display_params['table_view']['list'] = $table_views;

    if (isset($_POST['forcesubmit']) || !isset($display_params['table_view']['value'])) {
        $tmp = array_keys($table_views);
        $display_params['table_view']['value'] = $tmp[0];
        setcookie('display_params',serialize($display_params),$cookie_info['time'],"",$cookie_info['server']);
    }
    
    if (!isset($_POST['reset'])) {
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
    
    if (isset($_POST['ajax']) && $_POST['ajax']) {
        // print_r($_POST);
        // echo("<br /><br />");
        // print_r($shared_params);
        // echo("<br /><br />");
        // print_r($fw_params);
        // echo("<br /><br />");
        // print_r($display_params);
        echo generate_table($display_params, $fw_params, $shared_params, $country_list, $region_list, $table_views, $user_db);
    }