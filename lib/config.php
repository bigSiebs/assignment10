<?php
/*
1 = Check if the file uploaded is actually an image no matter what extension it has
2 = The uploaded files must have a specific image extension
*/
 
$validationType = 1;
 
if ($validationType == 1)
{
    $mime = array('image/gif' => 'gif',
                  'image/jpeg' => 'jpeg',
                  'image/png' => 'png',
                  'application/x-shockwave-flash' => 'swf',
                  'image/psd' => 'psd',
                  'image/bmp' => 'bmp',
                  'image/tiff' => 'tiff',
                  'image/tiff' => 'tiff',
                  'image/jp2' => 'jp2',
                  'image/iff' => 'iff',
                  'image/vnd.wap.wbmp' => 'bmp',
                  'image/xbm' => 'xbm',
                  'image/vnd.microsoft.icon' => 'ico');
}
else if($validationType == 2) { // Second choice? Set the extensions
    $imageExtensionsAllowed = array('jpg', 'jpeg', 'png', 'gif','bmp');
}
 
$uploadImageToFolder = 'uploads/';
?>