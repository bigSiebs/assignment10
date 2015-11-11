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

// $username initalized and sanitized in top.php
$activityName = "";
$category = "Select one";

$onCampus = false; // not checked

$town = "";
$state = "VT";

// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 1d: Form error flags: Initalize ERROR flags, one for each form element
// we validate, in the order they appear in SECTION 1c

$usernameError = false;
$activityNameError = false;
$categoryError = false;
$townError = false;

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
    $activityData[] = $username;

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

    // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
    //
    // SECTION 2c: Validation: Check each value for possible errors or empty.

    if ($username == "") {
        $errorMsg[] = "Please enter your NetID.";
        $usernameError = true;
    } elseif (!verifyAlphaNum($username)) {
        $errorMsg[] = "Your NetID appears to include invalid charaters.";
        $usernameError = true;
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
        $errorMsg[] = "The town name appears to include invalid charaters.";
        $townError = true;
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
            $query = "INSERT INTO tblActivities SET";
            $query .= " fnkSubmitNetId = ?,";
            $query .= " fldName = ?,";
            $query .= " fldCategory = ?,";
            $query .= " fldOnCampus = ?,";
            //if ($cost != "") {
            //    $query .= "fldCost = ?";
            //    $activityData [] = $cost;
            //}
            $query .= " fnkTownId = 1"; //hard-coded to Burlington for now
            
            $activity = $thisDatabaseWriter->insert($query, $activityData, 0, 0, 0, 0, false, false);
            //$recordID = $thisDatabaseWriter->lastInsert();
            // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
            //
        // SECTION 2f: Create message

            $message = "<h2>Your activity has been submitted for approval.</h2>";
            $message.= "<p>A copy of the information appears below.</p>";

            foreach ($_POST as $key => $value) {
                if ($key != 'btnSubmit') {
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

            $email = $username . "@uvm.edu";

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
                        <label for="txtUsername" class="required">NetID
                            <input type="text" id="txtUsername" name="txtUsername"
                                   value="<?php print $username; ?>"
                                   tabindex="100" maxlength="45" readonly class="no-edit
                                    <?php if ($usernameError) print ' mistake'; ?>"
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
                        
                        <fieldset class="listbox1">
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
                    </fieldset> <!-- end listbox1 -->
                    
                    <fieldset class="checkbox">
                        <legend></legend>
                        <label><input type="checkbox" 
                            id="chkOnCampus" 
                            name="chkOnCampus" 
                            value="On Campus"
                            <?php if ($onCampus) print " checked "; ?>
                            tabindex="300">Is this activity on campus?</label>
                    </fieldset> <!-- end checkbox -->
                    
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
                        
                    </fieldset> <!-- end basic-info -->

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
