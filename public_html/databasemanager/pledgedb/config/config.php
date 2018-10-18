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

if (is_readable('../../config.php')) {  // load global config file
    require_once('../../config.php');
} else {
    die("Cannot read config.php file. If this is a new installation, locate the config.php.new file, enter the required information, and rename if config.php.");
}

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
        "is_dev" => null
     );
 
    /**
     * Get whether this is the development version
     * 
     * @return boolean
     */
    public static function is_dev() {
        if (is_null(self::$config["is_dev"])) {
            self::$config["is_dev"] = (strpos($_SERVER['REQUEST_URI'],"-dev") !== false) || (strpos($_SERVER['REQUEST_URI'],"_dev") !== false);
        }
        return self::$config["is_dev"];
    }
    
    /**
     * Return database connection information
     * 
     * @return array
     */
    public static function db_info() {
        global $pledge_db_config;
        if (self::is_dev()) {  // in theory supporting the option to have different databases for developer calculator, but not currently used
            return $pledge_db_config;
        } else {
            return $pledge_db_config;
        }            
    }
    
    public static function dev_calc_creds() {
		global $dev_calc_creds;
        return $dev_calc_creds;
    }

    public static function api_url($key = NULL) {
        global $URL_calc_api;
        global $URL_calc_api_dev;
        if (isset($key)) {
            if ($key="dev") {
                $value = $URL_calc_api_dev;
            } else {
                $value = $URL_calc_api;
            }
            return $value;
        } else {
            return $URL_calc_api;
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
