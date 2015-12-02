<?php
include "../top.php";
?>

<article id="main">
    <!--query:- SELECT pmkActivityId, fldName, fldOnCampus, fldTownName, fldState, fldVote, fldDateVoted FROM tblActivities A INNER JOIN tblVotes V ON A.pmkActivityId = V.fnkActivityId INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId WHERE pmkActivityId = 2 
    -->

    <?php
    if (isset($_GET["activity"])) {
        // no need to sanitize in this post, make sure int
        $activityID = (int) $_GET["activity"];
    } else {
        $activityID = "";
    }

    if ($activityID != "") {
        // if the activity Id is greater than zero, print the activity
        // query database to get all the info on this activity
        // Query of the data given the activity ID
        $query = "SELECT pmkActivityId, fldName, fldCategory, fldOnCampus,";
        $query .= " fldTownName, fldState, fldLocation, fldCost, fldURL,";
        $query .= " fldDescription, fnkSubmitNetId, fldDateSubmitted, fldApproved";
        $query .= " FROM tblActivities A";
        $query .= " INNER JOIN tblVotes V ON A.pmkActivityId = V.fnkActivityId";
        $query .= " INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId";
        $query .= " WHERE pmkActivityId = ?";
        $data = array($activityID);

        // Fetch data from database
        //$test = $thisDatabaseReader->testquery($query, $data, 1, 0, 0, 0, false, false);
        $info = $thisDatabaseReader->select($query, $data, 1, 0, 0, 0, false, false);

        if (!$info) {
            print "<p>The activity ID is invalid.</p>";
        }
    }

    if (isset($_POST['btnRemove'])) { // if confirmed
        // get activity ID from hidden field
        $activityID = (int) htmlentities($_POST["hidActivityId"], ENT_QUOTES, "UTF-8");

        // QUERY 1 - deletes form tblActivities
        $query1 = "DELETE FROM tblActivities";
        $query1 .= " WHERE pmkActivityID = ?";

        // QUERY 2 - deletes relational records
        $query2 = "DELETE FROM tblVotes";
        $query2 .= " WHERE fnkActivityID = ?";

        $data = array($activityID);
        //
        //        $info1 = $thisDatabaseWriter->testquery($query1, $data, 1, 0, 0, 0, false, false);
        //        $info2 = $thisDatabaseWriter->testquery($query2, $data, 1, 0, 0, 0, false, false);
        $delete1 = $thisDatabaseWriter->delete($query1, $data, 1, 0, 0, 0, false, false);
        $delete2 = $thisDatabaseWriter->delete($query2, $data, 1, 0, 0, 0, false, false);

        if (!$delete1) {
            print "<p>Unable to delete this activity.</p>";
        }

        if (!$delete2) {
            print "<p>Unable to remove relational records.</p>";
        }

        if ($delete1 AND $delete2) {
            $deleted = true;
        } else {
            $deleted = false;
        }
    }

    if (isset($_POST['btnRemove']) AND $deleted) {
        print "<p>Activity " . $activityID . " and its relational records have been removed.";
    } else if ($activityID == "") { // If activity has not been selected
        print "<p>A valid activity has not been selected. A list of all activities appears below. Please select the item you'd like to remove.</p>";

        $query = "SELECT pmkActivityId, fldName";
        $query .= " FROM tblActivities";
        $query .= " ORDER BY fldDateSubmitted";

        $selectAll = $thisDatabaseReader->select($query, "", 0, 1, 0, 0, false, false);

        print "<section>";
        print '<ul>';

        foreach ($selectAll as $record) {
            $appendURL = "?activity=" . $record['pmkActivityId'];

            print '<li>';
            print '<a href="' . $appendURL . '">';
            print $record['fldName'];
            print '</a>';
            print '</li>';
        }

        print "</section>";
    } else if ($info) { // if valid activity
        print "<h2>Please confirm you want to remove the activity described below.</h2>";
        print '<section id="confirm-remove">';

        // Get fld names
        $fieldKeys = array_keys($info[0]);
        $fields = array_filter($fieldKeys, 'is_string');

        print '<h3>Name: ' . $info[0]['fldName'] . '</h3>';
        print '<ol>';

        foreach ($fields as $field) {
            if ($field != "fldName") {
                print "<li>";

                $camelCase = preg_split('/(?=[A-Z])/', substr($field, 3));

                $fieldName = "";

                foreach ($camelCase as $oneWord) {
                    $fieldName .= $oneWord . " ";
                }

                print '<b>' . $fieldName . ': </b>';

                print $info[0][$field];
                print "</li>";
            }
        }

        print '</ol>';

        print '<p>Removing this record will also remove the following votes.</p>';

        $query = "SELECT fnkNetId, fnkActivityId, fldVote, fldDateVoted";
        $query .= " FROM tblVotes";
        $query .= " WHERE fnkActivityId = ?";
        $query .= " ORDER BY fldDateVoted";
        $data = array($activityID);

        $info2 = $thisDatabaseReader->select($query, $data, 1, 1, 0, 0, false, false);

        print '<table>';

        // Get fld names
        $voteKeys = array_keys($info2[0]);
        $voteFields = array_filter($voteKeys, 'is_string');

        print "<tr>";

        foreach ($voteFields as $field) {
            print "<th>";

            $camelCase = preg_split('/(?=[A-Z])/', substr($field, 3));

            $fieldName = "";

            foreach ($camelCase as $oneWord) {
                $fieldName .= $oneWord . " ";
            }

            print $fieldName;
            print '</th>';
        }
        
        foreach ($info2 as $record) {
            print "<tr>";
            foreach ($voteFields as $field) {
                print '<td>';
                print $record[$field];
                print '</td>';
            }
            print "</tr>";
        }

        print '</table>';

        print '</section>';
        ?>

        <form action="<?php print $phpSelf; ?>" method="post"
              id="frmRemove">
            <fieldset class="wrapper">        
                <input type="hidden" id="hidActivityId" name="hidActivityId"
                       value="<?php print $activityID ?>"> 
                <input type="submit" id="btnRemove" name="btnRemove"
                       value="Remove" tabindex="100" class="button">
            </fieldset>
        </form>
        <?php
    }
    ?>
</article>

<?php include "../footer.php"; ?>
