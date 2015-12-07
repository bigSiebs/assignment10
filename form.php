<?php
include "top.php";

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1 Initialize variables
//
// SECTION: 1a.
// variables for the classroom purposes to help find errors.
$debug = false;
if (isset($_GET["debug"])) { // ONLY do this in a classroom environment
    $debug = true;
}
if ($debug)
    print "<p>DEBUG MODE IS ON</p>";

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1b Security
//
// define security variable to be used in SECTION 2a.
$yourURL = $domain . $phpSelf;

//%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION: 1c form variables
//
// Initialize variables one for each form element
// in the order they appear on the form
// If activity is included in URL, then the admin wants to edit a record
// We need to get the record's info from the database

$activityID = -1;

$user = $username;
$affiliation = "Student";

$activityName = "";
$category = "Select one";
$onCampus = false; // not checked

$town = "Burlington";
$state = "VT";
$distance = 0;

$location = "";
$cost = "";
$url = "";
$comments = "";

$approved = false; // not checked

if (isset($_GET['activity']) AND adminCheck($thisDatabaseReader, $username)) { // ADMINS ONLY
    $activityID = (int) $_GET['activity'];

    // Build query to get info from database
    $query = "SELECT fldName, fldCategory, fldOnCampus, fnkSubmitNetId,";
    $query .= " fldTownName, fldState, fldDistance,";
    $query .= " fldLocation, fldCost, fldURL, fldDescription,";
    $query .= " fldApproved, fldAffiliation";
    $query .= " FROM tblActivities";
    $query .= " INNER JOIN tblTowns ON pmkTownId = fnkTownId";
    $query .= " INNER JOIN tblAffiliates ON pmkNetId = fnkSubmitNetId";
    $query .= " WHERE pmkActivityId = ?";
    $data = array($activityID);

    // Fetch data from database
    $info = $thisDatabaseReader->select($query, $data, 1, 0, 0, 0, false, false);

    // If array is not empty (ie, activity ID was valid)
    if ($info) {
        $edit = true; // use UPDATE query instead of INSERT for activity
        // All info will be in first array of array
        $user = $info[0]['fnkSubmitNetId'];
        $affiliation = $info[0]['fldAffiliation'];

        $activityName = $info[0]['fldName'];
        $category = $info[0]['fldCategory'];
        $onCampus = $info[0]['fldOnCampus']; // int cast to boolean

        $town = $info[0]['fldTownName'];
        $state = $info[0]['fldState'];
        $distance = $info[0]['fldDistance'];

        $location = $info[0]['fldLocation'];
        $cost = $info[0]['fldCost'];
        $url = $info[0]['fldURL'];
        $comments = $info[0]['fldDescription'];

        $approved = $info[0]['fldApproved']; // int cast to boolean
    }
}

// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 1d: Form error flags: Initalize ERROR flags, one for each form element
// we validate, in the order they appear in SECTION 1c

$userError = false;
$activityNameError = false;
$categoryError = false;
$townError = false;
$distanceError = false;
$locationError = false;
$costError = false;
$urlError = false;
$commentsError = false;

// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 1e: Misc. variables
// Array to hold error messages
$errorMsg = array();

// Array to hold form values to be inserted into mySQL database
$townData = array();
$activityData = array();
// Although usersData is used later, it's not added here because an insert/update
// in that table requires that the data be in different orders

$mailed = false; // Not mailed yet
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 2: Process for when the form is submitted

