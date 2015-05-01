<?php

include_once 'functions.php';

function db_get_country_table($min_by_year = NULL, $max_by_year = NULL) {
    $db = db_connect();
    $yearwhere = "";
    if (isset($min_by_year)) { $yearwhere = " AND pledge.by_year >= " . $min_by_year . " "; }
    if (isset($max_by_year)) { $yearwhere .= " AND pledge.by_year <= " . $max_by_year . " "; }

    $query = "SELECT public, id, country.iso3 AS iso3, name, conditional, quantity, reduction_percent, ";
    $query .= "rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details ";
    $query .= "FROM country, pledge WHERE country.iso3 = pledge.iso3 " . $yearwhere ;
    $query .= "ORDER BY name, by_year, conditional;";

    $result = mysql_query($query, $db);
    mysql_close($db);
    if (!$result) {
        die('Invalid query: ' . mysql_error());
    } else {
        return $result;
    }
}    

$result = db_get_country_table($_REQUEST['min_year'], $_REQUEST['max_year']);
$data = array();
while($row = mysql_fetch_assoc($result))
{
   $data[] = $row;
}

$colNames = array_keys(reset($data))
?>

<table border="1">
<tr>
  <?php
     //print the header
     foreach($colNames as $colName)
     {
        echo "<th>$colName</th>";
     }
  ?>
</tr>

  <?php
     //print the rows
     foreach($data as $row)
     {
        echo "<tr>";
        foreach($colNames as $colName)
        {
           echo "<td>".$row[$colName]."</td>";
        }
        echo "</tr>";
     }
  ?>
</table>
