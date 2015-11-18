<?php
//&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&*&
//
// file is for custom functions that you dont know where else to put
// 

// The idea here is to look for sql injection techniques and replace them with
// a character which will cause the sql statement to simply fail
function sanitize($string, $spacesAllowed = true, $semiColonAllowed = true) {
    $replaceValue = "Q";

    if (!$semiColonAllowed) {
        $string = str_replace(';', $replaceValue, $string);
    }
    
    if (!$spacesAllowed) {
        $string = str_replace(' ', $replaceValue, $string);
    }
    $string = htmlentities($string, ENT_QUOTES);

    $string = str_replace('%20', $replaceValue, $string);

    return $string;
}


// this is a confirm function that asks the user if they are
// sure they want to get rid of the last entry
function confirm($arr){
    foreach ($arr as $key => $value) {
    echo "Key: $key; Value: $value<br />\n";
}
    
    
}