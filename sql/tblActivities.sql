
CREATE TABLE IF NOT EXISTS `tblActivities` (
    `pmkActivityId` int(11) NOT NULL AUTO_INCREMENT,
    `fldName` varchar(255) NOT NULL,
    `fldCategory` varchar(100) NOT NULL,
    `fldOnCampus` tinyint(1) NOT NULL,
    `fnkTownId` int(11) NOT NULL,
    `fldLocation` varchar(255) DEFAULT NULL,
    `fldCost` int(12) DEFAULT NULL,
    `fldURL` varchar(255) DEFAULT NULL,
    `fldDescription` text,
    `fnkSubmitNetId` varchar(12) NOT NULL,
    `fldDateSubmitted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `fldApproved` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;