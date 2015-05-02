<?php
require_once "HTTP/Request.php";

function get_countries() {
    $req =& new HTTP_Request("http://climateequityreference.org/calculator/api/?q=countries");
    $req->setMethod(HTTP_REQUEST_METHOD_GET);
    if (!PEAR::isError($req->sendRequest())) {
        // Note: json_decode returns arrays as StdClass, so have to cast
        $response = (array) json_decode($req->getResponseBody());
    } else {
        throw new Exception($req->getMessage());
    }
    return $response;
}

function get_params() {
    $req =& new HTTP_Request("http://climateequityreference.org/calculator/api/?q=params");
    $req->setMethod(HTTP_REQUEST_METHOD_GET);
    if (!PEAR::isError($req->sendRequest())) {
        $response = (array) json_decode($req->getResponseBody());
    } else {
        throw new Exception($req->getMessage());
    }
    return $response;
}

function get_country_data($iso3) {
    $req =& new HTTP_Request("http://climateequityreference.org/calculator/api/");
    $req->setMethod(HTTP_REQUEST_METHOD_POST);
    $req->addPostData('years', 2020); // Note hard-coded year
    $req->addPostData('countries', $iso3);
    if (!PEAR::isError($req->sendRequest())) {
        $response = json_decode($req->getResponseBody());
        // Oddly, the decode procedure sometimes seems to duplicate the first element.
        // Test by comparing to the number of elements we expect (1).
        if (count($response) > 1) {
           $response = array_slice($response, 1);
        }
    } else {
        throw new Exception($req->getMessage());
    }
    return $response;
}
?>

<html>
    <head>
        <title>Testing GDRs API</title>
    </head>
    <body>
        <h1>Testing GDRs API</h1>
        <form method="post">
            <select name="country">
                <?php
                    $countries = get_countries();
                    foreach ($countries as $country) {
                        $ca = (array) $country;
                        $option = '<option value="';
                        $option .= $ca['iso3'];
                        $option .= '">';
                        $option .= $ca['name'];
                        $option .= '</option>';
                        echo $option;
                    }
                ?>
            </select>
            <input type="submit" value="calculate" />
        </form>
        <?php
            if (isset($_POST['country'])) {
                echo '<h2>output</h2>';
                echo '<table>';
                echo '<tr><td>item</td><td>value</td></tr>';
                $country_data_list = get_country_data($_POST['country']);
                $country_data = (array) $country_data_list[0];
                foreach ($country_data as $item=>$value) {
                    $row = '<tr>';
                    $row .= '<td>' . $item . '</td>';
                    $row .= '<td>' . $value . '</td>';
                    $row .= '</tr>';
                    echo $row;
                }
                echo '</table>';
                
                echo '<h2>parameters</h2>';
                echo '<table>';
                echo '<tr><td>item</td><td>value</td></tr>';
                $params = get_params();
                foreach ($params as $item=>$info) {
                    $info_array = (array) $info;
                    $row = '<tr>';
                    $row .= '<td>' . $item . '</td>';
                    $row .= '<td>' . $info_array['value'] . '</td>';
                    $row .= '</tr>';
                    echo $row;
                }
                echo '</table>';
            }
        ?>
    </body>
</html>
