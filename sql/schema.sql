-- phpMyAdmin SQL Dump
-- version 4.2.9
-- http://www.phpmyadmin.net
--
-- Host: webdb.uvm.edu
-- Generation Time: Dec 07, 2015 at 12:05 PM
-- Server version: 5.5.45-37.4-log
-- PHP Version: 5.3.3

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Database: `JSIEBERT_ACTIVITIES`
--

-- --------------------------------------------------------

--
-- Table structure for table `tblActivities`
--

CREATE TABLE IF NOT EXISTS `tblActivities` (
`pmkActivityId` int(11) NOT NULL,
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
) ENGINE=InnoDB AUTO_INCREMENT=57 DEFAULT CHARSET=utf8;


-- --------------------------------------------------------

--
-- Table structure for table `tblAdministrators`
--

CREATE TABLE IF NOT EXISTS `tblAdministrators` (
  `pmkNetId` varchar(12) NOT NULL,
  `fldFirstName` varchar(255) NOT NULL,
  `fldLastName` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tblAffiliates`
--

CREATE TABLE IF NOT EXISTS `tblAffiliates` (
  `pmkNetId` varchar(12) NOT NULL,
  `fldAffiliation` varchar(255) NOT NULL DEFAULT 'Other',
  `fldLastName` varchar(100) DEFAULT NULL,
  `fldFirstName` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tblPhotos`
--

CREATE TABLE IF NOT EXISTS `tblPhotos` (
`pmkPhotoId` int(11) NOT NULL,
  `fnkActivityId` int(11) NOT NULL,
  `fnkNetId` varchar(12) NOT NULL,
  `fldCaption` varchar(256) NOT NULL,
  `fldApproved` tinyint(1) NOT NULL DEFAULT '0',
  `fldFileName` varchar(256) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tblTowns`
--

CREATE TABLE IF NOT EXISTS `tblTowns` (
`pmkTownId` int(11) NOT NULL,
  `fldTownName` varchar(100) NOT NULL,
  `fldState` char(2) NOT NULL,
  `fldDistance` int(11) NOT NULL
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `tblVotes`
--

CREATE TABLE IF NOT EXISTS `tblVotes` (
  `fnkNetId` varchar(12) NOT NULL,
  `fnkActivityId` varchar(255) NOT NULL,
  `fldVote` tinyint(1) NOT NULL DEFAULT '0',
  `fldDateVoted` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tblActivities`
--
ALTER TABLE `tblActivities`
 ADD PRIMARY KEY (`pmkActivityId`);

--
-- Indexes for table `tblAdministrators`
--
ALTER TABLE `tblAdministrators`
 ADD PRIMARY KEY (`pmkNetId`);

--
-- Indexes for table `tblAffiliates`
--
ALTER TABLE `tblAffiliates`
 ADD PRIMARY KEY (`pmkNetId`);

--
-- Indexes for table `tblPhotos`
--
ALTER TABLE `tblPhotos`
 ADD PRIMARY KEY (`pmkPhotoId`);

--
-- Indexes for table `tblTowns`
--
ALTER TABLE `tblTowns`
 ADD PRIMARY KEY (`pmkTownId`);

--
-- Indexes for table `tblVotes`
--
ALTER TABLE `tblVotes`
 ADD PRIMARY KEY (`fnkNetId`,`fnkActivityId`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tblActivities`
--
ALTER TABLE `tblActivities`
MODIFY `pmkActivityId` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=57;
--
-- AUTO_INCREMENT for table `tblPhotos`
--
ALTER TABLE `tblPhotos`
MODIFY `pmkPhotoId` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=25;
--
-- AUTO_INCREMENT for table `tblTowns`
--
ALTER TABLE `tblTowns`
MODIFY `pmkTownId` int(11) NOT NULL AUTO_INCREMENT,AUTO_INCREMENT=9;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
