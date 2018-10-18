<?php

require_once("frameworks/frameworks.php");
include_once("help/HWTHelp/HWTHelp.php");
$glossary = new HWTHelp('def_link', 'glossary.php', 'calc_gloss');

/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/

$emerg_path_id["LED"] = Framework::get_emerg_path_id('1.5LowEnergyDemand');
$emerg_path_id["1.5"] = Framework::get_emerg_path_id('1.5Cmarkerpathway');
$emerg_path_id["2.0"] = Framework::get_emerg_path_id('2.0Cmarkerpathway');

$ambition_checked["LED"] = '';
$ambition_checked["1.5"] = '';
$ambition_checked["2.0"] = '';

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
    
    if ($shared_params['cum_since_yr']['value'] == 1850) {
        $cumsince_checked[1850] = $checked_string;
        $cbdr_ndx = 0;
    } elseif ($shared_params['cum_since_yr']['value'] == 1950) {
        $cumsince_checked[1950] = $checked_string;
        $cbdr_ndx = 3;
    } elseif ($shared_params['cum_since_yr']['value'] == 1990) {
        $cumsince_checked[1990] = $checked_string;
        $cbdr_ndx = 6;
    } else {
        // If the current settings do not match the options on the settings panel
        $cbdr_ndx = -10;
    }

    if (($fw_params['dev_thresh']['value'] == 0) && ($fw_params['interp_btwn_thresh']['value'] == 0)) {
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
    
}

?>

