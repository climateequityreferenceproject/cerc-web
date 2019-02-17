<?php
include_once 'config/config.php';
include_once('config/caveat_fields.php');
include_once 'process.php';
include_once "guzzle.phar"; // needed to access calc API; currently using version 6.3.3 from https://github.com/guzzle/guzzle

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Climate Equity Reference Calculator Pledges Database</title>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
        <script type="text/javascript" src="tinymce/js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
        
        <script type="text/javascript" src="js/pledges.js"></script>
        <script type="text/javascript" src="tinymce/js/pledge_editor.js"></script>
        <script type="text/javascript" src="js/jquery.floatThead.min.js?v=1.2.12"></script>
        
        <link rel="stylesheet" type="text/css" href="css/pledges.css" />
    </head>
    <body>
        <div id="header-wrapper">
        <h1>Climate Equity Reference Calculator - pledges database entry form</h1>
        <form name="add" id="add" method="post" action="">
            <input type="hidden" name="form" value="add"/>
            <input type="hidden" id="db" name="db" value="<?php echo(unserialize($_COOKIE['db'])); ?>"/>
            <!-- Country and region drop-downs -->
            <?php
            // before writing the regions dropdown field, we check via calculator API
            // whether there are any new regions that we should add
            check_for_new_regions(); 
            ?>
            <?php echo make_ctryregion_list($edit_array); ?>
            <script>
                // remove the GHG time series table if the country/region is changed.
                $(document).ready(function(){ $("#iso3").change(function() { $('#datatable-container').html(""); }); });
                $(document).ready(function(){ $("#region").change(function() { $('#datatable-container').html(""); }); });
            </script>
            <!-- Conditional/unconditional-->
            <input type="checkbox" name="conditional" id="conditional" value="1" <?php echo ((get_conditional_value($edit_array) == 1) ? 'checked="checked"' : ''); ?> />
            <label for="conditional"> Conditional</label>
            <input type="checkbox" name="public" id="public" value="1" <?php echo ((get_public_value($edit_array) == 1) ? 'checked="checked"' : ''); ?> />
            <label for="public"> Pledge is public (otherwise only visible in dev-calculator)</label>
            <!-- Pledge details-->
            <br /><span>BAU/target includes: </span>
            <input type="checkbox" name="include_nonco2" id="include_nonco2" value="1" <?php echo ((get_include_nonco2($edit_array) == 1) ? 'checked="checked"' : ''); ?> />
            <label for="include_nonco2"> non-CO<sub>2</sub></label>
            <input type="checkbox" name="include_lulucf" id="include_lulucf" value="1" <?php echo ((get_include_lulucf($edit_array) == 1) ? 'checked="checked"' : ''); ?> />
            <label for="include_lulucf"> LULUCF</label>
            <br /><br />
            <?php
            $abs_checked = (get_quantity_value($edit_array) === 'absolute') ? 'checked = "checked"' : '';
            $quant_checked = (get_quantity_value($edit_array) === 'intensity') ? 'checked = "checked"' : '';
            $targ_Mt_checked = (get_quantity_value($edit_array) === 'target_Mt') ? 'checked = "checked"' : '';
            $below_checked = (get_relto_value($edit_array) === 'below') ? 'checked = "checked"' : '';
            $of_checked = (get_relto_value($edit_array) === 'of') ? 'checked = "checked"' : '';
            $year_checked = (get_yearbau_value($edit_array) === 'year') ? 'checked = "checked"' : '';
            $bau_checked  = (get_yearbau_value($edit_array) === 'bau')  ? 'checked = "checked"' : '';
            ?>
            <table>
                <tr>
                    <td rowspan="3" style="vertical-align:middle;background:#ffffee;border-color:#523A0B;">Reduce</td>
                    <td rowspan="2" style="vertical-align:middle;border-top-color:#523A0B;">
                        <label><input type="radio" name="quantity" value="absolute" <?php echo $abs_checked; ?>/> absolute emissions</label>
                        <br />
                        <label><input type="radio" name="quantity" value="intensity" <?php echo $quant_checked; ?> /> intensity</label>
                    </td>
                    <td rowspan="2" style="vertical-align:middle;border-top-color:#523A0B;">
                        to
                        <?php // option_number(1, 100, 1, get_reduction_percent($edit_array)); ?>
                        <input name="reduction_percent" id="reduction_percent" type="text" style="width:5em;" value="<?php echo get_reduction_percent($edit_array); ?>"></input> %
                    </td>
                    <td rowspan="2" style="vertical-align:middle;border-top-color:#523A0B;">
                        <label><input type="radio" name="rel_to" value="below" <?php echo $below_checked; ?> /> below</label>
                        <br />
                        <label><input type="radio" name="rel_to" value="of" <?php echo $of_checked; ?> /> of</label>
                    </td>
                    <td rowspan="2" style="vertical-align:middle;border-top-color:#523A0B;">
                        <label><input type="radio" name="year_or_bau" value="year" <?php echo $year_checked; ?> /> value in</label>
                        <select name="rel_to_year" id="rel_to_year">
                        <?php option_number(1990, 2010, 1, get_relto_year($edit_array)); ?>
                        </select>
                        <br />
                        <label><input type="radio" name="year_or_bau" value="bau" <?php echo $bau_checked; ?> /> BAU</label>
                    </td>
                    <td rowspan="3" style="vertical-align:middle;background:#ffffee;border-color:#523A0B;">
                        by
                        <select name="by_year" id="by_year">
                        <?php option_number(2010, 2050, 1, get_by_year($edit_array)); ?>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td></td>
                </tr>
                <tr>
                    <td style="border-bottom-color:#523A0B;"><label><input type="radio" name="quantity" value="target_Mt" <?php echo $targ_Mt_checked; ?> /> to target emissions</label></td>
                    <td colspan=3 style="border-bottom-color:#523A0B;">
                        of
                        <input name="target_Mt" id="target_Mt" type="text" style="width:5em;" value="<?php echo get_value($edit_array,'target_Mt'); ?>"></input> Mt CO<sub>2</sub>eq (total for gases selected)
                    </td>
                </tr>
            </table>
            <table>
                <thead>
                    <tr><th colspan="4>">Provide the implicit, explicit or assumed breakdown of target year emissions<br>
                    <tr><th></th><th>fossil CO<sub>2</sub></th><th>non-CO<sub>2</sub></th><th>LULUCF</th></tr>
                </thead>
                <tr>
                    <td style="border-color:#523A0B;">Target year emissions breakdown: Mt CO<sub>2</sub>eq</td>
                    <td style="border-color:#523A0B;"><input name="target_Mt_CO2" id="target_Mt_CO2" type="text" style="width:5em;" value="<?php echo get_value($edit_array,'target_Mt_CO2'); ?>"></input></td>
                    <td style="border-color:#523A0B;"><input name="target_Mt_nonCO2" id="target_Mt_nonCO2" type="text" style="width:5em;" value="<?php echo get_value($edit_array,'target_Mt_nonCO2'); ?>"></input></td>
                    <td style="border-color:#523A0B;"><input name="target_Mt_LULUCF" id="target_Mt_LULUCF" type="text" style="width:5em;" value="<?php echo get_value($edit_array,'target_Mt_LULUCF'); ?>"></input></td>
                </tr>
            </table>
            The breakdown is useful if users choose to display a sector combination that is different from the pledge. <br />
            You have to specify all three for this to work. These values are only used to determine the internal ratio of the <br />
            pledged/implied emissions in the target year, not the size of the pledge itself (which is determined by the information <br />
            provided immediately above). Note that the values that you enter here is not necessarily the breakdown of the pledge, but <br />
            the breakdown of emissions that we expect in the pledge year given what has been pledged (e.g. if a pledge doesn't contain <br />
            information about LULUCF, we might want to enter BAU values for LULUCF here). If this breakdown is not provided, the default <br />
            method (using ratio of source categories in our BAU for the target year for sources included in the pledge, hold non-included <br />
            sources at BAU levels) is used instead. <br />
            Also note that you really should include a helptext to describe your assumptions to the end-user (pledge_breakdown_assumptions).
            <br /><br />

