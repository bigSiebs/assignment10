<?php

include "top.php";

print "<article>";

if (!adminCheck($username)) {
    print "<h2>Sorry.</h2>";
    print "<p>You don't have access to this page.</p>";

} else {
    if (isset($_GET['activity'])) {
        $activityID = (int) $_GET['activity'];

        $check = " SELECT pmkActivityId";
        $check .= " FROM tblActivities";
        $check .= " WHERE fldApproved = ?";
        $checkData = array(0);

        // Call select method
        $unapproved = $thisDatabaseReader->select($check, $checkData, 1, 0, 0, 0, false, false);

        $validID = false;

        foreach ($unapproved as $record) {
            if ($record['pmkActivityId'] == $activityID) {
                $validID = true;
            }
        }

        print '<section id="update-status">';
        
        if ($validID) {
            $update = " UPDATE tblActivities SET";
            $update .= " fldApproved = ?";
            $update .= " WHERE pmkActivityId = ?";
            $updateData = array(1, $activityID);

            $updated = $thisDatabaseWriter->update($update, $updateData, 1, 0, 0, 0, false, false);

            if ($updated) {
                print "<p>Activity " . $activityID . " has been approved.</p>";
            }
        } else {
            print "<p>Invalid activity ID.</p>";
        }
        
        print "</section>";
    }
    print "<section>";

    print "<h2>Unapproved Activities</h2>";
    print "<p>The following activities need to be approved:</p>";

    $query = "SELECT pmkActivityId, fldName, fldOnCampus, fldTownName, fldState";
    $query .= " FROM tblActivities A";
    $query .= " INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId";
    $query .= " WHERE fldApproved = ?";
    $query .= " ORDER BY fldDateSubmitted";
    $queryData = array(0);

    // Call select method
    $info = $thisDatabaseReader->select($query, $queryData, 1, 1, 0, 0, false, false);

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

    print "<th>Approve</th>";
    print "</tr>";

    // For loop to print records
    foreach ($info as $record) {
        print '<tr>';
        // Uses field names (AKA headers) as keys to pick from arrays
        foreach ($headers as $field) {
            print '<td>' . htmlentities($record[$field]) . '</td>';
        }
        print '<td><a href="?activity=' . $record['pmkActivityId'] . '">Approve</a></td>';
        print '</tr>';
    }

    // Close table
    print '</table>';

    print "</section>";
}

print "</article";

include 'footer.php';
?>