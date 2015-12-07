<?php
include "top.php";

// SECTION: 1 Initialize variables
//
// SECTION: 1a.
// variables for the classroom purposes to help find errors.
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

if (isset($_GET["activity"])) { // ONLY do this in a classroom environment
    $activityID = (int) $_GET["activity"];
}

// Build query to get info from database
$checkQuery = "SELECT fldName";
$checkQuery .= " FROM tblActivities";
$checkQuery .= " WHERE pmkActivityId = ?";
$data = array($activityID);

// Fetch data from database
$check = $thisDatabaseReader->select($checkQuery, $data, 1, 0, 0, 0, false, false);

if (!isset($_POST['btnSubmit']) AND !$check) {
    print "<h2>Submit an Image</h2>";
    print "<p>It appears a valid activity has not been selected.";
    print " You can select an activity below.</p>";

    $query = "SELECT pmkActivityId, fldName";
    $query .= " FROM tblActivities";
    $query .= " ORDER BY fldDateSubmitted";

    $selectAll = $thisDatabaseReader->select($query, "", 0, 1, 0, 0, false, false);

    print '<section class="panel"';
    print '<ul>';

    foreach ($selectAll as $record) {
        $appendURL = "?activity=" . $record['pmkActivityId'];

        print '<li>';
        print '<a href="' . $appendURL . '">';
        print $record['fldName'];
        print '</a>';
        print '</li>';
    }

    print '</ul>';
    print '</section>';
} else {

    $user = $username;

// file Variables
    $fileName = "";
    $fileLocation = "";

    $photoCaption = "";

// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 1d: Form error flags: Initalize ERROR flags, one for each form element
// we validate, in the order they appear in SECTION 1c
// Assume no error in uploading file
    $uploadError = false;
    $movedFile = false;

    $captionError = false;


// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 1e: Misc. variables
// Array to hold error messages
    $errorMsg = array();

    $photoData = array();   // array to hold the data on the photo submitted

    $mailed = false; // Not mailed yet
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 2: Process for when the form is submitted
// Ideal Situation - user submits form & $_FILES array is not empty
    if (isset($_POST['btnSubmit']) AND ! empty($_FILES)) {
        // config.php is used to validate the image file
        include 'lib/config.php';

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
        // CODE WITH GET FOR EASY TESTING
        // array of PhotoData order: ActivityId, NetId, imageLink, Caption, approve
        // 1.) Add the activityId to the photoData array
        $activityID = (int) htmlentities($_POST["hidActivityId"], ENT_QUOTES, "UTF-8");
        $photoData[] = $activityID;

        // 2.) Add the netId of the user to the photoData array
        $user = htmlentities($_POST["hidNetId"], ENT_QUOTES, "UTF-8");
        $photoData[] = $user;

        // 3.) ADD THE IMAGE HERE!!
        $file = $_FILES['imgFileToUpload'];
        $fileName = $file['name'];

        // 4.) Add the Caption of the photo to the array
        $photoCaption = htmlentities($_POST['txtCaption'], ENT_QUOTES, "UTF-8");
        $photoData[] = $photoCaption;

        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //
    // SECTION 2c: Validation: Check each value for possible errors or empty.
        // But check all passed in variables
        // Validation for image!!!
        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        // Get File Extension (if any)
        $ext = strtolower(substr(strrchr($fileName, "."), 1));

        // Check for a correct extension. The image file hasn't an extension? Add one
        ////%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        //// Validation
        if ($validationType == 1) {
            $fileLocation = getimagesize($_FILES['imgFileToUpload']['tmp_name']);

            if (empty($fileLocation)) { // No file, should never go here b/c of if statement
                $errorMsg[] = "The uploaded file doesn't seem to be an image.";
                $uploadError = true;
            } else { // An Image?
                $fileMime = $fileLocation['mime'];

                if ($ext == 'jpc' || $ext == 'jpx' || $ext == 'jb2') {
                    $extension = $ext;
                } else {
                    $extension = ($mime[$fileMime] == 'jpeg') ? 'jpg' : $mime[$fileMime];
                }

                if (!$extension) {
                    $extension = '';
                    $fileName = str_replace('.', '', $fileName);
                }
            }
        } // closes validation if == 1
        else if ($validationType == 2) {
            if (!in_array($ext, $imageExtensionsAllowed)) {
                $exts = implode(', ', $imageExtensionsAllowed);
                $errorMsg[] = "You must upload a file with one of the following extensions: " . $exts;
                $uploadError = true;
            }

            $extension = $ext;
        }


        // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
        if ($photoCaption == "") {
            $errorMsg[] = "The photo caption can not be empty.";
            $captionError = true;
        } elseif (!verifyAlphaNum($photoCaption)) {
            $errorMsg[] = "The name you've provided for the activity contains invalid characters.";
            $captionError = true;
        }

        // SECTION 2d: Process form - passed validation (errorMsg is empty)

        if (!$errorMsg) {
            if ($debug) {
                print "<p>Form is valid.</p>";
            }
            // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
            //
        // SECTION 2e: Save data: Insert data into database
            // and upload the file to the 'uploads' directory
            // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
            // Rename the fileName!!! Query to find a unique name:
            // activityId#_numphotos $activityID
            // query to get $photoCount
            // query = SELECT count(pmkPhotoId) FROM `tblPhotos` WHERE fnkActivityId = 9
            $query = "SELECT count(pmkPhotoId) AS photoCount";
            $query .= " FROM tblPhotos";
            $query .= " WHERE fnkActivityId = ?";
            $data = array($activityID);

            $info = $thisDatabaseReader->select($query, $data, 1, 0, 0, 0, false, false);
            $photoCount = $info[0]['photoCount'];

            // Will be appended to filename
            $photoID = $photoCount + 1;

            // rename the file
            $uploadName = $activityID . '-' . $photoID . '.' . $extension;

            // Attempts to move file to upload directory, moveFile boolean!
            $movedFile = move_uploaded_file($file['tmp_name'], $uploadImageToFolder . $uploadName);

            // add the new filename to the photoData array for insert query
            $photoData[] = $uploadName;

            $query2 = "INSERT INTO tblPhotos SET";
            $query2 .= " fnkActivityId = ?";
            $query2 .= ", fnkNetId = ?";
            $query2 .= ", fldCaption = ?";
            $query2 .= ", fldFileName = ?";

            $photoInsert = $thisDatabaseWriter->insert($query2, $photoData, 0, 0, 0, 0, false, false);

            if ($photoInsert) {
                $uploaded = true;
            } else {
                $uploaded = false;
            }

            // SECTION 2f: Create message
            $message = "<h2>Thank you! Your image has been submitted for approval.</h2>";

            $message .= "<p>An administrator will review the submission to ensure";
            $message .= " that it does not violate our guidelines.";
            $message .= " You should receive a follow-up email if the image";
            $message .= " is inappropriate, incomplete, etc.</p>";

//        $message.= "<p>A copy of the submitted information appears below.</p>";
//
//
//        foreach ($_POST as $key => $value) {
//            if ($key != 'btnSubmit') {
//                $message.= "<p>";
//                $camelCase = preg_split('/(?=[A-Z])/', substr($key, 3));
//
//                foreach ($camelCase as $one) {
//                    $message.= $one . ' ';
//                }
//                $message = trim($message);
//
//                $message.= ": " . htmlentities($value, ENT_QUOTES, "UTF-8") . "</p>";
//            }
//        }
//
//        $message.= "<hr>";
//
//        foreach ($file as $key => $value) {
//            if ($key == "name") {
//                $message.="<p>";
////                $message.=$key . " ";
//                $message.="File Name ";
//                $message = trim($message);
//                $message.= ": " . htmlentities($value, ENT_QUOTES, "UTF-8") . "</p>";
//            }
//        }

            $message .= "<br><p>Thanks again!</p>";
            $message .= "<br><p>The UVM Checkkit admin team</p>";


            // %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
            //
        // SECTION 2g: Mail to user

            $email = $user . "@uvm.edu";

            $to = $email; // the person who filled out form
            $cc = ""; // admins
            $bcc = "";
            $from = "UVM Chekkit <aychu@uvm.edu>";

            // subject of mail should match form
            $subject = "Thanks for submitting an image to UVM diddit!";

            // send mail
            $mailed = sendMail($to, $cc, $bcc, $from, $subject, $message);
        } // ends form is valid
    } // ends if form was submitted
// SECTION 3 -- display form
    ?>

    <article>

        <?php
// %^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%^%
//
// SECTION 3a
// If its the first time coming to form or there are errors, display form.
        if ((isset($_POST["btnSubmit"]) AND ! empty($_FILES)) AND empty($errorMsg)) {

            print "<h2>Your request has ";

            if (!$movedFile OR ! $uploaded) {
                print 'not ';
            }

            print "been processed.</h2>";

            if ($mailed) {
                print '<section class="panel success-panel">';
                print "<p>A copy of this message has been sent to: " . $email . ".</p>";
                print $message;
                print "</section>";
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

            <h2>Submit a photo to diddit!</h2>
            <form action="<?php print $phpSelf; ?>" 
                  method="post" 
                  id="frmAddPhoto"
                  class="panel"
                  enctype="multipart/form-data">


                <!--Select image to upload:-->
                <fieldset class="wrapper">
                    <legend>You selected the following activity:</legend>

                    <?php
                    $query = "SELECT fldName";
                    $query .= " FROM tblActivities";
                    $query .= " WHERE pmkActivityId = ?";
                    $data = array($activityID);

                    $info = $thisDatabaseReader->select($query, $data, 1, 0, 0, 0, false, false);
                    print "<p><b>" . $info[0]["fldName"] . "</b></p>";
                    ?>

                    <input type="hidden" id="hidActivityId" name="hidActivityId"
                           value="<?php print $activityID; ?>">

                    <input type="hidden" id="hidNetId" name="hidNetId"
                           value="<?php print $user; ?>">

                    <label>Select image:
                        <input type="file" name="imgFileToUpload" id="imgFileToUpload"
                               tabindex="100">
                    </label>

                    <label>Photo caption 
                        <input type ="text" id="txtCaption" name="txtCaption" 
                               value="<?php print $photoCaption; ?>"
                               tabindex="200" maxlength="255"
                               <?php if ($captionError) print 'class="mistake"'; ?>
                               onfocus="this.select()"
                               autofocus>
                    </label>

                    <fieldset class="buttons">
                        <legend></legend>
                        <input type="submit" id="btnSubmit" name="btnSubmit"
                               value="Submit" tabindex="900" class="button">
                    </fieldset> <!-- ends buttons -->
                </fieldset> <!-- end wrapper! -->
            </form> <!-- end form! -->

        </article>

        <?php
    } // ends submit
} // ends else (from checking activity number)
include 'footer.php';
?>
