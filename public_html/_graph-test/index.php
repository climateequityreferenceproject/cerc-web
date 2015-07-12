<?php
include_once('../graphs/graph_core.php');
//$svg_tmp_dir = "/Users/ch/NetBeansProjects/PhpProject1/";
$svg_tmp_dir = "/***REMOVED***/html/tmp"; // svg files of country graphs are generated here

if (isset($_REQUEST['country'])) { $country = $_REQUEST['country']; }
if (isset($_REQUEST['iso3'])) { $country = $_REQUEST['iso3']; }
if (isset($_REQUEST['iso'])) { $country = $_REQUEST['iso']; }
if (!isset($country)) { die("Need to specify a country to view."); }
$country = strtolower($country);

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
                    $bau[$i] = $line[$i];
                    break;
                case 'high_eff':
                    $high['eff'][$i] = $line[$i];
                    break;
                case 'med_eff':
                    $med['eff'][$i] = $line[$i];
                    break;
                case 'low_eff':
                    $low['eff'][$i] = $line[$i];
                    break;
                case 'high_res':
                    $high['res'][$i] = $line[$i];
                    break;
                case 'med_res':
                    $med['res'][$i] = $line[$i];
                    break;
                case 'low_res':
                    $low['res'][$i] = $line[$i];
                    break;
                case 'bonus_res':
                    $bonus['res'][$i] = $line[$i];
                    break;
                case 'pledge_year':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['year'] = $line[$i]; }
                    break;
                case 'pledge_target':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['target'] = $line[$i]; }
                    break;
                case 'pledge_cond':
                    if (strlen($line[$i])>0) { $pledges[($i-1990)]['cond'] = $line[$i]; }
                    break;
            }
        }
    }
}
unset($data_input);
//$bau[1990]=6219.52402096666;	$bau[1991]=6195.07454243333;	$bau[1992]=6295.80398883333;	$bau[1993]=6430.33940266667;	$bau[1994]=6499.92337170001;	$bau[1995]=6597.66521113333;	$bau[1996]=6812.32331963333;	$bau[1997]=6867.1211785;	$bau[1998]=6868.76062033333;	$bau[1999]=6930.95715546666;	$bau[2000]=7075.60941596666;	$bau[2001]=6979.19637616667;	$bau[2002]=7011.19569663333;	$bau[2003]=7057.53880113333;	$bau[2004]=7198.37899183333;	$bau[2005]=7228.29315706667;	$bau[2006]=7150.74356553334;	$bau[2007]=7287.7501202;	$bau[2008]=7090.75312513333;	$bau[2009]=6642.3195861;	$bau[2010]=6854.72819093334;	$bau[2011]=6716.99302193333;	$bau[2012]=6487.84705213334;	$bau[2013]=6638.2826136;	$bau[2014]=6624.01748066667;	$bau[2015]=6737.74444273333;	$bau[2016]=6849.04588683333;	$bau[2017]=6939.6818601;	$bau[2018]=7013.8854775;	$bau[2019]=7070.59463293334;	$bau[2020]=7126.71523373334;	$bau[2021]=7183.96520966667;	$bau[2022]=7242.36203936667;	$bau[2023]=7301.92362056666;	$bau[2024]=7362.66825433334;	$bau[2025]=7424.61468173333;	$bau[2026]=7487.78206843333;	$bau[2027]=7552.1900212;	$bau[2028]=7617.85861136666;	$bau[2029]=7684.8083686;	$bau[2030]=7753.06028896667;
//$high['eff'][1990]=6219.52402096667;	$high['eff'][1991]=6195.07454243333;	$high['eff'][1992]=6295.80398883333;	$high['eff'][1993]=6430.33940266667;	$high['eff'][1994]=6499.9233717;	$high['eff'][1995]=6597.66521113333;	$high['eff'][1996]=6812.32331963333;	$high['eff'][1997]=6867.1211785;	$high['eff'][1998]=6868.76062033333;	$high['eff'][1999]=6930.95715546667;	$high['eff'][2000]=7075.60941596667;	$high['eff'][2001]=6979.19637616667;	$high['eff'][2002]=7011.19569663333;	$high['eff'][2003]=7057.53880113334;	$high['eff'][2004]=7198.37899183333;	$high['eff'][2005]=7228.29315706667;	$high['eff'][2006]=7150.74356553333;	$high['eff'][2007]=7287.7501202;	$high['eff'][2008]=7090.75312513333;	$high['eff'][2009]=6642.3195861;	$high['eff'][2010]=6854.72819093333;	$high['eff'][2011]=6716.99302193333;	$high['eff'][2012]=6488.38675297555;	$high['eff'][2013]=6638.34471169353;	$high['eff'][2014]=6185.18676383521;	$high['eff'][2015]=5546.71508009725;	$high['eff'][2016]=4588.40401442788;	$high['eff'][2017]=3343.25414146546;	$high['eff'][2018]=2114.97317889971;	$high['eff'][2019]=1022.12125637746;	$high['eff'][2020]=24.1262297087148;	$high['eff'][2021]=-785.721011822341;	$high['eff'][2022]=-1576.25229060982;	$high['eff'][2023]=-2341.04162509852;	$high['eff'][2024]=-3078.22611859103;	$high['eff'][2025]=-3784.69499346239;	$high['eff'][2026]=-4456.21340431818;	$high['eff'][2027]=-5093.22232489465;	$high['eff'][2028]=-5690.85469362487;	$high['eff'][2029]=-6248.81737448913;	$high['eff'][2030]=-6765.75995160981;
//$med['eff'][1990]=6219.52402096667;	$med['eff'][1991]=6195.07454243333;	$med['eff'][1992]=6295.80398883333;	$med['eff'][1993]=6430.33940266667;	$med['eff'][1994]=6499.9233717;	$med['eff'][1995]=6597.66521113333;	$med['eff'][1996]=6812.32331963333;	$med['eff'][1997]=6867.1211785;	$med['eff'][1998]=6868.76062033333;	$med['eff'][1999]=6930.95715546667;	$med['eff'][2000]=7075.60941596667;	$med['eff'][2001]=6979.19637616667;	$med['eff'][2002]=7011.19569663333;	$med['eff'][2003]=7057.53880113334;	$med['eff'][2004]=7198.37899183333;	$med['eff'][2005]=7228.29315706667;	$med['eff'][2006]=7150.74356553333;	$med['eff'][2007]=7287.7501202;	$med['eff'][2008]=7090.75312513333;	$med['eff'][2009]=6642.3195861;	$med['eff'][2010]=6854.72819093333;	$med['eff'][2011]=6716.99302193333;	$med['eff'][2012]=6488.2605651937;	$med['eff'][2013]=6638.33003322159;	$med['eff'][2014]=6289.8678034487;	$med['eff'][2015]=5833.50562952213;	$med['eff'][2016]=5135.09432339256;	$med['eff'][2017]=4216.34350531788;	$med['eff'][2018]=3308.05756341996;	$med['eff'][2019]=2498.83716342772;	$med['eff'][2020]=1761.4946475995;	$med['eff'][2021]=1166.35309687608;	$med['eff'][2022]=585.481960577252;	$med['eff'][2023]=23.3991868706042;	$med['eff'][2024]=-518.828265739467;	$med['eff'][2025]=-1039.1911953484;	$med['eff'][2026]=-1534.82628805502;	$med['eff'][2027]=-2006.39113490197;	$med['eff'][2028]=-2450.5182587422;	$med['eff'][2029]=-2867.27603489403;	$med['eff'][2030]=-3255.91219729285;
//$low['eff'][1990]=6219.52402096667;	$low['eff'][1991]=6195.07454243333;	$low['eff'][1992]=6295.80398883333;	$low['eff'][1993]=6430.33940266667;	$low['eff'][1994]=6499.9233717;	$low['eff'][1995]=6597.66521113333;	$low['eff'][1996]=6812.32331963333;	$low['eff'][1997]=6867.1211785;	$low['eff'][1998]=6868.76062033333;	$low['eff'][1999]=6930.95715546667;	$low['eff'][2000]=7075.60941596667;	$low['eff'][2001]=6979.19637616667;	$low['eff'][2002]=7011.19569663333;	$low['eff'][2003]=7057.53880113334;	$low['eff'][2004]=7198.37899183333;	$low['eff'][2005]=7228.29315706667;	$low['eff'][2006]=7150.74356553333;	$low['eff'][2007]=7287.7501202;	$low['eff'][2008]=7090.75312513333;	$low['eff'][2009]=6642.3195861;	$low['eff'][2010]=6854.72819093333;	$low['eff'][2011]=6716.99302193333;	$low['eff'][2012]=6488.17388408323;	$low['eff'][2013]=6638.31997614385;	$low['eff'][2014]=6361.3310110845;	$low['eff'][2015]=6028.22944172544;	$low['eff'][2016]=5505.2660321465;	$low['eff'][2017]=4806.17271706855;	$low['eff'][2018]=4112.82308970459;	$low['eff'][2019]=3494.17756578131;	$low['eff'][2020]=2932.37133230958;	$low['eff'][2021]=2482.4607313068;	$low['eff'][2022]=2044.21110603243;	$low['eff'][2023]=1621.00012568067;	$low['eff'][2024]=1213.51395342235;	$low['eff'][2025]=823.181037379207;	$low['eff'][2026]=452.103210464132;	$low['eff'][2027]=99.6457354804304;	$low['eff'][2028]=-231.676246175312;	$low['eff'][2029]=-542.014557054233;	$low['eff'][2030]=-830.868382022946;    
//$high['res'][1990]=6219.52402096666;	$high['res'][1991]=6195.07454243333;	$high['res'][1992]=6295.80398883333;	$high['res'][1993]=6430.33940266667;	$high['res'][1994]=6499.92337170001;	$high['res'][1995]=6597.66521113333;	$high['res'][1996]=6812.32331963333;	$high['res'][1997]=6867.1211785;	$high['res'][1998]=6868.76062033333;	$high['res'][1999]=6930.95715546666;	$high['res'][2000]=7075.60941596666;	$high['res'][2001]=6979.19637616667;	$high['res'][2002]=7011.19569663333;	$high['res'][2003]=7057.53880113333;	$high['res'][2004]=7198.37899183333;	$high['res'][2005]=7228.29315706667;	$high['res'][2006]=7150.74356553334;	$high['res'][2007]=7287.7501202;	$high['res'][2008]=7090.75312513333;	$high['res'][2009]=6642.3195861;	$high['res'][2010]=6854.72819093334;	$high['res'][2011]=6716.99302193333;	$high['res'][2012]=6487.84705213334;	$high['res'][2013]=6638.2826136;	$high['res'][2014]=6294.25430314442;	$high['res'][2015]=5819.47549000744;	$high['res'][2016]=5314.29950339775;	$high['res'][2017]=4685.53743092323;	$high['res'][2018]=4055.10364115364;	$high['res'][2019]=3324.05459973055;	$high['res'][2020]=2617.68346329589;	$high['res'][2021]=1945.69786209928;	$high['res'][2022]=1314.00595325974;	$high['res'][2023]=719.14007744683;	$high['res'][2024]=160.804236018606;	$high['res'][2025]=-360.038867402871;	$high['res'][2026]=-839.321953617789;	$high['res'][2027]=-1302.84396791849;	$high['res'][2028]=-1720.61443661971;	$high['res'][2029]=-2086.99180157334;	$high['res'][2030]=-2423.12417766747;
//$med['res'][1990]=6219.52402096666;	$med['res'][1991]=6195.07454243333;	$med['res'][1992]=6295.80398883333;	$med['res'][1993]=6430.33940266667;	$med['res'][1994]=6499.92337170001;	$med['res'][1995]=6597.66521113333;	$med['res'][1996]=6812.32331963333;	$med['res'][1997]=6867.1211785;	$med['res'][1998]=6868.76062033333;	$med['res'][1999]=6930.95715546666;	$med['res'][2000]=7075.60941596666;	$med['res'][2001]=6979.19637616667;	$med['res'][2002]=7011.19569663333;	$med['res'][2003]=7057.53880113333;	$med['res'][2004]=7198.37899183333;	$med['res'][2005]=7228.29315706667;	$med['res'][2006]=7150.74356553334;	$med['res'][2007]=7287.7501202;	$med['res'][2008]=7090.75312513333;	$med['res'][2009]=6642.3195861;	$med['res'][2010]=6854.72819093334;	$med['res'][2011]=6716.99302193333;	$med['res'][2012]=6487.84705213334;	$med['res'][2013]=6638.2826136;	$med['res'][2014]=6447.27948742901;	$med['res'][2015]=6209.01168855524;	$med['res'][2016]=5824.62889755114;	$med['res'][2017]=5346.84642766232;	$med['res'][2018]=4872.21571982104;	$med['res'][2019]=4325.05168945497;	$med['res'][2020]=3814.67878576685;	$med['res'][2021]=3349.13237378375;	$med['res'][2022]=2930.36039494248;	$med['res'][2023]=2552.35838702984;	$med['res'][2024]=2204.59939311747;	$med['res'][2025]=1884.23253981807;	$med['res'][2026]=1590.1354686609;	$med['res'][2027]=1302.76708479374;	$med['res'][2028]=1037.98183845004;	$med['res'][2029]=801.260635737931;	$med['res'][2030]=589.204104184193;
//$low['res'][1990]=6219.52402096666;	$low['res'][1991]=6195.07454243333;	$low['res'][1992]=6295.80398883333;	$low['res'][1993]=6430.33940266667;	$low['res'][1994]=6499.92337170001;	$low['res'][1995]=6597.66521113333;	$low['res'][1996]=6812.32331963333;	$low['res'][1997]=6867.1211785;	$low['res'][1998]=6868.76062033333;	$low['res'][1999]=6930.95715546666;	$low['res'][2000]=7075.60941596666;	$low['res'][2001]=6979.19637616667;	$low['res'][2002]=7011.19569663333;	$low['res'][2003]=7057.53880113333;	$low['res'][2004]=7198.37899183333;	$low['res'][2005]=7228.29315706667;	$low['res'][2006]=7150.74356553334;	$low['res'][2007]=7287.7501202;	$low['res'][2008]=7090.75312513333;	$low['res'][2009]=6642.3195861;	$low['res'][2010]=6854.72819093334;	$low['res'][2011]=6716.99302193333;	$low['res'][2012]=6487.84705213334;	$low['res'][2013]=6638.2826136;	$low['res'][2014]=6450.56996057701;	$low['res'][2015]=6218.17430722437;	$low['res'][2016]=5840.65107158674;	$low['res'][2017]=5373.26724632668;	$low['res'][2018]=4916.37629558497;	$low['res'][2019]=4400.0165825418;	$low['res'][2020]=3924.25149976725;	$low['res'][2021]=3491.79214019192;	$low['res'][2022]=3102.95911319795;	$low['res'][2023]=2748.06848301714;	$low['res'][2024]=2423.39449643324;	$low['res'][2025]=2132.1627863042;	$low['res'][2026]=1876.28294848635;	$low['res'][2027]=1663.55939447847;	$low['res'][2028]=1490.34680119837;	$low['res'][2029]=1354.8061989747;	$low['res'][2030]=1265.01317458301;
//$bonus['res'][1990]=6219.52402096666;	$bonus['res'][1991]=6195.07454243333;	$bonus['res'][1992]=6295.80398883333;	$bonus['res'][1993]=6430.33940266667;	$bonus['res'][1994]=6499.92337170001;	$bonus['res'][1995]=6597.66521113333;	$bonus['res'][1996]=6812.32331963333;	$bonus['res'][1997]=6867.1211785;	$bonus['res'][1998]=6868.76062033333;	$bonus['res'][1999]=6930.95715546666;	$bonus['res'][2000]=7075.60941596666;	$bonus['res'][2001]=6979.19637616667;	$bonus['res'][2002]=7011.19569663333;	$bonus['res'][2003]=7057.53880113333;	$bonus['res'][2004]=7198.37899183333;	$bonus['res'][2005]=7228.29315706667;	$bonus['res'][2006]=7150.74356553334;	$bonus['res'][2007]=7287.7501202;	$bonus['res'][2008]=7090.75312513333;	$bonus['res'][2009]=6642.3195861;	$bonus['res'][2010]=6854.72819093334;	$bonus['res'][2011]=6716.99302193333;	$bonus['res'][2012]=6487.84705213334;	$bonus['res'][2013]=6638.2826136;	$bonus['res'][2014]=6624.01748066667;	$bonus['res'][2015]=6737.74444273333;	$bonus['res'][2016]=6849.04588683333;	$bonus['res'][2017]=6939.68186007828;	$bonus['res'][2018]=7013.88349651398;	$bonus['res'][2019]=7066.7785932253;	$bonus['res'][2020]=6989.2629159768;	$bonus['res'][2021]=6753.61329182678;	$bonus['res'][2022]=6424.35480856458;	$bonus['res'][2023]=5967.52756126418;	$bonus['res'][2024]=5531.19492170936;	$bonus['res'][2025]=5136.43893919429;	$bonus['res'][2026]=4779.5602734961;	$bonus['res'][2027]=4444.332766999;	$bonus['res'][2028]=4139.99113626158;	$bonus['res'][2029]=3863.66443649957;	$bonus['res'][2030]=3612.74413293671;
//$pledges[] = array('year'=>2020, 'target'=>5999.48332036534, 'cond'=>'unconditional');
//$pledges[] = array('year'=>2025, 'target'=>5348.93693622934, 'cond'=>'unconditional');
//$pledges[] = array('year'=>2025, 'target'=>5204.37107308800, 'cond'=>'unconditional');

