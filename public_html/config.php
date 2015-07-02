<?php

$svn_LastChangedDate = "$LastChangedDate$";
$svn_LastChangedRevision = "$LastChangedRevision$";

// we don't really know where the calculator is accessed from, so we want to 
// construct certain links to retain the current "domain name space"
if(isset($_SERVER['HTTP_X_FORWARDED_HOST'])) {
    $host_name = $_SERVER['HTTP_X_FORWARDED_HOST'];
} else {
    $host_name = $_SERVER['HTTP_HOST'];     
}

if (strpos(dirname(__FILE__), "gd/gdrights.org")) {
    // pre-move calculator 
     
    // core database locations
    $core_db = "/***REMOVED***/databases/gdrs_core_pub.sql3";
    $core_db_dev = "/***REMOVED***/databases/gdrs_core.sql3";

    // set various paths
    // note, when changing the various temporary storage paths, ensure make sure to also change the cron job that deletes old junk there
    $user_db_store = "/***REMOVED***/sessions/gdrs-db";  // temporary copies of user databases
    $svg_tmp_dir = "/***REMOVED***/html/tmp"; // svg files of country graphs are generated here
    $xls_tmp_dir = $user_db_store; // where temporary files are saved when xls download is generated
    //$xls_file_slug = "gdrs_all_output_"; // this is the beginning of the file name of the Excel download
    //$xls_copyright_notice = "Greenhouse Development Rights Online Calculator (http://" . $host_name . ")"; // this is the message for cell A1 in the downloaded Excel file
    $xls_file_slug = "cerc_all_output_"; // this is the beginning of the file name of the Excel download
    $xls_copyright_notice = "Climate Equity Reference Project Online Calculator (http://" . $host_name . ")"; // this is the message for cell A1 in the downloaded Excel file

    // calculator engine
    $calc_engine_path = "/***REMOVED***/gdrscode/engine/gdrsclib/dist/Public/GNU-Linux-x86/gdrsclib";
    $calc_engine_path_dev = "/***REMOVED***/gdrscode/engine/gdrsclib/dist/Development/GNU-Linux-x86/gdrsclib";

    // pledge db connection information
    $pledge_db_config = array( 
                "dbname" => "pledges",
                "user" => "pledges",
                "pwd" => "***REMOVED***",
                "host" => "localhost"
                );

    // define certain URLs. If pointing to a directory, include trailing slash
    $URL_calc = 'http://' . $host_name . '/calculator/';
    $URL_calc_dev = 'http://' . $host_name . '/calculator_dev/';
    $URL_sc = 'http://' . $host_name . '/scorecard/';
    $URL_sc_dev = 'http://' . $host_name . '/scorecard_dev/';
    $URL_gloss = "http://" . $host_name . "/calculator/glossary.php";
    $URL_gloss_dev = "http://" . $host_name . "/calculator_dev/glossary.php";
    $URL_calc_api = 'http://' . $host_name . '/calculator/api/';
    $URL_calc_api_dev = 'http://' . $host_name . '/calculator_dev/api/';

} else {
    // post-move calculator 
     
    // core database locations
    $core_db = "/***REMOVED***/databases/gdrs_core_pub.sql3";
    $core_db_dev = "/***REMOVED***/databases/gdrs_core.sql3";

    // set various paths
    // note, when changing the various temporary storage paths, ensure make sure to also change the cron job that deletes old junk there (this might not be necessary anymore since the WebGUI itself is not deleting old junk (I think))
    $user_db_store = "/***REMOVED***/sessions/user-dbs";  // temporary copies of user databases
    $svg_tmp_dir = "/***REMOVED***/html/tmp"; // svg files of country graphs are generated here
    $xls_tmp_dir = $user_db_store; // where temporary files are saved when xls download is generated
    //$xls_file_slug = "gdrs_all_output_"; // this is the beginning of the file name of the Excel download
    //$xls_copyright_notice = "Greenhouse Development Rights Online Calculator (http://" . $host_name . ")"; // this is the message for cell A1 in the downloaded Excel file
    $xls_file_slug = "cerc_all_output_"; // this is the beginning of the file name of the Excel download
    $xls_copyright_notice = "Climate Equity Reference Project Online Calculator (http://" . $host_name . ")"; // this is the message for cell A1 in the downloaded Excel file

    // calculator engine
    $calc_engine_path = "/***REMOVED***/gdrscode/engine/gdrsclib/dist/Public/GNU-Linux-x86/gdrsclib";
    $calc_engine_path_dev = "/***REMOVED***/gdrscode/engine/gdrsclib/dist/Development/GNU-Linux-x86/gdrsclib";

    // pledge db connection information
    $pledge_db_config = array( 
                "dbname" => "pledges_cerp",
                "user" => "pledges_cerp",
                "pwd" => "***REMOVED***",
                "host" => "localhost"
                );

    // define certain URLs. If pointing to a directory, include trailing slash
    // need ===false to cover the possible (even very likely) non-false value 0
    if ((strpos($host_name, "calculator-dev.") === false) && (strpos($host_name, "calculator.") === false)) {
        // accessed through directory (should not occur due to redirects, but better safe than sorry)
        $URL_calc = 'http://' . $host_name . '/calculator/';
        $URL_calc_dev = 'http://' . $host_name . '/calculator-dev/';
        $URL_sc = 'http://' . $host_name . '/scorecard/';
        $URL_sc_dev = 'http://' . $host_name . '/scorecard_dev/';
        $URL_gloss = "http://" . $host_name . "/calculator/glossary.php";
        $URL_gloss_dev = "http://" . $host_name . "/calculator-dev/glossary.php";
        $URL_calc_api = 'http://' . $host_name . '/calculator/api/';
        $URL_calc_api_dev = 'http://' . $host_name . '/calculator-dev/api/';        
    } else {
        // accessed through subdomain
        $URL_calc = 'http://calculator.climateequityreference.org/';
        $URL_calc_dev = 'http://calculator-dev.climateequityreference.org/';
        $URL_sc = 'http://calculator.climateequityreference.org/scorecard/';           // this doesn't really work, but also should not be needed; scorecard is not in the same subdomain...
        $URL_sc_dev = 'http://calculator.climateequityreference.org/scorecard_dev/';   // this doesn't really work, but also should not be needed; scorecard is not in the same subdomain...
        $URL_gloss = 'http://calculator.climateequityreference.org/glossary.php';
        $URL_gloss_dev = 'http://calculator-dev.climateequityreference.org/glossary.php';
        $URL_calc_api = 'http://calculator.climateequityreference.org/api/';
        $URL_calc_api_dev = 'http://calculator-dev.climateequityreference.org/api/';
    }
}

