<?php
   
// this file is included below the <body> tag
// of the index.php. The idea is that here you 
// would put code that returns html/javascript 
// code that would in turn generate a popup notice
// for users to inform them of news from time to
// time. probably a good idea to have some sort
// of cookie based solution to check whether users
// have seen the pop up lately as to not bother
// them too much.


// This is for displaying a popup notice that is at the same time a MailChimp signup form for calculator update notices
if (!(isset($_COOKIE['MCPopupCount']))) {
    $popupcount = 1;
} else {
    $popupcount = $_COOKIE['MCPopupCount'] + 1;
}
if ($popupcount < 12) { // we only gonna show the notice 12 times in total
    if ($popupcount <= 3) {
    $notice_frequency = 86400; // time between Update Notice popups in seconds. 3600 = 1 hour, 86400 = 1 day
    } elseif ($popupcount <= 6) {
    $notice_frequency = 86400*3; // time between Update Notice popups in seconds. 3600 = 1 hour, 86400 = 1 day
    } elseif ($popupcount <= 10) {
    $notice_frequency = 86400*7; // time between Update Notice popups in seconds. 3600 = 1 hour, 86400 = 1 day
    } else { 
    $notice_frequency = 86400*10; // time between Update Notice popups in seconds. 3600 = 1 hour, 86400 = 1 day
    }
    if (!(isset($_COOKIE['MCPopup']))) {
        setcookie('MCPopup', 'MCPopup', time() + $notice_frequency, '/') ;
        setcookie('MCPopupCount', $popupcount, time() + 86400*365, '/') ;
        setcookie('MCPopupClosed', '', time() - 3600, '/') ;
        // now we can actually output the javascipt code that pops up the popup
        echo '        <script type="text/javascript" src="//downloads.mailchimp.com/js/signup-forms/popup/unique-methods/embed.js" data-dojo-config="usePlainJson: true, isDebug: false"></script>';
        echo '        <script type="text/javascript">window.dojoRequire(["mojo/signup-forms/Loader"], function(L) { L.start({"baseUrl":"mc.us19.list-manage.com","uuid":"74a392f7e778f1f1c3f4f2aa3","lid":"9eff2395e5","uniqueMethods":true}) })</script>';
    }
}