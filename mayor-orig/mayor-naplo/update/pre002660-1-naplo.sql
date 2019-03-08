
CREATE TABLE `kerdoivSzabadValasz` (
  `szabadValaszId` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `kerdesId` int(10) unsigned NOT NULL,
  `szoveg` varchar(255) COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`szabadValaszId`),
  FOREIGN KEY `fk1` (`kerdesId`) references `kerdoivKerdes`(`kerdesId`) on update cascade on delete cascade
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

