<?php

include "top.php";

$numberRecords = 10;

print "<article>";
print "<h2>The List</h2>";
print "<p>Click on the names of the activities to get more information.";
print " Please feel free to vote up or down activities you've done before.</p>";

// Simple query to count records
$queryTotal = "SELECT COUNT(fldName) AS count";
$queryTotal .= " FROM tblActivities";
$queryTotal .= " WHERE fldApproved = ?";
$totalData = array(1);

// SELECT all records
$total = $thisDatabaseReader->select($queryTotal, $totalData, 1, 0, 0, 0, false, false);

// Make sure to set query number as int for security
if (isset($_GET['start']) AND ($_GET['start'] > 0
        AND ($_GET['start'] < $total[0]['count']))) {
    $startRecord = (int) $_GET['start'];
} else {
    $startRecord = 0;
}

print '<div>';
print '<h4>Displaying records ';

print ($startRecord + 1) . ' - ';
if ($startRecord + $numberRecords > $total[0]['count']) {
    print $total[0]['count'];
} else {
    print $startRecord + $numberRecords;
}
print ' of ' . $total[0]['count'] . '</h4>';

print '<ol class="inline-list no-bullet" id="nav-list">';
print '<li';
if ($startRecord - $numberRecords < 0) {
    print ' class="unavailable"';
}
print '><a href="?start=' . ($startRecord - $numberRecords) . '">';
print 'Previous</a></li>';
print '<li';
if ($startRecord + $numberRecords >= $total[0]['count']) {
    print ' class="unavailable"';
}
print '><a href="?start=' . ($startRecord + $numberRecords) . '">';
print 'Next</a></li>';
print '</ol>';
print '</div>';

// Get activity ID
if (isset($_POST['btnUpVote']) OR isset($_POST['btnDownVote'])) {
    if (isset($_POST['btnUpVote'])) {
        $activityID = (int) htmlentities($_POST["hidActivityId"], ENT_QUOTES, "UTF-8");
        $vote = 1;
    } else {
        $activityID = (int) htmlentities($_POST["hidActivityId"], ENT_QUOTES, "UTF-8");
        $vote = -1;
    }

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
                print '<section class="panel success-panel">';
                print "<p>Your vote has been tallied. Thanks for voting!</p>";
                print '</section>';
            }
            
        } else {
            // Check that voter won't exceed min/max
            $newVote = $checkVote[0]['fldVote'] + $vote; // $checkVote should contain one value
            
            // Check that new vote won't exceed 1 or fall below -1
            if ($newVote > 1) { // Vote exceeds max
                print '<section class="panel alert-panel">';
                print "<p>Sorry, you cannot upvote this activity again.</p>";
                print '</section>';
            } else if ($newVote < -1) { // Vote falls below min
                print '<section class="panel alert-panel">';
                print "<p>Sorry, you cannot downvote this activity again.</p>";
                print '</section>';
            } else { // vote is valid
                $updateQuery = " UPDATE tblVotes SET";
                $updateQuery .= " fldVote = ?";
                $updateQuery .= " WHERE fnkActivityId = ? AND";
                $updateQuery .= " fnkNetId = ?";
                $updateData = array($newVote, $activityID, $username);

                $updated = $thisDatabaseWriter->update($updateQuery, $updateData, 1, 1, 0, 0, false, false);
                
                if ($updated) {
                    print '<section class="panel success-panel">';
                    print "<p>Your vote has been changed. Thanks for voting!</p>";
                    print '</section>';
                }
            }
        }
    }
}

// NEED TO ADD LIMIT CLAUSE
$query = "SELECT pmkActivityId, fldName, fldCategory, fldOnCampus, fldTownName, fldState";
$query .= ", fldDistance, fldLocation, fldCost, fldURL, fldDescription, fnkSubmitNetId";
$query .= " FROM tblActivities A";
$query .= " INNER JOIN tblVotes V ON A.pmkActivityId = V.fnkActivityId";
$query .= " INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId";
$query .= " WHERE fldApproved = 1";
$query .= " GROUP BY A.fldName";
$query .= " ORDER BY SUM(fldVote) DESC";
$query .= " LIMIT " . $numberRecords . " OFFSET " . $startRecord;
$data = array(""); // for some reason, variables don't work in array for this
$val = array(1, 1, 0, 0);

