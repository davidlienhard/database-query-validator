CREATE TABLE `user` (
  `userID` INT NOT NULL AUTO_INCREMENT,
  `userName` VARCHAR(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `userDescription` TEXT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `userPermissions` INT NOT NULL DEFAULT '0',
  PRIMARY KEY (`userID`)
);
