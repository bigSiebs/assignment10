<?php

include "top.php";

print "<article>";

// Get activity ID
if (isset($_GET['activity'])) {
    $activityID = (int) $_GET['activity'];
} else {
    $activityID = "";
}

// Get vote
if (isset($_GET['vote'])) {
    $vote = (int) $_GET['vote'];
    // Reset value if not valid
    if (!($vote == -1 OR $vote == 1)) {
        $vote = "";
    }
} else {
    $vote = "";
}

// If activity ID and vote are appended to URL
if ($activityID AND $vote) {
    // Query database looking for activity ID
    $checkActivityQuery = "SELECT pmkActivityId";
    $checkActivityQuery .= " FROM tblActivities";
    $checkActivityQuery .= " WHERE pmkActivityId = ?";
    $checkActivityData = array($activityID);
    
    $checkActivity = $thisDatabaseReader->select($checkActivityQuery, $checkActivityData, 1, 0, 0, 0, false, false);
  
    
    // Make sure array returned something; signals that activity ID is valid
    // If invalid, print error
    if (!$checkActivity) {
        print "<p>Invalid activity number.</p>";
    
        
    } else { // if valid
        // Query database for user/activity vote combo
        $checkVoteQuery = "SELECT fldVote";
        $checkVoteQuery .= " FROM tblVotes";
        $checkVoteQuery .= " WHERE fnkActivityId = ? AND";
        $checkVoteQuery .= " fnkNetId = ?";
        $checkVoteData = array($activityID, $username); // username defined in top.php
        
        $checkVote = $thisDatabaseReader->select($checkVoteQuery, $checkVoteData, 1, 1, 0, 0, false, false);
        
        if (!$checkVote) { // If vote doesn't exist
            // INSERT RECORD
            print "<p>TEST: Vote doesn't exist.</p>";
            
            $insertQuery = "INSERT INTO tblVotes SET";
            $insertQuery .= " fnkNetId = ?,";
            $insertQuery .= " fnkActivityId = ?,";
            $insertQuery .= " fldVote = ?";
            $insertData = array($username, $activityID, $vote);
            
            $inserted = $thisDatabaseWriter->insert($insertQuery, $insertData, 0, 0, 0, 0, false, false);
            
            if ($inserted) {
                    print "<p>Thanks for voting!</p>";
                }
            
        } else {
            // Check that voter won't exceed min/max
            print "<p>TEST: Vote exists.</p>";
            $newVote = $checkVote[0]['fldVote'] + $vote; // $checkVote should contain one value
            
            // Check that new vote won't exceed 1 or fall below -1
            if ($newVote > 1) { // Vote exceeds max
                print "<p>TEST: Vote exceeds max.</p>";
        
                
            } else if ($newVote < -1) { // Vote falls below min
                print "<p>TEST: Vote falls below min.</p>";
            
                
            } else { // vote is valid
                print "<p>TEST: New vote value is valid.</p>";
                $updateQuery = " UPDATE tblVotes SET";
                $updateQuery .= " fldVote = ?";
                $updateQuery .= " WHERE fnkActivityId = ? AND";
                $updateQuery .= " fnkNetId = ?";
                $updateData = array($newVote, $activityID, $username);
                
                $updated = $thisDatabaseWriter->update($updateQuery, $updateData, 1, 1, 0, 0, false, false);
                
                if ($updated) {
                    print "<p>Thanks for voting!</p>";
                }
            }
        }
    }
}

// NEED TO ADD LIMIT CLAUSE
$query = "SELECT pmkActivityId, fldName, fldOnCampus, fldTownName, fldState";
$query .= " FROM tblActivities A";
$query .= " INNER JOIN tblVotes V ON A.pmkActivityId = V.fnkActivityId";
$query .= " INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId";
$query .= " WHERE fldApproved = 1";
$query .= " GROUP BY A.fldName";
$query .= " ORDER BY SUM(fldVote) DESC LIMIT 10";
$data = array();
$val = array(1, 1, 0, 0);

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
print "<th>Vote Up</th>";
print "<th>Vote Down</th>";

print "</tr>";

// For loop to print records
foreach ($info as $record) {
    print '<tr>';
    // Uses field names (AKA headers) as keys to pick from arrays
    foreach ($headers as $field) {
        print '<td>' . htmlentities($record[$field]) . '</td>';
    }
    print '<td><a href="?activity=' . $record['pmkActivityId'];
    print '&vote=1' . '">&#x25B2</a></td>';
    
    print '<td><a href="?activity=' . $record['pmkActivityId'];
    print '&vote=-1' . '">&#x25BC</a></td>';
    
    print '</tr>';
}

// Close table
print '</table>';

print "</article>";

include "footer.php";
?>