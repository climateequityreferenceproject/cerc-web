<?php
if (isset($_GET['debug']) && $_GET['debug'] == 'yes') {
    ini_set('display_errors',1); 
    error_reporting(E_ALL);
}

include("core.php");
include("boilerplate.php");
include("form_functions.php");
$table_view_default = $display_params['table_view']['value'];
if (isset($_GET['iso3'])) {
    $display_params['display_ctry']['value'] = $_GET['iso3'];
    $display_params['table_view']['value'] = 'gdrs_country_report';
}
if (isset($_GET['year'])) {
    $display_params['display_yr']['value'] = $_GET['year'];
}
if (isset($_GET['show_avail_params']) && $_GET['show_avail_params'] === 'yes') {
    $show_avail_params = true;
} else {
    $show_avail_params = false;
}
if (isset($_POST['equity_cancel']) || isset($_POST['equity_cancel_top']) || isset($_POST['equity_submit']) || isset($_POST['equity_submit_top']) || (isset($_GET['equity']) && $_GET['equity'] === 'default')) {
    $equity_nosplash = true;
} else {
    $equity_nosplash = false;
}
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
           <?php
           if (!$equity_nosplash) {
               echo '<div id="lightbox"></div>';
               echo '<div id="equity_settings_container">';
               include("tables/equity_settings_panel.php");
               echo '</div>';
           }
           ?>
           <div id="calc_container" class="group">
            <form action="" method="post" name="form1" id="form1" class="group">

                <fieldset id="region_country_filter">
                    <legend class="open"><span>&nbsp;</span><?php echo _("Select regions and countries");?></legend>
                    <div>
                        <ul>
                            <li>Current list<span id="country_list_button"><button type="button">Edit list</button></span></li>
                            <li><select id="current_list" multiple="multiple" size="5" style="width:100%"></select></li>
                        </ul>&nbsp;
                    </div>
                </fieldset>

                <fieldset id="display_params">
                    <legend class="open"><span>&nbsp;</span><?php echo _("Display settings");?></legend>
                    <div>
                        <ul>
                            <?php echo select_options_list('table_view', $display_params, _("Table view: ")); ?>
                            <?php echo select_num('display_yr', $display_params, _("Year to display:")); ?>
                            <?php
                                // Choose country or region to display for country report
                                echo '<li><label for="display_ctry" class="select" title="' . _("Country or region to display for country report") . '">' . _("Country or region to display:") . '</label>';
                                echo '<select name="display_ctry" id="display_ctry" action="index.php">';
                                $valid_countryregion = false;
                                foreach ($region_list as $item) {
                                    $selected = '';
                                    if ($item['region_code'] === $display_params['display_ctry']['value']) {
                                        $selected = ' selected="selected"';
                                        $valid_countryregion = true;
                                    }
                                    echo '<option value="' . $item['region_code'] .  '"' . $selected . '>' . $item['name'] . '</option>';
                                }
                                foreach ($country_list as $item) {
                                    $selected = '';
                                    if ($item['iso3'] === $display_params['display_ctry']['value']) {
                                        $selected = ' selected="selected"';
                                        $valid_countryregion = true;
                                    }
                                    echo '<option value="' . $item['iso3'] .  '"' . $selected . '>' . $item['name'] . '</option>';
                                }
                                echo '</select></li>';
                                // Simply ignore any invalid country or region code
                                if (!$valid_countryregion) {
                                    $display_params['display_ctry']['value'] = null;
                                    $display_params['table_view']['value'] = $table_view_default;
                                }
                            ?>
                            
                            <?php echo select_num('decimal_pl', $display_params, _("Decimal places:")); ?>
                            </ul>&nbsp;
                        </div>
                    </fieldset>

                    <fieldset id="calc_params">
                        <legend class="open"><span>&nbsp;</span>Calculator settings</legend>
                        <div>
                            <ul>
                            <?php echo select_options_list('emergency_path', $shared_params, $glossary->getLink('gloss_path', false, _("Global mitigation pathway")) . ": "); ?>
                                <!--<li>
                                    <label for="baseline" class="select">Baseline: </label>
                                    <select name="baseline" id="baseline">
                                        <option selected="selected" value="default_gdrs">Default</option>
                                    </select>
                                </li>-->
                                <div id="cum_since_yr_wrapper">
                            <?php echo select_num('cum_since_yr', $shared_params, $glossary->getLink('cum_respons', false, _('Cumulative since')) . ": "); ?>
                                </div>
                                <li>
                                    <input type="checkbox" name="use_lulucf" id="use_lulucf" class="click" value="1" <?php if ($shared_params["use_lulucf"]['value'])
                                    echo 'checked="checked"'; ?>  />
                                <label for="use_lulucf" class="click"> <?php echo sprintf(_('Include %s'), $glossary->getLink('lu_emissions', false, _('land-use emissions'))); ?></label>
                            </li>
                            <li>
                                <input type="checkbox" name="use_nonco2" id="use_nonco2" class="click" value="1" <?php if ($shared_params["use_nonco2"]['value'])
                                           echo 'checked="checked"'; ?>  />
                                <label for="use_nonco2" class="click"> <?php echo sprintf(_('Include %s'), $glossary->getLink('non_co2_gases', false, _('non-CO<sub>2</sub> gases')));?></label>
                            </li>
                            <li>
                                <input type="checkbox" name="use_netexports" id="use_netexports" class="click" value="1" <?php if ($shared_params["use_netexports"]['value'])
                                           echo 'checked="checked"'; ?>  />
                                <label for="use_netexports" class="click"> <?php echo sprintf(_('Include %s'), $glossary->getLink('embodied_emissions', false, _('emissions embodied in trade'))); ?></label>
                            </li>
                            <?php
                                       echo select_num('r_wt', $fw_params, $glossary->getLink('r_weight', false, _('Responsibility weight')) . ":");
                                       echo select_num('percent_gwp_MITIGATION', $shared_params,$glossary->getLink('mit_cost', false, _('Mitigation cost as % GWP')) . ":");
                                       echo select_num('percent_gwp_ADAPTATION', $shared_params,$glossary->getLink('adapt_cost', false, _('Adaptation cost as % GWP')) . ":");
                                       // Progressivity
                                       echo '<li class="advanced"><fieldset class="progressivity">';
                                       echo '<legend class="closed"><span>&nbsp;</span>' . $glossary->getLink('progressivity', false, _('Progressivity')) . '</legend>';
                                       echo '<ul>';
                                       echo "<li>";
                                       echo select_num('dev_thresh', $fw_params, $glossary->getLink('gloss_dev_threshold', false, _('Development threshold ($PPP)')) . ":");
                                       echo "</li>";
                                       echo '<li class="advanced">';
                                       echo '<input type="checkbox" name="interp_btwn_thresh" id="interp_btwn_thresh" class="click" value="1" ' . ($fw_params["interp_btwn_thresh"]['value'] ? 'checked="checked"' : '') . '/>';
                                       echo '<label for="interp_btwn_thresh" class="click"> ' . _("Progressive between thresholds") . '</label>';
                                       echo "</li>";
                                       echo "<li>";
                                       echo select_num('lux_thresh', $fw_params, $glossary->getLink('lux_threshold', false, _('Luxury threshold ($MER)')) . ":");
                                       echo "</li>";
                                       echo "<li>";
                                       echo select_num('luxcap_mult', $fw_params, $glossary->getLink('lux_multiplier', false, _('Multiplier on incomes above the luxury threshold')) . ":");
                                       echo "</li>";
                                       echo "<li>";
                                       echo select_num('em_elast', $shared_params, $glossary->getLink('emiss_elast', false, _('Emissions elasticity')) . ":");
                                       echo "</li>";
                                       echo '</ul></fieldset></li>';

                                       // Kyoto obligations
                                       echo '<li class="advanced"><fieldset>';
                                       echo '<legend class="closed"><span>&nbsp;</span>' . $glossary->getLink('gloss_kyoto', false, _('Kyoto obligations')) . '</legend>';
                                       echo '<div><ul id="kab">';
                                       echo "<li>";
                                       echo '<input type="radio" name="use_kab_radio" id="use_kab" class="click" value="use_kab" ' . ($fw_params["use_kab"]['value'] && !$fw_params["kab_only_ratified"]['value'] ? 'checked="checked"' : '') . '/>';
                                       echo '<label for="use_kab" class="click"> ' . _("Include Kyoto obligations") . '</label>';
                                       echo "</li>";
                                       echo "<li>";
                                       echo '<input type="radio" name="use_kab_radio" id="dont_use_kab" class="click" value="dont_use_kab" ' . (!$fw_params["use_kab"]['value'] ? 'checked="checked"' : '') . '/>';
                                       echo '<label for="use_kab" class="click"> ' . _("Exclude Kyoto obligations") . '</label>';
                                       echo "</li>";
                                       echo "<li>";
                                       echo '<input type="radio" name="use_kab_radio" id="kab_only_ratified" class="click" value="kab_only_ratified" ' . ($fw_params["use_kab"]['value'] && $fw_params["kab_only_ratified"]['value'] ? 'checked="checked"' : '') . '/>';
                                       echo '<label for="kab_only_ratified" class="click"> ' . _("Exclude for only US &#38; Canada") . '</label>';
                                       echo "</li>";
                                       echo '</li><ul>&nbsp;</div><!-- end #kab -->';
                                       echo '</fieldset></li>';
                                       
                                       // Mitigation smoothing
                                       echo '<li class="advanced"><fieldset>';
                                       echo '<legend class="closed"><span>&nbsp;</span>' . $glossary->getLink('mit_lag', false, _('Mitigation smoothing')) . '</legend>';
                                       echo '<ul>';
                                       echo '<li>';
                                       if ($shared_params['use_mit_lag']['value']) {
                                           $checked_string = 'checked="checked"';
                                       } else {
                                           $checked_string = '';
                                       }
                                       echo '<input type="checkbox" name="use_mit_lag" id="use_mit_lag" class="click" value="1" ' . $checked_string . '  />';
                                       echo '<label for="use_mit_lag">Average the mitigation-period RCI</label>';
                                       echo '</li>';
                                       echo '</ul>';
                                       echo '</fieldset></li>';
                            ?>
                                   </ul>&nbsp;
                               </div>
                           </fieldset>

                           <input type="hidden" id="user_db" name="user_db" value="<?php echo $user_db; ?>" />
                           <input type="submit" name="submit" id="submit" class="click" value="<?php echo _("calculate") ?>" /> <!-- TODO: add validation -->
                           <input type="submit" name="reset" id="reset" class="click" value="<?php echo _("reset to default values") ?>" />
                       </form>

                       <div id="filterDiv">
                           <p><select id="regionList"></select></p>
                           <table>
                               <tr>
                                   <td>
                                       <label for="country_available"><?php echo _("Available Countries:")?></label><br/>
                                       <select id="country_available" size="5" multiple="multiple" />
                                   </td>
                                   <td class="button_btwn_list">
                                       <p><button class="button" name="btnAdd" id="btnAdd" type="button"><?php echo _("Add") ?> &gt;&gt;></button></p>
                                       <p><button class="button" name="btnRemove" id="btnRemove" type="button">&lt;&lt; <?php echo _("Remove") ?></button></p>
                                   </td>
                                   <td>
                                       <label for="country_selected"><?php echo _("Selected Countries:") ?></label><br/>
                                       <select id="country_selected" size="5" multiple="multiple" />
                                   </td>
                               </tr>
                           </table>

                       </div>
                       <div id="intro" class="group">
                <?php /* ?><!--<p><?php print_r($shared_params); ?></p>
                                         <p><?php print_r($fw_params); ?></p>
                                         <p><?php print_r($display_params); ?></p>--><?php */ ?>
                                       <p><?php
                                       $welcome_string = _('Welcome to the Greenhouse Development Rights Calculator. Use the controls to the left to change the parameter values and see the implications for national obligations. See <a href="http://gdrights.org/about/">About Greenhouse Development Rights</a> for more information.');
