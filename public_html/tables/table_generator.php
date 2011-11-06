<?php
    // TODO: This is meant to provide the intended interface, but is not the intended
    // implementation (yet)
    
    function generate_entry($label, $val) {
        return "<td><strong>" . $label . "</strong>" . $val . "</td>\n";
    }
    
    function generate_table($display_params, $fw_params, $shared_params, $table_views, $user_db) {
        $ep_index = $shared_params["emergency_path"]['value'];
        $ep_name = $shared_params["emergency_path"]['list'][$ep_index]['display_name'];
        
        $retval = "<h3><!--Table view: -->" . $table_views[$display_params["table_view"]['value']]['display_name'] . "</h3>\n";
        $retval .= '<div id="input_values" class="group"><table cellspacing="0" cellpadding="0">' . "\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry("Emergency pathway:", $ep_name);
        $retval .= generate_entry("Baseline:", "Default GDRs<!-- add baseline parameter variable and echo value here -->");
        $retval .= generate_entry("Development threshold:", $fw_params["dev_thresh"]['value']);
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry("Luxury threshold:", $fw_params["lux_thresh"]['value']);
        $retval .= generate_entry("% between thresholds:", $fw_params["mid_rate"]['value'] . "%");
        $retval .= generate_entry("Income level for tax:", $fw_params["tax_income_level"]['value']);
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry("Responsibility weight:", $fw_params["r_wt"]['value']);
        $retval .= generate_entry("Include land-use emissions:", $shared_params["use_lulucf"]['value'] ? "yes" : "no");
        $retval .= generate_entry("Include non-CO2 gases:", $shared_params["use_nonco2"]['value'] ? "yes" : "no");
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry("Cumulative since:", $shared_params["cum_since_yr"]['value']);
        $retval .= generate_entry("Total cost as % GWP:", $shared_params["percent_gwp"]['value']);
        $retval .= generate_entry("Emissions elasticity:", $shared_params["em_elast"]['value']);
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry("Use sequencing:", $shared_params["use_sequencing"]['value'] ? "yes" : "no");
        $retval .= generate_entry("Annex 1 reduction %:", $shared_params["percent_a1_rdxn"]['value'] . "%");
        $retval .= generate_entry("Sequencing base year:", $shared_params["base_levels_yr"]['value']);
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry("End of sequencing period:", $shared_params["end_commitment_period"]['value']);
        $retval .= generate_entry("A1 transition smoothing:", $shared_params["a1_smoothing"]['value']);
        switch ($shared_params["mit_gap_borne"]['value']) {
            case "1":
                $val = "Annex 1";
                break;
            case "2":
                $val = "Annex 2";
                break;
        }
        $retval .= generate_entry("Mitigation requirement gap borne by:", $val);
        $retval .= "</tr>\n";
        $retval .= '</table></div><!-- /input_values -->' . "\n";

        switch ($display_params["framework"]['value']) {
            case 'gdrs':
                switch ($display_params["table_view"]['value']) {
                    case 'gdrs_default':
                        $retval .= '<p><strong>Values in ' . $display_params["display_yr"]['value'] . '</strong></p>';
                        include("tables/gdrs_table.php");
                        return $retval . gdrs_table($user_db, $display_params["display_yr"]['value'], $display_params["decimal_pl"]['value']);
                        break;
                    case 'gdrs_tax':
                        $retval .= '<p><strong>Values in ' . $display_params["display_yr"]['value'] . '</strong></p>';
                        include("tables/gdrs_tax.php");
                        return $retval . gdrs_tax($user_db, $display_params["display_yr"]['value'], $display_params["decimal_pl"]['value']);
                        break;
                    case 'gdrs_RCI':
                        include("tables/gdrs_rci_ts.php");
                        return $retval . gdrs_rci_ts($user_db, $display_params["decimal_pl"]['value']);
                        break;
                    case 'gdrs_alloc':
                        include("tables/gdrs_alloc.php");
                        return $retval . gdrs_alloc($user_db, $display_params["decimal_pl"]['value']);
                        break;
                }
                break;
            case 'percap':
                switch($display_params["table_view"]['value']) {
                    case 'percap_alloc':
                        include("tables/percap_alloc.php");
                        return $retval . percap_alloc($user_db, $display_params["decimal_pl"]['value']);
                        break;
                }
                break;
        }
    }