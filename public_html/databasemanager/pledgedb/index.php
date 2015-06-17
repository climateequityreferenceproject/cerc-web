<?php
include_once 'config/config.php';
include_once 'process.php';
// need this to use the calculator API
require_once "HTTP/Request.php";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>Climate Equity Reference Calculator Pledges Database</title>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
        <script type="text/javascript" src="tinymce/js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
        
        <script type="text/javascript" src="js/pledges.js"></script>
        <script type="text/javascript" src="tinymce/js/pledge_editor.js"></script>
        
        <link rel="stylesheet" type="text/css" href="css/pledges.css" />
    </head>
    <body>
        <div id="header-wrapper">
        <h1>Climate Equity Reference Calculator - pledges database entry form</h1>
        <form name="add" id="add" method="post" action="">
            <input type="hidden" name="form" value="add"/>
            <!-- Country and region drop-downs -->
            <?php
            // before writing the regions dropdown field, we check the calculator
            // whether there are any new regions that we should add
            check_for_new_regions(); 
            ?>
            <?php echo make_ctryregion_list($edit_array); ?>
            <!-- Conditional/unconditional-->
            <?php
            if (get_conditional_value($edit_array) == 1) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            ?>
            <input type="checkbox" name="conditional" id="conditional" value="1" <?php echo $checked; ?> />
            <label for="conditional"> Conditional</label>
             <?php
            if (get_include_nonco2($edit_array) == 1) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            ?>
            <br /><span>BAU includes: </span>
            <input type="checkbox" name="include_nonco2" id="include_nonco2" value="1" <?php echo $checked; ?> />
            <label for="include_nonco2"> non-CO<sub>2</sub></label>
             <?php
            if (get_include_lulucf($edit_array) == 1) {
                $checked = 'checked="checked"';
            } else {
                $checked = '';
            }
            ?>
            <input type="checkbox" name="include_lulucf" id="include_lulucf" value="1" <?php echo $checked; ?> />
            <label for="include_lulucf"> LULUCF</label>
            <br /><br />
            <table>
                <tr>
                    <td>Reduce</td>
                    <td>
                        <?php
                        if (get_quantity_value($edit_array) === 'absolute') {
                            $abs_checked = 'checked = "checked"';
                            $quant_checked = '';
                        } else {
                            $abs_checked = '';
                            $quant_checked = 'checked = "checked"';
                        }
                        ?>
                        <label><input type="radio" name="quantity" value="absolute" <?php echo $abs_checked; ?>/> absolute emissions</label>
                        <br />
                        <label><input type="radio" name="quantity" value="intensity" <?php echo $quant_checked; ?> /> intensity</label>
                    </td>
                    <td>to</td>
                    <td>
<!--                        <select name="reduction_percent" id="reduction_percent">
                        <?php
                        // option_number(1, 100, 1, get_reduction_percent($edit_array));
                        ?>
                        </select>-->
                        <input name="reduction_percent" id="reduction_percent" type="text" style="width:5em;" value="<?php echo get_reduction_percent($edit_array); ?>"></input>%
                    </td>
                    <td>
                        <?php
                        if (get_relto_value($edit_array) === 'below') {
                            $below_checked = 'checked = "checked"';
                            $of_checked = '';
                        } else {
                            $below_checked = '';
                            $of_checked = 'checked = "checked"';
                        }
                        ?>
                        <label><input type="radio" name="rel_to" value="below" <?php echo $below_checked; ?> /> below</label>
                        <br />
                        <label><input type="radio" name="rel_to" value="of" <?php echo $of_checked; ?> /> of</label>
                    </td>
                    <td>
                        <?php
                        if (get_yearbau_value($edit_array) === 'year') {
                            $year_checked = 'checked = "checked"';
                            $bau_checked = '';
                        } else {
                            $year_checked = '';
                            $bau_checked = 'checked = "checked"';
                        }
                        ?>
                        <label><input type="radio" name="year_or_bau" value="year" <?php echo $year_checked; ?> /> value in</label>
                        <br />
                        <label><input type="radio" name="year_or_bau" value="bau" <?php echo $bau_checked; ?> /> BAU</label>
                    </td>
                    <td>
                        <select name="rel_to_year" id="rel_to_year">
                        <?php
                        option_number(1990, 2010, 1, get_relto_year($edit_array));
                        ?>
                        </select>
                    </td>
                    <td>by</td>
                    <td>
                        <select name="by_year" id="by_year">
                        <?php
                        option_number(2010, 2050, 1, get_by_year($edit_array));
                        ?>
                        </select>
                    </td>
                </tr>
            </table>
            <label>Link to more information: </label><input type="text" name="info_link" value="<?php echo get_text($edit_array, 'info_link');?>"/><br />
            <label>Source:</label><br/>
            <textarea name="source" cols="75" rows="2" ><?php echo get_text($edit_array, 'source');?></textarea><br />
            <label>Caveat:</label><br/>
<?php // TODO: create collapsable caveat entry form - more thoughts on that in caveat_help.php ?>
            <i><b>The caveat field is used to hold all sorts of additional structured and unstructured data about a pledge.<br />
                        For more information about the syntax and purposes, <a href="caveat_help.php">there is a special helpfile</a></b></i><br />
            <textarea name="caveat" cols="100" rows="7" class="mceNoEditor"><?php echo get_text($edit_array, 'caveat');?></textarea><br /><br />
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
            <div id="table">
               <?php include("get_table.php"); ?>
            </div>
        </form>
    </body>
</html>
