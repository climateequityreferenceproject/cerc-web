<?php
    include("graph_core.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"> 
<html xmlns="http://www.w3.org/1999/xhtml"> 
    <head>
        <title>Test</title>    
    </head>
    
    <body>
        <?php 
            $graph = new Graph(500, 250);
            $graph->set_xaxis(1990, 2030, "years", "");
            $graph->set_yaxis(-10, 20, "Test y", "GtC");
            $graph->add_series(array(1990=>-2, 2000=>0, 2005=>2, 2010=>3.5, 2020=>7, 2030=>12));
            $graph->add_series(array(1990=>-2, 2000=>0, 2005=>2, 2010=>3, 2020=>5, 2030=>7));
            $graph->add_series(array(1990=>-2, 2000=>0, 2005=>2, 2010=>2.5, 2020=>3, 2030=>2));
            $fname = $graph->svgplot_wedges(array("#0C0", "#00C"));
            
            $fname = "/tmp/" . basename($fname);
            
            echo '<p><a href="' . $fname . '">File</a></p>' . "\n";
            
        ?>
        <object data="<?php echo $fname; ?>" type="image/svg+xml" style="width:500px; height:250px; border: 1px solid #CCC;">
            <p>No SVG support</p>
        </object>
    </body>
</html>