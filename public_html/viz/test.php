<html>
  <head>
    <script type="text/javascript" src="http://www.google.com/jsapi"></script>
	<?php
	# Build up query
	$query = "select ";
	$query .= "name, year, rci, gdrs_alloc, bau_pc, gap from gdrs ";
	$query .= "where year >= 2010 and rci >= 1 ";
	$query .= "order by name ";
	$query .= "label name 'Country', year 'Year', rci 'RCI (%)', gdrs_alloc 'Allocation (tCO2/cap)', bau_pc 'Fossil BAU (tCO2/cap)', gap 'Gap between alloc & BAU (tCO2/cap)' ";
	$query .= "format year '%d', rci '%.1f%%', gdrs_alloc '%.1f', bau_pc '%.1f', gap '%.1f' ";
	
	# Create URL from database name
	$queryurl = "'" . 'gdrs-server.php?db=' . $_GET[db] . "'";
	echo <<< ENDJS
    <script type="text/javascript">
      google.load("visualization", "1", {packages:["motionchart"]});
      google.setOnLoadCallback(drawVisualization);
      function drawVisualization() {
        var query = new google.visualization.Query($queryurl);
        query.setQuery("$query");
        query.send(function(result) {
          if(result.isError()) {
            alert(result.getDetailedMessage());
          } else {
            var viz = new google.visualization.MotionChart(document.getElementById('visualization_div'));
            viz.draw(result.getDataTable(), {width: 800, height:400});
          }
        });
      }
    </script>	
ENDJS;
?>
  </head>

  <body>
    <div id="visualization_div"></div>
  </body>
</html>