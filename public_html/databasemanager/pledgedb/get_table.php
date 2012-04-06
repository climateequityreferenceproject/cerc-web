<?php
function db_get_country_table() {
    $db = mysql_connect('localhost', 'pledges', '***REMOVED***');
    if (!$db) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db("pledges", $db);
    
$query = <<<SQL
SELECT public, id, country.iso3 AS iso3, name, conditional, quantity, reduction_percent,
    rel_to, year_or_bau, rel_to_year, by_year, source, details
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

    $html = "<table>";
    $html .= "<tr>";
    $html .= "<th>public</th>";
    $html .= "<th>ISO code</th>";
    $html .= "<th>Name</th>";
    $html .= "<th>Conditional?</th>";
    $html .= "<th>Quantity</th>";
    $html .= "<th>Reduction (%)</th>";
    $html .= "<th>Type</th>";
    $html .= "<th>Relative to</th>";
    $html .= "<th>Reference year</th>";
    $html .= "<th>Target year</th>";
    $html .= "<th></th>";
    $html .= "<th>Source</th>";
    $html .= "<th>Details</th>";
    $html .= "</tr>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $html .= "<tr>";
        $chk = $row['public'] ? ' checked="checked"' : '';
        if ($row['public']) {
            $html .= '<td><input type="submit" value="Hide" name="' . $row['id'] . '"></td>';
        } else {
            $html .= '<td><input type="submit" value="Publish" name="' . $row['id'] . '"></td>';
        }
        $html .= "<td>" . $row['iso3'] . "</td>";
        $html .= "<td>" . $row['name'] . "</td>";
        $html .= "<td>" . ($row['conditional'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . $row['quantity'] . "</td>";
        $html .= "<td>" . $row['reduction_percent'] . "</td>";
        $html .= "<td>" . $row['rel_to'] . "</td>";
        $html .= "<td>" . $row['year_or_bau'] . "</td>";
        $html .= "<td>" . ($row['rel_to_year'] ? $row['rel_to_year'] : "") . "</td>";
        $html .= "<td>" . $row['by_year'] . "</td>";
        $html .= '<td><input type="submit" value="Delete" name="' . $row['id'] . '"></td>';
        $html .= "<td>" . $row['source'] . "</td>";
        $html .= "<td>" . $row['details'] . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";
    mysql_free_result($result);
    
    return $html;
}

function db_get_region_table() {
    $db = mysql_connect('localhost', 'pledges', '***REMOVED***');
    if (!$db) {
        die('Could not connect: ' . mysql_error());
    }
    mysql_select_db("pledges", $db);
    
$query = <<<SQL
SELECT public, id, region.region_code AS region_code, name, conditional, quantity, reduction_percent,
    rel_to, year_or_bau, rel_to_year, by_year, source, details
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

    $html = "<table>";
    $html .= "<tr>";
    $html .= "<th>public</th>";
    $html .= "<th>GDRs region code</th>";
    $html .= "<th>Name</th>";
    $html .= "<th>Conditional?</th>";
    $html .= "<th>Quantity</th>";
    $html .= "<th>Reduction (%)</th>";
    $html .= "<th>Type</th>";
    $html .= "<th>Relative to</th>";
    $html .= "<th>Reference year</th>";
    $html .= "<th>Target year</th>";
    $html .= "<th></th>";
    $html .= "<th>Source</th>";
    $html .= "<th>Details</th>";
    $html .= "</tr>";
    while ($row = mysql_fetch_array($result, MYSQL_ASSOC)) {
        $html .= "<tr>";
        $chk = $row['public'] ? ' checked="checked"' : '';
        if ($row['public']) {
            $html .= '<td><input type="submit" value="Hide" name="' . $row['id'] . '"></td>';
        } else {
            $html .= '<td><input type="submit" value="Publish" name="' . $row['id'] . '"></td>';
        }
        $html .= "<td>" . $row['region_code'] . "</td>";
        $html .= "<td>" . $row['name'] . "</td>";
        $html .= "<td>" . ($row['conditional'] ? "yes" : "no") . "</td>";
        $html .= "<td>" . $row['quantity'] . "</td>";
        $html .= "<td>" . $row['reduction_percent'] . "</td>";
        $html .= "<td>" . $row['rel_to'] . "</td>";
        $html .= "<td>" . $row['year_or_bau'] . "</td>";
        $html .= "<td>" . ($row['rel_to_year'] ? $row['rel_to_year'] : "") . "</td>";
        $html .= "<td>" . $row['by_year'] . "</td>";
        $html .= '<td><input type="submit" value="Delete" name="' . $row['id'] . '"></td>';
        $html .= "<td>" . $row['source'] . "</td>";
        $html .= "<td>" . $row['details'] . "</td>";
        $html .= "</tr>";
    }
    $html .= "</table>";
    mysql_free_result($result);
    
    return $html;
}

echo db_get_country_table();
echo "<br /><br />";
echo db_get_region_table();
