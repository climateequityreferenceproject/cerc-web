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
        <h1>Help text for the caveat field</h1>
        We use the Caveat field to store other random information about the pledge in JSON format. <br>
        <ul><li>it is ok to write other random text into the caveat field; only the information in curly brackets will be interpreted as JSON data.</li>
            <li>These additional JSON information appear all together in one set of curly brackets in this general syntax: <br>
                    {"key_1":"value_1", "key_2":"value_2", ... , "key_n":"value_n"}</li>
            <li>It is ok to mark up the texts within the JSON values with html, but any quotation marks must be written as html entities, i.e. &amp;quot; for double quotes and &amp;#39; for single quotes. Nowhere in the JSON data fields should quotation marks occur in plain (also be careful about other special chars like backslash, ampersand, question mark and hash symbol - test whether your input works as expected in the calculator).</li> 
<?php // TODO: create collapsable entry form for caveat field that encodes and decodes user input as needed and ensures proper JSON 
      // single and double quotes are tested and confirmed to break at least the custom helptext popup, if quotes are used as html encoded
      // entities in html tags, e.g. <a href=&quot;random_link.php&quot;> it also breaks. ?>
            <li>Currently, these keys are used by the calculator
            <ul><li>description_override = user defined pledge description</li>
                <li>help_link = link text for help text popup for user defined pledge<br />Start the help_link with a &lt;br&gt; tag to make the link appear underneath, rather than to the right of, the pledge description.</li>
                <li>help_title = title of the help text popup</li>
                <li>help_text = the text of the popup <br>links to other glossary items work using this syntax: &lt;a href=glossary.php#gloss_rci target=_self&gt; - note that there cannot be any quotes, single or otherwise in this html tag<br />It is recommended to break help text into paragraphs using &lt;p&gt; tags, in fact their use is encouraged even for single paragraph help texts (for css)</li>
                <li>unconditional = set to "yes" if a target is an unconditional target but the data structure of the calculator forces you to enter it as a conditional pledge.</li>
            </ul>
        </ul>
        <br /><br /> 
        Here is an example:<br />
        <textarea name="caveat" cols="120" rows="12" class="mceNoEditor">{"description_override":"reduce total emissions by 22% compared to Mexican INDC baseline", "help_label":"<br><b>important information on baseline calibration</b>", "help_title":"INDC Baseline Calibration", "help_text":"<p>Mexico has provided a BAU baseline in its INDC submissions. This BAU projection differs from the <a href=glossary.php#gloss_bau target=_self>no-policies baseline</a> used by the Climate Equity Reference Calculator. We have therefore adjusted the Mexican INDC pledge to match the baseline used by the calculator. The target emissions in 2030 are identical for both methods.</p>"}

Calculation of target "hack": (1) BAU(2030) in INDC submission (GHG only, no Black Carbon) is 973 Mt CO2e); (2) 2030 non-conditional target is 22% below BAU in 2030; (3) (1) and (2) gives an emissions target of 758,940 kt CO2e; (4) absolute 2000 emissions for MEX are 618,040Kt CO2e. (5) from (3) and (4) follows that the 2030 target is a limitation to 122.8% of 2020 levels. </textarea>
    </body>
</html>
