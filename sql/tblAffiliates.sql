
CREATE TABLE IF NOT EXISTS `tblAffiliates` (
    `pmkNetId` varchar(12) NOT NULL,
    `fldAffiliation` varchar(255) NOT NULL DEFAULT 'Other',
    `fldLastName` varchar(100) DEFAULT NULL,
    `fldFirstName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;