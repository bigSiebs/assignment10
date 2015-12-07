
CREATE TABLE IF NOT EXISTS `tblPhotos` (
    `pmkPhotoId` int(11) NOT NULL AUTO_INCREMENT,
    `fnkActivityId` int(11) NOT NULL,
    `fnkNetId` varchar(12) NOT NULL,
    `fldCaption` varchar(256) NOT NULL,
    `fldApproved` tinyint(1) NOT NULL DEFAULT '0',
    `fldFileName` varchar(256) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
