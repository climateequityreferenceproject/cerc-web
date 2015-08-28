<?php
// the GRDs API example code uses the old PEAR HTTP/Request package - 
// this has been depreciated in PEAR in favour of HTTP/Request2 since 2008; 
// if the hoster does not have the old one anymore, manually install HTTP/Request
// to the webspace and include the absolute path to it in the include_path, like so:
// set_include_path('/home3/seekersr/pear/share/pear' . PATH_SEPARATOR . get_include_path());
require_once "HTTP/Request.php";


function exists_API_DB($db = NULL) {
    if (isset($db)) {
        $req =& new HTTP_Request("http://calculator.climateequityreference.org/api/?q=data_ver&db=" . $db);
        $req->setMethod(HTTP_REQUEST_METHOD_GET);
        if (!PEAR::isError($req->sendRequest())) {
            return ($req->getResponseCode()==200) ? TRUE : FALSE;
        } else {
            throw new Exception($req->getMessage());
        }
    } else {
        return FALSE;
    }
}

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