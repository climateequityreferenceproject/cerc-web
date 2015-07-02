<?php
include_once('../graphs/graph_core.php');
//$svg_tmp_dir = "/Users/ch/NetBeansProjects/PhpProject1/";
$svg_tmp_dir = "/***REMOVED***/html/tmp"; // svg files of country graphs are generated here

$bau[1990]=6219.52402096666;	$bau[1991]=6195.07454243333;	$bau[1992]=6295.80398883333;	$bau[1993]=6430.33940266667;	$bau[1994]=6499.92337170001;	$bau[1995]=6597.66521113333;	$bau[1996]=6812.32331963333;	$bau[1997]=6867.1211785;	$bau[1998]=6868.76062033333;	$bau[1999]=6930.95715546666;	$bau[2000]=7075.60941596666;	$bau[2001]=6979.19637616667;	$bau[2002]=7011.19569663333;	$bau[2003]=7057.53880113333;	$bau[2004]=7198.37899183333;	$bau[2005]=7228.29315706667;	$bau[2006]=7150.74356553334;	$bau[2007]=7287.7501202;	$bau[2008]=7090.75312513333;	$bau[2009]=6642.3195861;	$bau[2010]=6854.72819093334;	$bau[2011]=6716.99302193333;	$bau[2012]=6487.84705213334;	$bau[2013]=6638.2826136;	$bau[2014]=6624.01748066667;	$bau[2015]=6737.74444273333;	$bau[2016]=6849.04588683333;	$bau[2017]=6939.6818601;	$bau[2018]=7013.8854775;	$bau[2019]=7070.59463293334;	$bau[2020]=7126.71523373334;	$bau[2021]=7183.96520966667;	$bau[2022]=7242.36203936667;	$bau[2023]=7301.92362056666;	$bau[2024]=7362.66825433334;	$bau[2025]=7424.61468173333;	$bau[2026]=7487.78206843333;	$bau[2027]=7552.1900212;	$bau[2028]=7617.85861136666;	$bau[2029]=7684.8083686;	$bau[2030]=7753.06028896667;
$high['eff'][1990]=6219.52402096667;	$high['eff'][1991]=6195.07454243333;	$high['eff'][1992]=6295.80398883333;	$high['eff'][1993]=6430.33940266667;	$high['eff'][1994]=6499.9233717;	$high['eff'][1995]=6597.66521113333;	$high['eff'][1996]=6812.32331963333;	$high['eff'][1997]=6867.1211785;	$high['eff'][1998]=6868.76062033333;	$high['eff'][1999]=6930.95715546667;	$high['eff'][2000]=7075.60941596667;	$high['eff'][2001]=6979.19637616667;	$high['eff'][2002]=7011.19569663333;	$high['eff'][2003]=7057.53880113334;	$high['eff'][2004]=7198.37899183333;	$high['eff'][2005]=7228.29315706667;	$high['eff'][2006]=7150.74356553333;	$high['eff'][2007]=7287.7501202;	$high['eff'][2008]=7090.75312513333;	$high['eff'][2009]=6642.3195861;	$high['eff'][2010]=6854.72819093333;	$high['eff'][2011]=6716.99302193333;	$high['eff'][2012]=6488.38675297555;	$high['eff'][2013]=6638.34471169353;	$high['eff'][2014]=6185.18676383521;	$high['eff'][2015]=5546.71508009725;	$high['eff'][2016]=4588.40401442788;	$high['eff'][2017]=3343.25414146546;	$high['eff'][2018]=2114.97317889971;	$high['eff'][2019]=1022.12125637746;	$high['eff'][2020]=24.1262297087148;	$high['eff'][2021]=-785.721011822341;	$high['eff'][2022]=-1576.25229060982;	$high['eff'][2023]=-2341.04162509852;	$high['eff'][2024]=-3078.22611859103;	$high['eff'][2025]=-3784.69499346239;	$high['eff'][2026]=-4456.21340431818;	$high['eff'][2027]=-5093.22232489465;	$high['eff'][2028]=-5690.85469362487;	$high['eff'][2029]=-6248.81737448913;	$high['eff'][2030]=-6765.75995160981;
$med['eff'][1990]=6219.52402096667;	$med['eff'][1991]=6195.07454243333;	$med['eff'][1992]=6295.80398883333;	$med['eff'][1993]=6430.33940266667;	$med['eff'][1994]=6499.9233717;	$med['eff'][1995]=6597.66521113333;	$med['eff'][1996]=6812.32331963333;	$med['eff'][1997]=6867.1211785;	$med['eff'][1998]=6868.76062033333;	$med['eff'][1999]=6930.95715546667;	$med['eff'][2000]=7075.60941596667;	$med['eff'][2001]=6979.19637616667;	$med['eff'][2002]=7011.19569663333;	$med['eff'][2003]=7057.53880113334;	$med['eff'][2004]=7198.37899183333;	$med['eff'][2005]=7228.29315706667;	$med['eff'][2006]=7150.74356553333;	$med['eff'][2007]=7287.7501202;	$med['eff'][2008]=7090.75312513333;	$med['eff'][2009]=6642.3195861;	$med['eff'][2010]=6854.72819093333;	$med['eff'][2011]=6716.99302193333;	$med['eff'][2012]=6488.2605651937;	$med['eff'][2013]=6638.33003322159;	$med['eff'][2014]=6289.8678034487;	$med['eff'][2015]=5833.50562952213;	$med['eff'][2016]=5135.09432339256;	$med['eff'][2017]=4216.34350531788;	$med['eff'][2018]=3308.05756341996;	$med['eff'][2019]=2498.83716342772;	$med['eff'][2020]=1761.4946475995;	$med['eff'][2021]=1166.35309687608;	$med['eff'][2022]=585.481960577252;	$med['eff'][2023]=23.3991868706042;	$med['eff'][2024]=-518.828265739467;	$med['eff'][2025]=-1039.1911953484;	$med['eff'][2026]=-1534.82628805502;	$med['eff'][2027]=-2006.39113490197;	$med['eff'][2028]=-2450.5182587422;	$med['eff'][2029]=-2867.27603489403;	$med['eff'][2030]=-3255.91219729285;
$low['eff'][1990]=6219.52402096667;	$low['eff'][1991]=6195.07454243333;	$low['eff'][1992]=6295.80398883333;	$low['eff'][1993]=6430.33940266667;	$low['eff'][1994]=6499.9233717;	$low['eff'][1995]=6597.66521113333;	$low['eff'][1996]=6812.32331963333;	$low['eff'][1997]=6867.1211785;	$low['eff'][1998]=6868.76062033333;	$low['eff'][1999]=6930.95715546667;	$low['eff'][2000]=7075.60941596667;	$low['eff'][2001]=6979.19637616667;	$low['eff'][2002]=7011.19569663333;	$low['eff'][2003]=7057.53880113334;	$low['eff'][2004]=7198.37899183333;	$low['eff'][2005]=7228.29315706667;	$low['eff'][2006]=7150.74356553333;	$low['eff'][2007]=7287.7501202;	$low['eff'][2008]=7090.75312513333;	$low['eff'][2009]=6642.3195861;	$low['eff'][2010]=6854.72819093333;	$low['eff'][2011]=6716.99302193333;	$low['eff'][2012]=6488.17388408323;	$low['eff'][2013]=6638.31997614385;	$low['eff'][2014]=6361.3310110845;	$low['eff'][2015]=6028.22944172544;	$low['eff'][2016]=5505.2660321465;	$low['eff'][2017]=4806.17271706855;	$low['eff'][2018]=4112.82308970459;	$low['eff'][2019]=3494.17756578131;	$low['eff'][2020]=2932.37133230958;	$low['eff'][2021]=2482.4607313068;	$low['eff'][2022]=2044.21110603243;	$low['eff'][2023]=1621.00012568067;	$low['eff'][2024]=1213.51395342235;	$low['eff'][2025]=823.181037379207;	$low['eff'][2026]=452.103210464132;	$low['eff'][2027]=99.6457354804304;	$low['eff'][2028]=-231.676246175312;	$low['eff'][2029]=-542.014557054233;	$low['eff'][2030]=-830.868382022946;    
//$line5[2025]=823.181037379207;$line5[2026]=823.181037379207;
//$line6[2025]=-3784.69499346239;$line6[2026]=-3784.69499346239;
//$line7[2025]=-9999;$line7[2026]=-9999;
//$line8[2025]=-3784.69499346239;$line8[2026]=-3784.69499346239;

    $min=0;
    $max=0;
    foreach ($bau as $year_idx => $val) {
        $min = min($min, $bau[$year_idx], $low['eff'][$year_idx], $med['eff'][$year_idx], $high['eff'][$year_idx]);
        $max = max($max, $bau[$year_idx], $low['eff'][$year_idx], $med['eff'][$year_idx], $high['eff'][$year_idx]);
    }
        
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
    $graph->set_yaxis($min, $max, "Mt CO&#8322;eq", "");

    $graph->add_series($bau, "bau", "bau");
    $graph->add_series($low['eff'], "loweq", "low_eq");
    $graph->add_series($med['eff'], "mideq", "medium_eq");
    $graph->add_series($high['eff'], "hieq", "high_eq");

    $yaxis_settings = $graph->get_yaxis_scale();
    $min = $yaxis_settings['min'];
    $max = $yaxis_settings['max'];
    
    $wedges = array(
                array(
                      'id' => 'wedge_eff',
                      'between' => array('hieq', 'loweq'),
                      'color' => '#92D050',
                      'stripes' => NULL,
                      'opacity' => 0.5,
                      'css_class' => 'wedge_eff'
                      ),
                    );
    $ignore_for_common = array();
    
    foreach (array(2020, 2025, 2030) as $year) {
        $type= "eff";
        $bar_min[$type.$year][$year] = $min;
        $bar_min[$type.$year][$year+1] = $min; 
        $bar_low[$type.$year][$year] = min($high['eff'][$year],$low['eff'][$year]); 
        $bar_low[$type.$year][$year+1] = min($high['eff'][$year],$low['eff'][$year]);
        $bar_high[$type.$year][$year] = max($high['eff'][$year],$low['eff'][$year]);
        $bar_high[$type.$year][$year+1] = max($high['eff'][$year],$low['eff'][$year]); 
        $bar_max[$type.$year][$year] = $max;
        $bar_max[$type.$year][$year+1] = $max; 

        $graph->add_series($bar_min[$type.$year], $type . '_bar_min' . $year, 'noline');
        $graph->add_series($bar_low[$type.$year], $type . '_bar_low' . $year, 'noline');
        $graph->add_series($bar_high[$type.$year], $type . '_bar_high' . $year, 'noline');
        $graph->add_series($bar_max[$type.$year], $type . '_bar_max' . $year, 'noline');

        array_push ($wedges, array(
                            'id' => 'lfl_bar_green_' . $type . '_' . $year,
                            'between' => array($type . '_bar_min' . $year, $type . '_bar_low' . $year),
                            'stripes' => NULL,
                            'css_class' => 'lfl_bar_green lfl_bar_' . $type . '_' . $year
                        ),
                        array(
                            'id' => 'lfl_bar_yell_' . $type . '_' . $year,
                            'between' => array($type . '_bar_low' . $year, $type . '_bar_high' . $year),
                            'stripes' => NULL,
                            'css_class' => 'lfl_bar_yellow lfl_bar_' . $type . '_' . $year
                        ),
                        array(
                            'id' => 'lfl_bar_red_' . $type . '_' . $year,
                            'between' => array($type . '_bar_high' . $year, $type . '_bar_max' . $year),
                            'stripes' => NULL,
                            'css_class' => 'lfl_bar_red lfl_bar_' . $type . '_' . $year
                        ));
        array_push ($ignore_for_common,$type . '_bar_min' . $year, $type . '_bar_low' . $year, $type . '_bar_high' . $year, $type . '_bar_max' . $year);
    }
    
    $graph_params = array('common_id' => 'historical', 'ignore_for_common' => $ignore_for_common, 'vertical_at' => 1990, 'code_output' => 'yes');                
    $graph_file = $graph->svgplot_wedges($wedges, $graph_params);
    
echo($graph_file);
//    $graph_file = basename($graph_file);
//
//    $width_string = $graph_width . "px";
//    $height_string = ($graph_height + $legend_height) . "px";
//    echo('<object data="http://climateequityreference.org/tmp/' . $graph_file . '" type="image/svg+xml" style="width:' . $width_string . '; height:' . $height_string . '; border: 1px solid #CCC;">');
//    echo("<p>No SVG support</p>");  
//    echo("</object>");
//    

