<?php
    class Axis {
        protected $min = 0;
        protected $max = 0;
        protected $label = "";
        protected $unit = "";
        
        function __construct($min, $max, $label, $unit, $use_limits) {
            $this->min = $min;
            $this->max = $max;
            $this->label = $label;
            $this->unit = $unit;
            $this->use_limits = $use_limits;
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
            
            $expon = round(log10($dx));
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
        public function set_xaxis($min, $max, $label, $unit, $use_limits=FALSE) {
            if (!$this->xaxis) {
                $this->xaxis = new Axis($min, $max, $label, $unit, $use_limits);
            }
        }
        public function set_yaxis($min, $max, $label, $unit, $use_limits=FALSE) {
            if (!$this->yaxis) {
                $this->yaxis = new Axis($min, $max, $label, $unit, $use_limits);
            }
        }
        
        public function add_series($points) {
            $this->series[] = $points;
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
        
        protected function svg_xaxis() {
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
                $yzero = -$yscale['min']/($yscale['max'] - $yscale['min']) * ($margin['top'] - $margin['bottom']);
            }
            
            $ypos = $this->dim['height'] - $margin['bottom'] - $yzero;
            $x1 = $margin['left'];
            $x2 = $margin['right'];
            
            $canvas_len = $margin['right'] - $margin['left'];
            $axis_len = $scale['max'] - $scale['min'];
            $canvas_step = floor($scale['step'] * $canvas_len/$axis_len);
            
            $retval = '<line x1="' . $x1 . '" y1="' . $ypos . '" x2="' . $x2 . '" y2="' . $ypos . '" stroke="black" />' . "\n";
            
            $y1 = $ypos;
            $y2 = $ypos + $this->ticksize;
            $xval = $scale['min'];
            for ($offset = 0; $offset <= $canvas_len; $offset += $canvas_step) {
                $x = $margin['left'] + $offset;
                $retval .= '<line x1="' . $x . '" y1="' . $y1 . '" x2="' . $x . '" y2="' . $y2 . '" stroke="black" />' . "\n";
                $retval .= '<text ' . $this->axis_text_attr . ' x="' . $x . '" y="' . ($y2 + 14) . '">' . "\n";
                $retval .= $xval;
                $retval .= "</text>\n";
                $xval += $scale['step'];
            }
            
            $x = $margin['left'] + round(0.5 * $canvas_len);
            $y = $ypos + 30;
            
            $retval .= '<text ' . $this->label_text_attr . ' x="' . $x . '" y="' . $y . '">' . "\n";
            $retval .= $label;
            $retval .= "</text>\n";
            
            
            return $retval;
        }
        
        protected function svg_yaxis() {
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
            
            $retval = '<line x1="' . $xpos . '" y1="' . $y1 . '" x2="' . $xpos . '" y2="' . $y2 . '" stroke="black" />' . "\n";
            
            $x1 = $xpos;
            $x2 = $xpos - $this->ticksize;
            $yval = $scale['min'];
            for ($offset = 0; $offset <= $canvas_len; $offset += $canvas_step) {
                $y = $this->dim['height'] - ($margin['bottom'] + $offset);
                $retval .= '<line x1="' . $x1 . '" y1="' . $y . '" x2="' . $x2 . '" y2="' . $y . '" stroke="black" />' . "\n";
                $retval .= '<text ' . $this->axis_text_attr . ' x="' . ($x2 - 14) . '" y="' . ($y + 5) . '">' . "\n";
                $retval .= $yval;
                $retval .= "</text>\n";
                $yval += $scale['step'];
            }
            
            $y = $this->dim['height'] - ($margin['bottom'] + round(0.5 * $canvas_len));
            $x = $xpos - 30;
            
            $retval .= '<text ' . $this->label_text_attr . ' x="' . 0 . '" y="' . 0 . '" transform="rotate(-90) translate(' . -$y . ',' . $x . ')">' . "\n";
            $retval .= $label;
            $retval .= "</text>\n";
            
            
            return $retval;
         }
        
        protected function svg_start() {
            $retval = '<?xml version="1.0" standalone="no"?>' . "\n";
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
        public function svgplot_wedges($colors) {
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
            
            $ncolors = count($colors);
            
            $svg = $this->svg_start();
            
            $svg .= $this->svg_xaxis();
            $svg .= $this->svg_yaxis();
            
            for ($i = 0; $i < count($this->series) - 1; $i++) {
                $colorndx = fmod($i, $ncolors);
                $points = "";
                foreach ($this->series[$i] as $x => $y) {
                    $xtransf = $xoff + round($xfact * ($x - $xscale['min']));
                    $ytransf = $yoff + round($yfact * ($y - $yscale['min']));
                    $points .= $xtransf . "," . ($this->dim['height'] - $ytransf) . " " ;
                }
                foreach (array_reverse($this->series[$i + 1], true) as $x => $y) {
                    $xtransf = $xoff + round($xfact * ($x - $xscale['min']));
                    $ytransf = $yoff + round($yfact * ($y - $yscale['min']));
                    $points .= $xtransf . "," . ($this->dim['height'] - $ytransf) . " " ;
                }
                $svg .= '<polygon stroke-width="1" stroke="#999" fill="' . $colors[$colorndx] . '" fill-opacity="0.8" points="' . $points . '" />' . "\n";
            }
            
            $svg .= $this->svg_end();
            
            $fname = tempnam("/***REMOVED***/html/tmp", "graph-") . ".svg";
            $fh = fopen($fname, 'w') or die("Cannot open file " . $fname);
            
            fwrite($fh, $svg);
            
            fclose($fh);
            
            return $fname;
            
        }
    }