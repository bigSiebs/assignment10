
CREATE TABLE IF NOT EXISTS tblTowns(
    pmkTownId int(11) NOT NULL AUTO_INCREMENT,
    fldTownName varchar(100) NOT NULL,
    fldState char(2) NOT NULL,
    fldDistance int(11) NOT NULL,
    PRIMARY KEY(pmkTownId)
);

INSERT INTO tblTowns (fldTownName, fldState, fldDistance) VALUES
    ('Burlington', 'VT', 0);