<?php

include "top.php";

print "<article>";
print "<h2>Welcome to UVM diddit!</h2>";
print "<p>UVM diddit is a site for current and former UVM students to suggest ";
print "activities that they feel every UVM student should do before they graduate. ";
print 'It was inspired by <a href="images/101-things.jpg">this list</a>, ';
print 'which is distributed to first-year students when they move onto campus. ';
print "<p>The list is fine, but we thought transforming the list into an ";
print "interactive website would reveal some new activities that were, for one ";
print "reason or another, left off the university's " . '"official"' . " list. ";
print "We're also hoping that this site brings to attention more niche activities ";
print "that people aren't aware of. UVM is filled with people of many different ";
print "interests, and ideally this site will allow others to experience those ";
print " interests and connect with people who share them.</p>";
print "<p>Finally, we should note that you can vote up or down any activity on ";
print "list. Some activities are definitely more essential than others, and ";
print "we wanted our list to reflect that.</p>";

$query = "SELECT fnkSubmitNetId, fldName";
$query .= " FROM tblActivities";
$query .= " WHERE fldApproved = ?";
$query .= " ORDER BY fldDateSubmitted DESC LIMIT 3";
$data = array(1);

// Call select method
$info = $thisDatabaseReader->select($query, $data, 1, 1, 0, 0, false, false);
//$info2 = $thisDatabaseReader->testquery($query, $data, $val[0], $val[1], $val[2], $val[3], false, false);
//print "<pre>";
//print_r($info);
// To troubleshoot returned array

if ($debug) {
    print "<p>DATA: <pre>";
    print_r($info);
    print "</pre></p>";
}

print "<h3>Most Recently Submitted Activities</h3>";

// For loop to print records
foreach ($info as $record) {
    print '<div class="panel">';
    print '<p>';
    print '<b>' . $record['fnkSubmitNetId'] . '</b> suggested ';
    print '<b>' .$record['fldName'] . '</b>';
    print '</p>';
    print '</div>';
}

print '<p class="text-center">';
print 'Click <a href="' . $path . 'list.php">here</a> to see the full list.';
print '</p>';

print "</article>";

include "footer.php";
?>