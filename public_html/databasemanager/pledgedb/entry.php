<?php
require_once "config/config.php";


?>

<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="en-US">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <title>GDRs Pledges Database: Entry screen</title>
<!--        <script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.6.2/jquery.min.js"></script>
        <script type="text/javascript" src="tinymce/js/tinymce/jscripts/tiny_mce/tiny_mce.js"></script>
        
        <script type="text/javascript" src="js/pledges.js"></script>
        <script type="text/javascript" src="tinymce/js/pledge_editor.js"></script>
        
        <link rel="stylesheet" type="text/css" href="css/pledges.css" />-->
    </head>
    <body>
        <form action="" method="post" name="entry-form" id="entry-form">
            <!-- public -->
            <input type="checkbox" name="public" id="public" value="public" checked="checked" />
            <label for="public"><?php _e("public");?></label>
            <!-- country/region -->
            <input type="radio" name="country_region" id="country" value="country" checked="checked" />
            <label for="country"><?php _e("country");?></label>
            <input type="radio" name="country_region" id="region" value="region" />
            <label for="country"><?php _e("region");?></label>
            <select id="country_region_sel">
                <option>First item</option>
            </select>
            <!-- pledge year -->
            <label for="pledge_year">pledge year</label>
            <select id="pledge_year">
                <option>2020</option>
            </select>
            <!-- condition/unconditional-->
            <input type="radio" name="conditional_yesno" id="unconditional" value="unconditional" checked="checked" />
            <label for="country"><?php _e("unconditional");?></label>
            <input type="radio" name="conditional_yesno" id="conditional" value="conditional" />
            <label for="country"><?php _e("conditional");?></label>
            
            <!-- notes and comments -->
            <?php
            $text_types = array(
                "source" => _("source"),
                "caveat" => _("caveat"),
                "details" => _("details")
            );
            echo '<br />';
            foreach ($text_types as $type=>$label) {
echo <<<EOHTML
   <label for="$type-text" class="$type">$label</label>
   <textarea name="$type" id="$type-text" class="$type">Sample text</textarea>
EOHTML;
                echo '<br />';
            }
            ?>
            
            <!-- pledge information by gas -->
            <?php
            $gases = array(
                "fossil" => _("fossil"),
                "non_co2" => _("non-CO<sub>2</sub>"),
                "landuse" => _("land-use")
            );
            echo '<br />';
            foreach ($gases as $gas=>$label) {
echo <<<EOHTML
   <label for="$gas-fieldset" class="$gas">$label</label>
   <fieldset id="$gas-fieldset" class="$gas">
       <p>Controls</p>
   </fieldset>
EOHTML;
                echo '<br />';
            }
            ?>
            
            <!-- Form submission -->
            <input type="submit" name="submit" id="submit" value="submit" />
            
        </form>
    </body>
</html>
