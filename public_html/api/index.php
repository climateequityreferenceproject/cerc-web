<?php
    include("../frameworks/frameworks.php");
    $msg['OK'] = '200 OK';
    $msg['gone'] = '410 Gone';
    $msg['badreq'] = '400 Bad request';

    function get_usr_vals(&$array) {
        foreach($array as $key => $val) {
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
        
        $user_db = NULL;
        $user_db_exists = TRUE;
        // Note that add_user_db_path will return FALSE if the file doesn't exist
        if ($_POST['db'] || $_GET['db']) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $user_db = Framework::add_user_db_path($_POST['db']);
            } else {
                $user_db = Framework::add_user_db_path($_GET['db']);
            }
            if ($user_db) {
                $shared_params = Framework::get_shared_params($user_db);
                $fw_params = $fw->get_fw_params($user_db);
            } else {
                $user_db_exists = FALSE;
            }
        }
        if (!$user_db) {
            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $create = TRUE;
            } else {
                $create = FALSE;
            }
            $db_array = Framework::dup_master_db('calc', $create);
            $master_db = $db_array['db'];
            if ($db_array['did_create']) {
                // Created a new one, so run it
                $fw->calculate($master_db, $shared_params_default, $fw_params_default);
            }
            // Copy an already calculated database (if it exists)
            $user_db = Framework::get_user_db($master_db);
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
                    } else {
                        $data = "GET: Must be one of 'q=new_db', 'q=params', 'q=params&db=dbname' ";
                        $data .= "'q=data_ver', 'q=data_ver&db=dbname', 'q=calc_ver', 'q=calc_ver&db=db_name'";
                        $status = $msg['badreq'];
                    }
                } else {
                    $status = $msg['gone'];
                    $data = 'The database ' . $user_db . ' does not exist. Use get=new_db to request a new database or use PUT without specifying a database.';
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
        
        return array('status' => $status, 'data' => $data);
    }

    ################################################
    #
    # Use the library
    #
    ################################################
    $result = process_request();
    
    // Remove the file, if not passed as a parameter
    if (file_exists($user_db) && !$_POST['db']) {
        unlink($user_db);
    }
    
    if ($result['status'] != $msg['OK'] || $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Content-type: text/plain");
    } else {
        header("Content-type: application/json");
    }
    echo $result['data'];
    
    
