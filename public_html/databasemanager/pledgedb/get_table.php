<?php
include_once 'functions.php';

function db_get_country_table() {
    $db = db_connect();
    
$query = <<<SQL
SELECT public, id, country.iso3 AS iso3, name, conditional, quantity, reduction_percent,
    rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details
    FROM country, pledge
    WHERE country.iso3 = pledge.iso3
    ORDER BY name, by_year, conditional;
SQL;
    
    $result = mysql_query($query, $db);
    if (!$result) {
        mysql_close($db);
        die('Invalid query: ' . mysql_error());
    }
    
    mysql_close($db);

    $html = '<table id="country_tbl" class="table countrytbl">';
    $html .= '<thead>';
    $html .= "<tr>";
    $html .= "<th>Edit</th>";
    $html .= "<th>Public</th>";
    $html .= "<th>ISO code</th>";
    $html .= "<th>Name</th>";
    $html .= "<th>Cond&#8217;l</th>";
    $html .= "<th>Non-<br />CO<sub>2</sub></th>";
    $html .= "<th>Land<br />use</th>";
    $html .= "<th>Quantity</th>";
    $html .= "<th>Red.<br />(%)</th>";
    $html .= "<th>Type</th>";
    $html .= "<th>Rel.<br />to</th>";
    $html .= "<th>Ref.<br />year</th>";
    $html .= "<th>Targ.<br />year</th>";
    $html .= "<th>Delete</th>";
    $html .= "<th>Info. link</th>";
    $html .= "<th>Source</th>";
    $html .= "<th>Caveat/Additional Data</th>";
    $html .= "<th>Details</th>";
    $html .= "</tr>";
    $html .= '</thead>';
    $html .= '<tbody>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $html .= "<tr>";
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
        $html .= "<td>" . $row['reduction_percent'] . "</td>";
        $html .= "<td>" . $row['rel_to'] . "</td>";
        $html .= "<td>" . $row['year_or_bau'] . "</td>";
        $html .= "<td>" . ($row['rel_to_year'] ? $row['rel_to_year'] : "") . "</td>";
        $html .= "<td>" . $row['by_year'] . "</td>";
        $html .= '<td><input type="submit" value="Delete" name="' . $row['id'] . '"></td>';
        $html .= "<td>" . $row['info_link'] . "</td>";
        $html .= "<td>" . $row['source'] . "</td>";
        $html .= "<td>" . $row['caveat'] . "</td>";
        $html .= "<td>" . $row['details'] . "</td>";
        $html .= "</tr>";
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
    rel_to, include_nonco2, include_lulucf, year_or_bau, rel_to_year, by_year, info_link, source, caveat, details
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

    $html = '<table id="region_tbl" class="table regiontbl">';
    $html .= '<thead>';
    $html .= "<tr>";
    $html .= "<th>Edit</th>";
    $html .= "<th>Public</th>";
    $html .= "<th>GDRs<br />code</th>";
    $html .= "<th>Name</th>";
    $html .= "<th>Cond&#8217;l</th>";
    $html .= "<th>Non-<br />CO<sub>2</sub></th>";
    $html .= "<th>Land<br />use</th>";
    $html .= "<th>Quantity</th>";
    $html .= "<th>Red.<br />(%)</th>";
    $html .= "<th>Type</th>";
    $html .= "<th>Rel.<br />to</th>";
    $html .= "<th>Ref.<br />year</th>";
    $html .= "<th>Targ.<br />year</th>";
    $html .= "<th>Delete</th>";
    $html .= "<th>Info. link</th>";
    $html .= "<th>Source</th>";
    $html .= "<th>Caveat/Additional Data</th>";
    $html .= "<th>Details</th>";
    $html .= "</tr>";
    $html .= '</thead>';
    $html .= '<tbody>';
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $html .= "<tr>";
        $html .= '<td><input type="submit" value="Edit" name="' . $row['id'] . '"></td>';
        $chk = $row['public'] ? ' checked="checked"' : '';
        if ($row['public']) {
            $html .= '<td><input type="submit" value="Hide" name="' . $row['id'] . '"></td>';
        } else {
            $html .= '<td><input type="submit" value="Publish" name="' . $row['id'] . '"></td>';
        }
        $html .= "<td>" . $row['region_code'] . "</td>";
        $html .= "<td>" . $row['name'] . "</td>";
        $html .= "<td>" . ($row['conditional'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . ($row['include_nonco2'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . ($row['include_lulucf'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . $row['quantity'] . "</td>";
        $html .= "<td>" . $row['reduction_percent'] . "</td>";
        $html .= "<td>" . $row['rel_to'] . "</td>";
        $html .= "<td>" . $row['year_or_bau'] . "</td>";
        $html .= "<td>" . ($row['rel_to_year'] ? $row['rel_to_year'] : "") . "</td>";
        $html .= "<td>" . $row['by_year'] . "</td>";
        $html .= '<td><input type="submit" value="Delete" name="' . $row['id'] . '"></td>';
        $html .= "<td>" . $row['info_link'] . "</td>";
        $html .= "<td>" . $row['source'] . "</td>";
        $html .= "<td>" . $row['caveat'] . "</td>";
        $html .= "<td>" . $row['details'] . "</td>";
        $html .= "</tr>";
    }
    $html .= '</tbody>';
    $html .= "</table>";
    mysql_free_result($result);
    
    return $html;
}

echo db_get_region_table();
echo "<br /><br />";
echo db_get_country_table();
