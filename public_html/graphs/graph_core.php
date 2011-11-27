<?php
    class Axis {
        protected $min = 0;
        protected $max = 0;
        protected $label = "";
        protected $unit = "";
        
        function __construct($min, $max, $label, $unit, $use_limits, $number_format) {
            $this->min = $min;
            $this->max = $max;
            $this->label = $label;
            $this->unit = $unit;
            $this->use_limits = $use_limits;
            $this->number_format = $number_format;
        }
        
        public function get_label() {
            return $this->label;
        }
        
        public function get_unit() {
            return $this->unit;
        }
        
        // This is from Tcl's plotchart module by Arjen Markus
        // Returns a list as "min, max, step"
        public function get_scale() {
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
            
            return array('min'=>$nicemin, 'max'=>$nicemax, 'step'=>$step * $factor);
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
        protected $series = array();
        protected $xaxis = null;
        protected $yaxis = null;
        protected $margin = 15;
        protected $textheight = 35; // Approx space to give text in pixels
        protected $ticksize = 4; // Tick size in pixels
        protected $axis_text_attr = 'text-anchor="middle" font-family="Arial" font-size="9pt"';
        protected $label_text_attr = 'text-anchor="middle" font-family="Arial" font-size="11pt"';
        
        function __construct($width, $height) {
            $this->dim['width'] = $width;
            $this->dim['height'] = $height;
        }
        
        // For now, axes are set once, not resest
        public function set_xaxis($min, $max, $label, $unit, $use_limits=FALSE, $number_format=TRUE) {
            if (!$this->xaxis) {
                $this->xaxis = new Axis($min, $max, $label, $unit, $use_limits, $number_format);
            }
        }
        public function set_yaxis($min, $max, $label, $unit, $use_limits=FALSE, $number_format=TRUE) {
            if (!$this->yaxis) {
                $this->yaxis = new Axis($min, $max, $label, $unit, $use_limits, $number_format);
            }
        }
        
        public function add_series($points, $id) {
            $this->series[$id] = $points;
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
            $retval .= ' stroke="' . $pattern['stripe_color'] . '" stroke-width="' . $pattern['stripe_width'] . '"/>';
            $retval .= '</pattern>' . "\n";
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
        
        protected function dec($num) {
            return max(0, 1 - floor(log10(abs($num))));
        }
        
        protected function svg_hrule($id) {
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
            
            for ($offset = 0; $offset <= $canvas_len; $offset += $canvas_step) {
                $y = $this->dim['height'] - ($margin['bottom'] + $offset);
                if ($y !== $yzero) {
                    $retval .= '<line class="' . $id . '" x1="' . $x1 . '" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y . '" width="1" stroke="black" stroke-dasharray="1,2" />' . "\n";
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
                $retval .= '<line class="axis" x1="' . $x1 . '" y1="' . $ypos . '" x2="' . $x2 . '" y2="' . $ypos . '" stroke="black" />' . "\n";
            }
            
            $dec = $this->dec($scale['max']);
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
                }                $x = $margin['left'] + $offset;
                if ($params['ticks']) {
                    $retval .= '<line x1="' . $x . '" y1="' . $y1 . '" x2="' . $x . '" y2="' . $y2 . '" stroke="black" />' . "\n";
                }
                $retval .= '<text ' . $this->axis_text_attr . ' x="' . $x . '" y="' . ($ybottom + 20) . '">' . "\n";
                $retval .= $val;
                $retval .= "</text>\n";
                $xval += $scale['step'];
            }
            
            $x = $margin['left'] + round(0.5 * $canvas_len);
            $y = $ypos + 35;
            
            $retval .= '<text class="axis-title" ' . $this->label_text_attr . ' x="' . $x . '" y="' . $y . '">' . "\n";
            $retval .= $label;
            $retval .= "</text>\n";
            
            
            return $retval;
        }
        
        protected function svg_yaxis($params = array('ticks'=>true, 'line' => true)) {
            $scale = $this->yaxis->get_scale();
            $label = $this->yaxis->get_label();
            if (($unit = $this->yaxis->get_unit()) !== "") {
                $label .= " (" . $unit . ")";
            }
            $margin = $this->get_margins();
            
            $xpos = $margin['left'];
            $y1 = $this->dim['height'] - $margin['bottom'];
            $y2 = $this->dim['height'] - $margin['top'];
            
            $canvas_len = $margin['top'] - $margin['bottom'];
            $axis_len = $scale['max'] - $scale['min'];
            $canvas_step = floor($scale['step'] * $canvas_len/$axis_len);
            
            $retval = "";
            
            if ($params['line']) {
                $retval .= '<line class="axis" x1="' . $xpos . '" y1="' . $y1 . '" x2="' . $xpos . '" y2="' . $y2 . '" stroke="black" />' . "\n";
            }
            
            $dec = $this->dec($scale['max']);
            $x1 = $xpos;
            $x2 = $xpos - $this->ticksize;
            $yval = $scale['min'];
            for ($offset = 0; $offset <= $canvas_len; $offset += $canvas_step) {
                if ($this->yaxis->number_format) {
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
                    $retval .= '<line x1="' . $x1 . '" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y . '" stroke="black" />' . "\n";
                }
                $retval .= '<text ' . $this->axis_text_attr . ' x="' . ($x2 - 14) . '" y="' . ($y + 5) . '">' . "\n";
                $retval .= $val;
                $retval .= "</text>\n";
                $yval += $scale['step'];
            }
            
            $y = $this->dim['height'] - ($margin['bottom'] + round(0.5 * $canvas_len));
            $x = $xpos - 35;
            
            $retval .= '<text class="axis-title" ' . $this->label_text_attr . ' x="' . 0 . '" y="' . 0 . '" transform="rotate(-90) translate(' . -$y . ',' . $x . ')">' . "\n";
            $retval .= $label;
            $retval .= "</text>\n";
            
            
            return $retval;
         }
        
        protected function svg_start($stylesheet) {
            $retval = '<?xml version="1.0" standalone="no"?>' . "\n";
            if ($stylesheet) {
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
            $retval .= '<svg width="' . $this->dim['width'] . 'px" height="' . $this->dim['height'] . 'px" version = "1.1"' . "\n";
            $retval .= '   baseProfile="basic"' . "\n";
            $retval .= '   xmlns="http://www.w3.org/2000/svg">' . "\n";
            return $retval;
        }
        
        protected function svg_end() {
            return "</svg>\n";
        }
        
        
        // For wedges, expect series to be in order--can be top-down or bottom-up
        // Colors are the colors of the wedges in sequence
        public function svgplot_wedges($wedges, $params=NULL) {
            if (!params) {
                $params = array(
                    'css' => array('filename' => NULL, 'embed' => false),
                    'common_id' => NULL,
                    'vertical_at' => NULL
                );
            }
            // Must have axes and series to plot
            if (!$this->xaxis || !$this->yaxis || count($this->series) == 0) {
                return;
            }
                        
            $xscale = $this->xaxis->get_scale();
            $yscale = $this->yaxis->get_scale();
            $margin = $this->get_margins();
            
            $xoff = $margin['left'];
            $yoff = $margin['bottom'];
            $xfact = ($margin['right'] - $margin['left'])/($xscale['max'] - $xscale['min']);
            $yfact = ($margin['top'] - $margin['bottom'])/($yscale['max'] - $yscale['min']);
            
            // Rescale all series
            $scaled_series = array();
            foreach ($this->series as $id => $points_array) {
                $scaled_series[$id] = array();
                foreach ($points_array as $x => $y) {
                    $xtransf = $xoff + round($xfact * ($x - $xscale['min']));
                    $ytransf = $yoff + round($yfact * ($y - $yscale['min']));
                    $scaled_series[$id][$xtransf] = $ytransf;
                }
            }

            // Search through and see what points all the series have in common
            if ($params['common_id']) {
                $common_series = array();
                $ref_series = current($scaled_series);
                $finished = false;
                $prev_x = NULL;
                $prev_y = NULL;
                foreach (array_keys($ref_series) as $ndx => $x) {
                    $yval = NULL;
                    foreach ($scaled_series as $id => $points_array) {
                        if (!$yval) {
                            $yval = $points_array[$x];
                        } else {
                            if ($points_array[$x] !== $yval) {
                                $finished = TRUE;
                                break;
                            }
                        }
                    }
                    if ($finished) {
                        if ($prev_x && $prev_y) {
                            $prepend = array($prev_x => $prev_y);
                            foreach ($scaled_series as $id => $val) {
                                $scaled_series[$id] = $prepend + $scaled_series[$id];
                            }
                        }
                        break;
                    }
                    $prev_x = $x;
                    $prev_y = $ref_series[$x];
                    $common_series[$x] = $ref_series[$x];
                    foreach ($scaled_series as $id => $val) {
                        unset($scaled_series[$id][$x]);
                    }
                }
                $scaled_series[$params['common_id']] = $common_series;
            }
            
            //******* Begin SVG **********//

            $svg = $this->svg_start($params['css']['filename']);
            
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
            $svg .= "</defs>\n";



            $svg .= $this->svg_hrule('hrule');
            
            $svg .= $this->svg_xaxis(array('ticks'=>false, 'line'=>true));
            $svg .= $this->svg_yaxis(array('ticks'=>false, 'line'=>false));
            
            if ($params['vertical_at']) {
                $y1 = $this->dim['height'] - $margin['bottom'];
                $y2 = $this->dim['height'] - $margin['top'];
                $xvert_scaled = $xoff + round($xfact * ($params['vertical_at'] - $xscale['min']));
                $svg .= '<line id="vertical" width="1" stroke="#cccccc"';
                $svg .= ' x1="' . $xvert_scaled . '" y1="' . $y1 . '" x2="' . $xvert_scaled . '" y2="' . $y2 . '"';
                $svg .= ' />' . "\n";
            }
            
            foreach ($wedges as $id => $wedge) {
                $s1 = $wedge['between'][0];
                $s2 = $wedge['between'][1];
                $points = "";
                foreach ($scaled_series[$s1] as $x => $y) {
                    $points .= $x . "," . ($this->dim['height'] - $y) . " " ;
                }
                foreach (array_reverse($scaled_series[$s2], true) as $x => $y) {
                    $points .= $x . "," . ($this->dim['height'] - $y) . " " ;
                }
                $svg .= '<polygon stroke-width="0" stroke="#000"';
                if ($wedge['id']) {
                    $svg .= ' id="' . $wedge['id'] . '"';
                }
                if ($wedge['color']) {
                    $svg .= ' fill="' . $wedge['color'] . '"';
                } elseif ($wedge['stripes']) {
                    $svg .= ' style="fill:url(#' . $wedge['stripes'] . ');"';
                }
                if ($wedge['opacity']) {
                    $svg .= ' fill-opacity="' . $wedge['opacity'] . '"';
                }
                $svg .= ' points="' . $points . '" />' . "\n";
            }
            
            foreach ($scaled_series as $id => $point_array) {
                $points = "";
                foreach ($point_array as $x => $y) {
                    $points .= $x . "," . ($this->dim['height'] - $y) . " " ;
                }
                $svg .= '<polyline id="' . $id . '" stroke-width="1" stroke="#000"';
                $svg .= ' points="' . $points . '" fill="none" />' . "\n";
            }
            
            $svg .= $this->svg_end();
            
            $fname = tempnam("/***REMOVED***/html/tmp", "graph-") . ".svg";
            $fh = fopen($fname, 'w') or die("Cannot open file " . $fname);
            
            fwrite($fh, $svg);
            
            fclose($fh);
            
            return $fname;
            
        }
    }