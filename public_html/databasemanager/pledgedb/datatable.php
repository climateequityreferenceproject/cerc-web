<?php include_once('api_functions.php'); ?>
<?php
        $parms1 = array();
        if (isset($_REQUEST['country'])) {
            $parms1['countries'] = $_REQUEST['country'];
        } else {
            die("Need to specify the country via the 'country' URL parameter (or POST) as ISO3 code.");
        }
        $parms1['years'] = "1989";
        for ($x = 1990; $x <= 2030; $x++) { $parms1['years'] .= ",".$x; }
        //Global Settings
        $parms1['use_lulucf'] = 1;
        $parms1['use_nonco2'] = 1;
        $parms1['emergency_path'] = 13;
        $parms1['cum_since_yr'] = 1850;
        $parms1['dev_thresh'] = 7500;
        $parms1['interp_btwn_thresh'] = 1;
        $parms1['lux_thresh'] = 50000;
        $parms1['r_wt'] = 0.5;

        if (isset($_COOKIE['db'])) {
            $db = unserialize($_COOKIE['db']); 
        } else {
            $db = get_new_API_DB();
            // cookies must be sent before any output from your script
            setcookie("db", serialize($db), time()+604800);
        }
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
        
<html>
    <head>
    </head>
    <body>
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