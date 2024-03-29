<?php
if (isset($_GET['debug']) && $_GET['debug'] == 'yes') {
    ini_set('display_errors',1);
    error_reporting(E_ALL);
}
// implements maintenance mode - check _maintenance-off file for details and usage
if (file_exists("_maintenance-on")) {
    include("_maintenance-on");
    exit();
}

function microtime_float() {
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function display_ctry_dropdown($display_params, $region_list, $country_list, $param_name, $title, $label, $allow_empty_option = FALSE) {
    $retval['html']  = '<label for="' . $param_name . '" class="select" title="' . $title . '">' . $label . '</label>';
    $retval['html'] .= '<select name="' . $param_name . '" id="' . $param_name . '" action="index.php">';
    $retval['valid_countryregion'] = false;
    $optionslist = "";
    foreach ($region_list as $item) {
        $selected = '';
        if ($item['region_code'] === $display_params[$param_name]['value']) {
            $selected = ' selected="selected"';
            $retval['valid_countryregion'] = true;
        }
        $optionslist .= '<option value="' . $item['region_code'] .  '"' . $selected . '>' . $item['name'] . '</option>';
    }
    foreach ($country_list as $item) {
        $selected = '';
        if ($item['iso3'] === $display_params[$param_name]['value']) {
            $selected = ' selected="selected"';
            $retval['valid_countryregion'] = true;
        }
        $optionslist .= '<option value="' . $item['iso3'] .  '"' . $selected . '>' . $item['name'] . '</option>';
    }
    if ((!$retval['valid_countryregion']) & ($allow_empty_option)) {
        $retval['html'] .= '<option value="" selected="selected"></option>';
    }
    $retval['html'] .= $optionslist;
    $retval['html'] .= '</select>';

    return $retval;
}

include("core.php");
include("form_functions.php");
$table_view_default = $display_params['table_view']['value'];
if (isset($_GET['iso3'])) {
    $display_params['display_ctry']['value']   = explode(",", $_GET['iso3'])[0];
    $display_params['display_ctry_2']['value'] = explode(",", $_GET['iso3'])[1];
    $display_params['display_ctry_3']['value'] = explode(",", $_GET['iso3'])[2];
    $display_params['display_ctry_4']['value'] = explode(",", $_GET['iso3'])[3];
    $display_params['table_view']['value'] = 'gdrs_country_report';
} else {
    $display_params['display_ctry']['value'] = Framework::get_world_code();
    $display_params['table_view']['value'] = 'gdrs_country_report';
}
if (isset($_GET['year'])) {
    $display_params['display_yr']['value'] = $_GET['year'];
}
if (isset($_REQUEST['show_avail_params']) && $_REQUEST['show_avail_params'] === 'yes') {
    $show_avail_params = true;
} else {
    $show_avail_params = false;
}

$equity_nosplash = false;
$equity_nosplash = $equity_nosplash || isset($_REQUEST['equity_cancel']);
$equity_nosplash = $equity_nosplash || isset($_POST['equity_cancel_top']);
$equity_nosplash = $equity_nosplash || isset($_POST['equity_submit']);
$equity_nosplash = $equity_nosplash || isset($_POST['equity_submit_top']);
$equity_nosplash = $equity_nosplash || (isset($_GET['equity']) && $_GET['equity'] === 'default');
$equity_nosplash = $equity_nosplash || isset($_GET['iso3']);

if ((isset($_REQUEST['download'])) || (isset($_REQUEST['dl']))) {
    $url  = (Framework::is_dev()) ? $URL_calc_dev : $URL_calc;
    $url .= "tables/download_tabsep.php?" . $_SERVER['QUERY_STRING'] . "&db=" . basename($user_db);
    header('Location: ' . $url, true, 303);
    die();
}

if (is_file('inc/popup_notice.php')) {
    require_once('inc/popup_notice.php');
    $popup_code = get_popup_code();
}

?>
<!DOCTYPE html>
    <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>Climate Equity Reference Calculator</title>
    <meta name="description" content="">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <link rel="shortcut icon"  href="img/favicon_32.ico" />
    <link rel="stylesheet" href="css/cescalc.css?v=1.3">
    <link rel="stylesheet" href="css/tablesorter.css?v=1.0">
    <link rel="stylesheet" href="css/smoothness/jquery-ui-1.8.9.custom.css?v=1.0" />
    <!--[if IE 6]>
        <link href="css/ie6.css" media="screen, projection" rel="stylesheet" type="text/css" />
    <![endif]-->
    <!--[if IE 7]>
        <link href="css/ie7.css" media="screen, projection" rel="stylesheet" type="text/css" />
    <![endif]-->

    <script type="text/javascript" src="//code.jquery.com/jquery-1.12.3.min.js"></script>
    <script type="text/javascript" src="//code.jquery.com/ui/1.9.2/jquery-ui.min.js"></script>
    <!--<script type="text/javascript" src="js/jquery-1.6.4.min.js"></script>-->
    <!--<script type="text/javascript" src="js/jquery-ui-1.8.9.custom.min.js?v=1.0"></script>-->
    <script type="text/javascript" src="js/jquery.tablesorter.js?v=1.0"></script>
    <script type="text/javascript" src="js/calc.js?v=1.3c"></script>
    <script type="text/javascript" src="graphs/graph_interactivity.js?v=1.0"></script>
    <?php include("inc/googleanalytics.php"); ?>
    </head>
    <body id="gdrs_calculator">
        <?php if(isset($popup_code)) { echo ($popup_code); } ?>
        <div id="loading"></div>
        <div id="container">
        <?php include("inc/calc_branding.inc.php"); ?>
        <?php include("inc/calc_nav_menu_main.inc.php"); ?>
        <?php
        if (!$equity_nosplash) {
           echo '<div id="lightbox"></div>';
           echo '<div id="equity_settings_container">';
           include("tables/equity_settings_panel.php");
           echo '</div>';
        }
        ?>
        <div id="calc_container" class="group">
            <?php include("inc/banner.php"); // includes one or several dismissable banner(s) ?>
            <?php
            if (Framework::is_dev() || (Framework::user_is_developer())) {
                if (!(Framework::is_dev())) { echo '<div style="background-color:lightgreen;">This is the public calculator with some developer tools activated because I think you are a developer (there is a cookie on your computer that tells me that you have access to the developer version of the calculator)</div>'; }
                echo '<!-- The data encoding type, enctype, MUST be specified as below -->';
                echo '<form enctype="multipart/form-data" action="" name="upload_form" method="POST">';
                echo '    <!-- MAX_FILE_SIZE must precede the file input field -->';
                echo '    <input type="hidden" name="MAX_FILE_SIZE" value="8388608" />'; // This is 8 MB
                echo '    <!-- Name of input element determines name in $_FILES array -->';
                echo '    SQLite3 database: <input name="upload_db" type="file" />';
                echo '    and then <input type="submit" value="upload" />';
                echo '    or <input type="submit" value="reset" />';
                echo '    (Current database: ' . basename($user_db) . ')';
                echo '</form>';
            }
            ?>
            <form action="" method="post" name="form1" id="form1" class="group">

                <fieldset id="region_country_filter">
                    <legend class="open"><span>&nbsp;</span><?php echo _("Select regions and countries");?></legend>
                        <ul class="group">
                            <li>Current list<span id="country_list_button"><button type="button">Edit list</button></span></li>
                            <li><select id="current_list" multiple="multiple" size="5" style="width:100%"></select></li>
                        </ul>
                </fieldset>

                <fieldset id="display_params">
                    <legend class="open"><span>&nbsp;</span><?php echo _("Display settings");?></legend>
                    <ul class="group">
                        <li>
                            <?php echo select_options_list('table_view', $display_params, _("Table view: ")); ?>
                        </li>
                        <li>
                            <?php
                                // Choose country or region to display for country report
                                $val = display_ctry_dropdown($display_params, $region_list, $country_list, "display_ctry", _("Country or region to display for country report"), _("Country or region to display:"));
                                echo $val['html'];
                                // Simply ignore any invalid country or region code
                                if (!$val['valid_countryregion']) {
                                    $display_params['display_ctry']['value'] = null;
                                    $display_params['table_view']['value'] = $table_view_default;
                                }
                            ?>
                        </li>
                        <li>
                            <?php echo select_num('display_yr', $display_params, _("Year to display:")); ?>
                        </li>
                        <li>
                            <?php echo select_num('reference_yr', $display_params, _("Base Year for table:")); ?>
                        </li>
                        <li class="advanced">
                            <fieldset class="country_report_advanced" id="country_report_advanced">
                            <legend class="closed"><span>&nbsp;</span>Advanced Display Settings</legend>
                            <ul class="group">
                                <li>
                                    <?php
                                    echo select_options_list('graph_range', $display_params, _("Year range for chart:"));
                                    ?>
                                </li>
                                <li>
                                    <?php echo display_ctry_dropdown($display_params, $region_list, $country_list, "display_ctry_2", "Comparison country/region 1", "Comparison country/region 1", TRUE)['html']; ?>
                                </li>
                                <li>
                                    <?php echo display_ctry_dropdown($display_params, $region_list, $country_list, "display_ctry_3", "Comparison country/region 2", "Comparison country/region 2", TRUE)['html']; ?>
                                </li>
                                <li>
                                    <?php echo display_ctry_dropdown($display_params, $region_list, $country_list, "display_ctry_4", "Comparison country/region 3", "Comparison country/region 3", TRUE)['html']; ?>
                                </li>
                            </ul>
                        </li>
                        <?php // for now, we only want LULUCF selection for country report in the _dev version
                        if (Framework::is_dev()) { ?>
                        <li>
                            <?php
                            // echo select_options_list('display_gases', $display_params, _("Gases to display:"), "select", _("Gases/sectors to include in the pledge assessments in the country report. This setting is independent from the inclusion of non-CO2 and LULUCF in the calculation of responsibility (below)."));
                            echo select_options_list('display_gases', $display_params, _("Include LULUCF in report:"), "select", _("Select whether you want to include LULUCF in the chart and tables in the country report. This setting is independent from the inclusion of LULUCF in the calculation of responsibility (below)."));
                            ?>
                        </li>
                        <?php } ?>
                        <li>
                            <?php echo select_num('decimal_pl', $display_params, _("Decimal places:")); ?>
                        </li>
                        <?php // for now, we only want advanced chart settings in the -dev calculator
                        if (Framework::is_dev()) { ?>
                        <li class="advanced">
                            <fieldset class="ch_settings" id="ch_settings">
                            <legend class="closed"><span>&nbsp;</span>Ceecee's Play Area</legend>
                            <ul class="group">
                                <li>
                                    <button onclick="toggledisplay_by_class('physical')">toggle</button>
                                </li>
                                <li>
                                    <button onclick="toggledisplay_by_class('physical', 'show')">on</button>
                                </li>
                                <li>
                                    <button onclick="toggledisplay_by_class('physical', 'hide')">off</button>
                                </li>
                            </ul>
                        </li>
                        <?php } ?>
                    </ul>
                </fieldset>

                <fieldset id="calc_params">
                    <legend class="open"><span>&nbsp;</span>Calculator settings</legend>
                    <ul class="group">
                        <li>
                            <?php echo select_options_list('emergency_path', $shared_params, $glossary->getLink('gloss_path', false, _("Global mitigation pathway")) . ": "); ?>
                            <!--<li>
                                <label for="baseline" class="select">Baseline: </label>
                                <select name="baseline" id="baseline">
                                    <option selected="selected" value="default_gdrs">Default</option>
                                </select>
                            </li>-->
                        </li>

                        <li class="advanced"><fieldset class="responsibility">
                        <legend class="open"><span>&nbsp;</span><?php echo $glossary->getLink('gloss_responsibility', false, _('Responsibility')); ?></legend>
                        <ul class="group">
                            <li id="cum_since_yr_wrapper">
                                <?php echo select_num('cum_since_yr', $shared_params, $glossary->getLink('cum_respons', false, _('Cumulative since')) . ": "); ?>
                            </li>
                            <li>
                                <input type="checkbox" name="use_nonco2" id="use_nonco2" class="click" value="1" <?php if ((int) $shared_params["use_nonco2"]['value'] === 1)
                               echo 'checked="checked"'; ?>  />
                                <label for="use_nonco2" class="click"> <?php echo sprintf(_('Include %s'), $glossary->getLink('non_co2_gases', false, _('non-CO<sub>2</sub> gases')));?></label>
                            </li>
                            <!--- we don't support LULUCF anymore but will show the unchecked checkbox to indirectly communicate that decision -->
                            <li>
                                <input type="checkbox" name="use_lulucf" id="use_lulucf" class="click" value="1" disabled
                                     <?php //if ((int) $shared_params["use_lulucf"]['value'] === 1) //echo 'checked="checked"'; ?>  />
                                <label for="use_lulucf" class="click" style="color:grey !important;"><?php echo sprintf(_('Include %s'), $glossary->getLink('lu_emissions', false, _('land-use emissions'))); ?></label>
                            </li>
                            <li>
                                <input type="checkbox" name="use_netexports" id="use_netexports" class="click" value="1" <?php if ((int) $shared_params["use_netexports"]['value'] === 1)
                               echo 'checked="checked"'; ?>  />
                                <label for="use_netexports" class="click"> <?php echo sprintf(_('Include %s'), $glossary->getLink('embodied_emissions', false, _('emissions embodied in trade'))); ?></label>
                            </li>
                            <li>
                                    <?php echo select_num('r_wt', $fw_params, $glossary->getLink('r_weight', false, _('Responsibility weight')) . ":"); ?>
                            </li>
                        </ul>
                        </fieldset>
                        </li>

                        <?php
                            // Progressivity
                            echo '<li class="advanced"><fieldset class="progressivity">';
                            echo '<legend class="closed"><span>&nbsp;</span>' . $glossary->getLink('progressivity', false, _('Progressivity')) . '</legend>';
                            echo '<ul class="group">';
                            echo "<li>";
                            echo select_num('dev_thresh', $fw_params, $glossary->getLink('gloss_dev_threshold', false, _('Development threshold ($PPP)')) . ":");
                            echo "</li>";
                            echo '<li class="advanced">';
                            echo '<input type="checkbox" name="interp_btwn_thresh" id="interp_btwn_thresh" class="click" value="1" ' . ($fw_params["interp_btwn_thresh"]['value'] ? 'checked="checked"' : '') . '/>';
                            echo '<label for="interp_btwn_thresh" class="click"> ' . _("Progressive between thresholds") . '</label>';
                            echo "</li>";
                            echo "<li>";
                            echo select_num('lux_thresh', $fw_params, $glossary->getLink('lux_threshold', false, _('Luxury threshold ($MER)')) . ":", "level2");
                            echo "</li>";
                            echo "<li>";
                            echo select_num('luxcap_mult', $fw_params, $glossary->getLink('lux_multiplier', false, _('Multiplier on incomes above the luxury threshold')) . ":", 'long-label-short-select level2');
                            echo "</li>";
                            echo "<li>";
                            echo select_num('em_elast', $shared_params, $glossary->getLink('emiss_elast', false, _('Emissions elasticity')) . ":");
                            echo "</li>";
                            
                            echo '<li class="advanced" ';
                            echo (Framework::is_dev() || (Framework::user_is_developer())) ? '>' : 'style="display:none;" >';
                            echo '<input type="checkbox" name="do_luxcap" id="do_luxcap" class="click" value="1" ' . ($fw_params["do_luxcap"]['value'] ? 'checked="checked"' : '') . '/>';
                            echo '<label for="do_luxcap" class="click"> ' . _("Use luxury-capped baselines") . '</label>';
                            echo "</li>";

                            echo '<li class="advanced">';
                            echo '<input type="checkbox" name="show_tax_tables" id="show_tax_tables" class="click" value="1" ' . (isset($_REQUEST['show_tax_tables']) ? 'checked="checked"' : '') . '/>';
                            echo '<label for="show_tax_tables" class="click"> ' . _("Show illustrative \"tax table\"") . '</label>';
                            echo "</li>";
                            echo '</ul></fieldset></li>';

                            // Cost of Climate Action
                            echo '<li class="advanced"><fieldset class="incremental_cost">';
                            echo '<legend class="closed"><span>&nbsp;</span>' . $glossary->getLink('incr_cost', false, _('Incremental costs of climate action')) . '</legend>';
                            echo '<ul class="group">';
                            echo "<li>";
                            echo select_num('percent_gwp_MITIGATION', $shared_params,$glossary->getLink('mit_cost', false, _('Mitigation cost as % GWP')) . ":", 'long-label-short-select');
                            $fmt_string = 'Assuming a total global mitigation cost of %1$s (%2$s of GWP), this yields a global average incremental mitigation cost of %3$s per tonne CO<sub>2</sub>e in %4$s.';
                            $mitcost_string = '$<span id= "cost_total">' . number_format($cost_of_carbon['cost_blnUSDMER']) . '</span> billion';
                            $perccost_string = '<span id= "cost_perc_gwp">' . number_format($cost_of_carbon['cost_perc_gwp'], 1) . '</span>%';
                            $carbcost_string = '$<span id= "cost_per_tonne">' . number_format($cost_of_carbon['cost_USD_per_tCO2']) . '</span>';
                            $costyear_string = '<span id= "cost_year">' . (int) $cost_of_carbon['year'] . '</span>';
                            echo "<p class='level2'>" . sprintf(_($fmt_string),
                                $mitcost_string,
                                $perccost_string,
                                $carbcost_string,
                                $costyear_string) . "</p>";
                            echo "</li>";
                            echo "<li>";
                            echo select_num('percent_gwp_ADAPTATION', $shared_params,$glossary->getLink('adapt_cost', false, _('Adaptation cost as % GWP')) . ":");
                            echo "</li>";
                            echo '</ul></fieldset></li>';

                            // Kyoto obligations
                            echo '<li class="advanced"><fieldset>';
                            echo '<legend class="closed"><span>&nbsp;</span>' . $glossary->getLink('gloss_kyoto', false, _('Kyoto obligations')) . '</legend>';
                            echo '<ul id="kab" class="group">';
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
                            echo '</li></ul><!-- end #kab -->';
                            echo '</fieldset></li>';

                            // Mitigation smoothing
                            echo '<li class="advanced"><fieldset>';
                            echo '<legend class="closed"><span>&nbsp;</span>' . $glossary->getLink('mit_lag', false, _('Mitigation smoothing')) . '</legend>';
                            echo '<ul class="group">';
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
                               </ul>
                           </fieldset>

                           <input type="hidden" id="user_db" name="user_db" value="<?php echo $user_db; ?>" />
                           <input type="hidden" id="show_avail_params" name="show_avail_params" value="<?php echo ($show_avail_params ? 'yes' : 'no'); ?>" />
                           <input type="submit" name="submit" id="submit" class="click" value="<?php echo _("calculate") ?>" /> <!-- TODO: add validation -->
                           <input type="submit" name="reset" id="reset" class="click" value="<?php echo _("reset to initial values") ?>" />
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
                                        $welcome_string = _('Use the controls to the left to change the parameter values and see the implications for national fair shares.');
                                        //                                       $welcome_string .= ' ' . sprintf(_('Use the controls to the left to change the parameters in the %1$s and see the implications for the %2$s and other indicators.'), $glossary->getLink('def_gdrs', false, _('GDRs framework')), $glossary->getLink('gloss_rci', false, _('Responsibility and Capacity Index (RCI)')));
                                        echo $welcome_string;
                                        ?></p>

                                        <!-- <a id="scorecard_2" target="_blank" href="<?php // echo $scorecard_url ?>"><?php echo _("") ?></a> -->
                                        <form action="index.php" method="post" name="eqbtn_form" id="eqbtn_form">
                                            <div id="review_equity_settings">
                                                <button id="equity_settings_button" type="submit">Review equity settings</button>
                                            </div>
                                        </form>

                                       <div id="save">
                    <?php
                                       if ($display_params['framework']['value'] === 'gdrs') {
                                           if (Framework::is_dev() || (Framework::user_is_developer())) {
                                                // Allow XLS download with various advanced parameters
                                                echo '<ul>';
                                                echo '<li class="advanced"><fieldset class="xls_download_advanced">';
                                                echo '<legend class="closed"><span>&nbsp;</span>Advanced Excel download</legend>';
                                                echo "<form method='get' action='tables/download_tabsep.php'>";
                                                echo "<ul>";
                                                echo "    <input type='hidden' name='db' value='" . Framework::get_db_name($user_db) . "'> ";
                                                echo "    <li>Download Years:";
                                                echo "    <input type='text' name='dl_years' size='12' style='width:16em;'><br>";
                                                echo "(comma-separated list of individual years or ranges, for example '2013,2020,2030' or '1850,1950,1990,2010-2030')</li>";
                                                echo "    <li>Download Countries:";
                                                echo "    <input type='text' name='countries' size='12' style='width:16em;'><br>";
                                                echo "(comma-separated list of individual iso3 <u>country</u> codes, for example 'IND,DEU,CHK'). Will download the specified countries.</li>";
                                                echo "    <li>Filename for the Download:";
                                                echo "    <input type='text' name='filename' size='45' style='width:16em;' value='" . ($xls_file_slug . time() . ".xls") . "'></li>";
                                                echo "    <li><input type='checkbox' name='tax_tables' value='1'>Include tax tables</li>";
                                                echo "    <input type='submit' value='download'>";
                                                echo "</form>";
                                                echo '</li>';
                                                echo '</ul>';
                                           }
                                           echo '<p>';
                                           echo '<a href="tables/download_tabsep.php?db=' . Framework::get_db_name($user_db) . '">' . _("Download complete Excel table") . '</a>';
                                           if (Framework::is_dev() || (Framework::user_is_developer())) {
                                                // Allow downloading of database
                                                echo ' | <a href="util/download_db.php?db=' . Framework::get_db_name($user_db) .'">' . _("Download SQLite3 database") . '</a>';
                                            }
                                           # echo '<p><a href="viz/test.php?db=' . $user_db . '">Google visualization test</a></p>';
                                           echo '</p>';
                                       }
                  ?>
                                   </div><!-- /save -->
                                   <!--<p><?php /* ?><?php print_r(Framework::get_frameworks()); ?><?php */ ?></p>-->
                                </div><!-- /intro -->

                                <div id="data">
                                <!-- get parameters as a table -->
                                <?php
                                if ($show_avail_params) {
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
                                        if (isset($_REQUEST['dataversion'])) {
                                            if (!((New EmptyFramework)->get_data_ver() == $_REQUEST['dataversion'])) {
                                                echo '<p class="alert">' . _("Please note that the calculator database has been updated since your link was generated. Your settings have been preserved but the results may differ slightly.") . '</p>';
                                            }
                                        }
                                    ?>

                                    <div id="calc_parameters">
                                        <?php echo generate_params_table($display_params, $fw_params, $shared_params, $country_list, $region_list, $table_views); ?>
                                    </div><!-- end #calc_parameters -->

                                    <div id="calc_results">
                                        <?php
                                        $time_start = microtime_float();
                                        echo generate_results_table($display_params, $shared_params, $country_list, $region_list, $user_db);
                                        // include("tables/sample_table.php");
                                        // this only works for the first country report. needs fixing.
                                        // specifically, what I am actually interested in is figuring out how much time re-calculation of db
                                        // takes and how much time the retrieval of values and processing for display
                                        // echo "<div>processed in " . round(microtime_float() - $time_start,3) . " seconds</div>";
                                        ?>

                                    </div><!-- end #calc_results -->
                               </div><!-- end #data -->
                           </div><!-- end #calc_container -->
                           <div id="popup"></div><!-- help #popup window -->
            <br class="clear"/>
        </div><!-- end #container -->
        <?php include("inc/calc_footer.inc.php"); ?>
    </body>
</html>
