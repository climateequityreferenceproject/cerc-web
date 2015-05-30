<?php

require_once("frameworks/frameworks.php");
include_once("help/HWTHelp/HWTHelp.php");
$glossary = new HWTHelp('def_link', 'glossary.php', 'calc_gloss');

/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

$emerg_path_id["1.5"] = Framework::get_emerg_path_id('1.5Cmarkerpathway');
$emerg_path_id["2.0"] = Framework::get_emerg_path_id('2.0Cmarkerpathway');
$emerg_path_id["G8"] = Framework::get_emerg_path_id('G8Pathway');

$ambition_checked["1.5"] = '';
$ambition_checked["2.0"] = '';
$ambition_checked["G8"] = '';

$rc_checked["r100"] = '';
$rc_checked["r50c50"] = '';
$rc_checked["c100"] = '';

$dt_checked['low'] = '';
$dt_checked['med'] = '';
$dt_checked['high'] = '';

$cumsince_checked[1850] = '';
$cumsince_checked[1950] = '';
$cumsince_checked[1990] = '';

$do_progressive = 0;

$cbdr_ndx = 5;

if (isset($_COOKIE['db'])) {
    // NOTE: Use '==', not '===': sometimes comparing strings to ints
    
    $checked_string = 'checked="checked"';
    foreach ($emerg_path_id as $key => $val) {
        if ($val == $shared_params['emergency_path']['value']) {
            $ambition_checked[$key] = $checked_string;
        }
    }
    
    if ($fw_params['r_wt']['value'] == 1.0) {
        $rc_checked["r100"] = $checked_string;
        $cbdr_ndx = 0;
    } elseif ($fw_params['r_wt']['value'] == 0.5) {
        $rc_checked["r50c50"] = $checked_string;
        $cbdr_ndx = 3;
    } elseif ($fw_params['r_wt']['value'] == 0.0) {
        $rc_checked["c100"] = $checked_string;
        $cbdr_ndx = 6;
    } else {
        // If the current settings do not match the options on the settings panel
        $cbdr_ndx = -10;
    }
    
    if (($fw_params['dev_thresh']['value'] == 2500) && ($fw_params['interp_btwn_thresh']['value'] == 0)) {
        $dt_checked['low'] = $checked_string;
        $cbdr_ndx += 1;
    } elseif (($fw_params['dev_thresh']['value'] == 7500) && ($fw_params['interp_btwn_thresh']['value'] == 0)) {
        $dt_checked['med'] = $checked_string;
        $cbdr_ndx += 2;
    } elseif (($fw_params['dev_thresh']['value'] == 7500) && ($fw_params['interp_btwn_thresh']['value'] == 1)) {
        $dt_checked['high'] = $checked_string;
        $do_progressive = 1;
        $cbdr_ndx += 3;
    } else {
        // If the current settings do not match the options on the settings panel
        $cbdr_ndx = -10;
    }
    
    if ($shared_params['cum_since_yr']['value'] == 1850) {
        $cumsince_checked[1850] = $checked_string;
    } elseif ($shared_params['cum_since_yr']['value'] == 1950) {
        $cumsince_checked[1950] = $checked_string;
    } elseif ($shared_params['cum_since_yr']['value'] == 1990) {
        $cumsince_checked[1990] = $checked_string;
    }
}

?>

<form action="" method="post" name="equity_settings" id="equity_settings" class="group">
    <h2>Equity Settings</h2>
    <?php echo $glossary->getLink('gloss_esettings', false, _("Help")); ?>
