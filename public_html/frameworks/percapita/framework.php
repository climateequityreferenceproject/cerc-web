<?php
    class PerCapita extends Framework {
        public static $exec_path = NULL;
        
        // No parameters--just equal per capita emissions
        private $fw_params_default = array();
        
        public $table_views = array(
						    'percap_alloc' => array(
                                'display_name' => 'Equal per capita allocations',
                                'time_series' => true
                            )
                        );
        
        public function calculate($user_db, $shared_params, $fw_params) {
            $this->db_connect($user_db);
            
            // Does the gdrs table even exist? If not, say "changed"
            $params_changed = !$this->db_table_exists('fw_percap');
            
            foreach ($shared_params as $id => $param_array) {
                if ($this->update_param($param_array)) {
                    $params_changed = true;
                }
            }
            
            // Steps for per capita
            //  1) Make new percapita table by calculating directly
            //  2) Register it as a new scheme
            // Calc is: either baseline or popshare * emergency pathway
            if ($params_changed) {
            
$query = <<< EOSQL
    CREATE TEMPORARY VIEW totals AS
    SELECT year, SUM(pop_person) AS tot_person FROM core GROUP BY year;
EOSQL;

$query .= <<< EOSQL
    DROP TABLE IF EXISTS fw_percap;
    CREATE TABLE fw_percap AS SELECT core.iso3 AS iso3, core.year AS year, 
        CAST(ifnull(1000 * pop_person/totals.tot_person * emerg_path_GtC, baseline_MtC) AS REAL) AS allocation_MtC
    FROM core, view_base_with_ep, totals
    WHERE core.iso3 = view_base_with_ep.iso3 AND
        core.year = viewb_ase_with_ep.year AND
        totals.year = core.year;
    INSERT OR IGNORE INTO schemes VALUES ("Equal per capita allocation", "percap");
EOSQL;

                $this->get_db()->beginTransaction();
                $this->get_db()->exec($query);
                $this->get_db()->commit();
                
            }
            
            $this->db_close();
            
            return $params_changed;
        }
        
        public function get_default_fw_params() {
            return $this->fw_params_default;
        }
    }
    
    // Register the framework
    Framework::register_fw('percap', 'Equal per capita', 'PerCapita');
