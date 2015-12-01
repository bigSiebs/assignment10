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

$approved = false;

if (isset($_GET['activity']) AND adminCheck($thisDatabaseReader, $username)) { // ADMINS ONLY
    $activityID = (int) $_GET['activity'];

    // Build query to get info from database
    $query = "SELECT fldName, fldCategory, fldOnCampus, fnkSubmitNetId,";
    $query .= " fldTownName, fldState, fldDistance,";
    $query .= " fldLocation, fldCost, fldURL, fldDescription";
    $query .= " fldApproved";
    $query .= " FROM tblActivities";
    $query .= " INNER JOIN tblTowns ON pmkTownId = fnkTownId";
    $query .= " WHERE pmkActivityId = ?";
    $data = array($activityID);
    
    // Fetch data from database
    $info = $thisDatabaseReader->select($query, $data, 1, 0, 0, 0, false, false);

    // If array is not empty (ie, activity ID was valid)
    if ($info) {
        $edit = true; // use UPDATE query instead of INSERT
        
        // All info will be in first array of array
        
        $user = $info[0]['fnkSubmitNetId'];

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
        
        $approved = $info[0]['fldApproved'];
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
    
    $location = htmlentities($_POST['txtLocation'], ENT_QUOTES, "UTF-8");
    if ($location != "") {
        $activityData[] = $location;
    }
    
    $cost = htmlentities($_POST['txtCost'], ENT_QUOTES, "UTF-8");
    if ($cost != "") {
        $activityData[] = $cost;
    }
    
    $url = htmlentities($_POST['txtURL'], ENT_QUOTES, "UTF-8");
    if ($url != "") {
        $activityData[] = $url;      
    }

    $comments = htmlentities($_POST['txtComments'], ENT_QUOTES, "UTF-8");
    if ($comments != "" ) {
        $activityData[] = $comments;        
    }
    
    // Saved as 0/1 for database
    if (isset($_POST["chkApproved"])) {
        $approved = 1;
        $activityData[] = $approved;
    } else {
        $approved = 0;
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
    if ($location != "" AND !verifyAlphaNum($location)) {
        $errorMsg[] = "The location info appears to contain invalid characters.";
        $locationError = true;
    }
    
    // cost field can be blank
    if ($cost != "" AND !verifyNumeric($cost)) {
        $errorMsg[] = "The cost must be a number.";
        $costError = true;
    }
    
    // URL field can be blank
    if ($url  != "" AND !filter_var($url, FILTER_VALIDATE_URL)) {
        $errorMsg[] = "The URL you've provided is invalid.";
        $urlError = true;
    }
    
    // Description field can be blank
    if ($comments != "" AND !verifyAlphaNum($comments)) {
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
        $townSelectQuery = "SELECT pmkTownId";
        $townSelectQuery .= " FROM tblTowns";
        $townSelectQuery .= " WHERE fldTownName = ? AND";
        $townSelectQuery .= " fldState = ?";
        $townSelectData = array($town, $state);
        
        $townSelect = $thisDatabaseReader->select($townSelectQuery, $townSelectData,
                1, 1, 0, 0, false, false);
        
        if($townSelect) { // If not empty, grab ID
            $townID = $townSelect[0]['pmkTownId'];
        
            
        } else {
            $townInsertQuery = "INSERT INTO tblTowns SET";
            $townInsertQuery .= " fldTownName = ?,";
            $townInsertQuery .= " fldState = ?,";
            $townInsertQuery .= " fldDistance = ?";
            
            $townInsert = $thisDatabaseWriter->insert($townInsertQuery, $townData,
                    0, 0, 0, 0, false, false);
            
            if ($townInsert) {
                $townID = $thisDatabaseWriter->lastInsert(); // keep after debug
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
        
        $query .= " fnkSubmitNetId = ?,";
        $query .= " fldName = ?,";
        $query .= " fldCategory = ?,";
        $query .= " fldOnCampus = ?";
        
        if ($location != "") {
            $query .= ", fldLocation = ?";
        }
        
        if ($cost != "") {
            $query .= ", fldCost = ?";
        }
        
        if ($url != "") {
            $query .= ", fldURL =?";
        }
        
        if ($comments != "") {
            $query .= ", fldDescription = ?";
        }
        
        if ($approved) {
            $query .= ", fldApproved = ?";
        }
        
        $query .= ", fnkTownId = ?";
        
        if ($update) { // IMPORTANT: do not forget to add this to UPDATE queries
            $query .= " WHERE pmkActivityId = ?";
            $activityData[] = $activityID;
            
            $activity = $thisDatabaseWriter->update($query, $activityData, 1, 0, 0, 0, false, false);
        
            
        } else {
            $activity = $thisDatabaseWriter->insert($query, $activityData, 0, 0, 0, 0, false, false);
            
            // Need to create vote so that record can be ordered
            $lastActivityID = $thisDatabaseWriter->lastInsert();
            
            // By default, vote score = 0
            $voteInsertQuery = "INSERT INTO tblVotes SET";
            $voteInsertQuery .= " fnkNetId = ?,";
            $voteInsertQuery .= " fnkActivityId = ?";
            $voteInsertData = array($user, $lastActivityID);
            
            $vote = $thisDatabaseWriter->insert($voteInsertQuery, $voteInsertData,
                    0, 0, 0, 0, false, false);
        }
        
        // No matter what, check if user is in table already
        $userSelectQuery = "SELECT pmkNetId";
        $userSelectQuery .= " FROM tblAffiliates";
        $userSelectQuery .= " WHERE pmkNetId = ?";
        $userSelectData = array($user);
        
        $userSelect = $thisDatabaseReader->select($userSelectQuery, $userSelectData,
                1, 0, 0, 0, false, false);
        
        if (!$userSelect) { // if user select query is empty
            $userInsertQuery = "INSERT INTO tblAffiliates SET";
            $userInsertQuery .= " pmkNetId = ?";
            $userInsertData = array($user);
            
            $userInsert = $thisDatabaseWriter->insert($userInsertQuery, $userInsertData,
                    0, 0, 0, 0, false, false);
        }
        
        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 2f: Create message

        $message = "<h2>Your activity has been submitted for approval.</h2>";
        $message.= "<p>A copy of the information appears below.</p>";

        foreach ($_POST as $key => $value) {
            if ($key != 'btnSubmit' AND $key != 'chkApproved') {
                $message.= "<p>";
                $camelCase = preg_split('/(?=[A-Z])/', substr($key, 3));

                foreach ($camelCase as $one) {
                    $message.= $one . ' ';
                }
                $message.= "= " . htmlentities($value, ENT_QUOTES, "UTF-8") . "</p>";
            }
        }

        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 2g: Mail to user

        $email = $user . "@uvm.edu";

        $to = $email; // the person who filled out form
        $cc = ""; // would add advisor here
        $bcc = "";
        $from = "UVM Activities <jsiebert@uvm.edu>";

        // subject of mail should match form
        $todaysDate = strftime("%x");
        $subject = "Thanks for submitting a new UVM Activity, " . $todaysDate;

        $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);
    } // ends form is valid
} // ends if form was submitted
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 3: Display form
// 
?>

<article>
    <h2>Form</h2>

    <?php
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
    // SECTION 3a
// If its the first time coming to form or there are errors, display form.
    if (isset($_POST["btnSubmit"]) AND empty($errorMsg)) { // closing marked with 'end body submit'
        print "<h2>Your request has ";

        if (!$mailed) {
            print 'not ';
        }

        print "been processed.</h2>";

        if ($mailed) {
            print "<p>A copy of this message has been sent to: " . $email . ".</p>";
            print "<p>Mail message:</p>";
            print $message;
        }
    } else {


        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
        // SECTION 3b: Error messages: Display any error message before we print form

        if ($errorMsg) {
            print '<div class="errors">';
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

        <form action="<?php print $phpSelf; ?>"
              method="post"
              id="frmAddActivity">

            <fieldset class="wrapper">
                <legend></legend>
                <p>Please provide the following information about the activity.</p>

                <fieldset class="basic-info">
                    <legend>Basic Information</legend>
                    
                    <input type="hidden" id="hidActivityId" name="hidActivityId"
                       value="<?php print $activityID; ?>"
                       >
                    
                    <label for="txtUsername" class="required">NetID
                        <input type="text" id="txtUsername" name="txtUsername"
                               value="<?php print $user; ?>"
                               tabindex="100" maxlength="45"
                               <?php if (!adminCheck($thisDatabaseReader, $username))
                                       print 'readonly'; ?>
                               class="no-edit 
                               <?php if ($userError) print ' mistake'; ?>"
                               onfocus="this.select()"
                               autofocus>
                    </label>

                    <label for="txtActivityName" class="required">Activity Name
                        <input type="text" id="txtActivityName" name="txtActivityName"
                               value="<?php print $activityName; ?>"
                               tabindex="110" maxlength="255" 
                               <?php if ($activityNameError) print 'class="mistake"'; ?>
                               onfocus="this.select()"
                               autofocus>
                    </label>

                    <label for="lstCategory">Category</label>
                    <select id="lstCategory" name="lstCategory"
                        <?php if ($categoryError) print 'class="mistake"'; ?>
                                tabIndex="200">
                                    <?php
                                    // Array for listbox options
                                    $categoryChoices = array("Select one", "Outdoor", "School-Related", "Social");

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

                    <label><input type="checkbox" 
                                      id="chkOnCampus" 
                                      name="chkOnCampus" 
                                      value="On Campus"
                                      <?php if ($onCampus) print " checked "; ?>
                                      tabindex="300">Is this activity on campus?</label>
                </fieldset> <!-- end basic-info -->
                
                <fieldset class="location-info">
                    <legend>Location Information</legend>
                    <label for="txtTown" class="required">Town
                        <input type="text" id="txtTown" name="txtTown"
                               value="<?php print $town; ?>"
                               tabindex="400" maxlength="255" 
                               <?php if ($townError) print 'class="mistake"'; ?>
                               onfocus="this.select()"
                               autofocus>
                    </label>

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
                    
                    <label for="txtDistance" class="required">Distance from Burlington (in miles)
                        <input type="text" id="txtDistance" name="txtDistance"
                               value="<?php print $distance; ?>"
                               tabindex="420" maxlength="255" 
                               <?php if ($distanceError) print 'class="mistake"'; ?>
                               onfocus="this.select()"
                               autofocus>
                    </label>

                </fieldset> <!-- end location-info -->
                
                <fieldset class="optional-info">
                    <legend>Optional Information</legend>
                    
                    <label for="txtLocation" class="required">Location Description
                        <input type="text" id="txtLocation" name="txtLocation"
                               value="<?php print $location; ?>"
                               tabindex="500" maxlength="255" 
                               <?php if ($locationError) print 'class="mistake"'; ?>
                               onfocus="this.select()"
                               autofocus>
                    </label>
                    
                    <label for="txtCost" class="required">Cost to Participate
                        <input type="text" id="txtCost" name="txtCost"
                               value="<?php print $cost; ?>"
                               tabindex="510" maxlength="255" 
                               <?php if ($costError) print 'class="mistake"'; ?>
                               onfocus="this.select()"
                               autofocus>
                    </label>
                    
                    <label for="txtURL" class="required">URL
                        <input type="text" id="txtURL" name="txtURL"
                               value="<?php print $url; ?>"
                               tabindex="520" maxlength="255" 
                               <?php if ($urlError) print 'class="mistake"'; ?>
                               onfocus="this.select()"
                               autofocus>
                    </label>
                    
                    <label for="txtComments" class="required">Description</label>
                    <textarea id="txtComments"
                                  name="txtComments"
                                  tabindex="600"
                                  <?php if ($commentsError) print 'class="mistake"'; ?>
                                  onfocus="this.select()"><?php print $comments; ?>
                    </textarea>
                    
                </fieldset> <!-- end optional-info -->
                
                <?php
                // If user is admin, print preapprove checkbox
                if (adminCheck($thisDatabaseReader, $username)) {
                    print '<fieldset class="admin-only">';
                    print '<legend>Administrative</legend>';
                    print '<label><input type="checkbox" id="chkApproved"';
                    print 'name="chkApproved" value="Approved"';
                    if ($approved) print " checked ";
                    print 'tabindex="700">Preapprove activity</label>';
                    print '</fieldset>';
                }
                
                ?> 
                
                <fieldset class="buttons">
                    <legend></legend>
                    <input type="submit" id="btnSubmit" name="btnSubmit" value="Submit" tabindex="900" class="button">
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
