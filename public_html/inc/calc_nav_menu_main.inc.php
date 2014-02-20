<?php
    if (Framework::is_dev()) {
        $gloss_url = "http://gdrights.org/calculator_dev/glossary.php";
    } else {
        $gloss_url = "http://gdrights.org/calculator/glossary.php";
    }

    if (Framework::is_dev()) {
        $scorecard_home_url = 'http://www.gdrights.org/scorecard_dev/';
    } else {
        $scorecard_home_url = 'http://www.gdrights.org/scorecard/';
    }
?>
<div id="nav">
    <ul class="group">
        <li><a href="http://gdrights.org/Calculator-about">About the Calculator and Scorecard</a></li>
        <li><a href="<?php echo $scorecard_home_url;?>">Climate Equity Pledge Scorecard</a></li>
        <li><a href="<?php echo $gloss_url;?>">Glossary</a></li>
        <li><a href="http://gdrights.org/about">About Greenhouse Development Rights</a></li>
        <li><a href="http://www.ecoequity.org">EcoEquity </a></li>
        <li class="last"><a href="http://www.sei-international.org">SEI</a></li>
    </ul>
</div><!-- end #nav -->