<!--            <label>Link to more information: </label><input type="text" name="info_link" value="<?php echo get_text($edit_array, 'info_link');?>"/><br />-->
            <label>Source:</label><br/>
            <textarea name="source" cols="75" rows="2" ><?php echo get_text($edit_array, 'source');?></textarea><br />
            <label>Caveat/Additional Data:</label><br/>
            <i><b>The caveat field is used to hold all sorts of additional structured and unstructured data about a pledge.<br />
                        For more information about the syntax and purposes, <a href="caveat_help.php">there is a special helpfile</a></b></i><br />
            <!-- the area formerly known as the caveat field -->
            <div id="caveat-top-container" style="position:relative;">
            <div id="caveat-container" style="overflow-y:auto; height: 180px; width: 50em; border:1px dotted grey; padding:8px;">
                <script>
                    $(document).ready(function(){
                        $('input[type="checkbox"]').on('change', function() {
                            $('input[name="' + this.name + '"]').not(this).prop('checked', false);
                        });
                    });
                </script>

                <?php

                    $output_array = array ();
                    $additional_caveat_data = array();
                    preg_match("/{.*}/", get_text($edit_array, 'caveat'), $output_array);

                    if (isset($output_array[0])) {
                        $additional_caveat_data= json_decode($output_array[0], TRUE);
                        $caveat_text = trim(str_replace($output_array[0],"", get_text($edit_array, 'caveat')));
                    } else {
                        $caveat_text = trim(get_text($edit_array, 'caveat'));

                    }
