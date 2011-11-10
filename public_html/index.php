<?php
include("core.php");
include("boilerplate.php");
include("form_functions.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head profile="http://gmpg.org/xfn/11">
        <link type="text/css" href="css/smoothness/jquery-ui-1.8.9.custom.css" rel="Stylesheet" />
        <?php
        echo get_head("Greenhouse Development Rights online calculator", array(array('href' => "css/gdrscalc.css", 'media' => "all"), array('href' => "css/tablesorter.css", 'media' => "all")));
        ?>
        <!--[if IE 6]>
            <link href="css/ie6.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <![endif]-->
        <!--[if IE 7]>
            <link href="css/ie7.css" media="screen, projection" rel="stylesheet" type="text/css" />
        <![endif]-->
        <!--<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.3.2/jquery.min.js"></script>-->
        <script type="text/javascript" src="js/jquery-1.4.4.min.js"></script>
        <script type="text/javascript" src="js/jquery-ui-1.8.9.custom.min.js"></script>
        <script type="text/javascript" src="js/jquery.tablesorter.js"></script>
        <script type="text/javascript" src="js/calc.js"></script>
    </head>
    <body id="gdrs_calculator">
           <div id="loading"></div>
        <?php echo get_navigation(); ?>
        <div id="calc_container" class="group">
            <form action="" method="post" name="form1" id="form1" class="group">

                <fieldset id="basic_adv">
                    <legend class="open"><span>&nbsp;</span>Select basic/advanced view</legend>
                    <div>
                        <ul>
                            <li>
                                <input type="radio" name="basic_adv" id="basic" class="click" value="basic"
                                <?php
                                if ($display_params["basic_adv"]['value'] == "basic") {
                                    echo 'checked="checked"';
                                    $advanced = false;
                                }
                                ?> />
                                <label for="basic" class="click radio">Basic</label>
                                <input type="radio" name="basic_adv" id="adv" class="click" value="adv"
                                <?php
                                if ($display_params["basic_adv"]['value'] == "adv") {
                                    echo 'checked="checked"';
                                    $advanced = true;
                                }
                                ?> />
                                <label for="adv" class="click radio">Advanced</label>
                            </li>
                        </ul>&nbsp;
                        <!-- DO NOT REMOVE the &nbsp; after the last element within each of the divs enclosing everything after legend in fieldsets.
                        A jQuery bug (?) breaks the .hide part of .toggle when the &nbsp; is not there -->
                    </div>
                </fieldset>

                <fieldset id="region_country_filter">
                    <legend class="open"><span>&nbsp;</span>Select regions and countries</legend>
                    <div>
                        <ul>
                            <li>Current list<span style=" float: right;"><button type="button" onclick="$('#filterDiv').dialog('open');">Edit list</button></span></li>
                            <li><select id="current_list" multiple="multiple" size="5" style="width:100%"></select></li>
                        </ul>&nbsp;
                    </div>
                </fieldset>

                <fieldset id="display_params">
                    <legend class="open"><span>&nbsp;</span>Display settings</legend>
                    <div>
                        <ul>
                            <?php echo select_options_list('table_view', $display_params, "Table view: ", $advanced); ?>
                            <?php echo select_num('display_yr', $display_params, "Year to display:", $advanced); ?>
                            <?php echo select_num('decimal_pl', $display_params, "Decimal places:", $advanced); ?>
                            </ul>&nbsp;
                        </div>
                    </fieldset>

                    <fieldset id="calc_params">
                        <legend class="open"><span>&nbsp;</span>Calculator settings</legend>
                        <div>
                            <ul>
                            <?php echo select_options_list('emergency_path', $shared_params, "Emergency pathway: ", $advanced); ?>
                                <li>
                                    <label for="baseline" class="select">Baseline: </label>
                                    <select name="baseline" id="baseline">
                                        <!-- TODO: Make this dynamic -->
                                        <option selected="selected" value="default_gdrs">Default</option>
                                    </select>
                                </li>
                                <div id="cum_since_yr_wrapper">
                            <?php echo select_num('cum_since_yr', $shared_params, "Cumulative since:", $advanced); ?>
                                </div>
                                <li>
                                    <input type="checkbox" name="use_lulucf" id="use_lulucf" class="click" value="1" <?php if ($shared_params["use_lulucf"]['value'])
                                    echo 'checked="checked"'; ?>  />
                                <label for="use_lulucf" class="click"> Include land-use emissions</label>
                            </li>
                            <li>
                                <input type="checkbox" name="use_nonco2" id="use_nonco2" class="click" value="1" <?php if ($shared_params["use_nonco2"]['value'])
                                           echo 'checked="checked"'; ?>  />
                                <label for="use_nonco2" class="click"> Include non-CO2 gases</label>
                            </li>
                            <li>
                                <input type="checkbox" name="use_netexports" id="use_netexports" class="click" value="1" <?php if ($shared_params["use_netexports"]['value'])
                                           echo 'checked="checked"'; ?>  />
                                <label for="use_netexports" class="click"> Include emissions embodied in trade</label>
                            </li>
                            <?php
                                       if ($display_params['framework']['value'] === 'gdrs') {
                                           echo select_num('dev_thresh', $fw_params, "Development threshold:", $advanced);
                                           echo select_num('lux_thresh', $fw_params, "Luxury threshold:", $advanced);
                                           if ($advanced) {
                                               echo "<li>";
                                               echo '<input type="checkbox" name="do_luxcap" id="do_luxcap" class="click" value="1" ' . ($fw_params["do_luxcap"]['value'] ? 'checked="checked"' : '') . '/>';
                                               echo '<label for="do_luxcap" class="click"> Cap baselines at luxury threshold</label>';
                                               echo "</li>";
//                                               echo "<li>";
//                                               echo '<input type="checkbox" name="interp_btwn_thresh" id="use_sequencing" class="click" value="1" ' . ($shared_params["interp_btwn_thresh"]['value'] ? 'checked="checked"' : '') . '/>';
//                                               echo '<label for="interp_btwn_thresh" class="click"> Progressive between thresholds</label>';
//                                               echo "</li>";
                                               echo "<li>";
                                               echo '<fieldset><legend class="closed"><span>&nbsp;</span>Income toward capacity</legend>';
                                               echo "<div><ul>";
                                               echo "<li><label>Below devt threshold: </label>0%</li>";
                                               echo select_num('mid_rate', $fw_params, "% between thresholds:", $advanced);
                                               echo "<li><label>Above luxury threshold: </label>100%</li>";
                                               echo "</ul>&nbsp;</div>";
                                               echo "</fieldset>";
                                               echo "</li>";
                                           }
                                           // echo select_num('tax_income_level', $fw_params, "Income level for tax:",$advanced);
                                           echo select_num('r_wt', $fw_params, "Responsibility weight:", $advanced);
                                       }
                                       echo select_num('percent_gwp', $shared_params, "Total cost as % GWP:", $advanced);
                                       echo select_num('em_elast', $shared_params, "Emissions elasticity:", $advanced);
                                       if ($advanced) {
                                           // Later, other frameworks may use these. But right now only GDRs
                                           if ($display_params['framework']['value'] === 'gdrs') {
                                               echo "<li><fieldset>";
                                               echo '<legend class="closed"><span>&nbsp;</span>Sequencing</legend>';
                                               echo '<div><ul id="sequencing">';
                                               echo "<li>";
                                               echo '<input type="checkbox" name="use_sequencing" id="use_sequencing" class="click" value="1" ' . ($shared_params["use_sequencing"]['value'] ? 'checked="checked"' : '') . '/>';
                                               echo '<label for="use_sequencing" class="click"> Use sequencing</label>';
                                               echo "</li>";
                                               echo select_num('percent_a1_rdxn', $shared_params, "A1 reduction %:", $advanced);
                                               echo select_num('base_levels_yr', $shared_params, "Sequencing base yr:", $advanced);
                                               echo select_num('end_commitment_period', $shared_params, "End of period:", $advanced);
                                               echo select_num('a1_smoothing', $shared_params, "A1 smoothing:", $advanced);
                                               echo "<li>";
                                               echo '<p>Mitigation requirement gap borne by: </p><ul><li>';
                                               echo '<input type="radio" name="mit_gap_borne" id="annex1" class="click" value="1" ' . ($shared_params["mit_gap_borne"]['value'] == "1" ? 'checked="checked"' : '') . "/>";
                                               echo '<label for="annex1" class="click radio"> Annex 1</label>';
                                               echo '<input type="radio" name="mit_gap_borne" id="annex2" class="click" value="2" ' . ($shared_params["mit_gap_borne"]['value'] == "2" ? 'checked="checked"' : '') . "/>";
                                               echo '<label for="annex2" class="click radio"> Annex 2</label>';
                                               echo '</li></ul></li><ul>&nbsp;<!-- end #sequencing -->';
                                               echo '</div></fieldset></li>';
                                           }
                                       }
                            ?>
                                   </ul>&nbsp;
                               </div>
                           </fieldset>

                           <input type="hidden" id="user_db" name="user_db" value="<?php echo $user_db; ?>" />
                           <input type="submit" name="submit" id="submit" class="click" value="calculate" /> <!-- TODO: add validation -->
                           <input type="submit" name="reset" id="reset" class="click" value="reset to default values" />
                       </form>

                       <div id="filterDiv" style=" display: none;">
                           <p style="text-align: left;"><select id="regionList" onchange="changeRegionList();"></select></p>
                           <table>
                               <tr>
                                   <td>
                                       <label for="country_available">Available Countries:</label><br/>
                                       <select style="width:250px"  id="country_available" size="5">

                                       </select>
                                   </td>
                                   <td style="vertical-align:middle">
                                       <p><button class="button" name="btnAdd" type="button"  style="width: 120px" onclick="moveElement('country_available','country_selected');">Add >></button></p>
                                       <p><button class="button" name="btnRemove" type="button" style="width: 120px" onclick="moveElement('country_selected','country_available');"><< Remove</button></p>
                                   </td>
                                   <td>
                                       <label for="country_selected">Selected Countries:</label><br/>
                                       <select style="width:250px"  id="country_selected" size="5">

                                       </select>
                                   </td>
                               </tr>
                           </table>

                       </div>
                       <div id="intro" class="group">
                <?php /* ?><!--<p><?php print_r($shared_params); ?></p>
                                         <p><?php print_r($fw_params); ?></p>
                                         <p><?php print_r($display_params); ?></p>--><?php */ ?>
                                       <p>Welcome to the Greenhouse Development Rights Calculator. Use the controls to the left to change the
                                           parameters in the GDRs framework and see the implications for the Responsibility and Capacity Index (RCI) and other indicators.</p>
                                       <div id="save">
                    <?php
                                       if ($display_params['framework']['value'] === 'gdrs') {
                                           echo '<p><a href="tables/download_tabsep.php?db=' . $user_db . '">Download complete Excel table</a></p>';
                                           # echo '<p><a href="viz/test.php?db=' . $user_db . '">Google visualization test</a></p>';
                                       }
                    ?>
                                   </div><!-- /save -->
                                   <!--<p><?php /* ?><?php print_r(Framework::get_frameworks()); ?><?php */ ?></p>-->
                               </div><!-- /intro -->
                               <div id="data">
                <?php
                                        if (isset($_COOKIE['db']) && !$up_to_date) {
                                            echo '<p class="alert">The calculator or database has been updated since you last visited. Your settings are being reset.</p>';
                                        }
                                       echo generate_table($display_params, $fw_params, $shared_params, $table_views, $user_db);
                                       // include("tables/sample_table.php");
                ?>
                                   </div><!-- end #data -->
                               </div><!-- end #calc_container -->
        <?php echo get_footer(Framework::get_data_ver(), Framework::get_calc_ver()); ?>
    </body>
</html>