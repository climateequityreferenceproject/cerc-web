<?php
    class GreenhouseDevRights extends Framework {
        public static $exec_path = NULL;
        
        private $fw_params_default = array(
                            'dev_thresh' => array(
                                'description' => 'Below this income, individual income (and associated emissions) are excluded from capacity and responsibility',
                                'advanced' => false,
                                'db_param' => 'dev_thresh',
                                'value' => NULL,
                                'min' => 0.0,
                                'max' => 20000.0,
                                'step' => 500.0,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'lux_thresh' => array(
                                'description' => 'Above this income, 100% of individual income (and associated emissions) count toward capacity and responsibility',
                                'advanced' => true,
                                'db_param' => 'lux_thresh',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1000000,
                                'step' => array(
                                    array('cutoff' => 30000, 'step' => 5000),
                                    array('cutoff' => 50000, 'step' => 20000),
                                    array('cutoff' => 500000, 'step' => 25000),
                                    array('cutoff' => NULL, 'step' => 100000)
                                ),
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'interp_btwn_thresh' => array(
                                'description' => 'If this is checked, then capacity and responsibility increase steadily between the development and luxury thresholds',
                                'advanced' => true,
                                'db_param' => 'interp_between_thresholds',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'r_wt' => array(
                                'description' => 'The weight given to responsibility for historical emissions vs. capacity to contribute to mitigation and adaptation costs in RCI',
                                'advanced' => false,
                                'db_param' => 'respweight',
                                'value' => NULL,
                                'min' => 0.0,
                                'max' => 1.0,
                                'step' => 0.1,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'do_luxcap' => array(
                                'description' => 'If this is checked, then baselines are capped at the luxury threshold',
                                'advanced' => true,
                                'db_param' => 'do_luxcap',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'luxcap_mult' => array(
                                'description' => 'An amount to multiply the luxury threshold to calculate capacity',
                                'advanced' => true,
                                'db_param' => 'lux_thresh_mult',
                                'value' => NULL,
                                'min' => 1.0,
                                'max' => 10.0,
                                'step' => 1.0,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'use_kab' => array(
                                'description' => 'If this is checked, then baselines are capped at the luxury threshold',
                                'advanced' => true,
                                'db_param' => 'use_kab',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                            'kab_only_ratified' => array(
                                'description' => 'If this is checked, then baselines are capped at the luxury threshold',
                                'advanced' => true,
                                'db_param' => 'kab_only_ratified',
                                'value' => NULL,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            ),
                        );
                        
        public $table_views = array(
                            'gdrs_default' => array(
                                'display_name' => 'Overview',
                                'time_series' => false
                            ),
                            'gdrs_tax' => array(
                                'display_name' => 'Fair share (user-estimated incremental costs)',
                                'time_series' => false
                            ),
                            'gdrs_RCI' => array(
                                'display_name' => 'RCI time series',
                                'time_series' => true
                            ),
                            'gdrs_alloc' => array(
                                'display_name' => 'Allocations time series',
                                'time_series' => true
                            ),
                            'gdrs_alloc_pc' => array(
                                'display_name' => 'Allocations per capita time series',
                                'time_series' => true
                            ),
                            'gdrs_country_report' => array(
                                'display_name' => 'Country/region report',
                                'time_series' => false
                            )
                        );
        
        public function calculate($user_db, $shared_params, $fw_params) {
            $this->db_connect($user_db);
            
            // Does the gdrs table even exist? If not, say "changed"
            $params_changed = !$this->db_table_exists('gdrs');
            
            foreach ($shared_params as $id => $param_array) {
                if ($this->update_param($param_array)) {
                    $params_changed = true;
                }
            }
            
            foreach ($fw_params as $id => $param_array) {
                if ($this->update_param($param_array)) {
                    $params_changed = true;
                }
            }
            
            $this->db_close();
            
            $year_range = $this->get_year_range($user_db);
            if ($shared_params['cum_since_yr']['value'] < $year_range['min_year']) {
                $shared_params['cum_since_yr']['value'] = $year_range['min_year'];
                $this->db_connect($user_db);
                if ($this->update_param($shared_params['cum_since_yr'])) {
                    $params_changed = true;
                }
                $this->db_close();
            }
            
            // Make sure that there's a path to the executable...
            $did_exec = FALSE;
            if ($params_changed && realpath(self::$exec_path)) {
                $execstring = self::$exec_path . ' --db "' . $user_db . '"';
                exec($execstring);
                $did_exec = TRUE;
            }
            return $did_exec;
        }
        
        public function get_default_fw_params() {
            return $this->fw_params_default;
        }
        
        public function cost_of_carbon($user_db, $year) {
$sql = <<< ENDSQL
SELECT (1000 * 3/11) * cost_blnUSDMER/
	NULLIF(baseline_MtC - allocation_MtC, 0)
	AS cost_USD_per_tCO2, cost_blnUSDMER, cost_perc_gwp, year
FROM	(SELECT 0.01 * params.real_val * SUM(core.gdp_blnUSDMER) AS cost_blnUSDMER,
        params.real_val AS cost_perc_gwp, core.year AS year
		FROM core, params WHERE params.param_id = 'billpercgwp_mit'
		AND core.year = $year),
	(SELECT SUM(baseline_MtC) AS baseline_MtC
		FROM view_baseline
		WHERE year = $year),
	(SELECT SUM(allocation_MtC) AS allocation_MtC
		FROM gdrs
		WHERE year = $year);

ENDSQL;
            $this->db_connect($user_db);
            $retval = $this->get_db()->query($sql)->fetch(PDO::FETCH_NAMED);
            $this->db_close();
            
            return $retval;
        }
    }
    
    
    // Register the framework
    Framework::register_fw('gdrs', 'Greenhouse Development Rights', 'GreenhouseDevRights');
