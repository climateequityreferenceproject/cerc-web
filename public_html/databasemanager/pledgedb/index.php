<?php
include_once 'process.php';

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head profile="http://gmpg.org/xfn/11">
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>GDRs Pledges Database</title>
        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
        <script type="text/javascript" src="tinymce/js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
        
        <script type="text/javascript" src="js/pledges.js"></script>
        <script type="text/javascript" src="tinymce/js/pledge_editor.js"></script>
        
        <link rel="stylesheet" type="text/css" href="css/pledges.css" />
    </head>
    <body>
        <div id="header-wrapper">
        <h1>GDRs pledges database entry form</h1>
        <form name="add" id="add" method="post" action="">
            <input type="hidden" name="form" value="add"/>
            <!-- Country and region drop-downs -->
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
                        <select name="reduction_percent" id="reduction_percent">
                        <?php
                        option_number(1, 100, 1, get_reduction_percent($edit_array));
                        ?>
                        </select>%
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
            <label>Source:</label><br/>
            <textarea name="source" cols="75" rows="2" ><?php echo get_text($edit_array, 'source');?></textarea><br />
            <label>Details:</label><br />
            <textarea name="details" cols="75" rows="2" ><?php echo get_text($edit_array, 'details');?></textarea>
            <input type="submit" value ="Add" />
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
