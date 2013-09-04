<?php

/*
* To change this template, choose Tools | Templates
* and open the template in the editor.
*/
?>

<form action="" method="post" name="equity_settings" id="equity_settings" class="group">
    <h2>Equity Settings</h2>
    <input type="submit" name="equity_reset" id="equity_reset" class="click" value='<?php echo _("reset to defaults") ?>' />

    <ul>
        <li class="setting">
            <fieldset id="pathway">
                <legend class="open"><span>&nbsp;</span><?php $glossary->getLink('gloss_path', false, _("Level of Global Ambition")); ?>Level of Global Ambition</legend>
                    <h4>Select a mitigation pathway:</h4>
                    <label for="ambition-high"><input type="radio" name="ambition" id="ambition-high" class="click" value="1.5" />1.5&#176;C marker pathway (Try to limit warming to 1.5&#176;C)</label><br />
                    <label for="ambition-med"><input type="radio" name="ambition" id="ambition-med" class="click" value="2.0" checked="checked" />2&#176;C marker pathway (Try to limit warming to 2.0&#176;C)</label><br />
                    <label for="ambition-low"><input type="radio" name="ambition" id="ambition-low" class="click" value="G8" />G8 marker pathway</label>

            </fieldset>
        </li>
        <li class="setting">
            <fieldset id="cbdr">
                <legend class="open"><span>&nbsp;</span>Common but Differentiated Responsibilities and Capacities </legend>

                <div id="cbdr-radio-container">

                    <h4>Responsibility vs. Capacity, relative weight</h4>
                    <label for="r100"><input type="radio" name="rvsc" id="r100" class="click" value="r100" />100% Responsibility</label><br />
                    <label for="r50c50"><input type="radio" name="rvsc" id="r50c50" class="click" value="r50c50" checked="checked" />50% Responsibility / 50% Capacity</label><br />
                    <label for="c100"><input type="radio" name="rvsc" id="c100" class="click" value="c100" />100% Capacity</label>

                    <h4>Development threshold</h4>
                    <label for="dev-low"><input type="radio" name="devthresh" id="dev-low" class="click" value="0" />$0</label><br />
                    <label for="dev-med"><input type="radio" name="devthresh" id="dev-med" class="click" value="7500" checked="checked" />$7,500</label><br />
                    <label for="dev-high"><input type="radio" name="devthresh" id="dev-high" class="click" value="7500+prog" />$7,500 plus progressivity factor</label>
                </div>
                <div id="cbdr-grid-container" class="group">
                    <div id="grid-col-1" class="group">
						<p id="left-label"><strong>More regressive <br />(lower development threshhold)</strong></p>
                    </div>
                    <div id="grid-col-2" class="group">
                        <p id="top-label"><strong>Responsibility given more weight</strong></p>
                        <ul>
                            <li>
                                <a id="cbdr-1" href="#">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-2" href="#">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-3" href="#">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-4" href="#">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-5" href="#" class="selected">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-6" href="#">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-7" href="#">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-8" href="#">&nbsp;</a>
                            </li>
                            <li>
                                <a id="cbdr-9" href="#">&nbsp;</a>
                            </li>
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
                    <label for="d1850"><input type="radio" name="date" id="d1850" class="click" value="1850" />1850</label>
                    <label for="d1950"><input type="radio" name="date" id="d1950" class="click" value="1950" />1950</label>
                    <label for="d1990"><input type="radio" name="date" id="d1990" class="click" value="1990" checked="checked" />1990</label>
            </fieldset>
        </li>

    </ul>
    <input type="submit" name="equity_submit" id="equity_submit" class="click" value='<?php echo _("save") ?>' />
    <input type="submit" name="equity_cancel" id="equity_cancel" class="click" value='<?php echo _("cancel") ?>' />

</form><!-- end equity_settings -->
