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
        $query = "SELECT pmkActivityId, fldName, fldOnCampus, fldTownName, fldState";
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
        print "<p>A valid activity has not been selected.</p>";
    
        
    } else if ($info) { // if valid activity
        ?>
        <h2> Please confirm that you want to remove the below entry:</h2>    
        <table id="confirmRemove">
            <tr>
                <td> Activity ID </td>
                <td> Activity Name </td>
                <td> Location - City </td>
                <td> Location - State </td>
            </tr>
            <tr>
                <td> <?php print $info[0]["pmkActivityId"] ?> </td>
                <td> <?php print $info[0]["fldName"] ?> </td>
                <td> <?php print $info[0]["fldTownName"] ?> </td>
                <td> <?php print $info[0]["fldState"] ?> </td>
            </tr>    
        </table>

        <form action="<?php print $phpSelf; ?>" method="post"
              id="frmRemove">
            <legend>Please confirm that you would like to remove this activity.</legend>
            <fieldset class="wrapper">        
                <input type="hidden" id="hidActivityId" name="hidActivityId"
                       value="<?php print $activityID ?>"> 
                <input type="submit" id="btnRemove" name="btnRemove"
                       value="Confirm" tabindex="100" class="button">
            </fieldset>
        </form>
        <?php
    }
    ?>
</article>

<?php include "../footer.php"; ?>
