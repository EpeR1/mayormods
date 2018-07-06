
-- create bejegyzesTipusId

DROP TABLE IF EXISTS `bejegyzesTipus`;
CREATE TABLE `bejegyzesTipus` (
  `bejegyzesTipusId` tinyint unsigned NOT NULL auto_increment,
  `tipus` enum('dicséret','fegyelmi','üzenet') COLLATE utf8_hungarian_ci NOT NULL,
  `fokozat` tinyint(3) unsigned NOT NULL,
  `bejegyzesTipusNev` varchar(128),
  `hianyzasDb` tinyint(3) unsigned DEFAULT NULL,
  `jogosult` SET('szaktanár','osztályfőnök','vezetőség','admin') COLLATE utf8_hungarian_ci DEFAULT NULL,
  PRIMARY KEY (`bejegyzesTipusId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

INSERT INTO `bejegyzesTipus` (`bejegyzesTipusId`,`tipus`,`fokozat`,`bejegyzesTipusNev`,`hianyzasDb`) VALUES 
(1,'fegyelmi',1,NULL,NULL),(2,'fegyelmi',2,NULL,NULL),(3,'fegyelmi',3,NULL,NULL),(4,'fegyelmi',4,NULL,NULL),(5,'fegyelmi',5,NULL,NULL),
(6,'fegyelmi',6,NULL,NULL),(7,'fegyelmi',7,NULL,NULL),(8,'fegyelmi',8,NULL,NULL),(9,'fegyelmi',9,NULL,NULL),(10,'fegyelmi',10,NULL,NULL),
(11,'fegyelmi',11,NULL,NULL),(12,'fegyelmi',12,NULL,NULL),(13,'fegyelmi',13,NULL,NULL),(14,'fegyelmi',14,NULL,NULL),(15,'fegyelmi',15,NULL,NULL),
(16,'fegyelmi',16,NULL,NULL),(17,'fegyelmi',17,NULL,NULL),(18,'fegyelmi',18,NULL,NULL),(19,'fegyelmi',19,NULL,NULL),(20,'fegyelmi',20,NULL,NULL),
(21,'dicséret',1,NULL,NULL),(22,'dicséret',2,NULL,NULL),(23,'dicséret',3,NULL,NULL),(24,'dicséret',4,NULL,NULL),(25,'dicséret',5,NULL,NULL),
(26,'dicséret',6,NULL,NULL),(27,'dicséret',7,NULL,NULL),(28,'dicséret',8,NULL,NULL),(29,'dicséret',9,NULL,NULL),(30,'dicséret',10,NULL,NULL),
(31,'dicséret',11,NULL,NULL),(32,'dicséret',12,NULL,NULL),(33,'dicséret',13,NULL,NULL),(34,'dicséret',14,NULL,NULL),(35,'dicséret',15,NULL,NULL),
(36,'dicséret',16,NULL,NULL),(37,'dicséret',17,NULL,NULL),(38,'dicséret',18,NULL,NULL),(39,'dicséret',19,NULL,NULL),(40,'dicséret',20,NULL,NULL),
(50,'üzenet',0,'üzenet',NULL);

UPDATE `bejegyzesTipus` SET `jogosult`='szaktanár,osztályfőnök,vezetőség,admin' WHERE `fokozat`=0;