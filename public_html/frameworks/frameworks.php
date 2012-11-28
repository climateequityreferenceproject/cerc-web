<?php
    abstract class Framework {
    
        // ****************************************************************
        //
        // Class properties and methods
        //
        // ****************************************************************
        
        public static $frameworks = array();
        public static $master_db = "";
        public static $user_db_path = "";
        private static $world = array(
            'code' => "world",
            'name' => "World"
        );
        
        // -----------------------------------------------------------------
        // Each instance of the class should should have a main file called "framework.php"
        // in its own folder -- the framework.php file can include other files.
        // 
        // At the end of the framework.php file the framework should register itself, e.g., with:
        //    Framework::register_fw('percap', 'Equal per capita', 'PerCapita');
        // 
        // The class name should match the actual class name
        // ----------------------------------------------------------------
        public static function register_fw($id, $fw_name, $fw_classname) {
            self::$frameworks[$id] = array(
                'name' => $fw_name,
                'class' => $fw_classname
            );
        }
        
        // ----------------------------------------------------------------
        // Return true or false if the calculator is or is not being
        // called from a folder name with "dev" in it.
        // ----------------------------------------------------------------
        public static function is_dev() {
            // Note: might return "0" (a correct, non-false value)--have to check for false
            if (strpos($_SERVER['PHP_SELF'], 'dev')===false) {
                return false;
            } else {
                return true;
            }
        }
        
        // ----------------------------------------------------------------
        // Return the list of frameworks as display_name => id
        // This can be used by web interface to display the list
        // of available frameworks, and then to create an instance
        // of the appropriate class.
        // ----------------------------------------------------------------
        public static function get_frameworks() {
            $retval = array();
            foreach (self::$frameworks as $key => $val) {
                $retval[$key] = array("display_name"=>self::$frameworks[$key]['name']);
            }
            return $retval;
        }
        
        // ----------------------------------------------------------------
        // Create a copy of the master database for individual use and
        // return the filename.
        // ----------------------------------------------------------------
        // This creates a temporary database that will be deleted either immediately after use or using cron
        // If $db is specified, will make a copy of that, rather than using the master
        public static function get_user_db($db = NULL) {
            $user_db = tempnam(self::$user_db_path, "fw-sql3-");
            if ($db) {
                $db_to_copy = $db;
            } else {
                $db_to_copy = self::$master_db;
            }
            copy($db_to_copy, $user_db) or die("Couldn't create '" + $user_db + "'");
            return $user_db;
        }
        
        public static function get_world_code() {
            return self::$world['code'];
        }
        
        public static function get_world_name() {
            return self::$world['name'];
        }
        
        
        public static function db_up_to_date($db) {
            if (file_exists($db)) {
                return filemtime($db) > filemtime(self::$master_db);
            } else {
                return false;
            }
        }
        
        public static function get_good_db() {
            // Future-proof: right now keep the path, but in future might just use basename
            if (isset($_POST['user_db'])) {
                $user_db_nopath = basename($_POST['user_db']);
            } elseif (isset($_GET['db'])) {
                $user_db_nopath = basename($_GET['db']);
            } elseif (isset($_COOKIE['db'])) {
                $user_db_nopath = unserialize(stripslashes($_COOKIE['db']));
            } else {
                $user_db_nopath = null;
            }
            if ($user_db_nopath && self::add_user_db_path($user_db_nopath)) {
                $user_db = self::add_user_db_path($user_db_nopath);
                if (!self::db_up_to_date($user_db)) {
                    unlink($user_db);
                    $user_db = null;
                }
            } else {
                $user_db = null;
            }
            
            return $user_db;

        }
        
        // This creates a permanent copy of the master database, unless it is overwritten using this command
        //    $modifier: text to add to the master database name, in a standard format
        //               calls to this function using the same modifier will point to the same copy
        //    $create:   flag to create the database (if FALSE, this just returns the path)
        //    $force:    flag to create the database even if a) it exists and b) has the same modified time
        //               as the master. normally this is FALSE
        //
        //    Returns: Array('did_create' => TRUE or FALSE, 'db' => full path to db file)
        public static function dup_master_db($modifier, $create = FALSE, $force = FALSE) {
            $did_create = FALSE;
            $dbname = $modifier . "_" . basename(self::$master_db);
            $db = dirname(self::$master_db) . "/" . $dbname;
            if ($create) {
                // Definitely create if a) forcing or b) file doesn't already exist
                $do_create = $force || !file_exists($db);
                if (!$do_create) {
                    // Compare time stamps--maybe no need
                    if (filemtime($db) < filemtime(self::$master_db)) {
                        $do_create = TRUE;
                        unlink($db);
                    }
                }
                if ($do_create) {
                    if (copy(self::$master_db, $db)) {
                        $did_create = TRUE;
                    }
                }
            }
            return array('did_create' => $did_create, 'db' => $db);
        }
        
        public static function add_user_db_path($dbname) {
            // Note that this will return FALSE if the file does not exist
            return realpath(self::$user_db_path . '/' . $dbname);
        }
        
        // Just a synonym for basename
        public static function get_db_name($user_db) {
            return basename($user_db);
        }
        
        protected function db_cnx($user_db) {
            try {
                if ($user_db) {
                    $db_cnx = new PDO('sqlite:'.$user_db);
                } else {
                    $db_cnx = new PDO('sqlite:'.self::$master_db);
                }

            } catch (PDOException $e) {
                print "Error connecting to database: " . $e->getMessage() . "<br/>";
                die();
            }
            
            return $db_cnx;
        }
        
        public static function get_data_ver($user_db = NULL) {
            $db_cnx = self::db_cnx($user_db);
            
            $query_result = $db_cnx->query('SELECT data_version FROM meta;')->fetchAll();
            return $query_result[0]['data_version'];
        }

        public static function get_calc_ver($user_db = NULL) {
            $db_cnx = self::db_cnx($user_db);
            
            $query_result = $db_cnx->query('SELECT calc_version FROM meta;')->fetchAll();
            return $query_result[0]['calc_version'];
        }
        
        public static function get_year_range($user_db = NULL) {
            $db_cnx = self::db_cnx($user_db);
            
            // Create the pathway/baseline temporary view
            $query = self::sql_views_baseline_ep();
            $db_cnx->beginTransaction();
            $db_cnx->exec($query);
            $db_cnx->commit();
            $query = "SELECT MIN(year) AS min_year FROM temp_base_with_ep WHERE Baseline_MtC IS NOT NULL;";           
            $stmt = $db_cnx->query($query);
            $result = $stmt->fetchAll();
            $min_year = $result[0][0];
            $stmt->closeCursor();
            $query = "SELECT MAX(year) AS max_year FROM temp_base_with_ep WHERE (Baseline_MtC IS NOT NULL) AND (emerg_path_GtC IS NOT NULL);";
            $stmt = $db_cnx->query($query);
            $result = $stmt->fetchAll();          
            $max_year = $result[0][0];

            // Close down nicely
            $db_cnx = NULL;
            
            return array('min_year' => $min_year, 'max_year' => $max_year);

        }
        
        public static function get_emerg_paths($user_db = NULL) {
            $db_cnx = self::db_cnx($user_db);
           
            $retval = array();
            
            // This query ensures that only pathways that have numbers entered in the 'pathways' table get listed
$query = <<< EOSQL
            SELECT name_short, name_long, pathway_id, advanced FROM pathway_names,
(SELECT DISTINCT pathway FROM pathways WHERE pathway IS NOT NULL) AS pwname_temp
WHERE name_short = pwname_temp.pathway ORDER BY pathway_id;
EOSQL;
            foreach ($db_cnx->query($query) as $pathways) {
                $retval[] = array(
                    'id' => $pathways['pathway_id'],
                    'advanced' => $pathways['advanced'],
                    'short_code' =>  $pathways['name_short'],
                    'display_name' => $pathways['name_long']
                );
            }
            
            // Close down nicely
            $db_cnx = NULL;
            
            return $retval;
        }
        
        public static function get_country_list($user_db = NULL) {
            $db_cnx = self::db_cnx($user_db);
            
            $query_result = $db_cnx->query('SELECT iso3, name FROM country ORDER BY NAME;')->fetchAll(PDO::FETCH_ASSOC);
            
            // Close down nicely
            $db_cnx = NULL;
            
            return $query_result;
        }

        
        public static function get_region_list($iso3, $user_db = NULL) {
            $db_cnx = self::db_cnx($user_db);
            
            if ($iso3) {
                $query = "SELECT flags.flag AS region_code, long_name AS name FROM flags, flag_names ";
                $query .= "WHERE iso3 = '" . $iso3 . "'  AND value = 1 AND flag_names.flag = flags.flag";
                $query_result = $db_cnx->query($query)->fetchAll(PDO::FETCH_ASSOC);
            } else {
                $query_result = $db_cnx->query('SELECT flag AS region_code, long_name AS name FROM flag_names;')->fetchAll(PDO::FETCH_ASSOC);

                $world_result = array(
                    'region_code' => self::$world['code'],
                    'name' => self::$world['name']
                );
                // Add $world_result as the first item in the region list
                array_unshift($query_result, $world_result);
            }
            
            // Close down nicely
            $db_cnx = NULL;

            return $query_result;
        }
        
        // ----------------------------------------------------------------
        // Return the full list of parameters that are shared by all
        // frameworks. Each framework might have its own, framework-
        // specific parameters (defined in its own framework.php file).
        // Most of this code is simply getting the list of emergency
        // pathways from the database to fill into the shared parameters
        // array.
        // ----------------------------------------------------------------
        public static function get_shared_params($user_db = NULL) {
            $retval = self::$shared_params_default;
            $db_cnx = self::db_cnx($user_db);
            
            foreach ($db_cnx->query('SELECT param_id, int_val, real_val FROM params;') as $param_val) {
                foreach ($retval as $key=>$value) {
                    if ($value['db_param'] === $param_val['param_id']) {
                        switch ($value['type']) {
                            case 'int':
                                $retval[$key]['value'] = intval($param_val['int_val']);
                                break;
                            case 'real':
                                $retval[$key]['value'] = floatval($param_val['real_val']);
                                break;                                
                        }
                        break; // Leave inner loop: found it
                    }
                }
            } 

            // Get the list of emergency pathways: note that get_emerg_paths orders by pathway ID
            $emerg_path_list = array();
            foreach (self::get_emerg_paths($user_db) as $val) {
                $emerg_path_list[$val['id']] = $val;
            }
            $retval['emergency_path']['list'] = $emerg_path_list;
            
            // Check the year range and apply to 'cum_since_year': Note that this does not update the user's current choice of cum_since_year
            $year_range = self::get_year_range($user_db);
            $retval['cum_since_yr']['min'] = $year_range['min_year'];
            
            // Close down nicely
            $db_cnx = NULL;
            
            return $retval;
        }
        
        // A helper function to check for whether a parameter is (most likely) a boolean
        public static function is_bool($param_key, $param_array) {
            $retval = false;
            if (isset($param_array[$param_key]['type'])) {
                if ($param_array[$param_key]['type'] === 'int') {
                    if(($param_array[$param_key]['min'] == 0) && ($param_array[$param_key]['max'] == 1)) {
                        $retval = true;
                    }
                }
            }
            return $retval;
        }
        
        // ----------------------------------------------------------------
        // The shared parameters array: A structured collection of all of
        // the parameters that might be used by any of the frameworks. Each
        // entry is a parameter with information that might or might not
        // be used by the web interface. Any irrelevant (or uninitialized)
        // information is set to NULL.
        // ----------------------------------------------------------------
        private static $shared_params_default = array(
                            'cum_since_yr' => array(
                                'description' => 'The year when historical responsibility begins',
                                'advanced' => false,
                                'db_param' => 'cumsince',
                                'value' => NULL,
                                'min' => NULL,
                                'max' => 2010,
                                'step' => 10,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'use_lulucf' => array(
                                'description' => 'Include land-use emissions in baseline (from 1950 only)',
                                'advanced' => false,
                                'db_param' => 'use_lulucf',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'use_netexports' => array(
                                'description' => 'Include emissions embodied in traded goods',
                                'advanced' => false,
                                'db_param' => 'use_netexports',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'use_nonco2' => array(
                                'description' => 'Include non-CO2 gases in baseline (from 1990 only)',
                                'advanced' => false,
                                'db_param' => 'use_nonco2',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'emergency_path' => array(
                                'description' => 'The global emissions pathway under an "emergency" mitigation program',
                                'advanced' => false,
                                'db_param' => 'emerg_path_id',
                                'value' => NULL,
                                'min' => NULL,
                                'max' => NULL,
                                'step' => NULL,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'emergency_program_start' => array(
                                'description' => 'Start year of the emergency program',
                                'advanced' => false,
                                'db_param' => 'emergstart',
                                'value' => NULL,
                                'min' => NULL,
                                'max' => NULL,
                                'step' => NULL,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'baseline' => array(
                                'description' => 'Collection of baseline emissions for all countries',
                                'advanced' => false,
                                'db_param' => NULL,
                                'value' => 'default_gdrs',
                                'min' => NULL,
                                'max' => NULL,
                                'step' => NULL,
                                'list' => array(
                                    'default_gdrs' => array('display_name' => 'Default')
                                ),
                                'type' => 'string'
                            ),
                            'percent_gwp' => array(
                                'description' => 'The total annual cost of mitigation and adaptation as % GDP',
                                'advanced' => false,
                                'db_param' => 'billpercgwp',
                                'value' => NULL,
                                'min' => 0.0,
                                'max' => 100.0,
                                'step' => 0.5,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'use_sequencing' => array(
                                'description' => 'Set whether A1 acts first (checked) or A1 and NA1 act together (unchecked)',
                                'advanced' => true,
                                'db_param' => 'usesequence',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'percent_a1_rdxn' => array(
                                'description' => 'If A1 acts first, how far domestic emissions must drop relative to baseline',
                                'advanced' => true,
                                'db_param' => 'a1_perc_rdxn',
                                'value' => NULL,
                                'min' => 0.0,
                                'max' => 100.0,
                                'step' => 1.0,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'base_levels_yr' => array(
                                'description' => 'If A1 acts first, the reference year for A1 emissions reduction target',
                                'advanced' => true,
                                'db_param' => 'a1_ref_year',
                                'value' => NULL,
                                'min' => 1850,
                                'max' => 2010,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'end_commitment_period' => array(
                                'description' => 'If A1 acts first, the target year for A1 emissions reductions: after this, A1 and NA1 act together',
                                'advanced' => true,
                                'db_param' => 'sequenceyear',
                                'value' => NULL,
                                'min' => 2011,
                                'max' => 2030,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'a1_smoothing' => array(
                                'description' => 'A smoothing parameter for A1 to move toward target reduction by end of emissions period--larger values are less abrupt',
                                'advanced' => true,
                                'db_param' => 'a1_shape_param',
                                'value' => NULL,
                                'min' => 0.0,
                                'max' => 5.0,
                                'step' => 0.1,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'mit_gap_borne' => array(
                                'description' => 'The country group (A1 or A2) that makes up difference between domestic emissions reductions and emergency pathway during the first sequencing period',
                                'advanced' => true,
                                'db_param' => 'assign_mit_gap_to',
                                'value' => NULL,
                                'min' => 1,
                                'max' => 2,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'em_elast' => array(
                                'description' => 'How emissions vary with income within countries: equal to 1 if emissions are directly proportional to income',
                                'advanced' => true,
                                'db_param' => 'emisselast',
                                'value' => NULL,
                                'min' => 0.5,
                                'max' => 1.5,
                                'step' => 0.1,
                                'list' => NULL,
                                'type' => 'real'
                            )
                        );
        
        // ****************************************************************
        //
        // Instance properties and methods
        //
        // ****************************************************************
        
        // ----------------------------------------------------------------
        // This is the main framework-specific method as far as the abstract
        // class is concerned: it must be redefined by each Framework
        // instance, and carries out the calculations expected of it.
        // ----------------------------------------------------------------
        // This method must be redefined by each Framework instance
        abstract public function calculate($db, $shared_params, $fw_params);
        
        abstract public function get_default_fw_params();
        
        // ----------------------------------------------------------------
        // Methods to implement the database connection, which is maintained
        // over the life of each instance of a specific framework. (So, there
        // may be multiple connections for each instance of the Framework
        // abstract class.)
        // ----------------------------------------------------------------
        protected $db = NULL;
        
        protected function get_db() {
            return $this->db;
        }
        
        protected function db_connect($user_db) {
            try {
                $this->db = new PDO('sqlite:'.$user_db);
            } catch (PDOException $e) {
                print "Error connecting to database: " . $e->getMessage() . "<br/>";
                die();
            }
        }
        
        protected function db_close() {
            $this->db = NULL;
        }
        
        // ----------------------------------------------------------------
        // A convenience function to ensure that a table exists before using
        // it.
        // ----------------------------------------------------------------
        protected function db_table_exists($table) {
            $retval = false;
            // If no db open, return false
            if ($this->db) {
                $result_set = $this->db->query("PRAGMA table_info(" . $table . ");");
                foreach ($result_set as $record) {
                    if (count($record) > 0) {
                        $retval = true;
                        $result_set->closeCursor();
                        break;
                    }
                }
            }
            return $retval;
        }
        
        // ----------------------------------------------------------------
        // Update a value in the database if needed. It takes a set of
        // parameter information of the same form as in the shared
        // parameters array, and checks the value of that parameter
        // within the database. If it needs to be changed, then it is
        // changed. Then the method returns a boolean stating whether
        // the value was changed (true) or not (false).
        // ----------------------------------------------------------------
        protected function update_param($param) {
            $retval = false;
            // If no db connection, or not in DB, silently return "not changed"
            if ($this->db && $param['db_param']) {
                $querystring = "SELECT count() FROM params WHERE param_id='";
                $querystring .= $param['db_param'] . "' AND ";
                $querystring .= $param['type'] . "_val=" . $param['value'] . ";";
                $ret_array = $this->db->query($querystring)->fetchAll();
                $retval = !($ret_array[0][0]);
            }
            if ($retval) {
                $querystring = "UPDATE params SET ";
                $querystring .= $param['type'] . "_val = ";
                $querystring .= $param['value'] . " WHERE param_id='";
                $querystring .= $param['db_param'] . "';";
                // Make sure that the call is flushed
                $this->db->query($querystring)->closeCursor();
            }
            return $retval;
        }
        
        // ----------------------------------------------------------------
        // Basic getters and setters that are common to the instances of
        // all classes.
        // ----------------------------------------------------------------
        public $table_views = array();
        
        public function get_fw_params($user_db = NULL) {
            $retval = $this->get_default_fw_params();
            try {
                if ($user_db) {
                    $db_cnx = new PDO('sqlite:'.$user_db);
                } else {
                    $db_cnx = new PDO('sqlite:'.self::$master_db);
                }
            } catch (PDOException $e) {
                print "Error connecting to database: " . $e->getMessage() . "<br/>";
                die();
            }
            
            foreach ($db_cnx->query('SELECT param_id, int_val, real_val FROM params;') as $param_val) {
                foreach ($retval as $key=>$value) {
                    if ($value['db_param'] === $param_val['param_id']) {
                        switch ($value['type']) {
                            case 'int':
                                $retval[$key]['value'] = intval($param_val['int_val']);
                                break;
                            case 'real':
                                $retval[$key]['value'] = floatval($param_val['real_val']);
                                break;                                
                        }
                        break; // Leave inner loop: found it
                    }
                }
            }
            
            // Close down nicely
            $db_cnx = NULL;
            
            return $retval;
        }
        
        public function get_table_views() {
            return $this->table_views;
        }
        
        // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        //
        // SQL helper functions--return a query
        //
        // ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
        
        // ----------------------------------------------------------------
        // Pass the names of shared parameters and create a
        // temporary view called "sel_params"
        // ----------------------------------------------------------------
        protected function make_view_sel_params($params) {
            $retval = "DROP VIEW IF EXISTS sel_params; CREATE TEMPORARY VIEW sel_params AS SELECT\n";
            foreach ($params as $param) {
                $db_param = self::$shared_params_default[$param]['db_param'];
                $type = self::$shared_params_default[$param]['type'] . "_val";
                $retval .= "SUM(CASE WHEN param_id = '" . $db_param ."' THEN " . $type . " ELSE 0 END) AS " . $db_param . "\n";
            }
            $retval .= "FROM params";
            return $retval;
        }
        
        // ----------------------------------------------------------------
        // Return the SQL to create a view for the baseline and emergency pathway
        // Creates views:
        //   temp_ep
        //   temp_baseline
        //   temp_base_with_ep
        // ----------------------------------------------------------------
        protected function sql_views_baseline_ep() {
            return <<< EOSQL
 DROP VIEW IF EXISTS __Source_Filter; CREATE TEMPORARY VIEW __Source_Filter AS SELECT
        SUM(CASE WHEN param_id = "use_lulucf" THEN int_val ELSE 0 END) AS use_lulucf,
        SUM(CASE WHEN param_id = "use_nonco2" THEN int_val ELSE 0 END) AS use_nonco2
    FROM params;
    
 DROP VIEW IF EXISTS temp_ep; CREATE TEMPORARY VIEW temp_ep AS SELECT
    year,
    SUM(CASE WHEN pathways.source="fossil" THEN pathways.emergpath_GtC ELSE 0 END) +
    __Source_Filter.use_lulucf * SUM(CASE WHEN pathways.source="lulucf" THEN pathways.emergpath_GtC ELSE 0 END) +
    __Source_Filter.use_nonco2 * SUM(CASE WHEN pathways.source="nonco2" THEN pathways.emergpath_GtC ELSE 0 END)
        AS emerg_path_GtC
     FROM pathway_names, pathways, params, __Source_Filter
     WHERE
        pathway_names.pathway_id IN (SELECT int_val FROM params WHERE param_id = "emerg_path_id") AND
        pathway_names.name_short = pathways.pathway AND
        params.param_id = "emergstart" AND
        year > params.int_val
     GROUP BY year;
    
 DROP VIEW IF EXISTS temp_baseline; CREATE TEMPORARY VIEW temp_baseline AS
    SELECT core.iso3 AS iso3, core.year AS year,
    core.fossil_CO2_MtC + __Source_Filter.use_lulucf * ifnull(core.LULCF_MtC, nullif(__Source_Filter.use_lulucf, 1)) +
        __Source_Filter.use_nonco2 * ifnull(core.NonCO2_MtCe, nullif(__Source_Filter.use_nonco2, 1)) AS baseline_MtC
    FROM __Source_Filter, core;

 DROP VIEW IF EXISTS temp_base_with_ep; CREATE TEMPORARY VIEW temp_base_with_ep
    AS SELECT * FROM
        temp_baseline LEFT JOIN temp_ep ON (temp_baseline.year = temp_ep.year);

EOSQL;
        }
    }

    // ----------------------------------------------------------------
    // Initialization:
    //    1) Read in all class definitions
    //    2) Allow each class to register itself
    //    3) Initialize framework-specific static variables
    // ----------------------------------------------------------------
    include("frameworks_ini.php");