// this is the directory where the include files of the help db project
// specifically, db_gdrs_help.inc.php and error.html.php
$helpdb_include_path = $_SERVER['DOCUMENT_ROOT'] . '/helpdb/includes/';

// Excel download table header replacements
// trying to mask the GDRs legacy of the CERP calculator, without going crazy with
// changes to the core database, this array provides for a rename mask for the 
// table headers for the Excel data file download. 
// syntax: "old_value" => "new_value"
$excel_download_header_rename = array( 
            "gdrs_alloc_MtCO2"       => "allocation_MtCO2",
            "gdrs_r_MtCO2"           => "responsibility_MtCO2",
            "gdrs_c_blnUSDMER"       => "capacity_blnUSDMER",
            "gdrs_rci"               => "rci",
            "gdrs_pop_mln_above_dl"  => "pop_mln_above_dl",
            "gdrs_pop_mln_above_lux" => "pop_mln_above_lux"
            );

// the _dev calculator might be .htaccess protected. If so, we need to pass creds
$dev_calc_creds = array ("user"=>"***REMOVED***", "pass"=>"***REMOVED***");

// where the main website lives - we don't want to use $host_name for this as it might include subdomain names
$main_domain_host = "climateequityreference.org";
        
// Google Analytics - the tracking code is specific for the calculator; 
// the CERP website has its own
$ga_tracking_code = '***REMOVED***';