<!--    <input type="button" name="equity_reset_top" id="equity_reset_top" class="click" value='<?php //echo _("Reset to defaults") ?>' />-->
    <input type="submit" name="equity_submit_top" id="equity_submit_top" class="click" value='<?php echo _("Save and continue") ?>' />
    <input type="submit" name="equity_cancel_top" id="equity_cancel_top" class="click" value='<?php echo _("Cancel") ?>' />
    
    <ul>
        <li class="setting">
            <fieldset id="pathway">
                <legend class="open"><span>&nbsp;</span><?php echo $glossary->getLink('gloss_path', false, _("Level of Global Ambition")); ?></legend>
                <div class="input_set group">
                    <h4>Select a mitigation pathway:</h4>
                    <ul>
                        <li>
                            <label for="ambition-high"><input type="radio" name="emergency_path" id="ambition-high" class="click" value=<?php echo '"' . $emerg_path_id["1.5"] . '" ' . $ambition_checked["1.5"]; ?> /><?php echo $glossary->getLink('gloss_path', false, _("1.5&#176;C pathway")); ?> ("Greater than or equal to 50% chance of staying below 1.5&#176;C in 2100.")</label>
                        </li>
                        <li>
                            <label for="ambition-med"><input type="radio" name="emergency_path" id="ambition-med" class="click" value=<?php echo '"' . $emerg_path_id["2.0"] . '" ' . $ambition_checked["2.0"]; ?> /><?php echo $glossary->getLink('gloss_path', false, _("2&#176;C pathway")); ?> ("Greater than 66% chance of staying within 2&#176;C in 2100.")</label>
                        </li>
                        <li>
                            <label for="ambition-low"><input type="radio" name="emergency_path" id="ambition-low" class="click" value=<?php echo '"' . $emerg_path_id["G8"] . '" ' . $ambition_checked["G8"]; ?> /><?php echo $glossary->getLink('gloss_path', false, _("G8 pathway")); ?> (A weaker pathway, consistent with the 2009 G8 Declaration in L’Aquila)</label>
                        </li>
                    </ul>
                </div>
            </fieldset>
        </li>
        <li class="setting">
            <fieldset id="cbdr">
                <legend class="open"><span>&nbsp;</span>Common but Differentiated <?php echo $glossary->getLink('gloss_rci', false, _("Responsibilities and Capacities")); ?> </legend>
                <div class="input_set group">
                    <div id="cbdr-radio-container">

                        <h4>Responsibility vs. Capacity, relative weight</h4>
                        <ul>
                            <li>
                                <label for="r100"><input type="radio" name="r_wt" id="r100" class="click" value="1.00" <?php echo $rc_checked["r100"]; ?> />100% Responsibility</label>
                            </li>
                            <li>
                                <label for="r50c50"><input type="radio" name="r_wt" id="r50c50" class="click" value="0.50" <?php echo $rc_checked["r50c50"]; ?> />50% Responsibility / 50% Capacity</label>
                            </li>
                            <li>
                                <label for="c100"><input type="radio" name="r_wt" id="c100" class="click" value="0.00" <?php echo $rc_checked["c100"]; ?> />100% Capacity</label>
                            </li>
                        </ul>

                        <h4>Progressivity, between and within countries</h4>
                        <ul>
                            <li>
                                <label for="dev-low"><input type="radio" name="dev_thresh" id="dev-low" class="click" value="2500" <?php echo $dt_checked['low']; ?> />$2,500 development threshold (actually, a poverty threshold)</label>
                            </li>
                            <li>
                                <label for="dev-med"><input type="radio" name="dev_thresh" id="dev-med" class="click" value="7500" <?php echo $dt_checked['med']; ?> />$7,500 development threshold</label>
                            </li>
                            <li>
                                <label for="dev-high"><input type="radio" name="dev_thresh" id="dev-high" class="click" value="7500" <?php echo $dt_checked['high']; ?> />$7,500 development threshold, plus additional progressivity factors </label>
                            </li>
                        </ul>
                        <input type="hidden" name="interp_btwn_thresh" id="equity_progressivity" value="<?php echo $do_progressive ?>"/>
                    </div>
                    <div id="cbdr-grid-container" class="group">
                        <div id="grid-col-1" class="group">
                                                    <p id="left-label"><strong>Less progressive</strong></p>
                        </div>
                        <div id="grid-col-2" class="group">
                            <p id="top-label"><strong>Responsibility given more weight</strong></p>
                            <ul>
                                <?php
                                    for ($i = 1; $i <= 9; $i++) {
                                        $selected_string = '';
                                        if ($i == $cbdr_ndx) {
                                            $selected_string = 'class="selected"';
                                        }
                                        echo '<li><a id="cbdr-' . $i . '" ' . $selected_string . ' href="#">&nbsp;</a></li>';
                                    }
                                ?>
                            </ul>
                            <p id="bot-label"><strong>Capacity given more weight</strong></p>
                        </div>
                        <div id="grid-col-3" class="group">
                            <p id="right-label"><strong>More progressive</strong></p>
                        </div>
                    </div>
                </div>
            </fieldset>
        </li>

        <li class="setting">
            <fieldset id="historical_date">
                <legend class="open"><span>&nbsp;</span><?php echo $glossary->getLink('gloss_responsibility', false, _("Historical Responsibility")); ?> Start Date</legend>
                    <h4>Calculate responsibility based on emissions cumulative since:</h4>
                    <div class="input_set group">
                        <label for="d1850"><input type="radio" name="cum_since_yr" id="d1850" class="click" value="1850" <?php echo $cumsince_checked[1850]; ?> />1850</label>
                        <label for="d1950"><input type="radio" name="cum_since_yr" id="d1950" class="click" value="1950" <?php echo $cumsince_checked[1950]; ?> />1950</label>
                        <label for="d1990"><input type="radio" name="cum_since_yr" id="d1990" class="click" value="1990" <?php echo $cumsince_checked[1990]; ?> />1990</label>
                    </div>
            </fieldset>
        </li>

    </ul>
<!--    <input type="button" name="equity_reset" id="equity_reset" class="click" value='<?php //echo _("Reset to defaults") ?>' />-->
    <input type="submit" name="equity_submit" id="equity_submit" class="click" value='<?php echo _("Save and continue") ?>' />
    <input type="submit" name="equity_cancel" id="equity_cancel" class="click" value='<?php echo _("Cancel") ?>' />

</form><!-- end equity_settings -->
