<?php
    include("../frameworks/frameworks.php");
    $msg['OK'] = '200 OK';
    $msg['gone'] = '410 Gone';
    $msg['badreq'] = '400 Bad request';

    function process_request() {
        global $msg;

        /*** Calculator parameters *************************************************/
        // Function to update parameters array with last user values, if any
        function get_usr_vals(&$array) {
            foreach($array as $key => $val) {
                if (isset($_POST[$key])) {
                    $array[$key]['value'] = $_POST[$key];
                }
            }
        }

        $fw = new Framework::$frameworks["gdrs"]['class'];

        $shared_params_default = Framework::get_shared_params();
        $fw_params_default = $fw->get_fw_params();
        $params_default = array('shared' => $shared_params_default, 'gdrs' => $fw_params_default);
        ;
        if (isset($_COOKIE['api_shared_params'])) {
            $shared_params = unserialize(stripslashes($_COOKIE['api_shared_params']));
        } else {
            $shared_params = $shared_params_default;
        }
        if (isset($_COOKIE['api_fw_params'])) {
            $fw_params = unserialize(stripslashes($_COOKIE['api_fw_params']));    
        } else {
            $fw_params = $fw_params_default;
        }
        $params = array('shared' => $shared_params, 'gdrs' => $fw_params);

        if ($_POST['db']) {
            $user_db = $_POST['db'];
        } else {
            $user_db = Framework::get_user_db();
        }
        
        $data = '';
        $allow = null;
        switch ($_SERVER['REQUEST_METHOD']) {
            case 'OPTIONS':
                $allow = 'GET, POST';
                $status = $msg['OK'];
                $data = 'Available methods are GET and POST';
                break;
            case 'GET':
                if ($_GET['db']) {
                    $data = json_encode(array('db' => $user_db));
                    $status = $msg['OK'];
                } elseif ($_GET['params']) {
                    switch ($_GET['params']) {
                        case 'current':
                            $data = json_encode($params);
                            $status = $msg['OK'];
                            break;
                        case 'default':
                            $data = json_encode($params_default);
                            $status = $msg['OK'];
                            break;
                        default:
                            $data = "GET: params can be either 'current' or 'default'";
                            $status = $msg['badreq'];
                    }
                } else {
                    $data = "GET: Must be one of 'db=new', 'params=current', 'params=default'";
                    $status = $msg['badreq'];
                }
                break;
            case 'POST':
                if (!file_exists($user_db)) {
                    $status = $msg['gone'];
                    $data = 'The database ' . $user_db . 'does not exist. Use GET db=new to request a new database or use PUT without specifying a database.';
                }
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
                $params = array('shared' => $shared_params, 'gdrs' => $fw_params);

                setcookie('api_shared_params',serialize($shared_params),time()+60*60*24*365);
                setcookie('api_fw_params',serialize($fw_params),time()+60*60*24*365);
                
                $fw->calculate($user_db, $shared_params, $fw_params);
                include('api_tabsep.php');
                unset($fw);
                
                # The data_array is created in 'api_tabsep.php'
                $data = json_encode($data_array);
                $status = $msg['OK'];
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
    
    if ($result['status'] != $msg['OK'] || $_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        header("Content-type: text/plain");
    } else {
        header("Content-type: application/json");
    }
    echo $result['data'];
    
    
