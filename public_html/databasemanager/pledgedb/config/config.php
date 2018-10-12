<?php
require_once('../../../config.php'); // local global config file
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
                "dbname" => $pledge_db_config["dbname"],
                "user"   => $pledge_db_config["user"],
                "pwd"    => $pledge_db_config["pwd"],
                "host"   => $pledge_db_config["host"]
            ),
            "development" => array(  
                "dbname" => $pledge_db_config["dbname"],
                "user"   => $pledge_db_config["user"],
                "pwd"    => $pledge_db_config["pwd"],
                "host"   => $pledge_db_config["host"]
            )  
        ),
        "is_dev" => null,
        "dev_calc_creds" => array (
                "user" => $dev_calc_creds["user"], 
                "pass" => $dev_calc_creds["pass"]
        ),
        "api_url" => array (
                "public" => $URL_calc_api,
                "dev"    => $URL_calc_api_dev
        )
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
        if (self::is_dev()) {
            return self::$config['db']['development'];
        } else {
            return self::$config['db']['public'];
        }            
    }
    
    public static function dev_calc_creds() {
        return self::$config['dev_calc_creds'];
    }

    public static function api_url($key = NULL) {
        if (isset($key)) {
            $value = self::$config['api_url'];
            return $value[$key[0]];
        } else {
            return self::$config['api_url'];
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
