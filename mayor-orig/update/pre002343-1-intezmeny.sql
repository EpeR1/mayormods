DROP TABLE IF EXISTS tanarKepesites;
CREATE TABLE `tanarKepesites` (                                                                                                                                                                              
  `tanarId` int(10) unsigned NOT NULL,
  `targyId` smallint(5) unsigned NOT NULL,                                                                                                                                                                      
  PRIMARY KEY (`tanarId`,`targyId`),                                                                                                                                                                          
  KEY `tanarKepesites_FKIndex1` (`tanarId`),
  KEY `tanarKepesites_FKIndex2` (`targyId`),
    CONSTRAINT `tanarKepesites_ibfk_1` FOREIGN KEY (`tanarId`) REFERENCES `tanar` (`tanarId`) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT `tanarKepesites_ibfk_2` FOREIGN KEY (`targyId`) REFERENCES `targy` (`targyId`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;                                                                                                                                            
    