<?php

include "top.php";
    
print "<article>";

// NEED TO ADD LIMIT CLAUSE
$query = "SELECT fldName, fldOnCampus, fldTownName, fldState";
$query .= " FROM tblActivities A";
$query .= " INNER JOIN tblVotes V ON A.pmkActivityId = V.fnkActivityId";
$query .= " INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId";
$query .= " WHERE fldApproved = 1";
$query .= " GROUP BY A.fldName";
$query .= " ORDER BY SUM(fldVote) DESC LIMIT 10";
$data = array();
$val = array(1, 1, 0, 0);


$test = $thisDatabaseReader->testquery($query, $data, $val[0], $val[1], $val[2], $val[3], false, false);

// Call select method
$info = $thisDatabaseReader->select($query, $data, $val[0], $val[1], $val[2], $val[3], false, false);

// To troubleshoot returned array
if ($debug) {
    print "<p>DATA: <pre>";
    print_r($info);
    print "</pre></p>";
}

// Start printing table
print '<table>';
print '<tr>';

// Get headings from first subarray (removes indexes with filter function)
$fields = array_keys($info[0]);
$headers = array_filter($fields, 'is_string'); // Picks up only str values

// Print headings
foreach ($headers as $head) {
    $camelCase = preg_split('/(?=[A-Z])/', substr($head, 3));

    $heading = "";

    foreach ($camelCase as $oneWord) {
        $heading .= $oneWord . " ";
    }

    print '<th>' . $heading . '</th>';
}

print "</tr>";

// For loop to print records
foreach ($info as $record) {
    print '<tr>';
    // Uses field names (AKA headers) as keys to pick from arrays
    foreach ($headers as $field) {
        print '<td>' . htmlentities($record[$field]) . '</td>';
    }
    print '</tr>';
}

// Close table
print '</table>';

print "</article>";

include "footer.php";
?>