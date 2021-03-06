<?php

// if a user uses the _dev version of the calculator, we drop a cookie on her
// computer to identify them as developer, in which case the google analytics
// tracking code gets omitted (even during his use of the public calculator)
if ((strpos(dirname(__FILE__), '_dev')) || (strpos(dirname(__FILE__), '-dev'))) {
    $domain = ".climateequityreference.org";
    if (strpos(dirname(__FILE__),"gdrights.org")) { $domain = ".gdrights.org"; }
    setcookie("this_user_is_developer","true",time()+60*60*24*365.25/4, "/", $domain); // cookie life time = 1/4 of a year
}

$user_is_developer = (isset($_COOKIE['this_user_is_developer']) && ($_COOKIE['this_user_is_developer']=='true'));

if (!($user_is_developer)) {
    $ga_script = "\n";
    $ga_script .= "<script>";
    $ga_script .= "\n";
    $ga_script .= "  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){(i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)})(window,document,'script','//www.google-analytics.com/analytics.js','ga');";
    $ga_script .= "\n";
    $ga_script .= "ga('create', '" . $ga_tracking_code . "', 'auto');";
    $ga_script .= "\n";
    $ga_script .= "  ga('send', 'pageview');";
    $ga_script .= "\n";
    $ga_script .= "</script>";
    $ga_script .= "\n";

    echo ($ga_script);
}
