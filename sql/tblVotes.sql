
CREATE TABLE IF NOT EXISTS `tblVotes` (
    `fnkNetId` varchar(12) NOT NULL,
    `fnkActivityId` varchar(255) NOT NULL,
    `fldVote` tinyint(1) NOT NULL DEFAULT '0',
    `fldDateVoted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
