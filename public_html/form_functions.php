<?php
/*** Markup functions ************************************************************/
// Print options list for select input field with consecutive integer values
function select_num($param, $param_list, $label, $advanced) {
    // Prepare some variables
    $low = $param_list[$param]['min'];
    $high = $param_list[$param]['max'];
    $step = $param_list[$param]['step'];
    
    if (!isset($param_list[$param]['description'])) {
        $param_list[$param]['description'] = '';
    }
    
    // print nothing if the parameter is for Advanced view only and the selected view is Basic
    if ($param_list[$param]['advanced']) {
        if ($high <= 999999) { 
            $select_class = 'class="short advanced"';
        } else { 
            $select_class = 'class="advanced"';
        }
    } else {
        if ($high <= 999999) { 
            $select_class = 'class="short"';
        }
    }
    // otherwise print the select field with its label, all between <li></li> tags, 
    // flagging the selected value in the option list
    if (is_array($step)) {
        $step_array = $step;
        $step_ndx = 0;
        $step = $step_array[$step_ndx]['step'];
    } else {
        $step_array = NULL;
    }
    // Figure out how many decimal places (if any) using "step" as the clue
    if ($decpos = strpos($step, ".")) {
        $prec = strlen($step) - $decpos - 1;
        $fmt = "%." . $prec . "f";
    } else {
        $prec = 0;
        $fmt = "%d";
    }
    
    $retval = '<li><label for="' . $param . '" title="' . $param_list[$param]['description'] . '"';
    if ($param_list[$param]['advanced']) {
        if ($high > 999999) { 
            $retval .= 'class="select advanced"';
        } else { 
            $retval .= 'class="advanced"';
        }
    } else {
        if ($high > 999999) { 
            $retval .= 'class="select"';
        }
    }
    $retval .= '>' . $label . " </label>\n";
    $retval .= '<select name="' . $param . '" id="' . $param  . '" ' . $select_class . ">\n";
    $test_val = $param_list[$param]['value'];
    // If you don't use "round" then small rounding errors can throw this off
    for ($val=round($low, $prec); $val<=round($high, $prec); $val += $step) {
        $val = round($val, $prec);
        if ($val==$test_val) {
            $retval .= "<option value=\"".sprintf($fmt, $val)."\" selected=\"selected\">".sprintf($fmt, $val)."</option>\n";
        } else {
            $retval .= "<option value=\"".sprintf($fmt, $val)."\">".sprintf($fmt, $val)."</option>\n";
        }
        if ($step_array && $step_array[$step_ndx]['cutoff'] && $val >= $step_array[$step_ndx]['cutoff']) {
            $step_ndx++;
            $step = $step_array[$step_ndx]['step'];
        }
    }
    $retval .= "</select>\n";
    $retval .= "</li>\n";
    return $retval;
}

// Print options list for select input field with a list of text values (option names)
function select_options_list($param, $param_list, $label, $advanced) {
    $option_list = $param_list[$param]['list'];
    if (!isset($param_list[$param]['description'])) {
        $param_list[$param]['description'] = '';
    }
    // otherwise print the select field with its label, all between <li></li> tags, 
    // flagging the selected value in the option list
    
    if ($advanced) {
        $class = 'class="select advanced"';
    } else {
        $class = 'class="select"';
    }
    $retval = '<li><label for="' . $param . '" ' . $class . ' title="' . $param_list[$param]['description'] . '">' . $label . " </label>\n";

    if ($advanced) {
        $class = ' class="advanced"';
    } else {
        $class = '';
    }
    $retval .= '<select name="' . $param . '"' . $class . ' id="' . $param . '" >' . "\n";
    
    $test_val = $param_list[$param]['value'];
    foreach($option_list as $key => $val) {
        if (isset($val['advanced']) && $val['advanced']) {
            $class = ' class="advanced"';
        } else {
            $class = '';
        }
        if ($key==$test_val) {
            $retval .= '<option' . $class . ' value="' .$key. '" selected="selected">' .$val['display_name']. '</option>'  . "\n";
        } else {
            $retval .= '<option' . $class . ' value="' .$key. '">' .$val['display_name'] . '</option>' . "\n";
        }
    }
    $retval .= "</select>\n";
    $retval .= "</li>\n";
    return $retval;
}