if (isset($_POST['btnSubmit'])) {
    // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    //
    // SECTION 2a: Security

    if (!securityCheck($path_parts, $yourURL, true)) {
        $msg = '<p>Sorry, you cannot access this page. ';
        $msg.= 'Security breach detected and reported.';
        die($msg);
    }

    // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    //
    // SECTION 2b: Sanitize data
    // Remove any potential JS or HTML code from users input on the form.
    // Follow same order as declared in SECTION 1c.
    // Already sanitized when initalized, add direct to data array

    $activityID = (int) htmlentities($_POST["hidActivityId"], ENT_QUOTES, "UTF-8");
    if ($activityID > 0) {
        $update = true;
    } else {
        $update = false;
    }

    $user = htmlentities($_POST['txtUsername'], ENT_QUOTES, "UTF-8");
    $activityData[] = $user;

    $affiliation = htmlentities($_POST['radAffiliation'], ENT_QUOTES, "UTF-8");

    $activityName = htmlentities($_POST['txtActivityName'], ENT_QUOTES, "UTF-8");
    $activityData[] = $activityName;

    $category = $_POST['lstCategory'];
    $activityData[] = $category;

    // Saved as 0/1 for database
    if (isset($_POST["chkOnCampus"])) {
        $onCampus = 1;
    } else {
        $onCampus = 0;
    }
    $activityData[] = $onCampus;

    $town = htmlentities($_POST['txtTown'], ENT_QUOTES, "UTF-8");
    $townData[] = $town;

    $state = $_POST['lstState'];
    $townData[] = $state;

    $distance = htmlentities($_POST['txtDistance'], ENT_QUOTES, "UTF-8");
    $townData[] = $distance;

    // For all optional data, add it to array only if it's not empty
    $location = htmlentities($_POST['txtLocation'], ENT_QUOTES, "UTF-8");
    if ($location != "" OR $update) {
        $activityData[] = $location;
    }

    $cost = htmlentities($_POST['txtCost'], ENT_QUOTES, "UTF-8");
    if ($cost != "" OR $update) {
        $activityData[] = $cost;
    }

    $url = htmlentities($_POST['txtURL'], ENT_QUOTES, "UTF-8");
    if ($url != "" OR $update) {
        $activityData[] = $url;
    }

    $comments = htmlentities($_POST['txtComments'], ENT_QUOTES, "UTF-8");
    if ($comments != "" OR $update) {
        $activityData[] = $comments;
    }

    // Saved as 0/1 for database
    if (isset($_POST["chkApproved"])) {
        $approved = 1;
        $activityData[] = $approved;
    } else {
        $approved = 0;
        $activityData[] = $approved;
    }

    // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    //
    // SECTION 2c: Validation: Check each value for possible errors or empty.

    if ($user == "") {
        $errorMsg[] = "Please enter your NetID.";
        $userError = true;
    } elseif (!verifyAlphaNum($user)) {
        $errorMsg[] = "Your NetID appears to include invalid characters.";
        $userError = true;
    }

    if ($activityName == "") {
        $errorMsg[] = "Please enter the activity name.";
        $activityNameError = true;
    } elseif (!verifyAlphaNum($activityName)) {
        $errorMsg[] = "The name you've provided for the activity contains invalid characters.";
        $activityNameError = true;
    }

    if ($category == "Select one") {
        $errorMsg[] = "Please select a category to describe the activity.";
        $categoryError = true;
    }

    if ($town == "") {
        $errorMsg[] = "Please enter the town name.";
        $townError = true;
    } elseif (!verifyAlphaNum($town)) {
        $errorMsg[] = "The town name appears to include invalid characters.";
        $townError = true;
    }

    if ($distance == "") {
        $errorMsg[] = "Please enter the town's distance from UVM.";
        $distanceError = true;
    } elseif (!verifyNumeric($distance)) {
        $errorMsg[] = "The value entered for the distance must be strictly numeric.";
        $distanceError = true;
    }

    // Location field can be blank
    if ($location != "" AND ! verifyAlphaNum($location)) {
        $errorMsg[] = "The location info appears to contain invalid characters.";
        $locationError = true;
    }

    // cost field can be blank
    if ($cost != "" AND ! verifyNumeric($cost)) {
        $errorMsg[] = "The cost must be a number.";
        $costError = true;
    }

    // URL field can be blank
    if ($url != "" AND ! filter_var($url, FILTER_VALIDATE_URL)) {
        $errorMsg[] = "The URL you've provided is invalid.";
        $urlError = true;
    }

    // Description field can be blank
    if ($comments != "" AND ! verifyAlphaNum($comments)) {
        $errorMsg[] = "Your comments contain invalid characters.";
        $commentsError = true;
    }

    // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    //
    // SECTION 2d: Process form - passed validation (errorMsg is empty)

    if (!$errorMsg) {
        if ($debug) {
            print "<p>Form is valid.</p>";
        }

        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 2e: Save data: Insert data into database
        // Check if town is already in towns table
        $townSelectQuery = "SELECT pmkTownId";
        $townSelectQuery .= " FROM tblTowns";
        $townSelectQuery .= " WHERE fldTownName = ? AND";
        $townSelectQuery .= " fldState = ?";
        $townSelectData = array($town, $state);

        $townSelect = $thisDatabaseReader->select($townSelectQuery, $townSelectData, 1, 1, 0, 0, false, false);

        if ($townSelect) { // If not empty, grab ID
            $townID = $townSelect[0]['pmkTownId'];
        } else { // If empty, add town
            $townInsertQuery = "INSERT INTO tblTowns SET";
            $townInsertQuery .= " fldTownName = ?,";
            $townInsertQuery .= " fldState = ?,";
            $townInsertQuery .= " fldDistance = ?";

            $townInsert = $thisDatabaseWriter->insert($townInsertQuery, $townData, 0, 0, 0, 0, false, false);

            if ($townInsert) {
                $townID = $thisDatabaseWriter->lastInsert(); // for activity insert/update
            }
        }

        // Add pmkTownId for town to array
        $activityData[] = $townID;

        // Get first line of query
        if ($update) {
            $query = "UPDATE tblActivities SET";
        } else {
            $query = "INSERT INTO tblActivities SET";
        }

        // Add required fields to query
        $query .= " fnkSubmitNetId = ?,";
        $query .= " fldName = ?,";
        $query .= " fldCategory = ?,";
        $query .= " fldOnCampus = ?";

        // Add optional fields, if user submitted data
        if ($location != "" OR $update) {
            $query .= ", fldLocation = ?";
        }

        if ($cost != "" OR $update) {
            $query .= ", fldCost = ?";
        }

        if ($url != "" OR $update) {
            $query .= ", fldURL =?";
        }

        if ($comments != "" OR $update) {
            $query .= ", fldDescription = ?";
        }

        // Add approval and town ID
        $query .= ", fldApproved = ?";
        $query .= ", fnkTownId = ?";

        // For updates
        if ($update) { // IMPORTANT: do not forget to add this to UPDATE queries
            $query .= " WHERE pmkActivityId = ?";
            $activityData[] = $activityID;

            $activity = $thisDatabaseWriter->update($query, $activityData, 1, 0, 0, 0, false, false);

            // For inserts
        } else {
            $activity = $thisDatabaseWriter->insert($query, $activityData, 0, 0, 0, 0, false, false);

            // Need to create vote so that record can be ordered
            $lastActivityID = $thisDatabaseWriter->lastInsert();

            // By default, vote score = 0
            $voteInsertQuery = "INSERT INTO tblVotes SET";
            $voteInsertQuery .= " fnkNetId = ?,";
            $voteInsertQuery .= " fnkActivityId = ?";
            $voteInsertData = array($user, $lastActivityID);

            $vote = $thisDatabaseWriter->insert($voteInsertQuery, $voteInsertData, 0, 0, 0, 0, false, false);
        }

        // No matter what, check if user is in table already
        $userSelectQuery = "SELECT pmkNetId, fldAffiliation";
        $userSelectQuery .= " FROM tblAffiliates";
        $userSelectQuery .= " WHERE pmkNetId = ?";
        $userSelectData = array($user);

        $userSelect = $thisDatabaseReader->select($userSelectQuery, $userSelectData, 1, 0, 0, 0, false, false);

        if (!$userSelect) { // if user select query is empty, insert user
            $userInsertQuery = "INSERT INTO tblAffiliates SET";
            $userInsertQuery .= " pmkNetId = ?";
            $userInsertQuery .= ", fldAffiliation = ?";
            $userData = array($user, $affiliation);

            $userInsert = $thisDatabaseWriter->insert($userInsertQuery, $userData, 0, 0, 0, 0, false, false);

            // if user exists and affiliation doesn't match, update it
        } else if ($userSelect[0]["fldAffiliation"] != $affiliation) {
            $userUpdateQuery = "UPDATE tblAffiliates SET";
            $userUpdateQuery .= " fldAffiliation = ?";
            $userUpdateQuery .= " WHERE pmkNetId = ?";
            $userData = array($affiliation, $user);

            $userUpdate = $thisDatabaseWriter->update($userUpdateQuery, $userData, 1, 0, 0, 0, false, false);
        }

        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 2f: Create messages

        $message = "<h2>Thank you! Your activity has been submitted";
        if (!$approved) {
            $message .= " for approval";
        }
        $message .= ".</h2>";

        if (!$approved) {
            $message .= "<p>An administrator will review the submission to ensure";
            $message .= " that it does not violate our guidelines.";
            $message .= " You should receive a follow-up email if the information";
            $message .= " is inappropriate, incomplete, etc.</p>";
        }

        $message.= "<p>A copy of the submitted information appears below.</p>";

        foreach ($_POST as $key => $value) {
            if ($key != 'hidActivityId' AND
                    $key != 'btnSubmit' AND
                    $key != 'chkApproved') {
                $message.= "<p>";
                $camelCase = preg_split('/(?=[A-Z])/', substr($key, 3));

                foreach ($camelCase as $one) {
                    $message.= $one . ' ';
                }
                $message = trim($message);

                $message.= ": " . htmlentities($value, ENT_QUOTES, "UTF-8") . "</p>";
            }
        }

        $message .= "<br><p>Thanks again!</p>";
        $message .= "<br><p>The UVM diddit admin team</p>";
        
        $messageAdmin = "<h2>A new activity has been submitted to UVM diddit</h2>";
        $messageAdmin .= "<p>" . $user . " submitted " . $activityName . ".</p>";
        $messageAdmin .= "<p>Head over to the ";
        $messageAdmin .= '<a href="https://jsiebert.w3.uvm.edu/cs148develop/assignment10/admin/approve.php">approve</a> page to confirm this submission.</p>';

        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 2gA: Mail to user

        $email = $user . "@uvm.edu";

        $to = $email; // the person who filled out form
        $cc = ""; // admins
        $bcc = "";
        $from = "UVM Activities <jsiebert@uvm.edu>";

        // subject of mail should match form
        $subject = "Thanks for contributing to UVM diddit!";

        // SECTION 2gB: Mail to admin
        $toAdmin = "jsiebert@uvm.edu"; // joe
        $ccAdmin = "aychu@uvm.edu"; // alan
        $bccAdmin = "";
        $fromAdmin = "UVM Activities <jsiebert@uvm.edu>";

        // subject of mail should match form
        $subjectAdmin = "New diddit submission from " . $user;
        
        if (!$update) {
            $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);
            $mailedAdmin = sendMail($toAdmin, $ccAdmin, $bccAdmin,
                    $fromAdmin, $subjectAdmin, $messageAdmin);
        }
    }// ends form is valid
} // ends if form was submitted
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 3: Display form
//
?>