// Call select method
$info = $thisDatabaseReader->select($query, $data, $val[0], $val[1], $val[2], $val[3], false, false);

// To troubleshoot returned array
if ($debug) {
    print "<p>DATA: <pre>";
    print_r($info);
    print "</pre></p>";
}

$rank = $startRecord + 1;

// For loop to print records
foreach ($info as $record) {
    print '<div id="dropdown-' . $rank . '" ';
    print 'class="panel dropdown dropdown-processed">';
    
    print '<p class="float-left"><b>' . $rank . "</b></p>";
    
    // ** Add vote buttons **//
    // Add upvote form/button
    print '<form action="' . $phpSelf . '" method="post" ';
    print 'id="frmVote-' . $record['pmkActivityId'] . '" ';
    print 'class="float-left">';
    
    // Add hidden field to hold activity ID
    print '<fieldset class="vote-button">';
    print '<input type="hidden" id="hidActivityId' . $record['pmkActivityId'] . '" ';
    print 'name="hidActivityId" value="' . $record['pmkActivityId'] . '">';
    
    // Add up button
    print '<input type="submit" id="btnUpVote-' . $record['pmkActivityId'] . '" ';
    print 'name="btnUpVote" value="&#x25B2;" ';
    print 'tabindex="100" class="up-vote">';
    
    // Add down button
    print '<input type="submit" id="btnDownVote-' . $record['pmkActivityId'] . '" ';
    print 'name="btnDownVote" value="&#x25BC;" ';
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
    
    print '<a class="dropdown-link" href="#">';
    print $record['fldName'];
    print '</a>';
    
    print '</p>';
    
    print '<div class="dropdown-container" style="display: none;">';
    print '<ol class="no-bullet">';
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
    if ($record['fldCost'] == 0 AND $record['fldCost'] != "") {
        print "<li><b>Cost:</b> FREE";
    } else if($record['fldCost'] != "") {
        print '<li><b>Cost:</b> $' . $record['fldCost'] . '</li>';
        
    }
    if ($record['fldURL'] != '') {
        print '<li><b>URL:</b> <a target="_blank" href="' . $record['fldURL'] . '">Click here</a></li>';
    }
    if ($record['fldDescription'] != "") {
        print '<li><b>Description:</b> ' . $record['fldDescription'] . '</li>';
    }
    
    print '</ol>';
    
    print "<h5>User Photos</h5>";
    
    $photoQuery = "SELECT fldFileName, fldCaption";
    $photoQuery .= " FROM tblPhotos";
    $photoQuery .= " WHERE fnkActivityId = ? AND";
    $photoQuery .= " fldApproved = ?";
    $photoData = array($record['pmkActivityId'], 1);
    
    // Call select method
    $photos = $thisDatabaseReader->select($photoQuery, $photoData, 1, 1, 0, 0, false, false);
    
    if ($photos) {
        $photoCount = 1;
        
        print '<div class="row">';
        
        print '<ul class="no-bullet">';
        
        foreach ($photos as $photo) {
            if ($photoCount % 3 == 0) {
            }
            
            print '<li class="large-4 columns">';
            print '<a class="thumbnail" href="' . $path . 'uploads/';
            print $photo['fldFileName'] . '">';
            print '<img src="' . $path . 'uploads/' . $photo['fldFileName'] . '"';
            print ' alt="' . $photo['fldCaption'] . '">';
            print '</a></li>';
                
            $photoCount++;
           
        }
        
        print '</ul>';
        print '</div>';
    } else {
        print "<p>No pictures have been uploaded for this activity.</p>";
    }
    
    print '<p><a href="submit.php?activity=' . $record['pmkActivityId'] . '">';
    print 'Click here to upload a photo of your own!</a></p>';
    
    print '</div></div>';

    $rank++;
}

if (adminCheck($thisDatabaseReader, $username)) {
    print '<section class="panel">';

    print "<h2>For administrators</h2>";
    print '<p>Click <a href="' . $adminPath . 'approve.php">here</a>';
    print ' to see a list of submitted';
    print ' activities that are awaiting approval.</p>';

    print "</section>";
}

print "</article>";

include "footer.php";
?>