<?php

require_once("frameworks/frameworks.php");

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
    <input type="button" name="equity_reset" id="equity_reset" class="click" value='<?php echo _("reset to defaults") ?>' />
    
    <ul>
        <li class="setting">
            <fieldset id="pathway">
                <legend class="open"><span>&nbsp;</span><?php $glossary->getLink('gloss_path', false, _("Level of Global Ambition")); ?>Level of Global Ambition</legend>
                    <h4>Select a mitigation pathway:</h4>
                    <label for="ambition-high"><input type="radio" name="emergency_path" id="ambition-high" class="click" value=<?php echo '"' . $emerg_path_id["1.5"] . '" ' . $ambition_checked["1.5"]; ?> />1.5&#176;C marker pathway (Try to limit warming to 1.5&#176;C)</label><br />
                    <label for="ambition-med"><input type="radio" name="emergency_path" id="ambition-med" class="click" value=<?php echo '"' . $emerg_path_id["2.0"] . '" ' . $ambition_checked["2.0"]; ?> />2&#176;C marker pathway (Try to limit warming to 2.0&#176;C)</label><br />
                    <label for="ambition-low"><input type="radio" name="emergency_path" id="ambition-low" class="click" value=<?php echo '"' . $emerg_path_id["G8"] . '" ' . $ambition_checked["G8"]; ?> />G8 marker pathway (As per the 2009 G8 declaration in L’Aquila)</label>

            </fieldset>
        </li>
        <li class="setting">
            <fieldset id="cbdr">
                <legend class="open"><span>&nbsp;</span>Common but Differentiated Responsibilities and Capacities </legend>

                <div id="cbdr-radio-container">

                    <h4>Responsibility vs. Capacity, relative weight</h4>
                    <label for="r100"><input type="radio" name="r_wt" id="r100" class="click" value="1.00" <?php echo $rc_checked["r100"]; ?> />100% Responsibility</label><br />
                    <label for="r50c50"><input type="radio" name="r_wt" id="r50c50" class="click" value="0.50" <?php echo $rc_checked["r50c50"]; ?> />50% Responsibility / 50% Capacity</label><br />
                    <label for="c100"><input type="radio" name="r_wt" id="c100" class="click" value="0.00" <?php echo $rc_checked["c100"]; ?> />100% Capacity</label>

                    <h4>Development threshold</h4>
                    <label for="dev-low"><input type="radio" name="dev_thresh" id="dev-low" class="click" value="0" <?php echo $dt_checked['low']; ?> />$0</label><br />
                    <label for="dev-med"><input type="radio" name="dev_thresh" id="dev-med" class="click" value="7500" <?php echo $dt_checked['med']; ?> />$7,500</label><br />
                    <label for="dev-high"><input type="radio" name="dev_thresh" id="dev-high" class="click" value="7500" <?php echo $dt_checked['high']; ?> />$7,500 plus progressivity factor</label>
                    <input type="hidden" name="interp_btwn_thresh" id="equity_progressivity" value="<?php echo $do_progressive ?>"/>
                </div>
                <div id="cbdr-grid-container" class="group">
                    <div id="grid-col-1" class="group">
						<p id="left-label"><strong>More regressive <br />(lower development threshhold)</strong></p>
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
			<p id="right-label"><strong>More progressive  <br />(higher development threshhold)</strong></p>
                    </div>

                </div>


            </fieldset>
        </li>

        <li class="setting">
            <fieldset id="historical_date">
                <legend class="open"><span>&nbsp;</span>Historical Responsibility Start Date</legend>
                    <h4>Calculate responsibility based on emissions cumulative since:</h4>
                    <label for="d1850"><input type="radio" name="cum_since_yr" id="d1850" class="click" value="1850" <?php echo $cumsince_checked[1850]; ?> />1850</label>
                    <label for="d1950"><input type="radio" name="cum_since_yr" id="d1950" class="click" value="1950" <?php echo $cumsince_checked[1950]; ?> />1950</label>
                    <label for="d1990"><input type="radio" name="cum_since_yr" id="d1990" class="click" value="1990" <?php echo $cumsince_checked[1990]; ?> />1990</label>
            </fieldset>
        </li>

    </ul>
    <input type="submit" name="equity_submit" id="equity_submit" class="click" value='<?php echo _("save") ?>' />
    <input type="submit" name="equity_cancel" id="equity_cancel" class="click" value='<?php echo _("cancel") ?>' />

</form><!-- end equity_settings -->
