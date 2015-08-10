<?php
// the GRDs API example code used the old PEAR HTTP/Request package - 
// this has been depreciated in favour of HTTP/Request2 since 2008; 
// my hoster does not have the old one anymore, thus the manual install of HTTP/Request. 
set_include_path('/home3/seekersr/pear/share/pear' . PATH_SEPARATOR . get_include_path());
set_include_path('/Users/ch/Documents/ Documents/_SD Card/Copy/CERP/PHP INDC Comparable Effort/pear/share/pear' . PATH_SEPARATOR . get_include_path());
require_once "HTTP/Request.php";


function get_new_API_DB() {
    $req =& new HTTP_Request("http://calculator.climateequityreference.org/api/?q=new_db");
    $req->setMethod(HTTP_REQUEST_METHOD_GET);
    if (!PEAR::isError($req->sendRequest())) {
        $response = (array) json_decode($req->getResponseBody());
    } else {
        throw new Exception($req->getMessage());
    }
    return $response['db'];
}

function get_option_list($of_what, $db = NULL) {
    $req_string = "http://calculator.climateequityreference.org/api/?q=" . $of_what;
    if ($db) { $req_string .= "&db=" . $db; }
    $req =& new HTTP_Request($req_string);
    $req->setMethod(HTTP_REQUEST_METHOD_GET);
    if (!PEAR::isError($req->sendRequest())) {
        // Note: json_decode returns arrays as StdClass, so have to cast
        $response = (array) json_decode($req->getResponseBody());
    } else {
        throw new Exception($req->getMessage());
    }
    return $response;
}

function get_data($parms, $db = NULL) {
    $req =& new HTTP_Request("http://calculator.climateequityreference.org/api/");
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    if ($db) { $req->addPostData('db', $db); }
    foreach ($parms as $key=>$value) {
        $req->addPostData($key, $value); 
	}
    if (!PEAR::isError($req->sendRequest())) {
        $response = json_decode($req->getResponseBody());
        // For countries (but not for regions, CH) the decode procedure duplicates the first element: get the tail
        if (sizeof($response)>1) { $response = array_slice($response, 1); }
    } else {
        throw new Exception($req->getMessage());
    }
    return $response;
}

?>

<html>
    <head>
    </head>
    <body>
<?php
        $parms1 = array();
        if (isset($_REQUEST['country'])) {
            $parms1['countries'] = $_REQUEST['country'];
        } else {
            die("Need to specify the country via the 'country' URL parameter (or POST) as ISO3 code.");
        }
        $parms1['years'] = "1990";
        for ($x = 1991; $x <= 2030; $x++) { $parms1['years'] .= ",".$x; }
        //Global Settings
        $parms1['use_lulucf'] = 1;
        $parms1['use_nonco2'] = 1;
        $parms1['emergency_path'] = 13;
        $parms1['cum_since_yr'] = 1850;
        $parms1['dev_thresh'] = 7500;
        $parms1['interp_btwn_thresh'] = 1;
        $parms1['lux_thresh'] = 50000;
        $parms1['r_wt'] = 0.5;

        if (isset($_REQUEST['db'])) { $db = $_REQUEST['db']; } else { $db = get_new_API_DB(); }
        $data_list = get_data($parms1, $db);
        $keep_these_codes = array("year", "fossil_CO2_MtCO2", "LULUCF_MtCO2", "NonCO2_MtCO2e"); 
        $round_these_codes = array("fossil_CO2_MtCO2", "LULUCF_MtCO2", "NonCO2_MtCO2e", "total"); 
        foreach ($data_list as $entry) {
            $temp = (array) $entry;
            $data[$temp['year']] = $temp;
            foreach ($data[$temp['year']] as $key => $value) {
                if (!(in_array($key,$keep_these_codes))) {
                    unset($data[$temp['year']][$key]);
                }
            }
            $data[$temp['year']]['total'] = floatval(preg_replace("/[^0-9\.\-]/","",$data[$temp['year']]['fossil_CO2_MtCO2'])) + floatval(preg_replace("/[^0-9\.\-]/","",$data[$temp['year']]['LULUCF_MtCO2'])) + floatval(preg_replace("/[^0-9\.\-]/","",$data[$temp['year']]['NonCO2_MtCO2e']));
            foreach ($data[$temp['year']] as $key => $value) {
                if (in_array($key,$round_these_codes)) {
                    $data[$temp['year']][$key] = number_format($data[$temp['year']][$key],3);
                }
            }
        }
?>
        
        <table class="table datatbl">
        <thead>
          <tr>
            <th><?php echo implode('</th><th>', array_keys(current($data))); ?></th>
          </tr>
        </thead>
        <tbody>
        <?php foreach ($data as $row): array_map('htmlentities', $row); ?>
          <tr>
            <td><?php echo implode('</td><td>', $row); ?></td>
          </tr>
        <?php endforeach; ?>
        </tbody>
      </table>


    </body>
</html>