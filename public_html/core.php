<?php
    require_once('i18n.php');
    if (is_readable('config.php')) {
        require_once('config.php');
    } else {
        die("Cannot read config.php file. If this is a new installation, locate the config.php.new file, enter the required information, and rename if config.php.");
    }

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
    $fw = new Framework::$frameworks['gdrs']['class'];

    /*** Databases ************************************************************/

    // if user requests (typically via an URL parameter) to "activate" a custom db,
    // we copy it to the usersession directory and set the db parameter to that value
    if (isset($_GET['activate_db']) || isset($_POST['activate_db'])) {
        $activate_db = basename($_REQUEST['activate_db']);
        $origin = $database_folder = pathinfo($core_db)['dirname'] . "/" . $activate_db;
        $destination = $user_db_store . "/" . $activate_db;
        if (file_exists($origin)) {
            if (!copy($origin, $destination)) {
                // fail quiety for now 
        } else {
            $_REQUEST['db'] = $activate_db; // i know this is clunky but it's also fast
            $_GET['db']     = $activate_db; // i know this is clunky but it's also fast
            $_POST['db']    = $activate_db; // i know this is clunky but it's also fast
        }
        }
    }    

    $user_db = $fw->get_good_db();
    if (isset($_POST['reset'])) {
        if ($user_db) {
            unlink($user_db);
        }
        $user_db = null;
    }

    $up_to_date = true;
    if (!$user_db) {
        if (!isset($_POST['reset'])) {
            $up_to_date = false;
        }
        $db_array = $fw->dup_master_db('calc', TRUE);
        $master_db = $db_array['db'];
        if ($db_array['did_create']) {
            // Created a new one, so run it
            $fw->calculate($master_db, $shared_params, $fw->get_fw_params());
        }
        $user_db = $fw->get_user_db($master_db);
    } else if ($copydb) {
        // This will make a copy of the user_db
        $user_db = $fw->get_user_db($user_db);
    }
    $shared_params = $fw->get_shared_params($user_db);
    $fw_params = $fw->get_fw_params($user_db);
    setcookie('db',serialize($fw->get_db_name($user_db)),$cookie_info['time'],"",$cookie_info['server']);

    if ((isset($_GET['getdb']) && $_GET['getdb'] === "yes") || (isset($_POST['getdb']) && $_POST['getdb'] === "yes")) {
        echo $user_db;
        exit;
    }

    // Function to update parameters array with last user values, if any
    function get_usr_vals(&$array) {
        foreach(array_keys($array) as $key) {
            if (isset($_POST[$key]) || isset($_GET[$key])) {
                if (isset($_POST[$key])) {
                    $array[$key]['value'] = $_POST[$key]; // POST overrides any GET parameters in the URL, for JS
                } else {
                    $array[$key]['value'] = $_GET[$key];
                }
            } elseif (isset($_POST['submit']) && (New EmptyFramework)->is_bool($key, $array)) {
                // This is a checkbox: if not checked, it does not exist in $_POST
                $array[$key]['value'] = 0;
            }
        }
        // Special case
        if (isset($array['percent_gwp_MITIGATION']) && isset($array['percent_gwp_ADAPTATION'])) {
            $array['percent_gwp']['value'] = $array['percent_gwp_MITIGATION']['value'] + $array['percent_gwp_ADAPTATION']['value'];
        }
        // Special case
        if (isset($_POST['use_kab_radio'])) {
            switch ($_POST['use_kab_radio']) {
                case 'use_kab':
                    $use_kab = 1;
                    $kab_only_ratified = 0;
                    break;
                case 'dont_use_kab':
                    $use_kab = 0;
                    $kab_only_ratified = 0;
                    break;
                case 'kab_only_ratified':
                    $use_kab = 1;
                    $kab_only_ratified = 1;
                    break;
                default:
                    break;
            }
            if (array_key_exists('use_kab', $array)) {
                $array['use_kab']['value'] = $use_kab;
            }
            if (array_key_exists('kab_only_ratified', $array)) {
                $array['kab_only_ratified']['value'] = $kab_only_ratified;
            }
        }
    }

    // Reload parameters--might be different from defaults
    $shared_params = $fw->get_shared_params($user_db);
    $fw_params = $fw->get_fw_params($user_db);

    // Special case
    $shared_params['percent_gwp']['value'] = $shared_params['percent_gwp_MITIGATION']['value'] + $shared_params['percent_gwp_ADAPTATION']['value'];

    /*** Display parameters ****************************************************/

    $basic_adv = array (
        'basic' => 'Basic',
        'adv' => 'Advanced');

    $advanced = true;
    // TODO: replace country_grp w JS to show/hide rows and columns, or HTML table filter
    $country_list = $fw->get_country_list($user_db);
    $region_list = $fw->get_region_list(null, $user_db); // Don't specify a country
    $display_params = array ('basic_adv' => array(
                                'value'=>'adv',
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>array(
                                    'basic'=>array('display_name'=>'Basic'),
                                    'adv'=>array('display_name'=>'Advanced')
                                )
                            ),
                             'display_gases' => array(
                                'value'=>'gases_all',
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>array(
//                                    'gases_all'=>array('display_name'=>'all gases/sectors'),
//                                    'gases_fossil_only'=>array('display_name'=>'fossil CO₂ only'),
//                                    'gases_fossil_nonCO2'=>array('display_name'=>'fossil CO₂ and non-CO₂'),
//                                    'gases_fossil_LULUCF'=>array('display_name'=>'fossil CO₂ and LULUCF')
                                    'gases_all'=>array('display_name'=>'include LULUCF'),
                                    'gases_fossil_nonCO2'=>array('display_name'=>'exclude LULUCF'),
                                )
                            ),
                             'display_yr' => array(
                                'value'=>2030,
                                'advanced'=>false,
                                'min'=>1990,
                                'max'=>2035,
                                'step'=>1,
                                'list'=>NULL
                            ),
                             'reference_yr' => array(
                                'value'=>1990,
                                'advanced'=>false,
                                'min'=>1990,
                                'max'=>2035,
                                'step'=>1,
                                'list'=>NULL
                            ),
                             'chart_range' => array(
                                'value'=>'1990-2030',
                                'advanced'=>false,
                                'min'=>NULL,
                                'max'=>NULL,
                                'step'=>NULL,
                                'list'=>array(
                                    '1990-2030'=>array('display_name'=>'1990-2030'),
                                    '1990-2035'=>array('display_name'=>'1990-2035'),
                                    '2000-2030'=>array('display_name'=>'2000-2030'),
                                    '2000-2035'=>array('display_name'=>'2000-2035'),
                                    '2005-2030'=>array('display_name'=>'2005-2030'),
                                    '2005-2035'=>array('display_name'=>'2005-2035'),
                                    '2010-2030'=>array('display_name'=>'2010-2030'),
                                    '2010-2035'=>array('display_name'=>'2010-2035'),
                                )
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
                                'value'=>1,
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
        // Correct for legacy settings, where the cookie may have the 'basic' flag set
        $display_params['basic_adv']['value'] = 'adv';
        if (!isset($_POST['equity_cancel']) && !isset($_POST['equity_cancel_top'])) {
            get_usr_vals($display_params);
        }
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

    if (!isset($_POST['reset']) && !isset($_POST['equity_cancel']) && !isset($_POST['equity_cancel_top'])) {
        get_usr_vals($shared_params);
        get_usr_vals($fw_params);
    }

    $fw->calculate($user_db, $shared_params, $fw_params);

    // Use the most up-to-date parameter list: years might have changed
    $shared_params = $fw->get_shared_params($user_db);
    $fw_params = $fw->get_fw_params($user_db);

    // Special case
    $shared_params['percent_gwp']['value'] = $shared_params['percent_gwp_MITIGATION']['value'] + $shared_params['percent_gwp_ADAPTATION']['value'];

    $cost_of_carbon = $fw->cost_of_carbon($user_db, $display_params['display_yr']['value']);


    // Get a scorecard, calculator home and glossary url
    $query_string = $fw->get_params_as_query($user_db);
    if ($fw->is_dev()) {
        //$scorecard_url = $URL_sc_dev . '?' . $query_string;
        $calculator_url = $URL_calc_dev;
        $gloss_url = $URL_gloss_dev;
    } else {
        //$scorecard_url = $URL_sc . '?' . $query_string;
        $calculator_url = $URL_calc;
        $gloss_url = $URL_gloss;
    }

    /*** Cleanup ************************************************************/
    // Just to be sure, explicitly delete the object
    unset($fw);

    if (isset($_POST['ajax'])) {
        switch ($_POST['ajax']) {
            case 'table':
                echo generate_table($display_params, $fw_params, $shared_params, $country_list, $region_list, $table_views, $user_db);
                break;
            case 'carboncost':
                $cost_tot = number_format($cost_of_carbon['cost_blnUSDMER']);
                $cost_tonne = number_format($cost_of_carbon['cost_USD_per_tCO2']);
                $cost_perc = number_format($cost_of_carbon['cost_perc_gwp'], 1);
                $year = (int) $cost_of_carbon['year'];
                echo json_encode(array(
                    'totcost' => $cost_tot,
                    'pertonne' => $cost_tonne,
                    'percgwp' => $cost_perc,
                    'year' => $year
                ));
                break;
            default:
                break;

        }
    }

    function delete_old_tempfiles ($path, $age = 28800) { // $age is in seconds. Defaults to 8 hours
        $files = glob($path."/*");
        $now   = time();
        foreach ($files as $file) {
            if (is_file($file)) {
                if ($now - filemtime($file) >= $age) {
                    unlink($file);
                }
            }
        }
    }


// clean up old files from temporary folders. times are from Eric's old cron
// jobs - not clear why we keep svgs for a week (do they get re-used?)
// It's sufficienty to call this on every 100th page view on average - if run, it
// adds about 200ms to each page load and we don't want to slow down every single time
if (rand(1,100) == 1) {
    delete_old_tempfiles ($user_db_store , 28800); // 8 hours
    delete_old_tempfiles ($xls_tmp_dir , 28800);   // 8 hours
    delete_old_tempfiles ($svg_tmp_dir , 604800);  // 7 days
    $temp = shell_exec("date >> " . $user_db_store . "/log.txt");
}
