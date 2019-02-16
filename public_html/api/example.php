<?php
include_once "guzzle.phar"; // currently using version 6.3.3 from https://github.com/guzzle/guzzle

function get_countries() {
    $client = new \GuzzleHttp\Client();
    try {
         $response = (array) json_decode($client->request('GET', "http://calculator.climateequityreference.org/api/?q=countries")->getBody());
    } catch (Exception $e) {
         throw $e;
    }
    return $response;
}

function get_params() {
    $client = new \GuzzleHttp\Client();
    try {
         $response = (array) json_decode($client->request('GET', "http://calculator.climateequityreference.org/api/?q=params")->getBody());
    } catch (Exception $e) {
         throw $e;
    }
    return $response;
}

function get_country_data($iso3) {
    $POST_params['years'] = 2030; // Note hard-coded year
    $POST_params['countries'] = $iso3;
    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->request('POST', "http://calculator.climateequityreference.org/api/", [
                    'form_params' => $POST_params,
                    'allow_redirects' => ['strict' => true]]);
        $response = (array) json_decode($response->getBody());
        // Oddly, the decode procedure duplicates the first element.
        // Test by comparing to the number of elements we expect (1).
        if (count($response) > 1) {
           $response = array_slice($response, 1);
        }
    } catch (Exception $e) {
      throw $e;
    }
    return $response;
}
?>

<html>
    <head>
        <title>Testing CERc API</title>
    </head>
    <body>
        <h1>Testing CERc API</h1>
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