//                    var_dump($output_array,$additional_caveat_data,$caveat_text);
//                    die();
                    foreach ($caveat_fields as $caveat_data_type) {
                        echo '<label id="caveat_' . $caveat_data_type['name'] . '-label" style="position:relative;">' . $caveat_data_type['name'] . ': (hover for help)</label>' . "\n";
                        echo '<div id="caveat_' . $caveat_data_type['name'] . '-help" style="display: none;border:1px solid black;background-color:yellow;width:50em;position:absolute;top:2px;left:90px;padding:2px;line-height:12.5px">';
                        echo '<b>' . $caveat_data_type['title'] . '</b><br>';
                        echo $caveat_data_type['description'] . '</div>';
                        echo "<script>";
                        echo "document.getElementById('caveat_" . $caveat_data_type['name'] . "-label').onmouseover = function() { document.getElementById('caveat_" . $caveat_data_type['name'] . "-help').style.display = 'block'; }; ";
                        echo "document.getElementById('caveat_" . $caveat_data_type['name'] . "-label').onmouseout  = function() { document.getElementById('caveat_" . $caveat_data_type['name'] . "-help').style.display = 'none'; }; ";
                        echo "</script>";

                        switch ($caveat_data_type['type']) {
                            case 'textarea':
                                echo '<br />' . "\n";
                                echo '<textarea name="caveat_'.$caveat_data_type['name'].'" id="caveat_'.$caveat_data_type['name'].'" ';
                                echo 'cols="75" rows="3" class="mceNoEditor">';
                                echo $additional_caveat_data[$caveat_data_type['name']];
                                echo '</textarea><br />' . "\n" . '<br />' . "\n";
                                break;
                            case 'textbox':
                                echo '<br />' . "\n";
                                echo '<input type="text" style="width:53em;" name="caveat_'.$caveat_data_type['name'].'" id="caveat_'.$caveat_data_type['name'].'" ';
                                echo 'value="' . $additional_caveat_data[$caveat_data_type['name']] . '">';
                                echo '<br />' . "\n" . '<br />' . "\n";
                                break;
                            case 'boolean': ///// NOTE: THIS ACTUALLY IS NOT FINALIZED CODE
//                                echo '&nbsp;&nbsp;&nbsp;' . "\n";
//                                echo '<input type="checkbox" name="caveat_'.$caveat_data_type['name'].'" id="caveat_'.$caveat_data_type['name'].'" value="yes" />yes  ' . "\n";
//                                echo '<input type="checkbox" name="caveat_'.$caveat_data_type['name'].'" id="caveat_'.$caveat_data_type['name'].'" value="no" />no' . "\n";
//                                echo '<br />' . "\n" . '<br />' . "\n";
                                break;
                            case 'yes':
                                echo '&nbsp;&nbsp;&nbsp;' . "\n";
                                echo '<input type="checkbox" name="caveat_'.$caveat_data_type['name'].'" id="caveat_'.$caveat_data_type['name'].'" value="yes"';
                                echo (isset($additional_caveat_data[$caveat_data_type['name']]) ? ' checked="checked"' : '');
                                echo ' />yes  ' . "\n";
                                echo '<br />' . "\n" . '<br />' . "\n";
                                break;
                            case 'bau':
                                echo '<br />' . "\n";
                                echo 'Year: <input type="text" style="width:4em;" name="caveat_'.$caveat_data_type['name'].'_year" id="caveat_'.$caveat_data_type['name'].'_year" ';
                                echo 'value="' . $additional_caveat_data[$caveat_data_type['name'] . '_year'] . '">&nbsp;';
                                echo 'Total: <input type="text" style="width:4em;" name="caveat_'.$caveat_data_type['name'].'_total" id="caveat_'.$caveat_data_type['name'].'_total" ';
                                echo 'value="' . $additional_caveat_data[$caveat_data_type['name'] . '_total'] . '">&nbsp;';
                                echo 'Fossil: <input type="text" style="width:4em;" name="caveat_'.$caveat_data_type['name'].'_fossil" id="caveat_'.$caveat_data_type['name'].'_fossil" ';
                                echo 'value="' . $additional_caveat_data[$caveat_data_type['name'] . '_fossil'] . '">&nbsp;';
                                echo 'LULUCF: <input type="text" style="width:4em;" name="caveat_'.$caveat_data_type['name'].'_lulucf" id="caveat_'.$caveat_data_type['name'].'_lulucf" ';
                                echo 'value="' . $additional_caveat_data[$caveat_data_type['name'] . '_lulucf'] . '">&nbsp;';
                                echo 'nonCO2: <input type="text" style="width:4em;" name="caveat_'.$caveat_data_type['name'].'_nonco2" id="caveat_'.$caveat_data_type['name'].'_nonco2" ';
                                echo 'value="' . $additional_caveat_data[$caveat_data_type['name'] . '_nonco2'] . '">&nbsp;';
                                echo '<br />' . "\n" . '<br />' . "\n";
                                break;
                            default:
                                // Shouldn't reach here
                                break;
                        }
                    }
                ?>
                The actual caveats:<br>
                <textarea name="caveat" cols="75" rows="2"><?php echo $caveat_text; ?></textarea><br /><br />
            </div>


            <label>Details:</label><br />
            <textarea name="details" cols="75" rows="2" ><?php echo get_text($edit_array, 'details');?></textarea>
            <?php
            if ($edit_array) {
                printf('<input type="hidden" name="edit_id" value="%s"/>', $edit_array['id']);
                echo '<input type="submit" name="replace" value ="Replace" />';
                echo '<input type="submit" name="cancel" value ="Cancel" />';
            } else {
                echo '<input type="submit" value ="Add" />';
            }
            ?>
            <br />
        </form>
        </div>
        <form name="table" method="post" action="">
            <input type="hidden" name="form" value="table"/>
               <?php include("get_table.php"); ?>
                <script>
                    $(document).ready(function(){
                        $('table.countrytbl').floatThead({useAbsolutePositioning: false});
                        $('table.regiontbl').floatThead({useAbsolutePositioning: false});
                    });
                </script>
            </div>
        </form>
        <div id="datatable-wrapper" style="position:absolute;top:55px;left:800px;width:450px;height:500px;overflow:auto;">
            <div id="datatable-button">
                select a country/region and then <u>click here</u> to display its historical and BAU emissions data.
            </div>
            <div>
            <div id="datatable-container">
            </div>
            <script>
                $(document).ready(function(){
                    $('#datatable-button').click(function () {
                        if ($('input[name=country_or_region]:checked').val()==="region") {
                            $url = 'https://climateequityreference.org/pledges/entry/datatable.php?country=' + $("#region option:selected").val() + "&db=" + $("#db").val();
                            $('#datatable-container').html("<br><br><br>Loading data for " + $("#region option:selected").text() + " (it is normal for this to take a while)<br />" + $url);
                        } else {
                            $url = 'https://climateequityreference.org/pledges/entry/datatable.php?country=' + $("#iso3 option:selected").val() + "&db=" + $("#db").val();
                            $('#datatable-container').html("<br><br><br>Loading data for " + $("#iso3 option:selected").text() + " (it is normal for this to take a while)<br />" + $url);
                        }
                        $('#datatable-container').load($url);
                    });
                });
            </script>
        </div>
    </body>
</html>
