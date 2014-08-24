<?php
    include("../frameworks/frameworks.php");
    $msg = array();
    $msg['OK'] = '200 OK';
    $msg['gone'] = '410 Gone';
    $msg['badreq'] = '400 Bad request';

    function get_usr_vals(&$array) {
        foreach(array_keys($array) as $key) {
            if (isset($_POST[$key])) {
                $array[$key]['value'] = $_POST[$key];
            }
        }
    }
    
    function process_request() {
        global $msg;

        /*** Calculator parameters *************************************************/
        // Function to update parameters array with last user values, if any

        $fw = new Framework::$frameworks['gdrs']['class'];

        $shared_params_default = Framework::get_shared_params();
        $fw_params_default = $fw->get_fw_params();
        $params_default = array_merge($shared_params_default, $fw_params_default);
        
        $user_db = null;
        $user_db_exists = true;
        $made_temp_db = false;
        // Note that add_user_db_path will return FALSE if the file doesn't exist
        if ($_POST['db'] || $_GET['db']) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_db = Framework::add_user_db_path($_POST['db']);
            } else {
                $user_db = Framework::add_user_db_path($_GET['db']);
            }
            if (!Framework::db_up_to_date($user_db)) {
                $user_db = null;
            }
            if ($user_db) {
                $shared_params = Framework::get_shared_params($user_db);
                $fw_params = $fw->get_fw_params($user_db);
            } else {
                $user_db_exists = false;
            }
        }
        if (!$user_db) {
            if (isset($_GET['old_db'])) {
                $old_db = Framework::add_user_db_path($_GET['old_db']);
                // Copy the old database as a master
                $user_db = Framework::get_user_db($old_db, 'api');
            } else {
                // If already have a duplicated version, use that, otherwise create if needed
                $db_array = Framework::dup_master_db('calc', true);
                $master_db = $db_array['db'];
                if ($db_array['did_create']) {
                    // Created a new one, so run it
                    $fw->calculate($master_db, $shared_params_default, $fw_params_default);
                }
                // Copy an already calculated database (if it exists)
                $user_db = Framework::get_user_db($master_db, 'api');
            }
            if ($user_db) {
                // This will be a temp DB unless used GET to request a new one; that case is handled below
                $made_temp_db = true;
            }
            $shared_params = $shared_params_default;
            $fw_params = $fw_params_default;
        }
        
        $params = array_merge($shared_params, $fw_params);
        
        $data = '';
        $allow = null;
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                $allow = 'GET, POST';
                $status = $msg['OK'];
                $data = 'Available methods are GET and POST';
                break;
            case 'GET':
                if ($user_db_exists) {
                    if ($_GET['q'] === 'new_db') {
                        $data = json_encode(array('db' => Framework::get_db_name($user_db)));
                        $status = $msg['OK'];
                        $made_temp_db = false;
                    } elseif ($_GET['q'] === 'params') {
                        if ($_GET['db']) {
                            $data = json_encode($params);
                        } else {
                            $data = json_encode($params_default);
                        }
                        $status = $msg['OK'];
                    } elseif ($_GET['q'] === 'year_range') {
                        if ($_GET['db']) {
                            $data = json_encode(Framework::get_year_range($user_db));
                        } else {
                            $data = json_encode(Framework::get_year_range());
                        }
                        $status = $msg['OK'];
                    }  elseif ($_GET['q'] === 'pathways') {
                        if ($_GET['db']) {
                            $data = json_encode(Framework::get_emerg_paths($user_db));
                        } else {
                            $data = json_encode(Framework::get_emerg_paths());
                        }
                        $status = $msg['OK'];
                    } elseif ($_GET['q'] === 'data_ver') {
                        if ($_GET['db']) {
                            $data = json_encode(Framework::get_data_ver($user_db));
                        } else {
                            $data = json_encode(Framework::get_data_ver());
                        }
                        $status = $msg['OK'];
                    } elseif ($_GET['q'] === 'calc_ver') {
                        if ($_GET['db']) {
                            $data = json_encode(Framework::get_calc_ver($user_db));
                        } else {
                            $data = json_encode(Framework::get_calc_ver());
                        }
                        $status = $msg['OK'];
                    } elseif ($_GET['q'] === 'countries') {
                        if ($_GET['db']) {
                            $data = json_encode(Framework::get_country_list($user_db));
                        } else {
                            $data = json_encode(Framework::get_country_list());
                        }
                        $status = $msg['OK'];
                    } elseif ($_GET['q'] === 'regions') {
                        if (isset($_GET['country'])) {
                            $iso3 = $_GET['country'];
                        } else {
                            $iso3 = null;
                        }
                        if ($_GET['db']) {
                            $data = json_encode(Framework::get_region_list($iso3, $user_db));
                        } else {
                            $data = json_encode(Framework::get_region_list($iso3));
                        }
                        $status = $msg['OK'];
                    } else {
                        $data = "GET: Must be one of 'q=new_db', 'q=params', 'q=params&db=dbname' ";
                        $data .= "'q=data_ver', 'q=data_ver&db=dbname', 'q=calc_ver', 'q=calc_ver&db=db_name' ";
                        $data .= "'q=pathways', 'q=pathways&db=dbname', 'q=countries', 'q=countries&db=dbname'";
                        $data .= "'q=regions', 'q=regions&db=dbname', 'q=regions&country=iso3', 'q=regions&country=iso3&db=dbname'";
                        $status = $msg['badreq'];
                    }
                } else {
                    $status = $msg['gone'];
                    $data = 'The database ' . $_GET['db'] . ' does not exist. Use q=new_db to request a new database or use PUT without specifying a database.';
                }
                break;
            case 'POST':
                if ($user_db_exists) {
                    if ($_POST['years']) {
                        $yearquery = '(';
                        foreach (explode(',', $_POST['years']) as $year) {
                            // Is this a range?
                            if (strpos($year,':') === false) {
                                $yearquery .= 'year = ' . $year . ' OR ';
                            } else {
                                $yearrange = explode(':', $year);
                                $yearquery .= '(year >= ' . $yearrange[0] . ' AND year <=' . $yearrange[1] . ') OR ';
                            }
                        }
                        $yearquery .= '0)'; // A literal "false" to end the sequence of "ORs"
                    } else {
                        $yearquery = '1'; // If no years, then return a true value
                    }
                    $countries = array();
                    if ($_POST['countries']) {
                        $countryquery = '(';
                        foreach (explode(',', $_POST['countries']) as $country) {
                            $countries[] = $country;
                            $countryquery .= 'code = "' . $country . '" OR ';
                        }
                        $countryquery .= '0)';
                    } else {
                        $countryquery = '1';
                    }
                    if ($_POST['reset']) {
                        $shared_params = $shared_params_default;
                        $fw_params = $fw_params_default;
                    }
                    get_usr_vals($shared_params);
                    get_usr_vals($fw_params);
                    $params = array_merge($shared_params, $fw_params);

                    $fw->calculate($user_db, $shared_params, $fw_params);
                    include('api_tabsep.php');
                    unset($fw);

                    # The data_array is created in 'api_tabsep.php'
                    $data = json_encode($data_array);
                    $status = $msg['OK'];
                } else {
                    $status = $msg['gone'];
                    $data = 'The database ' . $user_db . ' does not exist. Use get=new_db to request a new database or use PUT without specifying a database.';
                }
                break;
            default:
                # default behavior
                $status = $msg['badreq'];
        }
            
        header("HTTP/1.0 " . $status);
        if ($allow) {
            header('Allow: ' . $allow);
        }
        
        // Remove the file, if not passed as a parameter
        if ($made_temp_db && file_exists($user_db)) {
            unlink($user_db);
        }
        
        return array('status' => $status, 'data' => $data);
    }

    ################################################
    #
    # Use the library
    #
    ################################################
    $result = process_request();
    
    if ($result['status'] != $msg['OK'] || $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Content-type: text/plain");
    } else {
        header("Content-type: application/json");
    }
    echo $result['data'];
    
    
