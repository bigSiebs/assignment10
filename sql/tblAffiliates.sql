
CREATE TABLE IF NOT EXISTS tblAffiliates(
    pmkNetId varchar(12) NOT NULL,  
    fldLastName varchar(100) NOT NULL,
    fldFirstName varchar(100) NOT NULL,
    PRIMARY KEY(pmkNetId)
);

INSERT INTO tblAffiliates VALUES
    ('aychu', 'Alan', 'Chu'),
    ('jsiebert', 'Joseph', 'Siebert');