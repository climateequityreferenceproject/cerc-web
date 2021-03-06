<?php
/**
 * glossary.php
 * 
 * PHP Version 5
 *
 * @package GDRsCalculator
 * @copyright 2011-2014 EcoEquity and Stockholm Environment Institute
 * @license GNU GPLv3 https://www.gnu.org/licenses/gpl-3.0.html
 * @link http://www.gdrights.org/
 */

require_once "help/HWTHelp/HWTHelp.php";
include("core.php");
?>
<!doctype html>
<!-- paulirish.com/2008/conditional-stylesheets-vs-css-hacks-answer-neither/ -->
<!--[if lt IE 7]> <html class="no-js ie6 oldie" lang="en"> <![endif]-->
<!--[if IE 7]>    <html class="no-js ie7 oldie" lang="en"> <![endif]-->
<!--[if IE 8]>    <html class="no-js ie8 oldie" lang="en"> <![endif]-->
<!-- Consider adding an manifest.appcache: h5bp.com/d/Offline -->
<!--[if gt IE 8]><!--> <html class="no-js" lang="en"> <!--<![endif]-->
<head>
  <meta charset="utf-8">

  <!-- Use the .htaccess and remove these lines to avoid edge case issues.
       More info: h5bp.com/b/378 -->
  <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">

  <title>Glossary | Climate Equity Reference Calculator</title>
  <meta name="description" content="">
  <meta name="author" content="">

  <!-- Mobile viewport optimized: j.mp/bplateviewport -->
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <!-- Place favicon.ico and apple-touch-icon.png in the root directory: mathiasbynens.be/notes/touch-icons -->

  <!-- CSS: implied media=all -->
  <!-- CSS concatenated and minified via ant build script-->
  <link rel="stylesheet" href="css/cescalc.css">
  <!-- end CSS-->

  <!-- More ideas for your <head> here: h5bp.com/d/head-Tips -->

  <!-- All JavaScript at the bottom, except for Modernizr / Respond.
       Modernizr enables HTML5 elements & feature detects; Respond is a polyfill for min/max-width CSS3 Media Queries
       For optimal performance, use a custom Modernizr build: www.modernizr.com/download/ -->
  <script src="js/modernizr-2.6.2.min.js"></script>
  <?php include("inc/googleanalytics.php"); ?>
  </head>  
  <body class="glossary group">
    <div id="container" class="group">
<!--    <header>
        <h1>Climate Equity Reference Calculator</h1>
    </header>-->
    <?php include("inc/calc_branding.inc.php"); ?>

    <div id="main" role="main" class="group">
        <h1>Glossary</h1>

        <?php
            $glossary = new HWTHelp('def_link', 'glossary.php', 'calc_gloss');
            echo $glossary->getHelpPage();
        ?>

    </div> <!-- end of #main -->
    <br><?php include("inc/calc_footer.inc.php"); ?>
  </div> <!-- end of #container -->


  <!-- JavaScript at the bottom for fast page loading -->

  <!-- scripts concatenated and minified via ant build script-->
  <!-- end scripts-->

	
  <!-- Change UA-XXXXX-X to be your site's ID -->
  <script>
    window._gaq = [['_setAccount','UA-XXXXX-X'],['_trackPageview'],['_trackPageLoadTime']];
    Modernizr.load({
      load: ('https:' == location.protocol ? '//ssl' : '//www') + '.google-analytics.com/ga.js'
    });
  </script>


  <!-- Prompt IE 6 users to install Chrome Frame. Remove this if you want to support IE 6.
       chromium.org/developers/how-tos/chrome-frame-getting-started -->
  <!--[if lt IE 7 ]>
    <script src="//ajax.googleapis.com/ajax/libs/chrome-frame/1.0.3/CFInstall.min.js"></script>
    <script>window.attachEvent('onload',function(){CFInstall.check({mode:'overlay'})})</script>
  <![endif]-->
  </body>

</html>