<?php
include "top.php";  
?>



<article id="main">
    <h2> Please confirm that you want to remove the below entry: </h2>    
    <!--query:- SELECT pmkActivityId, fldName, fldOnCampus, fldTownName, fldState, fldVote, fldDateVoted FROM tblActivities A INNER JOIN tblVotes V ON A.pmkActivityId = V.fnkActivityId INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId WHERE pmkActivityId = 2 
    -->

    <?php
    //make the activityID a global variable
    $activityID = -1;
// check to see if the remove button is set
// DELETE from tblActivities WHERE pmkActivityID = 4
    if (isset($_POST['btnRemove'])) {
        // run the query to delete  the record from tblActivities
        $activityID = (int) $_POST["hidActivityId"];
        // write the query to delete everything!
        $query = "DELETE from tblActivities";
        $query .= " WHERE pmkActivityID = ?";       
        $data = array($activityID);
        $info = $thisDatabaseReader->testquery($query, $data, 1, 0, 0, 0, false, false);
//        print "<p>".$activityId."</p>";
        // run the query to delete  the record from tblVotes
    }
    if (isset($_GET["hidActivityId"])) {
        //debug coding
//        print "<p> testing </p>";
//        print "<p>" . $_POST["hidActivityId"] . "</p>";
        // no need to sanitize in this post, make sure int
        $activityID = (int) $_GET["hidActivityId"];
    }
    // if the activity Id is greater than zero, print the activity
    $info = "";
    if ($activityID > 0) {
        // query database to get all the info on this activity
        // Query of the data given the activity ID
    $query = "SELECT pmkActivityId, fldName, fldOnCampus, fldTownName, fldState";
    $query .= " FROM tblActivities A";
    $query .= " INNER JOIN tblVotes V ON A.pmkActivityId = V.fnkActivityId";
    $query .= " INNER JOIN tblTowns T ON A.fnkTownId = T.pmkTownId";
    $query .= " WHERE pmkActivityId = ?";
    $data = array($activityID);
    
    // Fetch data from database
//    $test = $thisDatabaseReader->testquery($query, $data, 1, 0, 0, 0, false, false);
    $info = $thisDatabaseReader->select($query, $data, 1, 0, 0, 0, false, false);
    //Debugging 
//        print "<pre>";
//        print_r($info);
    }
    ?>
    <!--Make a table of the data-->
    <!-- Display table only if the remove button is not pressed -->
    <?php if(!isset($_POST['btnRemove'])): ?>
    <table id="confirmRemove">
        <tr>
            <td> Activity ID </td>
            <td> Activity Name </td>
            <td> Location - City </td>
            <td> Location - State </td>
        </tr>
        <tr>
            <td> <?php print $info[0]["pmkActivityId"]?> </td>
            <td> <?php print $info[0]["fldName"]?> </td>
            <td> <?php print $info[0]["fldTownName"]?> </td>
            <td> <?php print $info[0]["fldState"]?> </td>
        </tr>    
    </table>
    <?php endif; ?>
    
    <?php
    ?>
    <!--Make a form to confirm that you'll delete the record
        Only Display it if the btnRemove has not been pressed!-->
        <?php if(!isset($_POST['btnRemove'])): ?>
            <form action="<?php print $phpSelf; ?>"
              method="post"
              id="confirmRemoveForm">
                <legend>Confirm that you want to delete the entry</legend>
                <fieldset class="wrapper">
                <input type="submit" id="btnRemove" name="btnRemove" value="Remove" tabindex="900" class="button">
                <input type="hidden" id="hidActivityId" name="hidActivityId" value="<?php print $activityID ?>"> 
                </fieldset>
            </form>
        <?php endif; ?>
</article>

<?php include "footer.php"; ?>
