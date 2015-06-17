<?php
/**
 * Configuration master file
 * 
 * @package GDRSpledgeDB
 * @subpackage Config
 * @author Eric Kemp-Benedict (eric.kemp-benedict@sei-international.org)
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache License 2.0
 * @copyright 2012-2013 Stockholm Environment Institute and EcoEquity
 */

/**
 * Constants class
 * 
 * This is a small class, and is only one of several definitions and commands
 * in the configuration file. Its purpose is to encapsulate constants
 * used elswehere, without polluting the global namespace.
 *  
 */
class Constants {
    private static $config = array(  
        "db" => array(
            "public" => array(  
                "dbname" => "pledges",
                "user" => "pledges",
                "pwd" => "***REMOVED***",
                "host" => "localhost"
            ),
            "development" => array(  
                "dbname" => "pledges-dev",
                "user" => "pledges-dev",
                "pwd" => "***REMOVED***",
                "host" => "localhost"
            ),  
            "public_new" => array(  
                "dbname" => "pledges_cerp",
                "user" => "pledges_cerp",
                "pwd" => "***REMOVED***",
                "host" => "localhost"
            ),
            "development_new" => array(  
                "dbname" => "pledges_cerp-dev",
                "user" => "pledges_cerp-dev",
                "pwd" => "***REMOVED***",
                "host" => "localhost"
            )  
        ),
        "is_dev" => null
    );
    
    /**
     * Get whether this is the development version
     * 
     * @return boolean
     */
    public static function is_dev() {
        if (is_null(self::$config["is_dev"])) {
            self::$config["is_dev"] = strpos($_SERVER['REQUEST_URI'],"dev") !== false;
        }
        return self::$config["is_dev"];
    }
    
    /**
     * Return database connection information
     * 
     * @return array
     */
    public static function db_info() {
        if (strpos(dirname(__FILE__), "gd/gdrights.org")) {
            // pre-move calculator 
            if (self::is_dev()) {
                return self::$config['db']['development'];
            } else {
                return self::$config['db']['public'];
            }
        } else {
            // post-move calculator 
            if (self::is_dev()) {
                return self::$config['db']['development_new'];
            } else {
                return self::$config['db']['public_new'];
            }            
        }
    }
}

/*
 * Error reporting
 */
ini_set('log_errors', 1);
if (isset($_GET['debug']) && $_GET['debug'] == 'yes') {
    ini_set('display_errors',1); 
    error_reporting(E_ALL|E_STRICT);
}

/*
 * Load other config files
 */
require_once 'i18n.php';

/*
 * I18N locale
 */
if (isset($_GET['lang'])) {
    $locale = $_GET['lang'];
} else {
    $locale = 'en_EN';
}
i18n_init($locale);

?>
