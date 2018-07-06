
CREATE TABLE IF NOT EXISTS `kosziEsemeny` (                                                                                                                                                                            
  `kosziEsemenyId` int(10) unsigned NOT NULL auto_increment,
  `kosziEsemenyNev` varchar(50) NOT NULL,
  `kosziEsemenyLeiras` varchar(255) NOT NULL,
  `kosziEsemenyTipus` enum('iskolai rendezvény','DÖK rendezvény','tanulmányi verseny','sportverseny','foglalkozás','tevékenység','hiányzás') COLLATE utf8_hungarian_ci NOT NULL,
  `kosziEsemenyIntervallum`  tinyint(1) UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`kosziEsemenyId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE IF NOT EXISTS `kosziPont` (                                                                                                                                                                                 
  `kosziPontId` int(10) unsigned NOT NULL auto_increment,                                                                                                                                                  
  `kosziEsemenyId` int(10) unsigned NOT NULL,                                                                                                                                                              
  `kosziPontTipus` enum('résztvevő','segítő','szervező','fellépő (egyéni)','fellépő (osztály)','fellépő (csoport)','eredmény') NOT NULL DEFAULT 'résztvevő',                                               
  `kosziPont` int(10) unsigned NOT NULL DEFAULT 0,                                                                                                                                                         
  `kosziHelyezes` int(10) unsigned NULL DEFAULT NULL,                                                                                                                                                      
  UNIQUE KEY (`kosziEsemenyId`,`kosziPontTipus`,`kosziHelyezes`),                                                                                                                                          
  PRIMARY KEY (`kosziPontId`),                                                                                                                                                                             
  CONSTRAINT `kosziEsemeny_ibfk_1` FOREIGN KEY (`kosziEsemenyId`) REFERENCES `kosziEsemeny` (`kosziEsemenyId`) ON DELETE CASCADE ON UPDATE CASCADE                                                         
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;     
