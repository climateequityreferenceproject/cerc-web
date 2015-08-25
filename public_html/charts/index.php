<?php
include_once('../graphs/graph_core.php');
include_once('../config.php');
//$svg_tmp_dir = "/Users/ch/NetBeansProjects/PhpProject1/";
$svg_tmp_dir = "/***REMOVED***/html/tmp"; // svg files of country graphs are generated here

if (isset($_REQUEST['country'])) { $country = $_REQUEST['country']; }
if (isset($_REQUEST['iso3'])) { $country = $_REQUEST['iso3']; }
if (isset($_REQUEST['iso'])) { $country = $_REQUEST['iso']; }
if (!isset($country)) { die("Need to specify a country to view."); }
$country = strtolower($country);

if (isset($_REQUEST['legend'])) {
    $show_legend = (($_REQUEST['legend'] == 'omit') || ($_REQUEST['legend'] == '0')) ? FALSE : TRUE;
} else {
    $show_legend = (isset($_REQUEST['dl'])) ? FALSE : TRUE;    
}

switch ($_REQUEST['view']) {
    case 'eff':
        $types = array('eff');
        break;
    case 'res':
        $types = array('res');
        break;
    default:
        $types = array('eff' , 'res');
}

/**
 * Convert a comma separated file into an associated array.
 * The first row should contain the array keys.
 * 
 * Example:
 * 
 * @param string $filename Path to the CSV file
 * @param string $delimiter The separator used in the file
 * @return array
 * @link http://gist.github.com/385876
 * @author Jay Williams <http://myd3.com/>
 * @copyright Copyright (c) 2010, Jay Williams
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */
function csv_to_array($filename='', $delimiter=',', $country=NULL) {
	if(!file_exists($filename) || !is_readable($filename)) return FALSE;
	$header = NULL;
	$data = array();
	if (($handle = fopen($filename, 'r')) !== FALSE) {
		while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE) {
			if(!$header) { 
                            $header = $row; 
                        } else { 
                            if (isset($country)) {
                                if ($row[0]=$country) { $data[] = array_combine($header, $row); }
                            } else {
                                $data[] = array_combine($header, $row);    
                            }
                        }
		}
		fclose($handle);
	}
	return $data;
}


$data_input = csv_to_array('data.csv');
foreach ($data_input as $line) {
    if (strtolower($line['iso3'])==$country) {
        for ($i = 1990; $i <= 2030; $i++) {
            switch ($line['timeseries_name']) {
                case 'bau':
                    $bau[$i] = floatval($line[$i]);
                    break;
                case 'high_eff':
                    $high['eff'][$i] = floatval($line[$i]);
                    break;
                case 'med_eff':
                    $med['eff'][$i] = floatval($line[$i]);
                    break;
                case 'low_eff':
                    $low['eff'][$i] = floatval($line[$i]);
                    break;
                case 'high_res':
                    $high['res'][$i] = floatval($line[$i]);
                    break;
                case 'med_res':
                    $med['res'][$i] = floatval($line[$i]);
                    break;
                case 'low_res':
                    $low['res'][$i] = floatval($line[$i]);
                    break;
                case 'bonus_res':
                    $bonus['res'][$i] = floatval($line[$i]);
                    break;
                case 'population':
                    $population[$i] = floatval($line[$i]);
                    break;
                case 'pledge_year':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['year'] = intval($line[$i]); }
                    break;
                case 'pledge_target':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['target'] = floatval($line[$i]); }
                    break;
                case 'pledge_cond':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['cond'] = $line[$i]; }
                    break;
                case 'pledge_name':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['name'] = $line[$i]; }
                    break;
                case 'pledge_description':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['descr'] = $line[$i]; }
                    break;
            }
        }
    }
}
unset($data_input);
if (!isset($bau)) { die("I have no data for the country you are trying to view (" . $country . ")."); }

// removed pledge years with no pledge target
    foreach ($pledges as $key => $pledge) {
        if (!isset($pledge['target'])) { unset($pledges[$key]); }
    }
    if (sizeof($pledges)==0) { unset($pledges); } // a NULL value is better than an empty array

    if (isset($min_override)) {
        $min = $min_override;
    } else {
        $min=0;
        foreach ($bau as $year_idx => $val) {
            $min = min($min, $bau[$year_idx]);
            foreach ($types as $type) {
                $min = min($min, $low[$type][$year_idx], $med[$type][$year_idx], 
                                 $high[$type][$year_idx]);
                if ($type=="res") { $min = min($min, $bonus[$type][$year_idx]); }
            }
        }
        foreach ($pledges as $pledge) { $min = min($min, $pledge['target']); }
    }
    if (isset($max_override)) {
        $max = $max_override;
    } else {
        $max=0;
        foreach ($bau as $year_idx => $val) {
            $max = max($max, $bau[$year_idx]);
            foreach ($types as $type) {
                $max = max($max, $low[$type][$year_idx], $med[$type][$year_idx], 
                                 $high[$type][$year_idx]);
                if ($type=="res") { $max = max($max, $bonus[$type][$year_idx]); }
            }
        }
        foreach ($pledges as $pledge) { $max = max($max, $pledge['target']); }
    }
    $range = $max - $min;
    $step = 0;
    $major_step = -4;
    while ($step==0) {       
        foreach (array(1, 2.5, 5) as $minor_step) {
            $this_step = pow(10,$major_step) * $minor_step;
            //var_dump($this_step);
            if ((($range/$this_step) >=4) && (($range/$this_step) <=10)) { $step = $this_step; }            
        }
        $major_step++;
    }
