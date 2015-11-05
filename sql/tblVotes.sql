
CREATE TABLE IF NOT EXISTS tblVotes(
    fnkNetId varchar(12) NOT NULL,
    fnkActivityId varchar(255) NOT NULL,
    fldVote tinyint(1) NOT NULL CHECK (fldVote = -1 OR fldVote = 0 OR fldVote = 1),
    fldDateVoted timestamp NOT NULL DEFAULT NOW(),
    PRIMARY KEY(fnkNetId, fnkActivityId)
);


INSERT INTO tblVotes VALUES
    ('jsiebert', 1, -1, NOW()),
    ('aychu', 2, 1, NOW());