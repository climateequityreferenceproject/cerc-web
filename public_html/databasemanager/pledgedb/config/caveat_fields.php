<?php

$caveat_fields = array(
    array("name"=>"description_override", "title" => "User-defined pledge description", "type"=>"textarea", 
          "description"=>"user defined pledge description"),
    array("name"=>"help_label", "title" => "link text for help text popup for user defined pledge", "type"=>"textbox", 
          "description"=>"link text for help text popup for user defined pledge<br />Start the help_link with a &lt;br&gt; tag to make the link appear underneath, rather than to the right of, the pledge description."),
    array("name"=>"help_title", "title" => "title of the help text popup", "type"=>"textbox", 
          "description"=>"title of the help text popup"),
    array("name"=>"help_text", "title" => "the text of the popup", "type"=>"textbox", 
          "description"=>"the text of the popup <br>links to other glossary items work using this syntax: &lt;a href=glossary.php#gloss_rci target=_self&gt; - note that there cannot be any quotes, single or otherwise in this html tag<br />It is recommended to break help text into paragraphs using &lt;p&gt; tags, in fact their use is encouraged even for single paragraph help texts (for css)"),
    array("name"=>"unconditional", "title" => "Pledge-type overrive: unconditional", "type"=>"yes", 
          "description"=>"set to \"yes\" if a target is an unconditional target but the data structure of the calculator forces you to enter it as a conditional pledge."),
    array("name"=>"conditional", "title" => "Pledge-type overrive: conditional", "type"=>"yes", 
          "description"=>"set to \"yes\" if a target is a conditional target but the data structure of the calculator forces you to enter it as an unconditional pledge."),
    array("name"=>"pledge_qualifier", "title" => "Pledge Qualifier text", "type"=>"textbox", 
          "description"=>"text that appears in brackets after the, for example, \"China unconditional pledge\" text - to be used for example, to distinguish between low and high ends of pledge ranges"),
    array("name"=>"pledge_breakdown_assumptions", "title" => "Pledge Breakdown Assumptions Help Text", "type"=>"textarea", 
          "description"=>"This is the text that will be included to the user when they receive a warning that the gases they chose to display differ from the gases specified in the INDC. If not specified, they will be shown the default assumptions description. You can include HTML markup, including links to external analysis/assumptions, but remember: no single or double quotes in HTML tags!"),
);