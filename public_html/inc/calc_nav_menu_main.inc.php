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
        <li><a href="http://climateequityreference.org/Calculator-about" target="_blank">About the Climate Equity Reference Calculator</a></li>
<!--        <li><a href="<?php echo $scorecard_home_url;?>" target="_blank">Climate Equity Pledge Scorecard</a></li>-->
        <li class="last"><a href="<?php echo $gloss_url;?>" target="_blank">Glossary</a></li>
<!--        <li class="last"><a href="http://gdrights.org/about" target="_blank">About Greenhouse Development Rights</a></li>-->
    </ul>
</div><!-- end #nav -->