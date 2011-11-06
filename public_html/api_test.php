<?php
require_once "HTTP/Request.php";

$req =& new HTTP_Request("http://gdrights.org/calculator_dev/api/");
$req->setMethod(HTTP_REQUEST_METHOD_POST);
$req->addPostData("years", "2020");
$req->addPostData("countries", "USA");
if (!PEAR::isError($req->sendRequest())) {
     $response = json_decode($req->getResponseBody());
} else {
    $response = "";
}

print_r($response);
?>
