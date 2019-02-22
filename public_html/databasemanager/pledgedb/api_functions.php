<?php
require_once realpath(__DIR__ . "/../../inc/dependencies/autoload.php"); // loading guzzle http client

function exists_API_DB($db = NULL, $api_params = NULL) {
    if (isset($db)) {
      $url = ($api_params['dev'] == TRUE) ? Constants::api_url(['dev']) : Constants::api_url(['public']) ;
      $auth = NULL;
      if ($api_params['dev'] == TRUE) {
          $dev_calc_creds = Constants::dev_calc_creds();
          $auth = array($dev_calc_creds['user'], $dev_calc_creds['pass']);
      }
      $client = new \GuzzleHttp\Client();
      try {
           $response = $client->request('GET', $url . "?q=data_ver&db=" . $db, ['auth' => $auth])->getBody();
           return TRUE;
      } catch (Exception $e) {
           throw $e;
           return FALSE;
      }
    } else {
        return FALSE;
    }
}

function get_new_API_DB($api_params = NULL) {
    $url = ($api_params['dev'] == TRUE) ? Constants::api_url(['dev']) : Constants::api_url(['public']) ;
    $auth = NULL;
    if ($api_params['dev'] == TRUE) {
        $dev_calc_creds = Constants::dev_calc_creds();
        $auth = array($dev_calc_creds['user'], $dev_calc_creds['pass']);
    }
    $client = new \GuzzleHttp\Client();
    try {
         $response = (array) json_decode($client->request('GET', $url . "?q=new_db", ['auth' => $auth])->getBody());
    } catch (Exception $e) {
         throw $e;
         return FALSE;
    }
    return $response['db'];
}

function get_option_list($of_what, $db = NULL, $api_params = NULL) {
    $url = ($api_params['dev'] == TRUE) ? Constants::api_url(['dev']) : Constants::api_url(['public']) ;
    $req_string = $url . "?q=" . $of_what;
    if ($db) { $req_string .= "&db=" . $db; }
    $auth = NULL;
    if ($api_params['dev'] == TRUE) {
        $dev_calc_creds = Constants::dev_calc_creds();
        $auth = array($dev_calc_creds['user'], $dev_calc_creds['pass']);
    }
    $client = new \GuzzleHttp\Client();
    try {
         $response = (array) json_decode($client->request('GET', $req_string, ['auth' => $auth])->getBody());
    } catch (Exception $e) {
         throw $e;
    }
    return $response;
}

function get_data($parms, $db = NULL, $api_params = NULL) {
    $url = ($api_params['dev'] == TRUE) ? Constants::api_url(['dev']) : Constants::api_url(['public']) ;
    $auth = NULL;
    if ($api_params['dev'] == TRUE) {
        $dev_calc_creds = Constants::dev_calc_creds();
        $auth = array($dev_calc_creds['user'], $dev_calc_creds['pass']);
    }
    if ($db) { $POST_params['db'] = $db; }
    foreach ($parms as $key=>$value) {
        $POST_params[$key] = $value;
	  }
    $client = new \GuzzleHttp\Client();
    try {
        $response = $client->request('POST', $url, [
                    'form_params' => $POST_params, 'auth' => $auth,
                    'allow_redirects' => ['strict' => true]]);
        $response = (array) json_decode($response->getBody());
        // For countries (but not for regions, CH) the decode procedure duplicates the first element: get the tail
        if (sizeof($response)>1) { $response = array_slice($response, 1); }
    } catch (Exception $e) {
      throw $e;
    }
    return $response;
}
