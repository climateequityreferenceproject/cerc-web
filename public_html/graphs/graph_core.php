<?php
    class GraphException extends Exception { }

    class Axis {
        protected $min = 0;
        protected $max = 0;
        protected $label = "";
        protected $unit = "";
        
        function __construct($min, $max, $label, $unit, $use_limits, $number_format, $step, $dec) {
            $this->min = $min;
            $this->max = $max;
            $this->label = $label;
            $this->unit = $unit;
            $this->use_limits = $use_limits;
            $this->number_format = $number_format;
            $this->scale = $this->calc_scale($step);
            $this->dec = $dec;
        }
        
        public function get_label() {
            return $this->label;
        }
        
        public function get_unit() {
            return $this->unit;
        }
        
        public function get_scale() {
            return $this->scale;
        }
        
        public function transf_coord($coord, $margin_min, $margin_max) {
            $offset = $margin_min;
            $len = $margin_max - $margin_min;
            $factor = $len/($this->scale['max'] - $this->scale['min']);
            
            return $offset + round($factor * ($coord - $this->scale['min']));
        }
        
        // This is from Tcl's plotchart module by Arjen Markus
        // Returns a list as "min, max, step"
        private function calc_scale($step_override=NULL) {
            $xmin = $this->min;
            $xmax = $this->max;
            
            $dx = abs($xmax - $xmin);
            
            if ($dx == 0.0) {
                if ($xmin == 0.0) {
                    return array('min'=>-0.1, 'max'=>0.1, 'step'=>0.1);
                } else {
                    $dx = 0.2 * abs($xmax);
                    $xmin = $xmin - 0.5 * $dx;
                    $xmax = $xmin + 0.5 * $dx;
                }
            }
            
            $expon = floor(log10($dx));
            $factor = pow(10.0, $expon);
            
            $dx = $dx/$factor;
            
            foreach (array(1.4=>0.2, 2.0=>0.5, 5.0=>1.0, 10.0=>2.0) as $limit => $step) {
                if ($dx < $limit) {
                    break;
                }
            }
            
            if ($this->use_limits) {
                $nicemin = $this->min;
                $nicemax = $this->max;
            } else {
                $nicemin = $step * $factor * round($xmin/$factor/$step);
                $nicemax = $step * $factor * round($xmax/$factor/$step);

                if ($nicemax < $xmax) {
                    $nicemax = $nicemax + $step * $factor;
                }

                if ($nicemin > $xmin) {
                    $nicemin = $nicemin - $step * $factor;
                }
            }
            
            if (isset($step_override)) {
                return array('min'=>$nicemin, 'max'=>$nicemax, 'step'=>$step_override);
            } else {
                return array('min'=>$nicemin, 'max'=>$nicemax, 'step'=>$step * $factor);
            }
        }
        
    }
    
    // Usage:
    //  $graph = new Graph(width, height);
    //  $graph->set_xaxis(min, max, label, unit);
    //  $graph->set_yaxis(min, max, label, unit);
    //  $graph->add_series(points);
    //  ...
    //  $fname = $graph->svgplot_XX(...);
    //
    // The svgplot function returns a filename with the svg file
    //
    class Graph {
        protected $dim = array('width' => 0, 'height' => 0);
        protected $css = array('filename' => NULL, 'embed' => false);
        protected $have_css = false;
        protected $series = array();
        protected $xaxis = null;
        protected $yaxis = null;
        protected $glyphs = array();
        protected $margin = 20;
        protected $textheight = 35; // Approx space to give text in pixels
        protected $ticksize = 4; // Tick size in pixels
        protected $x_axis_text_attr = 'text-anchor="middle" font-family="Arial" font-size="9pt"';
        protected $y_axis_text_attr = 'text-anchor="end" font-family="Arial" font-size="9pt"';       
        protected $label_text_attr = 'text-anchor="middle" font-family="Arial" font-size="11pt"';
        
        function __construct($dim_array, $css = NULL) {
            $this->dim = $dim_array;
            if ($css) {
                $this->css = $css;
                $this->have_css = true;
            }
        }
        
        public function add_glyph($x, $y, $class, $id, $type, $size = 6, $style = null) {
            $margin = $this->get_margins();
            
            $xtransf = $this->xaxis->transf_coord($x, $margin['left'], $margin['right']);
            $ytransf = $this->yaxis->transf_coord($y, $margin['bottom'], $margin['top']);
            
            $glyph_string = self::make_glyph($class, $id, $type, $size, $style);
            
            $this->glyphs[$id] = array(
                'xtrans' => $xtransf,
                'ytrans' => $this->dim['height'] - $ytransf,
                'def' => $glyph_string,
                'original_values' => array('x' => $x, 'y' => $y)
            );
        }
        
        private static function make_glyph($class, $id, $type, $size, $style) {
            // Have to specify by half-width, radius, etc., so effectively round down to nearest even value
            $half_size = (int) $size / 2;
            $full_size = 2 * $half_size;
            switch ($type) {
                case 'circle':
                    $shape = 'circle';
                    $attr = 'r="' . $half_size . '"';
                    break;
                case 'diamond':
                    $shape = 'polygon';
                    $bottom = '0,' . $half_size;
                    $top = '0,-' . $half_size;
                    $right = $half_size . ',0';
                    $left = '-' . $half_size . ',0';
                    $attr = 'points="' . $right . ' ' . $bottom . ' ' . $left . ' ' . $top . '"';
                    break;
                case 'square':
                    $shape = 'rect';
                    $attr = 'width="' . $full_size . '" height="' . $full_size .'" x="-' . $half_size . '" y="' . $half_size . '"';
                    break;
                case 'triangle':
                    $shape = 'polygon';
                    $long_segment = (int) floor(2 * $full_size/sqrt(3.0));
                    $short_segment = (int) floor($long_segment / 2);
                    $bottom_left = '-' . $half_size . ',' . $short_segment;
                    $bottom_right = $half_size . ',' . $short_segment;
                    $top = '0,-' . $long_segment ;
                    $attr = 'points="' . $bottom_left . ' ' . $bottom_right . ' ' . $top . '"';
                    break;
                default:
                    throw new GraphException('Glyph type "' . $type . '" not recognized.');
            }
            if ($style) {
                $style_string = 'style="' . $style . '"';
            } else {
                $style_string = '';
            }
            return '<' . $shape . ' class="' . $class . '" id="' . $id . '" ' . $attr . ' ' . $style_string . ' />';
        }
        
        protected static function dec($num) {
            return max(0, 1 - floor(log10(abs($num))));
        }
        
        private static function translate($xoff, $yoff) {
            return 'transform="translate(' . $xoff . ',' . $yoff . ')"';
        }
        
        // For now, axes are set once, not resest
        public function set_xaxis($min, $max, $label, $unit, $use_limits=FALSE, $number_format=TRUE, $step=NULL) {
            if (!$this->xaxis) {
                $this->xaxis = new Axis($min, $max, $label, $unit, $use_limits, $number_format, $step);
            }
        }
        public function set_yaxis($min, $max, $label, $unit, $use_limits=FALSE, $number_format=TRUE, $step=NULL) {
            if (!$this->yaxis) {
                $this->yaxis = new Axis($min, $max, $label, $unit, $use_limits, $number_format, $step);
            }
        }
        public function set_yaxis2($min, $max, $label, $unit, $use_limits=FALSE, $number_format=TRUE, $step=NULL, $dec=NULL) {
            if (!$this->yaxis2) {
                $this->yaxis2 = new Axis($min, $max, $label, $unit, $use_limits, $number_format, $step, $dec);
            }
        }
        public function get_yaxis_scale() {
            return $this->yaxis->scale;
        }
        
        public function add_series($points, $id, $css_class=NULL) {
            $this->series[$id] = $points;
            if (isset($css_class)) { $this->css_classes[$id] = $css_class; }
        }
        
        protected function horiz_stripes($id, $pattern = NULL) {
            if (!$pattern) {
                $pattern = array(
                    'width' => '2%',
                    'height' => '2%',
                    'stripe_width' => 5,
                    'stripe_color' => '#CCC'
                );  
            }
            $retval =  '<pattern id="' . $id . '" x="0" y="0"';
            $retval .= ' width="' . $pattern['width'] . '" height="' . $pattern['height'] . '"';
            $retval .= ' patternUnits="objectBoundingBox">';
            $retval .= '<line class="stripe" x1="0" y1="0" x2="0" y2="100"';
            if (!$this->have_css) {
                $retval .= ' stroke="' . $pattern['stripe_color'] . '" stroke-width="' . $pattern['stripe_width'] . '"';
            }
            $retval .= ' /></pattern>' . "\n";
            return $retval;
        }
        
        // Hm... as implemented, not actually margins... gives x & y position of bounds on axes
        protected function get_margins() {
            $x = $this->margin;
            $y = $this->margin;
            
            return array(
                'left' => $x + $this->textheight,
                'right' => $this->dim['width'] - $x,
                'bottom' => $y + $this->textheight,
                'top' => $this->dim['height'] - $y
            );
        }
        
        // labels_match_scale - if true, all axis labels are multiples of the axis scale, is most often used to ensure that 0 is one of the axis labels.
        protected function svg_hrule($id, $params = array('labels_match_scale' => FALSE)) {
            $yscale = $this->yaxis->get_scale();
            $margin = $this->get_margins();
            
            $yval = $yscale['min'];
            $retval = "";
            
            $x1 = $margin['left'];
            $x2 = $margin['right'];
            
            $canvas_len = $margin['top'] - $margin['bottom'];
            $axis_len = $yscale['max'] - $yscale['min'];
            $canvas_step = floor($yscale['step'] * $canvas_len/$axis_len);
            
            $yzero = 0;
            if ($yscale['min'] < 0) {
                $yzero = -round($yscale['min']/($yscale['max'] - $yscale['min']) * ($margin['top'] - $margin['bottom']));
            }
            $yzero = $this->dim['height'] - $margin['bottom'] - $yzero;
            
            if ($params['labels_match_scale']) {
                if ($yscale['step']<1) {
                    $yval = ceil($yscale['min']*$yscale['step'])/$yscale['step'];
                } else {
                    $yval = ceil($yscale['min']/$yscale['step'])*$yscale['step'];
                }
                $first_offset = ($yval - $yscale['min']) * ($canvas_step/$yscale['step']);
            } else {
                $yval = $yscale['min'];
                $first_offset = 0;
            }
            for ($offset = $first_offset; $offset <= $canvas_len; $offset += $canvas_step) {
                $y = $this->dim['height'] - ($margin['bottom'] + $offset);
                if (($y !== $yzero) && ($yval!=0)){
                    $retval .= '<line class="' . $id . '" x1="' . $x1 . '" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y .'"';
                    if (!$this->have_css) {
                        $retval .= ' width="1" stroke="black" stroke-dasharray="1,2"';
                    }
                    $retval .= ' />' . "\n";
                }
                $yval += $yscale['step'];
            }
            
            return $retval;
        }
        
        protected function svg_xaxis($params = array('ticks'=>true, 'line' => true)) {
            $scale = $this->xaxis->get_scale();
            $label = $this->xaxis->get_label();
            if (($unit = $this->xaxis->get_unit()) !== "") {
                $label .= " (" . $unit . ")";
            }
            $margin = $this->get_margins();
            
            // Cross zero if need be
            $yscale = $this->yaxis->get_scale();
            $yzero = 0;
            if ($yscale['min'] < 0) {
                $yzero = -round($yscale['min']/($yscale['max'] - $yscale['min']) * ($margin['top'] - $margin['bottom']));
            }
            
            $ypos = $this->dim['height'] - $margin['bottom'] - $yzero;
            $ybottom = $this->dim['height'] - $margin['bottom'];
            $x1 = $margin['left'];
            $x2 = $margin['right'];
            
            $canvas_len = $margin['right'] - $margin['left'];
            $axis_len = $scale['max'] - $scale['min'];
            $canvas_step = floor($scale['step'] * $canvas_len/$axis_len);
            
            $retval = "";
            
            if ($params['line']) {
                $retval .= '<line class="axis" x1="' . $x1 . '" y1="' . $ypos . '" x2="' . $x2 . '" y2="' . $ypos . '"';
                if (!$this->have_css) {
                    $retval .= ' stroke="black"';
                }
                $retval .= '/>' . "\n";
            }
            
            $dec = self::dec($scale['max']);
            $y1 = $ypos;
            $y2 = $ypos + $this->ticksize;
            $xval = $scale['min'];
            for ($offset = 0; $offset <= $canvas_len; $offset += $canvas_step) {
                if ($this->xaxis->number_format) {
                    $val = number_format($xval, $dec);
                    // Avoid negative zero
                    if ((float) $val == 0.0) {
                        $val = number_format(0, $dec);
                    }
                } else {
                    $val = $xval;
                }
                $x = $margin['left'] + $offset;
                if ($params['ticks']) {
                    $retval .= '<line class="axis" x1="' . $x . '" y1="' . $y1 . '" x2="' . $x . '" y2="' . $y2 . '"';
                    if (!$this->have_css) {
                        $retval .= ' stroke="black"';
                    }
                    $retval .= '/>' . "\n";
                }
                $attr = $this->have_css ? '' : $this->x_axis_text_attr;
                $retval .= '<text class="axis-label-x" ' . $attr . ' x="' . $x . '" y="' . ($ybottom + 20) . '">' . "\n";
                $retval .= $val;
                $retval .= "</text>\n";
                $xval += $scale['step'];
            }
            $retval .= '<rect id="neg_area" class="neg_area" x="'.$margin['left'].'" y="'.$ypos.'" height="'.($ybottom - $ypos).'" width="'.($x - $margin['left']).'" />';
            $x = $margin['left'] + round(0.5 * $canvas_len);
            $y = $ypos + 35;
            
            $attr = $this->have_css ? '' : $this->label_text_attr;
            $retval .= '<text class="axis-title" ' . $attr . ' x="' . $x . '" y="' . $y . '">' . "\n";
            $retval .= $label;
            $retval .= "</text>\n";
            
            
            return $retval;
        }
        
        // x_offset moves the location of the axis relative to its default position
        // labels_match_scale - if true, all axis labels are multiples of the axis scale, is most often used to ensure that 0 is one of the axis labels.
        protected function svg_yaxis($params = array('ticks'=>true, 'line' => true, 'x_offset' => 0, 'labels_match_scale' => FALSE), $axis_number = 1) {
            $margin = $this->get_margins();
            switch ($axis_number) {
                case 2:
                    $scale = $this->yaxis2->get_scale();
                    $label = $this->yaxis2->get_label();
                    if (($unit = $this->yaxis2->get_unit()) !== "") { $label .= " (" . $unit . ")"; }
                    $xpos = $margin['right'] + $params['x_offset'];
                    $dec = (isset($this->yaxis2->dec)) ? $this->yaxis2->dec : self::dec($scale['max']);
                    break;
                default:
                    $scale = $this->yaxis->get_scale();
                    $label = $this->yaxis->get_label();
                    if (($unit = $this->yaxis->get_unit()) !== "") { $label .= " (" . $unit . ")"; }
                    $xpos = $margin['left'] + $params['x_offset'];
                    $dec = (isset($this->yaxis->dec)) ? $this->yaxis->dec : self::dec($scale['max']);
            }
            
            $y1 = $this->dim['height'] - $margin['bottom'];
            $y2 = $this->dim['height'] - $margin['top'];
            
            $canvas_len = $margin['top'] - $margin['bottom'];
            $axis_len = $scale['max'] - $scale['min'];
            $canvas_step = floor($scale['step'] * $canvas_len/$axis_len);
            
            $retval = "";
            
            if ($params['line']) {
                $retval .= '<line class="axis yaxis" x1="' . $xpos . '" y1="' . $y1 . '" x2="' . $xpos . '" y2="' . $y2 . '"';
                if (!$this->have_css) {
                    $retval .= ' stroke="black"';
                }
                $retval .= '/>' . "\n";
            }
            
            $x1 = $xpos;
            $x2 = $xpos - (($axis_number == 2) ? (-1)*$this->ticksize : $this->ticksize);
            
            if ($params['labels_match_scale']) {
                if ($scale['step']<1) {
                    $yval = ceil($scale['min']*$scale['step'])/$scale['step'];
                } else {
                    $yval = ceil($scale['min']/$scale['step'])*$scale['step'];
                }
                $first_offset = round(($yval - $scale['min']) * ($canvas_step/$scale['step'])); // add half of the font size                
            } else {
                $yval = $scale['min'];
                $first_offset = 0;
            }
            for ($offset = $first_offset; $offset <= $canvas_len; $offset += $canvas_step) {
                if (($axis_number == 2) ? $this->yaxis2->number_format : $this->yaxis->number_format) {
                    $val = number_format($yval, $dec);
                    // Avoid negative zero
                    if ((float) $val == 0.0) {
                        $val = number_format(0, $dec);
                    }
                } else {
                    $val = $yval;
                }
                $y = $this->dim['height'] - ($margin['bottom'] + $offset);
                if ($params['ticks']) {
                    $retval .= '<line x1="' . $x1 . '" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y . '"';
                    if (!$this->have_css) {
                        $retval .= ' stroke="black"';
                    }
                    $retval .= '/>' . "\n";
                }
                $attr = $this->have_css ? '' : $this->y_axis_text_attr;
                $retval .= '<text class="axis-label-y" ' . $attr . ' x="' . ($x2+ ($axis_number == 2 ? 20 : 0)) . '" y="' . ($y + 3) . '">' . "\n";
                $retval .= (isset($params['label_multiplier'])) ? ($params['label_multiplier'] * $val) : $val;
                $retval .= "</text>\n";
                $yval += $scale['step'];
            }
            
            $y = $this->dim['height'] - ($margin['bottom'] + round(0.5 * $canvas_len));
            $x = $xpos + (($axis_number == 2) ? 40 : -40);
            
            $attr = $this->have_css ? '' : $this->label_text_attr;
            $retval .= '<text class="axis-title" ' . $attr . ' x="' . 0 . '" y="' . 0 . '" transform="rotate(-90) translate(' . -$y . ',' . $x . ')">' . "\n";
            $retval .= $label;
            $retval .= "</text>\n";
            
            
            return $retval;
         }
        
        protected function svg_start($has_second_yaxis=FALSE) {
            $stylesheet = $this->css['filename'];
            $retval = '<?xml version="1.0" standalone="no"?>' . "\n";
            if ($stylesheet && !$this->css['embed']) {
                $url = (!empty($_SERVER['HTTPS'])) ? "https://" : "http://";
                $url .= $_SERVER['SERVER_NAME'];
                if (substr($stylesheet, 0, 1) !== '/') {
                    $relpath = $_SERVER['REQUEST_URI'];
                    if (substr($relpath, -1) !== '/') {
                        $relpath = dirname($relpath) . '/';
                    }
                    $url .= $relpath;
                }
                $stylesheet = $url . $stylesheet;
                $retval .= '<?xml-stylesheet type="text/css" href="' . $stylesheet . '" ?>' . "\n";
            }
            $width = ($has_second_yaxis) ? $this->dim['width'] + 50 : $this->dim['width'];
            $retval .= '<svg width="' . $width . 'px" height="' . $this->dim['height'] . 'px" version = "1.1"' . "\n";
            $retval .= '   baseProfile="basic"' . "\n";
            $retval .= '   xmlns="http://www.w3.org/2000/svg"' . "\n";
            $retval .= '   xmlns:xlink="http://www.w3.org/1999/xlink">' . "\n";
            if ($stylesheet && $this->css['embed']) {
                $retval .= '<style type="text/css"><![CDATA[' . "\n";
                $retval .= file_get_contents($stylesheet);
                $retval .= "\n" . ']]></style>' . "\n";
            }
            return $retval;
        }
        
        protected function svg_end() {
            return "</svg>\n";
        }

        // For wedges, expect series to be in order--can be top-down or bottom-up
        // Colors are the colors of the wedges in sequence
        public function svgplot_wedges($wedges, $params=NULL) {
            global $svg_tmp_dir;
            if (!$params) {
                $params = array(
                    'common_id' => NULL,
                    'vertical_at' => NULL
                );
            }
            // defaults
            if (is_null($params['show_data_tooltips'])) { $params['show_data_tooltips'] = FALSE; }
            // Must have axes and series to plot
            if (!$this->xaxis || !$this->yaxis || count($this->series) == 0) {
                return;
            }
                        
            $margin = $this->get_margins();
            
            // Rescale all series
            $scaled_series = array();
            foreach ($this->series as $id => $points_array) {
                $scaled_series[$id] = array();
                foreach ($points_array as $x => $y) {
                    $xtransf = $this->xaxis->transf_coord($x, $margin['left'], $margin['right']);
                    $ytransf = $this->yaxis->transf_coord($y, $margin['bottom'], $margin['top']);
                    $scaled_series[$id][$xtransf] = $ytransf;
                }
            }

            // Search through and see what points all the series have in common
            // if series names are given in an array "ignore_for_common" of the 
            // prarams opions array, these series are ignored here  
            if ($params['common_id']) {
                if (!isset($params['ignore_for_common'])) { $params['ignore_for_common'] = array(); }
                $common_series = array();
                $ref_series = current($scaled_series);
                $finished = false;
                $prev_x = NULL;
                $prev_y = NULL;
                foreach (array_keys($ref_series) as $ndx => $x) {
                    $yval = NULL;
                    if (!($finished)) { // ie. if all series still match each other (except the ones to be ignored)
                        // for value ($yval) of current $x, loop through all series (except ones to be ignored) and find out if all match
                        foreach ($scaled_series as $id => $points_array) {
                            if (!in_array($id, $params['ignore_for_common'])) {
                                if (!$yval) {
                                    $yval = $points_array[$x];
                                } else {
                                    // if points deviate by more than 0.005% they are considered not equal to each other
                                    if ((abs($points_array[$x] - $yval)/$yval) > 0.00005) {
    //                                if ($points_array[$x] !== $yval) {
                                        $finished = TRUE;
                                        break;
                                    }
                                }
                            }   
                        }
                    } else {
                        if ($prev_x && $prev_y) {
                            $prepend = array($prev_x => $prev_y);
                            foreach ($scaled_series as $id => $val) {
                                if (!in_array($id, $params['ignore_for_common'])) {
                                    $scaled_series[$id] = $prepend + $scaled_series[$id];
                                } else {
//                                    $scaled_series[$id] = array($prev_x => $scaled_series[$id][$prev_x]) + $scaled_series[$id];
                                }
                            }
                        }
                        break;
                    }
                    $prev_x = $x;
                    $prev_y = $ref_series[$x];
                    $common_series[$x] = $ref_series[$x];
                    foreach ($scaled_series as $id => $val) {
                        if (!in_array($id, $params['ignore_for_common'])) {  // don't delete current point if the series it to be ignored for the matching alg
                            unset($scaled_series[$id][$x]);
                        }
                    }
                }
                $scaled_series[$params['common_id']] = $common_series;
            }
            
            //******* Begin SVG **********//

            $svg = $this->svg_start($params['has_second_yaxis']);
            
            // Check for any striped patterns
            reset($scaled_series);
            $ref_series = current($scaled_series);
            $bb_left = min(array_keys($ref_series));
            $bb_right = max(array_keys($ref_series));
            $stripe_width = 5;
            $stripe_percent = round(100 * (2 * $stripe_width/($bb_right - $bb_left))) . "%";
            $stripe_pattern = array(
                'width' => $stripe_percent,
                'height' => '10%',
                'stripe_width' => $stripe_width,
                'stripe_color' => '#F00'
            );  
            $svg .= "<defs>\n";
            foreach ($wedges as $id => $wedge) {
                if ($wedge['stripes']) {
                    $svg .= $this->horiz_stripes($wedge['stripes'], $stripe_pattern);
                }
            }
            // Define glyphs
            foreach ($this->glyphs as $glyph) {
                $svg .= $glyph['def'] . "\n";
            }
            $svg .= "</defs>\n";

            $svg .= $this->svg_hrule('hrule',array('labels_match_scale' => $params['labels_match_scale']['y1']));
            
            $svg .= $this->svg_xaxis(array('ticks'=>false, 'line'=>true, 'labels_match_scale' => $params['labels_match_scale']['x']));
            $svg .= $this->svg_yaxis(array('ticks'=>false, 'line'=>false, 'labels_match_scale' => $params['labels_match_scale']['y1']));
            if ($params['has_second_yaxis']) { $svg .= $this->svg_yaxis(array('ticks'=>false, 'line'=>false, 'x_offset' => 10, 'labels_match_scale' => $params['labels_match_scale']['y2'],'label_multiplier'=>$params['label_multiplier']),2); }
            
            if ($params['vertical_at']) {
                $y1 = $this->dim['height'] - $margin['bottom'];
                $y2 = $this->dim['height'] - $margin['top'];
                $xvert_scaled = $this->xaxis->transf_coord($params['vertical_at'], $margin['left'], $margin['right']);
                $svg .= '<line id="vertical" class="yaxis" ';
                if (!$this->have_css) {
                    $svg .= ' width="1" stroke="#999"';
                }
                $svg .= ' x1="' . $xvert_scaled . '" y1="' . $y1 . '" x2="' . $xvert_scaled . '" y2="' . $y2 . '"';
                $svg .= ' />' . "\n";
            }
            
            foreach ($wedges as $id => $wedge) {
                $s1 = $wedge['between'][0];
                $s2 = $wedge['between'][1];
                $x_offset[0] = (isset($wedge['x_offset'])) ? $wedge['x_offset']['left'] : 0;
                $x_offset[1] = (isset($wedge['x_offset'])) ? $wedge['x_offset']['right'] : 0;
                $points = "";
                $i = 0;
                foreach ($scaled_series[$s1] as $x => $y) {
                    $points .= ($x + $x_offset[$i]) . "," . ($this->dim['height'] - $y) . " " ;
                    $i = $i +1;
                }
                $i = 1;
                foreach (array_reverse($scaled_series[$s2], true) as $x => $y) {
                    $points .= ($x + $x_offset[$i]) . "," . ($this->dim['height'] - $y) . " " ;
                    $i = $i - 1;
                }
                $svg .= '<polygon';
                if (!$this->have_css) {
                    $svg .= ' stroke-width="0" stroke="#000"';
                }
                if ($wedge['id']) {
                    $svg .= ' id="' . $wedge['id'] . '"';
                }
                if ($wedge['css_class']) { $svg .= ' class="' . $wedge['css_class'] . '"' ;}
                if ($wedge['color'] && !$this->have_css) {
                    $svg .= ' fill="' . $wedge['color'] . '"';
                } elseif ($wedge['stripes'] && !$this->have_css) {
                    $svg .= ' style="fill:url(#' . $wedge['stripes'] . ');"';
                }
                if ($wedge['opacity'] && !$this->have_css) {
                    $svg .= ' fill-opacity="' . $wedge['opacity'] . '"';
                }
                $svg .= ' points="' . $points . '" />' . "\n";
            }
            $tooltips = "";
            $marker_id=0;
            foreach ($scaled_series as $id => $point_array) {
                $points = "";
                if ($params['show_data_tooltips']) {
                    if ($params['common_id'] == $id) {  // if the current series is the series where we stored common values, it will be the one that contains the x/y coordinates but doesn't contain the original values, so we pick anohter one
                        while (list($key) = each($scaled_series)) {
                            if ((!in_array($key, $params['ignore_for_common'])) && ($key != $id)) {
                                $current_series = $this->series[$key];
                                $year_idx = 0;
                            }
                        }
                    } else {
                        $current_series = $this->series[$id];
                        $year_idx = count($current_series) - count($scaled_series[$id]);
                    }
                    $years = array_keys($current_series);
                }
                foreach ($point_array as $x => $y) {
                    $points .= $x . "," . ($this->dim['height'] - $y) . " " ;

                    if ($params['show_data_tooltips']) {
                        if(!in_array($id, $params['ignore_for_tooltips'])) {
                            $value = $current_series[$years[$year_idx]];
                            $value = number_format($value, max(0, 1 - floor(log10(abs($value)))));
                            $tooltips .= '<circle id="marker' . $marker_id . '" class="linemarker" cx="' . $x . '" cy="' . ($this->dim['height'] - $y) . '" r="4" />';
                            $tooltips .= '<circle id="target' . $marker_id . '" class="tooltiptarget" cx="' . $x . '" cy="' . ($this->dim['height'] - $y) . '" r="8" ';
                            $tooltips .= 'data="' . $years[$year_idx] . ": " . $value . ' Mt" />';
                            $marker_id ++;
                            $year_idx ++;
                        }
                    }
                }
                $svg .= '<polyline id="' . $id . '"';
                if ($this->css_classes[$id]) { $svg .= ' class="' . $this->css_classes[$id] . '"'; }
                if (!$this->have_css) {
                    $svg .= ' stroke-width="1" stroke="#000"';
                } 
                $svg .= ' points="' . $points . '"';
                if (!$this->have_css) {
                    $svg .= ' fill="none"';
                }
                $svg .= ' />' . "\n";
            }  
            
            // Place glyphs
            foreach ($this->glyphs as $id => $glyph) {
//                var_dump($glyph);
                $svg .= '<use id="use-' . $id . '" xlink:href="#' . $id . '" ';
                $svg .= self::translate($glyph['xtrans'], $glyph['ytrans']);
                $svg .= ' class="' . substr($id, 0, (strpos($id, "glyph") + 5)) .'" />' . "\n";

                if ($params['show_data_tooltips']) {
                    $value = $glyph['original_values']['y'];
                    $value = number_format($value, max(0, 1 - floor(log10(abs($value)))));
                    $tooltips .= '<circle id="target' . $marker_id . '" class="tooltiptarget" cx="' . $glyph['xtrans'] . '" cy="' . $glyph['ytrans'] . '" r="8" ';
                    $tooltips .= 'data="' . $glyph['original_values']['x'] . ": " . $value . ' Mt" />';
                    $marker_id ++;
                }
            }
            
            
            $tooltips .= '<g id="tooltipgroup" style="display:none;"><rect class="tooltipbgrd" y="-12" x="-40" width="80" height="15" />';
            $tooltips .= '<text id="tooltiptext" class="tooltiptext">' . $glyph['original_values']['x'] . ": " . $value . ' Mt</text></g>';
            $tooltipscript = '<script>$(document).ready(function() {
                                $(".tooltiptarget").mouseenter( function(){
                                    $("#tooltipgroup").attr("style","display:block;");
                                    $("#tooltipgroup").attr("transform","translate(" + $(this).attr("cx") + "," + ($(this).attr("cy") - 10) + ")");
                                    $("#tooltiptext").text( $(this).attr("data") );
                                    $("#marker" + $(this).attr("id").substr(6)).attr("style","display:block;");
                                });
                                $(".tooltiptarget").mouseleave( function(){
                                    $("#tooltiptext").text( "" );
                                    $("#tooltipgroup").attr("style","display:none;");
                                    $("#marker" + $(this).attr("id").substr(6)).attr("style","display:none;");
                                });
                              });</script>';
            if ($params['show_data_tooltips']) { $svg .= $tooltips; }
            $svg .= $this->svg_end();
            if ($params['show_data_tooltips']) { $svg .= $tooltipscript; }  // some browsers might take issue with jquery code inside the svg. 

            if ($params['code_output']) {
                return $svg;
            } else {
                $fname = tempnam($svg_tmp_dir, "graph-") . ".svg";
                $fh = fopen($fname, 'w') or die("Cannot open file " . $fname);

                fwrite($fh, $svg);

                fclose($fh);

                return $fname;
            }            
        }
    }
