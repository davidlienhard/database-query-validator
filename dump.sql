CREATE TABLE `user` (
  `userID` int NOT NULL AUTO_INCREMENT,
  `userName` varchar(200) NOT NULL DEFAULT '',
  `userMail` varchar(200) NOT NULL DEFAULT '',
  `userType` varchar(50) NOT NULL DEFAULT ''
  PRIMARY KEY (`userID`),
  KEY `userID` (`userID`)
);
