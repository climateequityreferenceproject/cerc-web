<?php

function do_legend($params = NULL) {
    $ret = "";
    $ret .= '<style type="text/css">"\n";
    $ret .= file_get_contents("legend.css");
    $ret .= </style>' . "\n";

    $ret .= <<< EOHTML
<table>
<tbody>
<tr style="border-bottom: 1px solid grey; border-top: 2px solid grey;">
<td colspan="2">
<div id="legend-title" style="font-weight: bold; font-size: 80%; text-align: center;">Chart Legend and Toggles</div>
</td>
</tr>
<tr style="border-bottom: 1px dotted grey;">
<td>
<div style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #000000;stroke-width: 5;stroke-linecap: round;stroke-linejoin: round;"></path></svg> Historical Emissions</div>
</td>
<td>
<div id="legend_bau" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #000000;stroke-width: 4;stroke-dasharray: 2,3;"></path></svg> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_bau">Baseline Emissions</button></div>
</td>
</tr>
<tr style="border-bottom: 1px dotted grey;">
<td style="vertical-align: top;">
<div id="legend_wedge_eff" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0L33,8L0,8Z" style="fill: #92D050;fill-opacity: 0.502;"></path></svg> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_eff_wedge">Effort Sharing Band</button>, delineated by</div>
<div id="legend_hi_eff" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #4F81BD;stroke-width: 4;stroke-linecap: round;stroke-linejoin: round;"></path></svg> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_hi_eff">High equity settings</button> and</div>
<div id="legend_lo_eff" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #FF0000;stroke-width: 4;stroke-linecap: round;stroke-linejoin: round;"></path></svg> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_lo_eff">Low equity settings</button> and represented by the</div>
<div id="legend_lfl_bar_eff" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L11,0L11,8L0,8Z" style="fill: #00B050;fill-opacity: 0.502;"></path><path d="M11,0L22,0L22,8L11,8Z" style="fill: #FFFF00;fill-opacity: 0.502;"></path><path d="M22,0L33,0L33,8L22,8Z" style="fill: #FC4C4E;fill-opacity: 0.502;"></path></svg> right “Leader | Fair Share | Laggard” Bar</div>
<div id="legend_lfl_bar_eff_years" class="legend_lfl_bar_eff_years" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0L33,8L0,8Z" style="fill: none;fill-opacity: 0;"></path></svg> …shown for <button class="graph_button" id="btn_lfl_eff_2020">2020</button> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_lfl_eff_2025">2025</button> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_lfl_eff_2030">2030</button></div>
</td>
<td>
<div id="legend_wedge_res" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0L33,8L0,8Z" style="fill: #6092E0;fill-opacity: 0.502;"></path></svg> <button style="" class="graph_button" id="btn_res_wedge">Resource Sharing Band</button>, delineated by</div>
<div id="legend_pc1850" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #4F81BD;stroke-width: 4;stroke-dasharray: 5,3;"></path></svg> <button class="graph_button" id="btn_res_pc1850">Cumulative Per Capita since 1850</button> and</div>
<div id="legend_cc2030" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #FF0000;stroke-width: 4;stroke-dasharray: 5,3;"></path></svg> <button class="graph_button" id="btn_res_cc2030">Contraction and Convergence in 2030</button> and represented by the</div>
<div id="legend_lfl_bar_res" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L11,0L11,8L0,8Z" style="fill: #00B050;fill-opacity: 0.502;"></path><path d="M11,0L22,0L22,8L11,8Z" style="fill: #FFFF00;fill-opacity: 0.502;"></path><path d="M22,0L33,0L33,8L22,8Z" style="fill: #FC4C4E;fill-opacity: 0.502;"></path></svg> left “Leader | Fair Share | Laggard” Bar</div>
<div id="legend_lfl_bar_res_years" class="legend_lfl_bar_res_years" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0L33,8L0,8Z" style="fill: none;fill-opacity: 0;"></path></svg> …shown for <button class="graph_button" id="btn_lfl_res_2020">2020</button> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_lfl_res_2025">2025</button> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_lfl_res_2030">2030</button></div>
<div style="font-size: 80%;">Other illustrative resource sharing cases:</div>
<div id="legend_pc2014" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #FFC000;stroke-width: 4;stroke-dasharray: 5,3;"></path></svg> <button class="graph_button" id="btn_res_pc2014">Immediate Equal Per Capita</button></div>
<div id="legend_grandf" style="font-size: 80%;"><svg width="33" height="8"><path d="M0,0L33,0" style="fill: none;stroke: #FC4C4E;stroke-width: 4;stroke-dasharray: 5,3;"></path></svg> <button class="graph_button" id="btn_res_grand">Full Grandfathering</button></div>
</td>
</tr>
<tr style="border-bottom: none;">
<td style="font-size: 80%;"><u>Pledges</u></td>
</tr>
<tr style="border-bottom: 2px solid grey;">
<td>
<div id="legend_pledge1" style="font-size: 80%;"><svg width="33" height="14" style="position:relative;top:4px;"><polygon class="uncond-glyph" id="uncond-glyph-1" points="23,7 17,13 11,7 17,1" style="stroke:#000;stroke-width:1px;fill:#ff3c3c;"></polygon></svg> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_pledge1">2020 Copenhagen target</button>, 17% below 2005 levels</div>
</td>
<td>
<div id="legend_pledge2" style="font-size: 80%;"><svg width="33" height="14" style="position:relative;top:4px;"><polygon class="uncond-glyph" id="uncond-glyph-1" points="23,7 17,13 11,7 17,1" style="stroke:#000;stroke-width:1px;fill:#ff3c3c;"></polygon></svg> <button style="background-color: rgb(200, 200, 200);" class="graph_button" id="btn_pledge2">2025 INDC</button>, 26-28% below 2005 levels</div>
</td>
</tr>
</tbody>
</table>
EOHTML;
    
    return $ret;
}
