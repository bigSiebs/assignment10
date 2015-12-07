<?php

include '../top.php';

print "<article>";

if (!adminCheck($thisDatabaseReader, $username)) {
    print "<h2>Sorry.</h2>";
    print "<p>You don't have access to this page.</p>";

// Must be admin to see below here    
} else {


    print "<h2>Manage User Photos</h2>";

    if (isset($_GET['activity'])) {
        $activityID = (int) ($_GET['activity']);
    } else {
        $activityID = -1; // to make query fail
    }

    // Check to see if there are images for this activity
    $checkQuery = "SELECT pmkPhotoId ";
    $checkQuery .= "FROM tblPhotos ";
    $checkQuery .= " WHERE fnkActivityId = ?";
    $checkData = array($activityID);

    $check = $thisDatabaseReader->select($checkQuery, $checkData, 1, 0, 0, 0, false, false);

    // If activity ID does not have any images associated with it
    if (!$check) {
        print "<p>You have not selected an activity that has images associated with it.</p>";

        print "<p>The following submitted photos need to be approved:</p>";

        print "<section>";
        $pendingQuery = "SELECT pmkPhotoId, pmkActivityId, fldName, fldCaption, fldFileName, fnkSubmitNetId";
        $pendingQuery .= " FROM tblPhotos";
        $pendingQuery .= " INNER JOIN tblActivities ON fnkActivityId = pmkActivityId";
        $pendingQuery .= " WHERE tblPhotos.fldApproved = ?";
        $pendingData = array(0);

        $pending = $thisDatabaseReader->select($pendingQuery, $pendingData, 1, 0, 0, 0, false, false);

        print '<div class="panel">';
        
        if (!$pending) {
            print "<p>No photos are pending approval.";
        } else {

            // Start printing table
            print '<table>';
            
            print '<tr>';
            
            $fields = array_keys($pending[0]);
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
            // add header columns for approve button
            print "<th>Approve</th>";
            print "</tr>";
            
            // for loop to print each record in search query
            foreach ($pending as $record) {
            $appendURL = '?activity=' . $record['pmkActivityId'];
            $appendURL .= '&photo=' . $record['pmkPhotoId'];
            $appendURL .= '&action=approve';
 
            // print the row
            print '<tr>';
            // Uses field names (AKA headers) as keys to pick from arrays
            foreach ($headers as $field) {
                if ($field == "fldFileName") {
                    print '<td><a href="' .$path . 'uploads/' . $record[$field] . '">'
                            . $record[$field] . '<a></td>';
                } else {
                    print '<td>' . $record[$field] . '</td>';
                }
            }
            // after printing out all fields, now links to approve
            print '<td><a href="' . $appendURL . '">Approve</a></td>';
            print '</tr>';
            
            
        } // closes foreach ($unapproved as $record) loop
            print "</table>";
        }
        
        print '</section>';
        
        print "<p>Users have submitted images for the following activities. Click any ";
        print "activity name to see them.</p>";

        // Query to get activities with images
        $listQuery = "SELECT DISTINCT pmkActivityId, fldName ";
        $listQuery .= "FROM tblActivities ";
        $listQuery .= "INNER JOIN tblPhotos ON pmkActivityId = fnkActivityId ";
        $listQuery .= "ORDER BY fldDateSubmitted";
        $listData = array();

        $activities = $thisDatabaseReader->select($listQuery, $listData, 0, 1, 0, 0, false, false);

        print "<ul>";

        foreach ($activities as $record) {
            // build URL for link
            $appendURL = "?activity=" . $record['pmkActivityId'];

            print "<li>";
            print '<a href="' . $appendURL . '">';
            print $record['fldName'];
            print '</a>';
            print '</li>';
        }

        print "</ul>";
    } else { // Activity ID has been entered and is valid
        // Display any approve, unapprove or remove information first
        if (isset($_GET['photo']) AND isset($_GET['action'])) {
            $photoID = (int) ($_GET['photo']);

            if (($_GET['action']) == "approve" OR ( $_GET['action']) == "unapprove") {
                if (($_GET['action']) == "approve") {
                    $approved = 1;
                } else {
                    $approved = 0;
                }

                $updateQuery = "UPDATE tblPhotos SET";
                $updateQuery .= " fldApproved = ?";
                $updateQuery .= " WHERE pmkPhotoId = ?";
                $updateData = array($approved, $photoID);

                $updated = $thisDatabaseWriter->update($updateQuery, $updateData, 1, 0, 0, 0, false, false);

                print '<div class="panel ';

                if ($updated) {
                    print ' success-panel">';

                    print '<p>Photo ' . $photoID . ' has been ';
                    print ($approved) ? 'approved' : 'unapproved';
                    print '.</p>';
                } else {
                    print ' alert-panel">';
                    print "<p>Unable to update this record.</p>";
                }

                print '</div>';
            } else if (($_GET['action']) == "remove") {
                print "<p>Test remove</p>";
            }
        }


        $photoQuery = "SELECT pmkPhotoId, fnkNetId, fldCaption, ";
        $photoQuery .= "fldFileName, fldApproved ";
        $photoQuery .= "FROM tblPhotos ";
        $photoQuery .= "WHERE fnkActivityId = ?";
        $photoData = array($activityID);

        // Call select method
        $photos = $thisDatabaseReader->select($photoQuery, $photoData, 1, 0, 0, 0, false, false);

        print "<table>";

        // Get headings from first subarray (removes indexes with filter function)
        $fields = array_keys($photos[0]);
        $headers = array_filter($fields, 'is_string'); // Picks up only str values

        print "<tr>";

        // Print headings
        foreach ($headers as $head) {
            $camelCase = preg_split('/(?=[A-Z])/', substr($head, 3));

            $heading = "";

            foreach ($camelCase as $oneWord) {
                $heading .= $oneWord . " ";
            }

            print '<th>' . $heading . '</th>';
        }

        print '<th>Remove</th>';

        print "</tr>";

        foreach ($photos as $photo) {
            $action = ($photo['fldApproved']) ? "unapprove" : "approve";
            $appendURL = '?activity=' . $activityID;
            $appendURL .= '&photo=' . $photo['pmkPhotoId'];
            $appendURL .= '&action=';

            $approvalLink = $appendURL . $action;
            $removalLink = $appendURL . 'remove';

            print '<tr>';

            foreach ($headers as $head) {
                print '<td>';
                if ($head == 'fldApproved') {
                    print '<a href="' . $approvalLink . '">';

                    if ($action == "approve") {
                        print "Approve";
                    } else {
                        print "Unapprove";
                    }

                    print "</a>";
                } else {
                    print $photo[$head];
                }

                print '</td>';
            }

            print "<td>";
            print '<a href="' . $removalLink . '">';
            print 'Delete';
            print '</a>';
            print '</td>';

            print '</tr>';
        }

        print "</table>";
    }
}

include "../footer.php";