//                                       $welcome_string .= ' ' . sprintf(_('Use the controls to the left to change the parameters in the %1$s and see the implications for the %2$s and other indicators.'), $glossary->getLink('def_gdrs', false, _('GDRs framework')), $glossary->getLink('gloss_rci', false, _('Responsibility and Capacity Index (RCI)')));
                                       echo $welcome_string;
                                       ?></p>
                                       <div id="save">
                    <?php
                                       if ($display_params['framework']['value'] === 'gdrs') {
                                           echo '<p><a href="tables/download_tabsep.php?db=' . $user_db . '">' . _("Download complete Excel table") . '</a></p>';
                                           # echo '<p><a href="viz/test.php?db=' . $user_db . '">Google visualization test</a></p>';
                                       }
                    ?>
                                   </div><!-- /save -->
                                   <!--<p><?php /* ?><?php print_r(Framework::get_frameworks()); ?><?php */ ?></p>-->
                               </div><!-- /intro -->

                               <div id="data">
                                <!-- get parameters as a table -->
                                <?php
                                if ($show_avail_params ) {
                                    echo '<div id="param_props"><table><thead>';
                                    echo '<th class="lj">parameter (<em>name in database</em>)</th>';
                                    echo '<th>current value</th>';
                                    echo '<th class="lj">description</th>';
                                    echo '</thead>';
                                    echo '<tbody>';
                                    $all_params = array_merge($shared_params, $fw_params);
                                    ksort($all_params);
                                    foreach ($all_params as $param => $props) {
                                        if (!is_null($props['db_param']) && !Framework::is_sequencing($props['db_param'])) {
                                         echo '<tr>';
                                         echo '<td class="lj"><strong>' . $param . '</strong> (<em>' . $props['db_param'] . '</em>)' . '</td>';
                                         echo '<td>' . $props['value'] . '</td>';
                                         echo '<td class="lj">' . $props['description'] . '</td>';
                                         echo '</tr>';
                                        }
                                    }
                                    echo '</tbody></table></div>';
                                }
                                 ?>
                               
                                    <?php
                                        if (isset($_COOKIE['db']) && !$up_to_date) {
                                            echo '<p class="alert">' . _("The calculator or database has been updated since you last visited. Your settings have been reset.") . '</p>';
                                        }
                                     ?>
                                
                                    <div id="calc_parameters">
                                       <?php echo generate_params_table($display_params, $fw_params, $shared_params, $country_list, $region_list, $table_views); ?>
                                    </div><!-- end #calc_parameters -->

                                    <div id="calc_results">
                                       <?php echo generate_results_table($display_params, $shared_params, $country_list, $region_list, $user_db); 
                                        // include("tables/sample_table.php");
                                       ?>
                                    </div><!-- end #calc_results -->
                               </div><!-- end #data -->
                           </div><!-- end #calc_container -->
                           <div id="popup"></div><!-- help #popup window -->
                           
        <?php echo get_footer(Framework::get_data_ver(), Framework::get_calc_ver()); ?>
    </body>
</html>