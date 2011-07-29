<?php
    class GreenhouseDevRights extends Framework {
        public static $exec_path = NULL;
        
        public $fw_params_default = array(
                            'dev_thresh' => array(
                                'description' => 'Below this income, individual income (and associated emissions) are excluded from capacity and responsibility',
                                'advanced' => false,
                                'db_param' => NULL,
                                'value' => 7500.0,
                                'min' => 0.0,
                                'max' => 20000.0,
                                'step' => 500.0,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'lux_thresh' => array(
                                'description' => 'Above this income, 100% of individual income (and associated emissions) count toward capacity and responsibility',
                                'advanced' => true,
                                'db_param' => NULL,
                                'value' => 25000.0,
                                'min' => 0.0,
                                'max' => 100000.0,
                                'step' => 500.0,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'mid_rate' => array(
                                'description' => 'The proportion of individual income (and associated emissions) that counts towards capacity and responsibility between thresholds',
                                'advanced' => true,
                                'db_param' => NULL,
                                'value' => 100.0,
                                'min' => 0.0,
                                'max' => 100.0,
                                'step' => 10.0,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'tax_income_level' => array(
                                'description' => '',
                                'advanced' => false,
                                'db_param' => NULL,
                                'value' => 'not implemented',
                                'min' => NULL,
                                'max' => NULL,
                                'step' => NULL,
                                'list' => NULL,
                                'type' => 'real'
                            ),
                            'r_wt' => array(
                                'description' => 'The weight given to responsibility for historical emissions vs. capacity to contribute to mitigation and adaptation costs in RCI',
                                'advanced' => false,
                                'db_param' => 'respweight',
                                'value' => 0.5,
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
                                'value' => 1,
                                'min' => 0,
                                'max' => 1,
                                'step' => 1,
                                'list' => NULL,
                                'type' => 'int'
                            )
                        );
                        
        public $table_views = array(
                            'gdrs_default' => array(
                                'display_name' => 'GDRs default',
                                'time_series' => false
                            ),
						    'gdrs_tax' => array(
                                'display_name' => 'GDRs tax',
                                'time_series' => false
                            ),
						    'gdrs_RCI' => array(
                                'display_name' => 'GDRs RCI time series',
                                'time_series' => true
                            ),
						    'gdrs_alloc' => array(
                                'display_name' => 'GDRs allocations',
                                'time_series' => true
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
            
            // Thresholds -- not treated same as other parameters
            // Only GDRs framework gets its own special parameter table!
            $num_thresh = 2;
            $t[0] = $fw_params['dev_thresh']['value'];
            $t[1] = $fw_params['lux_thresh']['value'];
            $rate[0] = 0.01 * $fw_params['mid_rate']['value'];
            $rate[1] = 1.0;
			$name[0] = 'development';
			$name[1] = 'luxury';
            $i = 0;
            foreach ($this->get_db()->query("SELECT * FROM thresholds") as $record) {
                if ($record["income"] != $t[$i] or $record["rate"] != $rate[$i]) {
                    $params_changed = true;
                }
                $i++;
            }
            // If any threshold changed, just go ahead and update the whole thing
            if ($params_changed) {
                $this->get_db()->query("DELETE FROM thresholds;")->closeCursor();
                for ($i = 0; $i < $num_thresh; $i++) {
                    $this->get_db()->query("INSERT INTO thresholds VALUES ('" . $name[$i] . "', " . $t[$i] . ", " . $rate[$i] . ");")->closeCursor();
                }
            }
            
            $this->db_close();
            
            // Make sure that there's a path to the executable...
            if ($params_changed && self::$exec_path) {
                $execstring = self::$exec_path . ' --db "' . $user_db . '"';
                exec($execstring);
            }
        }
    }
    
    // Register the framework
    Framework::register_fw('gdrs', 'Greenhouse Development Rights', 'GreenhouseDevRights');