<article>

    <?php
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 3a
// If its the first time coming to form or there are errors, display form.
    if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) {

        if ($update) {
            print '<section class="panel success-panel">';
            print '<p>The record has been updated.';
            print '</section>';
        } else {
            print "<h2>Your request has ";

            if (!$mailed) {
                print 'not ';
            }

            print "been processed.</h2>";

            if ($mailed) {
                print '<section class="panel success-panel">';
                print "<p>A copy of this message has been sent to: " . $email . ".</p>";
                print $message;
                print "</section>";
            }
        }
    } else {


        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 3b: Error messages: Display any error message before we print form

        if ($errorMsg) {
            print '<div class="panel alert-panel">';
            print '<h4>You need to address the following issues:</h4>';
            print "<ol>\n";
            foreach ($errorMsg as $err) {
                print "\t<li>" . $err . "</li>\n";
            }
            print "</ol>\n";
            print "</div>";
        }

        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 3c: HTML form: Display HTML form
        // Action is to this same page. $phpSelf is defined in top.php
        /* Note lines like: value="<?php print $email; ?>
         * These make the form sticky by displaying the default value or
         * the value that was typed in previously.
         * Also note lines like <?php if ($emailERROR) print 'class="mistake"'; ?>
         * These allow us to use CSS to identify errors with style. */
        ?>

        <h2>Add an Activity to the List!</h2>
        <form action="<?php print $phpSelf; ?>"
              method="post" class="panel"
              id="frmAddActivity">

            <fieldset class="wrapper">
                <legend></legend>
                <p>Please provide the following information about the activity.</p>

                <fieldset class="basic-info">
                    <legend>Basic Information</legend>

                    <input type="hidden" id="hidActivityId" name="hidActivityId"
                           value="<?php print $activityID; ?>"
                           >

                    <div class="row">
                        <div class="large-12 columns">
                            <label for="txtUsername" class="required">NetID
                                <input type="text" id="txtUsername" name="txtUsername"
                                       value="<?php print $user; ?>"
                                       tabindex="100" maxlength="45"
                                       <?php
                                       if (!adminCheck($thisDatabaseReader, $username))
                                            print 'readonly';
                                       if ($userError)
                                           print 'class="mistake"'; ?>
                                       onfocus="this.select()">
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-12 columns">
                            <label>Which of the following describes your relation to UVM?</label>
                            <?php
                            // Array for unique values in each block
                            $radioButtons = array(
                                array("radAffiliationStudent", "Student"),
                                array("radAffiliationAlumni", "Alumni"),
                                array("radAffiliationFacultyStaff", "Faculty/Staff"),
                                array("radAffiliationOther", "Other")
                            );

                            // Variable for tabIndex
                            $tabIndex = 150;

                            foreach ($radioButtons as $button) {
                                print "\n\t\t" . '<input type="radio"';
                                print "\n\t\t\t" . 'id="' . $button[0] . '"';
                                print "\n\t\t\t" . 'name="radAffiliation"';
                                print "\n\t\t\t" . 'value="' . $button[1] . '"';
                                if ($affiliation == $button[1]) {
                                    print "\n\t\t\tchecked";
                                }
                                print "\n\t\t\t" . 'tabindex="';
                                print strval($tabIndex) . '">';
                                print '<label for="' . $button[0] . '">';
                                print $button[1] . "</label>";

                                $tabIndex+= 10;
                            }
                            ?>
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-12 columns">
                            <label for="txtActivityName" class="required">Activity Name
                                <input type="text" id="txtActivityName" name="txtActivityName"
                                       value="<?php print $activityName; ?>"
                                       tabindex="110" maxlength="255"
                                       <?php if ($activityNameError) 
                                           print 'class="mistake"'; ?>
                                       onfocus="this.select()">
                            </label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-8 columns">
                            <label for="lstCategory">Category</label>
                            <select id="lstCategory" name="lstCategory"
                            <?php if ($categoryError) print 'class="mistake"'; ?>
                                    tabIndex="200">
                                        <?php
                                        // Array for listbox options
                                        $categoryChoices = array(
                                            "Select one",
                                            "Arts",
                                            "Entertainment",
                                            "Live",
                                            "Outdoor",
                                            "School-Related",
                                            "Social",
                                            "Sports",
                                            "Winter",
                                            "Other");

                                        foreach ($categoryChoices as $choice) {
                                            print "\n\t\t\t" . "<option ";
                                            if ($category == $choice) {
                                                print 'selected ';
                                            }
                                            print 'value="' . $choice . '">' . $choice . "</option>";
                                            print "\n";
                                        }
                                        ?>
                            </select>
                        </div>

                        <div class="large-4 columns">
                            <label><input type="checkbox"
                                          id="chkOnCampus"
                                          name="chkOnCampus"
                                          value="On Campus"
                                          <?php if ($onCampus) print " checked "; ?>
                                          tabindex="300">Is this activity on campus?</label>
                        </div>
                    </div>

                    <div class="row">
                        <div class="large-4 columns">
                            <label for="txtTown" class="required">Town
                                <input type="text" id="txtTown" name="txtTown"
                                       value="<?php print $town; ?>"
                                       tabindex="400" maxlength="255"
                                       <?php if ($townError) print 'class="mistake"'; ?>
                                       onfocus="this.select()">
                            </label>
                        </div>

                        <div class="large-2 columns">
                            <label for="lstState">State</label>
                            <select id="lstState" name="lstState" tabIndex="410">
                                <?php
                                // Array for listbox options
                                $stateChoices = array("MA", "NH", "NY", "QC", "VT");

                                foreach ($stateChoices as $choice) {
                                    print "\n\t\t\t" . "<option ";
                                    if ($state == $choice) {
                                        print 'selected ';
                                    }
                                    print 'value="' . $choice . '">' . $choice . "</option>";
                                    print "\n";
                                }
                                ?>
                            </select>
                        </div>
                        <div class="large-6 columns">
                            <label for="txtDistance" class="required">Distance from Burlington (in miles)
                                <input type="text" id="txtDistance" name="txtDistance"
                                       value="<?php print $distance; ?>"
                                       tabindex="420" maxlength="255"
                                       <?php if ($distanceError) print 'class="mistake"'; ?>
                                       onfocus="this.select()">
                            </label>
                        </div>
                    </div>
                </fieldset> <!-- end basic-info -->

                <fieldset class="optional-info">
                    <legend>Optional Information</legend>
                    <div class="row">
                        <div class="large-12 columns">
                            <label for="txtLocation" class="required">Detailed Location Description
                                <input type="text" id="txtLocation" name="txtLocation"
                                       value="<?php print $location; ?>"
                                       tabindex="500" maxlength="255"
                                       <?php if ($locationError) print 'class="mistake"'; ?>
                                       onfocus="this.select()">
                            </label>
                        </div>
                        <div class="large-12 columns">
                            <label for="txtCost" class="required">Cost to Participate (rounded to nearest dollar; if free, please enter 0)
                                <input type="text" id="txtCost" name="txtCost"
                                       value="<?php print $cost; ?>"
                                       tabindex="510" maxlength="255"
                                       <?php if ($costError) print 'class="mistake"'; ?>
                                       onfocus="this.select()">
                            </label>

                            <label for="txtURL" class="required">URL
                                <input type="text" id="txtURL" name="txtURL"
                                       value="<?php print $url; ?>"
                                       tabindex="520" maxlength="255"
                                       <?php if ($urlError) print 'class="mistake"'; ?>
                                       onfocus="this.select()">
                            </label>
                        </div>
                        <div class="large-12 columns">
                            <label for="txtComments" class="required">Description of Activity</label>
                            <textarea id="txtComments"
                                      name="txtComments"
                                      tabindex="600"
                                      <?php if ($commentsError) print 'class="mistake"'; ?>
                                      onfocus="this.select()"><?php print $comments; ?></textarea>
                        </div>
                    </div>
                </fieldset> <!-- end optional-info -->

                <?php
                // If user is admin, print preapprove checkbox
                if (adminCheck($thisDatabaseReader, $username)) {
                    print '<fieldset class="admin-only">';
                    print '<legend>Administrative</legend>';
                    print '<div class="row">';
                    print '<div class="large-12 columns">';
                    print '<label><input type="checkbox" id="chkApproved"';
                    print ' name="chkApproved" value="Approved"';
                    if ($approved)
                        print " checked";
                    print ' tabindex="700">Approve activity</label>';
                    print "</div></div>";
                    print '</fieldset>';
                }
                ?>

                <fieldset class="buttons">
                    <legend></legend>
                    <div class="row">
                        <div class="large-12 columns">
                            <input type="submit" id="btnSubmit" name="btnSubmit" value="Submit" tabindex="900" class="button">
                        </div>
                    </div>
                </fieldset> <!-- ends buttons -->

            </fieldset> <!-- end wrapper! -->
        </form> <!-- end form! -->

        <?php
    } // end body submit
    ?>

</article>

<?php
include 'footer.php';
?>