//    $min = floor($min/$step) * $step;
    $min = $min * 1.10;
    $max = $max * 1.10;

    $graph_width = 650;
    $graph_height = 450;
    $legend_height = 0;
    $graph = new Graph(array(
                    'width' => $graph_width,
                    'height' => $graph_height,
                    'legend_height' => $legend_height
                ), array(
                    'filename' => 'country_graphs.css',
                    'embed' => true
                )
                );
    // The TRUE means use the specified limits for the graph; the FALSE means don't format numbers
    $graph->set_xaxis(1990, 2030, "", "", TRUE, FALSE, 5);
    $graph->set_yaxis($min, $max, "Mt CO&#8322;eq", "", TRUE, TRUE, $step);
    $graph->add_series($bau, "bau", "bau");
    
    foreach ($types as $type) {
        $graph->add_series($low[$type], "low".$type, "low_".$type);
        $graph->add_series($med[$type], "mid".$type, "medium_".$type);
        $graph->add_series($high[$type], "hi".$type, "high_".$type);
        if ($type=="res") {$graph->add_series($bonus['res'], "bonusres", "bonus_res"); }
    }
        
    $yaxis_settings = $graph->get_yaxis_scale();
    $min = $yaxis_settings['min'];
    $max = $yaxis_settings['max'];

    // calculate the scale of the secondary y-axis to represent tons per capita reductions in 2030
    $max2 = ($max - $bau[2030])/$population[2030];
    $min2 = ($min - $bau[2030])/$population[2030];
    $range2 = (-1)*$min2;
    $step2 = 0;
    $major_step = -6;
    while ($step2==0) {       
        foreach (array(1, 2.5, 5) as $minor_step) {
            $this_step = pow(10,$major_step) * $minor_step;
            //var_dump($this_step);
            if ((($range2/$this_step) >=4) && (($range2/$this_step) <=10)) { 
                $step2 = $this_step; 
                $dec = (-1)*$major_step;
            }            
        }
        $major_step++;
    }