<form action="" method="post" name="equity_settings" id="equity_settings" class="group">
    <h2>Basic Equity Settings</h2>
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
                            <label for="ambition-1.5-LED"><input type="radio" name="emergency_path" id="ambition-high" class="click" value=<?php echo '"' . $emerg_path_id["LED"] . '" ' . $ambition_checked["LED"]; ?> /><?php echo $glossary->getLink('gloss_path', false, _("1.5&#176;C Low Energy Demand")); ?> (No <?php echo $glossary->getLink('gloss_beccs', false, _("BECCS")); ?>, minimal overshoot of 1.5&#176;C, nearly 66% chance of 1.5&#176;C in 2100)</label>
                        </li>
                        <li>
                            <label for="ambition-high"><input type="radio" name="emergency_path" id="ambition-med" class="click" value=<?php echo '"' . $emerg_path_id["1.5"] . '" ' . $ambition_checked["1.5"]; ?> /><?php echo $glossary->getLink('gloss_path', false, _("1.5&#176;C Standard")); ?> ("Greater than or equal to 50% chance of staying below 1.5&#176;C in 2100.")</label>
                        </li>
                        <li>
                            <label for="ambition-med"><input type="radio" name="emergency_path" id="ambition-low" class="click" value=<?php echo '"' . $emerg_path_id["2.0"] . '" ' . $ambition_checked["2.0"]; ?> /><?php echo $glossary->getLink('gloss_path', false, _("2&#176;C Standard")); ?> ("Greater than 66% chance of staying within 2&#176;C in 2100.")</label>
                        </li>
                    </ul>
                </div>
            </fieldset>
        </li>
        <li class="setting">
            <fieldset id="cbdr">
                <legend class="open"><span>&nbsp;</span>Common but Differentiated <?php echo $glossary->getLink('gloss_rci', false, _("Responsibilities and Capabilities")); ?> </legend>
                <div id="cbdr-radio-container">
                    <h4><?php echo $glossary->getLink('gloss_responsibility', false, _("Historical Responsibility")); ?>, calculated based on emissions cumulative since:</h4>
                    <ul>
                        <li>
                            <label for="d1850"><input type="radio" name="cum_since_yr" id="d1850" class="click" value="1850" <?php echo $cumsince_checked[1850]; ?> />1850</label>
                        </li>
                        <li>
                            <label for="d1950"><input type="radio" name="cum_since_yr" id="d1950" class="click" value="1950" <?php echo $cumsince_checked[1950]; ?> />1950</label>
                        </li>
                        <li>
                            <label for="d1990"><input type="radio" name="cum_since_yr" id="d1990" class="click" value="1990" <?php echo $cumsince_checked[1990]; ?> />1990</label>
                        </li>
                    </ul>
                    <h4><?php echo $glossary->getLink('gloss_capacity', false, _("Capability to Act")); ?>, calculated in increasingly <?php echo $glossary->getLink('progressivity', false, _("economically progressive ways")); ?>:</h4>
                    <ul>
                        <li>
                            <label for="dev-low"><input type="radio" name="dev_thresh" id="dev-low" class="click" value="0" <?php echo $dt_checked['low']; ?> />No development threshold (actually, a regressive approach)</label>
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
                        <p id="top-label"><strong>Earlier responsibility start date</strong></p>
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
                        <p id="bot-label"><strong>Later responsibility start date</strong></p>
                    </div>
                    <div id="grid-col-3" class="group">
                        <p id="right-label"><strong>More progressive</strong></p>
                    </div>
                </div>
                <h4><?php echo $glossary->getLink('r_weight', false, _("Relative Weight")); ?> for Historical Responsibility vs Economic Capability to Act</h4>
                <div class="input_set group" id="rci_weight_dropdown">
                    <select name="r_wt" id="r_wt_dropdown">
                        <?php 
                            for ($i = 0; $i <= 10; $i++) {
                                var_dump($r_wt, $i);
                                echo "<option value=\"" . ($i/10) . "\"" . (($fw_params['r_wt']['value'] == ($i/10)) ? " selected=\"selected\">" : ">") . ($i * 10) . "%</option>";
                            }
                        ?>
                    </select>
                    <div id="rci_wt_slider"></div>
                    <select name="c_wt" id="c_wt_dropdown">
                        <?php 
                            for ($i = 0; $i <= 10; $i++) {
                                echo "<option value=\"" . ($i/10) . "\"" . (($fw_params['r_wt']['value'] == (1 - ($i/10))) ? " selected=\"selected\">" : ">") . ($i * 10) . "%</option>";
                            }
                        ?>
                    </select>
                </div>
                <div class="input_set group" id="rci_weight_labels">
                    <div id="r_wt_label_div">
                        Responsibility
                    </div>
                    <div id="c_wt_label_div">
                        Capability
                    </div>
                </div>
                <script>  
                    $(function() {
                      var r_select = $( "#r_wt_dropdown" );
                      var c_select = $( "#c_wt_dropdown" );
                      var slider = $( "#rci_wt_slider" ).slider({
                        min: 0,
                        max: 10,
                        step: 1,
                        value: <?php echo ((1 - $fw_params['r_wt']['value']) * 10); ?>,
                        slide: function( event, ui ) {
                          r_select[ 0 ].selectedIndex = 10 - ui.value;
                          c_select[ 0 ].selectedIndex = ui.value;
                        }
                      });
                      $( "#r_wt_dropdown" ).change(function() {
                        slider.slider( "value", 10 - this.selectedIndex );
                        c_select[ 0 ].selectedIndex = 10 - this.selectedIndex;
                      });
                      $( "#c_wt_dropdown" ).change(function() {
                        slider.slider( "value", this.selectedIndex );
                        r_select[ 0 ].selectedIndex = 10 - this.selectedIndex;
                      });
                    });
                </script>
            </fieldset>
        </li>

    </ul>
<!--    <input type="button" name="equity_reset" id="equity_reset" class="click" value='<?php //echo _("Reset to defaults") ?>' />-->
    <input type="submit" name="equity_submit" id="equity_submit" class="click" value='<?php echo _("Save and continue") ?>' />
    <input type="submit" name="equity_cancel" id="equity_cancel" class="click" value='<?php echo _("Cancel") ?>' />
    <script>document.onkeyup = function(evt) { evt = evt || window.event; if (evt.keyCode == 27) { $('#equity_cancel').click(); } }; </script>
</form><!-- end equity_settings --> 
