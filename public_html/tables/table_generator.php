<?php
    require_once "frameworks/frameworks.php";
    
    function generate_entry($label, $val) {
        return "<td><strong>" . $label . "</strong>" . $val . "</td>\n";
    }
    
    function get_country_name($display_params, $country_list, $region_list) {
        $world_code = Framework::get_world_code();
        if ($display_params["table_view"]['value'] === 'gdrs_country_report') {
            $found_it = false;
            if ($display_params['display_ctry']['value'] === $world_code) {
                $found_it = true;
                $country_name = _("World");
            }
            if (!$found_it) {
                foreach ($country_list as $item) {
                    $selected = '';
                    if ($item['iso3'] === $display_params['display_ctry']['value']) {
                        $country_name = $item['name'];
                        $found_it = true;
                        break;
                    }
                }
            }
            if (!$found_it) {
                foreach ($region_list as $item) {
                    $selected = '';
                    if ($item['region_code'] === $display_params['display_ctry']['value']) {
                        $country_name = $item['name'];
                        break;
                    }
                }
            }
        }
        else $country_name = '';
        return $country_name;
    }
            
    function generate_params_table($display_params, $fw_params, $shared_params, $country_list, $region_list, $table_views) {
        $world_code = Framework::get_world_code();
        $ep_index = $shared_params["emergency_path"]['value'];
        $ep_name = $shared_params["emergency_path"]['list'][$ep_index]['display_name'];
        $table_name = $table_views[$display_params["table_view"]['value']]['display_name'];
        if (!$table_views[$display_params["table_view"]['value']]['time_series']) {
            $table_name = sprintf(_('%1$s in %2$s'), $table_name, $display_params["display_yr"]['value']);
        }
        $country_name = get_country_name($display_params, $country_list, $region_list);
        if ($country_name != '') {
            $table_name = sprintf(_('%1$s for %2$s'), $table_name, $country_name);
        }
        
        $retval = "<h3><!--Table view: -->" . $table_name . "</h3>\n";
        $retval .= '<table  id="input_values" class="group" cellspacing="0" cellpadding="0">' . "\n";
        // $retval .= '<caption><a href="#">Show parameters</a></caption><tbody>' ."\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry(_("Global mitigation pathway: "), $ep_name);
        // TODO: add baseline parameter variable and echo value here
        //$retval .= generate_entry(_("Baseline: ", "Default");
        if ($fw_params['use_kab']['value']) {
            if ($fw_params['kab_only_ratified']['value']) {
                $kab_string = _('only ratifying countries');
            } else {
                $kab_string = _('all Annex 1');
            }
        } else {
            $kab_string = _('none');
        }
        $retval .= generate_entry(_("Kyoto adjustment: "), $kab_string);
        $retval .= generate_entry(_("Development threshold: "), "\$" . number_format($fw_params["dev_thresh"]['value']));
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry(_("Luxury threshold: "), "\$". number_format($fw_params["lux_thresh"]['value']));
        $retval .= generate_entry(_("Cap baselines at luxury threshold: "), $fw_params["do_luxcap"]['value'] ? _("yes") : _("no"));
        $retval .= generate_entry(_("Progressive between thresholds: "), $fw_params["interp_btwn_thresh"]['value'] ? _("yes") : _("no"));
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry(_("Responsibility weight: "), number_format($fw_params["r_wt"]['value'],1));
        $retval .= generate_entry(_("Include land-use emissions: "), $shared_params["use_lulucf"]['value'] ? _("yes") : _("no"));
        $retval .= generate_entry(_("Include non-CO<sub>2</sub> gases: "), $shared_params["use_nonco2"]['value'] ? _("yes") : _("no"));
        $retval .= "</tr>\n";
        $retval .= "<tr>\n";
        $retval .= generate_entry(_("Cumulative since: "), $shared_params["cum_since_yr"]['value']);
        $retval .= generate_entry(_("Total cost as % GWP: "), number_format($shared_params["percent_gwp"]['value'],1) . "%");
        $retval .= generate_entry(_("Emissions elasticity: "), number_format($shared_params["em_elast"]['value'],1));
        $retval .= "</tr>\n";
        if ($shared_params["use_sequencing"]['value']) {
            $retval .= "<tr>\n";
            $retval .= generate_entry(_("Use sequencing: "), $shared_params["use_sequencing"]['value'] ? _("yes") : _("no"));
            $retval .= generate_entry(_("Annex 1 reduction %: "), $shared_params["percent_a1_rdxn"]['value'] . "%");
            $retval .= generate_entry(_("Sequencing base year: "), $shared_params["base_levels_yr"]['value']);
            $retval .= "</tr>\n";
            $retval .= "<tr>\n";
            $retval .= generate_entry(_("End of sequencing period: "), $shared_params["end_commitment_period"]['value']);
            $retval .= generate_entry(_("A1 transition smoothing: "), $shared_params["a1_smoothing"]['value']);
            switch ($shared_params["mit_gap_borne"]['value']) {
                case "1":
                    $val = _("Annex 1");
                    break;
                case "2":
                    $val = _("Annex 2");
                    break;
            }
            $retval .= generate_entry(_("Mitigation requirement gap borne by: "), $val);
            $retval .= "</tr>\n";
        }
        $retval .= '</tbody></table><!-- /input_values -->' . "\n";
        return $retval;
    }
    
    function generate_results_table($display_params, $shared_params, $country_list, $region_list, $user_db) {
        $world_code = Framework::get_world_code();
        $disp_year = $display_params["display_yr"]['value'];
        $dec = $display_params["decimal_pl"]['value'];
        $advanced = $display_params['basic_adv']['value'] !== 'basic';
        $ep_start = $shared_params['emergency_program_start']['value'];
        $use_nonco2 = $shared_params['use_nonco2']['value'] == 0 ? FALSE : TRUE;
        $country_name = get_country_name($display_params, $country_list, $region_list);
        
        switch ($display_params["framework"]['value']) {
            case 'gdrs':
                switch ($display_params["table_view"]['value']) {
                    case 'gdrs_default':
                        include("tables/gdrs_table.php");
                        return gdrs_table($user_db, $disp_year, $dec, $advanced);
                        break;
                    case 'gdrs_tax':
                        include("tables/gdrs_tax.php");
                        return gdrs_tax($user_db, $disp_year, $ep_start, $dec);
                        break;
                    case 'gdrs_RCI':
                        include("tables/gdrs_rci_ts.php");
                        return gdrs_rci_ts($user_db, $dec);
                        break;
                    case 'gdrs_alloc':
                        include("tables/gdrs_alloc.php");
                        return gdrs_alloc($user_db,$dec, 'total', $use_nonco2);
                        break;
                    case 'gdrs_alloc_pc':
                        include("tables/gdrs_alloc.php");
                        return gdrs_alloc($user_db,$dec, 'percap', $use_nonco2);
                        break;
                    case 'gdrs_country_report':
                        include("tables/gdrs_country_report.php");
                        return gdrs_country_report($user_db, $country_name, $shared_params, $display_params['display_ctry']['value'], $disp_year);
                        break;
                }
                break;
            case 'percap':
                switch($display_params["table_view"]['value']) {
                    case 'percap_alloc':
                        include("tables/percap_alloc.php");
                        return percap_alloc($user_db, $display_params["decimal_pl"]['value']);
                        break;
                }
                break;
        }
    }

    function generate_table($display_params, $fw_params, $shared_params, $country_list, $region_list, $table_views, $user_db){
        $retval = generate_params_table($display_params, $fw_params, $shared_params, $country_list, $region_list, $table_views);
        $retval .= generate_results_table($display_params, $shared_params, $country_list, $region_list, $user_db);
        return $retval;
    }
