<?php
include_once 'functions.php';

function table_head($id="", $css_class="") {
    $html = '<table id="' . $id . '" class="' . $css_class . '">';
    $html .= '<thead>';
    $html .= "<tr>";
    $html .= "<th rowspan=2>Edit</th>";
    $html .= "<th rowspan=2>Public</th>";
    $html .= "<th rowspan=2>ISO code</th>";
    $html .= "<th rowspan=2>Name</th>";
    $html .= "<th rowspan=2>Cond&#8217;l</th>";
    $html .= "<th rowspan=2>Non-<br />CO<sub>2</sub></th>";
    $html .= "<th rowspan=2>Land<br />use</th>";
    $html .= "<th colspan=5 style='text-align:justify;'>Target Details</th>";
    $html .= "<th rowspan=2>&nbsp;</th>";
    $html .= "<th colspan=3>Target&nbsp;Breakdown&nbsp;(Mt)</th>";
    $html .= "<th rowspan=2>Targ.<br />year</th>";
    $html .= "<th rowspan=2>Delete</th>";
    $html .= "<th rowspan=2>Caveat/Additional Data</th>";
    $html .= "<th rowspan=2>Details</th>";
    $html .= "<th rowspan=2>Info. link</th>";
    $html .= "<th rowspan=2>Source</th>";
    $html .= "</tr>";
    $html .= "<tr>";
    $html .= "<th>Type</th>";
    $html .= "<th>Amount</th>";
    $html .= "<th>Type<br></th>";
    $html .= "<th>Rel. to<br></th>";
    $html .= "<th>Ref. year</th>";
    $html .= "<th>fossil CO<sub>2</sub><br></th>";
    $html .= "<th>non CO<sub>2</sub><br></th>";
    $html .= "<th>LULUCF</sub><br></th>";
    $html .= "</tr>";
    $html .= '</thead>';
    return $html;
}

function table_row($row=NULL) {
    if (isset($row)) {
        $html = "<tr>";
        $html .= '<td><input type="submit" value="Edit" name="' . $row['id'] . '"></td>';
        $chk = $row['public'] ? ' checked="checked"' : '';
        if ($row['public']) {
            $html .= '<td><input type="submit" value="Hide" name="' . $row['id'] . '"></td>';
        } else {
            $html .= '<td><input type="submit" value="Publish" name="' . $row['id'] . '"></td>';
        }
        $html .= "<td>" . $row['iso3'] . "</td>";
        $html .= "<td>" . $row['name'] . "</td>";
        $html .= "<td>" . ($row['conditional'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . ($row['include_nonco2'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . ($row['include_lulucf'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . $row['quantity'] . "</td>";
        $html .= "<td>" . (($row['quantity']=="absolute_Mt") ? (remove_trailing_zeros($row['target_Mt'])."Mt") : (remove_trailing_zeros($row['reduction_percent'])."%")) . "</td>";
        $html .= "<td>" . $row['rel_to'] . "</td>";
        $html .= "<td>" . $row['year_or_bau'] . "</td>";
        $html .= "<td>" . ($row['rel_to_year'] ? $row['rel_to_year'] : "") . "</td>";
        $html .= "<td>" . '' . "</td>";
        $test = floatval($row['target_Mt_CO2']) + floatval($row['target_Mt_nonCO2']) + floatval($row['target_Mt_LULUCF']);
        $html .= "<td>" . (($test >0) ? remove_trailing_zeros($row['target_Mt_CO2']) : '') . "</td>";
        $html .= "<td>" . (($test >0) ? remove_trailing_zeros($row['target_Mt_nonCO2']) : '') . "</td>";
        $html .= "<td>" . (($test >0) ? remove_trailing_zeros($row['target_Mt_LULUCF']) : '') . "</td>";
        $html .= "<td>" . $row['by_year'] . "</td>";
        $html .= '<td><input type="submit" value="Delete" name="' . $row['id'] . '"></td>';
        $html .= "<td>" . $row['caveat'] . "</td>";
        $html .= "<td>" . $row['details'] . "</td>";
        $html .= "<td>" . $row['info_link'] . "</td>";
        $html .= "<td>" . $row['source'] . "</td>";
        $html .= "</tr>";
        return $html;
    } else {
        return NULL;
    }
}

function db_get_country_table() {
    $db = db_connect();
    
$query = <<<SQL
SELECT public, id, country.iso3 AS iso3, name, conditional, quantity, reduction_percent,
    rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, target_Mt,
    target_Mt_CO2, target_Mt_nonCO2, target_Mt_LULUCF, by_year, info_link, source, 
    caveat, details
    FROM country, pledge
    WHERE country.iso3 = pledge.iso3
    ORDER BY name, by_year, conditional;
SQL;
    
    $result = mysql_query($query, $db);
    if (!$result) { mysql_close($db); die('Invalid query: ' . mysql_error()); }
    
    mysql_close($db);

    $html = table_head("country_tbl", "table countrytbl");
    $html .= '<tbody>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $html .= table_row($row);
    }
    $html .= '</tbody>';
    $html .= "</table>";
    mysql_free_result($result);
    
    return $html;
}

function db_get_region_table() {
    $db = db_connect();
    
$query = <<<SQL
SELECT public, id, region.region_code AS region_code, name, conditional, quantity, reduction_percent,
    rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, target_Mt, target_Mt_CO2, 
    target_Mt_nonCO2, target_Mt_LULUCF, by_year, info_link, source, caveat, details
    FROM region, pledge
    WHERE region.region_code = pledge.region
    ORDER BY name, by_year, conditional;
SQL;
    
    $result = mysql_query($query, $db);
    if (!$result) {
        mysql_close($db);
        die('Invalid query: ' . mysql_error());
    }
    
    mysql_close($db);

    $html = table_head("region_tbl", "table regiontbl");
    $html .= '<tbody>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $row['iso3'] = $row['region_code'];
        $html .= table_row($row);
    }
    $html .= '</tbody>';
    $html .= "</table>";
    mysql_free_result($result);
    
    return $html;
}

echo db_get_region_table();
echo "<br /><br />";
echo db_get_country_table();
