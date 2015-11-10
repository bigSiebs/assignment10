
CREATE TABLE IF NOT EXISTS tblActivities(
    pmkActivityId int(11) NOT NULL AUTO_INCREMENT,
    fldName varchar(255) NOT NULL,
    fldCategory varchar(100) NOT NULL,
    fldOnCampus tinyint(1) NOT NULL CHECK(fldOnCampus = 0 OR fldOnCampus = 1),
    fnkTownId int(11) NOT NULL,
    fldDescription TEXT DEFAULT NULL,
    fnkSubmitNetId varchar(12) NOT NULL,
    fldApproved tinyint(1) NOT NULL DEFAULT 0 CHECK (fldVote = 0 OR fldVote = 1),
    PRIMARY KEY(pmkActivityId)
);

INSERT INTO tblActivities (fldName, fldCategory, fldOnCampus, fldTown, fldState, fldDescription, fnkSubmitNetId) VALUES
	('Participate in the Twilight Induction', 'School-Related', 1, 1, NULL, 'jsiebert'),
        ('Make a friend from each campus', 'Social', 1, 1, NULL, 'jsiebert');