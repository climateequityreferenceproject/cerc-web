<?php
    // this exists so I can call the methods within the abstract class 'Framework' non-statically
    // i.e. on an initiated object instance of the class, rather than calling them statically on the
    // un-initiated class itself (and since 'Framework' is an abstract class, it can't be initiated directly)
    // Alternatively, I could have initiated an instance of the 'GreenhouseDevRights' class each time
    // I needed to non-statically access methods from within the abstract class, but that seemed a violation
    // of the potential modular character of the overall cerc-web setup
    class EmptyFramework extends Framework {
        public static $exec_path = NULL;
        public static $param_log = NULL;
        private $fw_params_default = NULL;
        public $table_views = NULL;

        public function calculate($user_db, $shared_params, $fw_params) {
            return NULL;
        }

        public function get_default_fw_params() {
            return NULL;
        }

        public function cost_of_carbon($user_db, $year) {
            return NULL;
        }
    }


    // Register the framework
    Framework::register_fw('empty', 'Empty Framework', 'EmptyFramework');
