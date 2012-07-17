<?php
    function get_head($title, $stylesheets) {
        $retval = <<< EOHTML
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <meta name="generator" content="HTML Tidy for Linux/x86 (vers 1 September 2005), see www.w3.org" />
    <title>$title</title>
    <meta http-equiv="X-UA-Compatible" content="IE=EmulateIE7" />
    <meta name="generator" content="WordPress 2.7" /> <!-- leave this for stats please -->
    <!--<style type="text/css" media="screen">
        @import url( http://gdrights.org/wp-content/themes/custom_GDR/style.css );
    </style>-->
    <!--[imcss] *** Infinite Menus Core CSS: Keep this section in the document head for full validation. -->
    <style type="text/css">.imcm ul,.imcm li,.imcm div,.imcm span,.imcm a{text-align:left;vertical-align:top;padding:0px;margin:0;list-style:none outside none;border-style:none;background-image:none;clear:none;float:none;display:block;position:static;overflow:visible;line-height:normal;}.imcm li a img{display:inline;border-width:0px;}.imcm span{display:inline;}.imcm .imclear,.imclear{clear:both;height:0px;visibility:hidden;line-height:0px;font-size:1px;}.imcm .imsc{position:relative;}.imcm .imsubc{position:absolute;visibility:hidden;}.imcm li{list-style:none;font-size:1px;float:left;}.imcm ul ul li{width:100%;float:none !important;}.imcm a{display:block;position:relative;}.imcm ul .imsc,.imcm ul .imsubc {z-index:10;}.imcm ul ul .imsc,.imcm ul ul .imsubc{z-index:20;}.imcm ul ul ul .imsc,.imcm ul ul ul .imsubc{z-index:30;}.imde ul li:hover .imsubc{visibility:visible;}.imde ul ul li:hover  .imsubc{visibility:visible;}.imde ul ul ul li:hover  .imsubc{visibility:visible;}.imde li:hover ul  .imsubc{visibility:hidden;}.imde li:hover ul ul .imsubc{visibility:hidden;}.imde li:hover ul ul ul  .imsubc{visibility:hidden;}.imcm .imea{display:block;position:relative;left:0px;font-size:1px;line-height:1px;height:0px;width:1px;float:right;}.imcm .imea span{display:block;position:relative;font-size:1px;line-height:0px;}.dvs,.dvm{border-width:0px}/*\*//*/.imcm .imea{visibility:hidden;}/**/</style>
    <!--[if IE]><style type="text/css">.imcm .imea span{position:absolute;}.imcm .imclear,.imclear{display:none;}.imcm{zoom:1;} .imcm li{curosr:hand;} .imcm ul{zoom:1}.imcm a{zoom:1;}</style>
    <![endif]--><!--[if gte IE 7]><style type="text/css">.imcm .imsubc{background-image:url(ie_css_fix);}</style><![endif]--><!--end-->
    <!--[imstyles] *** Infinite Menu Styles: Keep this section in the document head for full validation. -->
    <link rel="stylesheet" href="/wp-content/themes/custom_GDR/imenus0.css" type="text/css" /><!--end-->
EOHTML;
        foreach ($stylesheets as $value) {
            $retval .= '<link rel="stylesheet" href="' . $value['href'] . '" type="text/css" media="' .$value['media'] . '" />';
        }
        
        return $retval;
    }


    function get_navigation() {
return <<< EOHTML
        <div id="container">
            <div id="header" onclick="location.href='http://gdrights.org/';" style="cursor: pointer;">  
                <div id="partners">
                    <ul>
                        <li><a id="ecoequity" href="http://www.ecoequity.org">EcoEquity</a></li>
                        <li><a id="seius" href="http://www.sei.se">SEI-US</a></li>
                    </ul>
                </div><!-- end #partners -->
                <!--   <div id="cssswitcheralt"><p>This design uses up to 20% less energy per square inch on CRT monitors. <a href="#" onclick="setActiveStyleSheet('default'); 
                return false;">Change to default style</a></p>
                </div>-->
            </div><!-- end #header -->
            <div id="headerlinks">
                <ul>
                    <!--|**START IMENUS**|imenus0,inline-->
                    <!--  ****** Infinite Menus Structure & Links ***** -->
                    <div class="imrcmain0 imgl" style="width:950px;z-index:999999;position:relative;">
                        <div class="imcm imde" id="imouter0">
                            <ul id="imenus0">
                                <li class="imatm" style="width:115px;"><a href="http://gdrights.org">Home</a></li>
                                <li class="imatm" style="width:115px;"><a href="http://gdrights.org/about"><span class="imea imeam"><span></span></span>About</a>
                                    <div class="imsc">
                                        <div class="imsubc" style="width:115px;top:-2px;left:-1px;">
                                            <ul style="">
                                                <li><a href="http://gdrights.org/about">About GDRs</a></li>	
                                                <li><a href="http://gdrights.org/partners">Partners &amp; Friends</a></li>
                                                <li><a href="http://gdrights.org/authors">Authors &amp; Contacts</a></li>
                                                <li><a href="http://gdrights.org/archive">Accomplishments</a></li>
                                                <li><a href="http://gdrights.org/in-the-news-archive">Notices &amp; Media</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </li>
                                <li class="imatm"  style="width:140px;"><a href="http://gdrights.org/publications">Publications</a></li>
                                <li class="imatm"  style="width:190px;"><a href="http://gdrights.org/events">Events &amp; Presentations</a></li>
                                <li class="imatm"  style="width:160px;"><a href="http://gdrights.org/Calculator-about">GDRs Calculator</a></li>
                                <li class="imatm"  style="width:120px;"><a href="http://www.ecoequity.org">EcoEquity </a> </li>
                                <li class="imatm"  style="width:110px;"><a href="http://www.sei.se" style="border-color:#4d59a4;">SEI</a></li>
                            </ul>
                            <div class="imclear">&nbsp;</div>
                        </div>
                    </div><!--|**END IMENUS**|-->
                </ul>
            </div><!-- end #headerlinks -->
EOHTML;
    }
    
    function get_footer($data_ver, $calc_ver) {
        $year = date("Y");
return <<< EOHTML
            <br class="clear"/>
        </div><!-- end #container -->
        <div id="footer">
            <p><strong>Greenhouse Development Rights</strong> is a project of <a href="http://www.ecoequity.org/">EcoEquity</a> and the <a href="http://www.sei-international.org">Stockholm Environment Institute</a> &#169; 2008-$year </p>
                <p>data version $data_ver  &nbsp;&nbsp;calculator version $calc_ver</p>
        </div><!-- end #footer -->
        <!--[imcode]*** Infinite Menus Settings / Code - This script reference must appear last. ***
        *Note: This script is required for scripted add on support and IE 6 sub menu functionality.
        *Note: This menu will fully function in all CSS2 browsers with the script removed.-->
        <script language="JavaScript" src="ocscript.js" type="text/javascript"></script><!-- fix file path - in gdrights.org site root? -->
EOHTML;
    }