//    $graph->set_yaxis2($min, $max, "Mt CO&#8322;eq", "", TRUE, TRUE, $step);
    $graph->set_yaxis2($min2, $max2, "Mt CO&#8322;eq/capital below baseline in 2030", "", TRUE, TRUE, $step2, $dec);

    $wedges = array(
                array(
                      'id' => 'wedge_eff',
                      'between' => array('hieff', 'loweff'),
                      'color' => '#92D050',
                      'stripes' => NULL,
                      'opacity' => 0.5,
                      'css_class' => 'wedge_eff'
                      ),
                array(
                      'id' => 'wedge_res',
                      'between' => array('hires', 'lowres'),
                      'color' => '#92D050',
                      'stripes' => NULL,
                      'opacity' => 0.5,
                      'css_class' => 'wedge_res'
                      ),
                    );
    $ignore_for_common = array();
    
    foreach ($types as $type) {
        foreach (array(2020, 2025, 2030) as $year) {
            $diff = ($type == 'eff') ? 1 : -1; 
            $bar_min[$type.$year][$year]       = $min;
            $bar_min[$type.$year][$year+$diff] = $min; 
            $bar_low[$type.$year][$year]       = min($high[$type][$year],$low[$type][$year]); 
            $bar_low[$type.$year][$year+$diff] = min($high[$type][$year],$low[$type][$year]);
            $bar_high[$type.$year][$year]       = max($high[$type][$year],$low[$type][$year]);
            $bar_high[$type.$year][$year+$diff] = max($high[$type][$year],$low[$type][$year]); 
            $bar_max[$type.$year][$year]       = $max;
            $bar_max[$type.$year][$year+$diff] = $max; 
            ksort($bar_min);
            ksort($bar_low);
            ksort($bar_high);
            ksort($bar_max);

            $graph->add_series($bar_min[$type.$year], $type . '_bar_min' . $year, 'noline');
            $graph->add_series($bar_low[$type.$year], $type . '_bar_low' . $year, 'noline');
            $graph->add_series($bar_high[$type.$year], $type . '_bar_high' . $year, 'noline');
            $graph->add_series($bar_max[$type.$year], $type . '_bar_max' . $year, 'noline');

            if (count($types)==2) { 
                $x_offset_val = 13; 
                $x_offset = ($type == 'eff') ? array('left'=>$x_offset_val,'right'=>-1*$x_offset_val) : array('left'=>-1*$x_offset_val,'right'=>$x_offset_val);
            } else { 
                $x_offset = ($type == 'eff') ? $x_offset = array('left'=>-8,'right'=>-8) : $x_offset = array('left'=>7,'right'=>7);
            }
            array_push ($wedges, array(
                                'id' => 'lfl_bar_green_' . $type . '_' . $year,
                                'between' => array($type . '_bar_min' . $year, $type . '_bar_low' . $year),
                                'stripes' => NULL,
                                'css_class' => 'lfl_bar_green lfl_bar_' . $type . '_' . $year,
                                'x_offset' => $x_offset
                            ),
                            array(
                                'id' => 'lfl_bar_yell_' . $type . '_' . $year,
                                'between' => array($type . '_bar_low' . $year, $type . '_bar_high' . $year),
                                'stripes' => NULL,
                                'css_class' => 'lfl_bar_yellow lfl_bar_' . $type . '_' . $year,
                                'x_offset' => $x_offset
                            ),
                            array(
                                'id' => 'lfl_bar_red_' . $type . '_' . $year,
                                'between' => array($type . '_bar_high' . $year, $type . '_bar_max' . $year),
                                'stripes' => NULL,
                                'css_class' => 'lfl_bar_red lfl_bar_' . $type . '_' . $year,
                                'x_offset' => $x_offset
                            ));
            array_push ($ignore_for_common,$type . '_bar_min' . $year, $type . '_bar_low' . $year, $type . '_bar_high' . $year, $type . '_bar_max' . $year);
        }
    }
    $glyph_id=0;
    foreach ($pledges as $key => $pledge) {
            if ($pledge['cond']=="conditional") {
                $graph->add_glyph($pledge['year'], $pledge['target'],
                        'cond-glyph cond-glyph-' . $glyph_id, 'cond-glyph-' . $glyph_id,
                        'circle', 10);
                $pledges[$key]['css_class'] = 'cond-glyph-' . $glyph_id;
            } else {
                $graph->add_glyph($pledge['year'], $pledge['target'],
                        'uncond-glyph uncond-glyph-' . $glyph_id, 'uncond-glyph-' . $glyph_id,
                        'diamond', 12);
                $pledges[$key]['css_class'] = 'uncond-glyph-' . $glyph_id;
            }
            $glyph_id++;
    }
    $graph_params = array(
                    'common_id' => 'historical', 
                    'ignore_for_common' => $ignore_for_common, 
                    'code_output' => 'yes', 
                    'has_second_yaxis' => TRUE,
                    'labels_match_scale' => array('x'=>FALSE,'y1'=>TRUE,'y2'=>TRUE),
                    'label_multiplier'=>(-1),
        );
    //$graph_params['vertical_at'] = 2026;

    // generate the svg code:
    $graph_file = $graph->svgplot_wedges($wedges, $graph_params);

    // build the legend, if needed
    if ($show_legend) {
        include_once('legend.php');
        $legend_params = array();
        $legend = do_legend($legend_params);
    }
    
// output
// common headers for downloads
if (isset($_REQUEST['dl'])) {
    header("Cache-Control: no-cache, must-revalidate");
    header("Pragma: no-cache");
    header("Content-Description: PHP/INTERBASE Generated Data" );
}
// what image file type do we want?
switch ($_REQUEST['type']) {
    case "jpg":
        $im = new Imagick();
        $dpi = isset($_REQUEST['dpi']) ? $_REQUEST['dpi'] : 200;
        $im->setResolution($dpi,$dpi);
        $im->readImageBlob($graph_file);
        $im->setImageFormat("jpeg");
        $im->setImageCompression(Imagick::COMPRESSION_JPEG);
        if (isset($_REQUEST['dl'])) {
            header('Content-type: '.$im->getFormat());
            header("Content-Disposition: attachment; filename=\"".$country.(isset($_REQUEST['view']) ? "_".$_REQUEST['view'] : "" ).".".$_REQUEST['type']."\"" );
            echo $im->getimageblob();
        } else {
            echo '<img src="data:image/jpg;base64,' . base64_encode($im) . '"  />';
        }
        break;
    case "png":
        $im = new Imagick();
        $dpi = isset($_REQUEST['dpi']) ? $_REQUEST['dpi'] : 200;
        $im->setResolution($dpi,$dpi);
        $im->readImageBlob($graph_file);
        $im->setImageFormat("png24");
        $im->setImageCompression(Imagick::COMPRESSION_ZIP);
        if (isset($_REQUEST['dl'])) {
            header('Content-type: '.$im->getFormat());
            header("Content-Disposition: attachment; filename=\"".$country.(isset($_REQUEST['view']) ? "_".$_REQUEST['view'] : "" ).".".$_REQUEST['type']."\"" );
            echo $im->getimageblob();
        } else {
            echo '<img src="data:image/png;base64,' . base64_encode($im) . '"  />';
        }
        break;
    default:
        if (isset($_REQUEST['dl'])) {
            header('Content-type: image/svg+xml');
            header("Content-Length: " . strlen($graph_file)); 
            header("Content-Disposition: attachment; filename=\"".$country.(isset($_REQUEST['view']) ? "_".$_REQUEST['view'] : "" ).".svg\"" );
        }
        echo($graph_file);
        if ($show_legend) { echo $legend; }
}