//$min_override = -7500;
//$max_override = 8000;

    if (isset($min_override)) {
        $min = $min_override;
    } else {
        $min=0;
        foreach ($bau as $year_idx => $val) {
            $min = min($min, $bau[$year_idx]);
            foreach ($types as $type) {
                $min = min($min, $low[$type][$year_idx], $med[$type][$year_idx], $high[$type][$year_idx]);
                if ($type=="res") { $min = min($min, $bonus[$type][$year_idx]); }
            }
        }
    }
    if (isset($max_override)) {
        $max = $max_override;
    } else {
        $max=0;
        foreach ($bau as $year_idx => $val) {
            $max = max($max, $bau[$year_idx]);
            foreach ($types as $type) {
                $max = max($max, $low[$type][$year_idx], $med[$type][$year_idx], $high[$type][$year_idx], $bonus[$type][$year_idx]);
                if ($type=="res") { $max = max($max, $bonus[$type][$year_idx]); }
            }
        }
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
    $min = floor($min/$step) * $step;
    $max = $max * 1.02;

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
    $graph->set_yaxis2($min/100, $max/100, "Mt CO&#8322;eq/capital below baseline in 2030", "", TRUE, TRUE, $step/100);
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
    foreach ($pledges as $pledge) {
            if ($pledge['cond']=="conditional") {
                $graph->add_glyph($pledge['year'], $pledge['target'],
                        'cond-glyph', 'cond-glyph-' . $glyph_id++,
                        'circle', 10);
            } else {
                $graph->add_glyph($pledge['year'], $pledge['target'],
                        'uncond-glyph', 'uncond-glyph-' . $glyph_id++,
                        'diamond', 12);
            }        
    }
    
    $graph_params = array('common_id' => 'historical', 'ignore_for_common' => $ignore_for_common, 'vertical_at' =>1990, 'code_output' => 'yes', 'has_second_yaxis' => TRUE);                
    $graph_file = $graph->svgplot_wedges($wedges, $graph_params);

    
// outut
//header('Content-type: image/svg+xml');
// dont set headers while debugging but normally a good idea
    
    echo($graph_file);
