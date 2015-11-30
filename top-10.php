<?php

include "top.php";

print "<article>";

// Get activity ID
if (isset($_POST['btnUpVote']) OR isset($_POST['btnDownVote'])) {
    if (isset($_POST['btnUpVote'])) {
        $activityID = (int) htmlentities($_POST["hidActivityId"], ENT_QUOTES, "UTF-8");
        $vote = 1;
    } else {
        $activityID = (int) htmlentities($_POST["hidActivityId"], ENT_QUOTES, "UTF-8");
        $vote = -1;
    }
    
    print '<section>';

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
        $selectUserQuery = "SELECT pmkNetId";
        $selectUserQuery .= " FROM tblAffiliates";
        $selectUserQuery .= " WHERE pmkNetId = ?";
        $selectUserData = array($username);

        $checkUser = $thisDatabaseReader->select($selectUserQuery, $selectUserData, 1, 0, 0, 0, false, false);

        if (!$checkUser) { // if user is not in affiliates table
            $userInsertQuery = "INSERT INTO tblAffiliates SET";
            $userInsertQuery .= " pmkNetId = ?";
            $userInsertData = array($username);

            $userInserted = $thisDatabaseWriter->insert($userInsertQuery, $userInsertData, 0, 0, 0, 0, false, false);
        }

        // Query database for user/activity vote combo
        $checkVoteQuery = "SELECT fldVote";
        $checkVoteQuery .= " FROM tblVotes";
        $checkVoteQuery .= " WHERE fnkActivityId = ? AND";
        $checkVoteQuery .= " fnkNetId = ?";
        $checkVoteData = array($activityID, $username); // username defined in top.php

        $checkVote = $thisDatabaseReader->select($checkVoteQuery, $checkVoteData, 1, 1, 0, 0, false, false);
        
        $inserted = "";
        $updated = "";
        
        if (!$checkVote) { // If vote doesn't exist

            $insertQuery = "INSERT INTO tblVotes SET";
            $insertQuery .= " fnkNetId = ?,";
            $insertQuery .= " fnkActivityId = ?,";
            $insertQuery .= " fldVote = ?";
            $insertData = array($username, $activityID, $vote);

            $inserted = $thisDatabaseWriter->insert($insertQuery, $insertData, 0, 0, 0, 0, false, false);

            if ($inserted) {
                print "<p>Your vote has been tallied. Thanks for voting!</p>";
            }
            
        } else {
            // Check that voter won't exceed min/max
            $newVote = $checkVote[0]['fldVote'] + $vote; // $checkVote should contain one value
            
            // Check that new vote won't exceed 1 or fall below -1
            if ($newVote > 1) { // Vote exceeds max
                print "<p>Sorry, you cannot upvote this activity again.</p>";
            } else if ($newVote < -1) { // Vote falls below min
                print "<p>Sorry, you cannot downvote this activity again.</p>";
            } else { // vote is valid
                $updateQuery = " UPDATE tblVotes SET";
                $updateQuery .= " fldVote = ?";
                $updateQuery .= " WHERE fnkActivityId = ? AND";
                $updateQuery .= " fnkNetId = ?";
                $updateData = array($newVote, $activityID, $username);

                $updated = $thisDatabaseWriter->update($updateQuery, $updateData, 1, 1, 0, 0, false, false);
                
                if ($updated) {
                    print "<p>Your vote has been changed. Thanks for voting!</p>";
                }
            }
        }
    }
}

print "</section>";

// NEED TO ADD LIMIT CLAUSE
$query = "SELECT pmkActivityId, fldName, fldCategory, fldOnCampus, fldTownName, fldState";
$query .= ", fldDistance, fldLocation, fldCost, fldURL, fldDescription, fnkSubmitNetId";
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

$rank = 1;

// For loop to print records
foreach ($info as $record) {
    print '<div id="dropdown-' . $rank . '" ';
    print 'class="dropdown dropdown-processed">';
    
    // ** Add vote buttons **//
    // Add upvote form/button
    print '<form action="' . $phpSelf . '" method="post" ';
    print 'id="frmVote-' . $record['pmkActivityId'] . '">';
    
    // Add hidden field to hold activity ID
    print '<fieldset class="vote-button">';
    print '<input type="hidden" id="hidActivityId' . $record['pmkActivityId'] . '" ';
    print 'name="hidActivityId" value="' . $record['pmkActivityId'] . '">';
    
    // Add up button
    print '<input type="submit" id="btnUpVote-' . $record['pmkActivityId'] . '" ';
    print 'name="btnUpVote" value="&#x25B2" ';
    print 'tabindex="100" class="up-vote">';
    
    // Add down button
    print '<input type="submit" id="btnDownVote-' . $record['pmkActivityId'] . '" ';
    print 'name="btnDownVote" value="&#x25BC" ';
    print 'tabindex="110" class="down-vote">';
    print '</fieldset>';
    print '</form>';
    
    print '<p>';
    
        // Make admin-only [Edit] column, which allows admin to edit records
    if (adminCheck($thisDatabaseReader, $username)) {
        $appendURL = '?activity=' . $record['pmkActivityId'];
        print '[<a href="form.php' . $appendURL . '">';
        print 'Edit</a>] ';
        print '[<a href="' . $adminPath . 'remove.php' . $appendURL . '">';
        print 'Remove</a>] ';
    }
    
    print $rank . '. ';
    print '<a class="dropdown-link" href="#">';
    print $record['fldName'];
    print '</a>';
    
    print '</p>';
    
    print '<div class="dropdown-container" style="display: none;">';
    print '<ol>';
    print '<li><b>Submitted by:</b> ' . $record['fnkSubmitNetId'] . '</li>';
    print '<li><b>Category:</b> ' . $record['fldCategory'] . '</li>';
    print '<li><b>On Campus?</b> ';
    if ($record['fldOnCampus'] == 1) {
        print "YES";
    } else {
        print "NO";
    }
    print "</li>";
    print '<li><b>Town:</b> ' . $record['fldTownName'] . ', ' . $record['fldState'] . '</li>';
    if ($record['fldDistance'] != 0) {
        print '<li><b>Distance from Burlington:</b> ~' . $record['fldDistance'] . ' miles</li>';
    }
    print '<li><b>Cost:</b> ';
    if ($record['fldCost'] == 0 AND $record['fldCost'] != "") {
        print "FREE";
    } else {
        print '$' . $record['fldCost'] . '</li>';
    }
    if ($record['fldURL'] != '') {
        print '<li><b>URL:</b> <a href="' . $record['fldURL'] . '">Click here</a></li>';
    }
    print '<li><b>Description:</b> ' . $record['fldDescription'] . '</li>';
    print '</ol></div></div>';

    $rank++;
}

if (adminCheck($thisDatabaseReader, $username)) {
    print "<section>";

    print "<h2>For administrators</h2>";
    print '<p>Click <a href="' . $adminPath . 'approve.php">here</a>';
    print ' to see a list of submitted';
    print ' activities that are awaiting approval.</p>';

    print "</section>";
}

print "</article>";

include "footer.php";
?>