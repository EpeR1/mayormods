CREATE TABLE `tankorTipus` (
  `tankorTipusId` int(10) unsigned NOT NULL auto_increment,
  `oratervi` enum('óratervi','tanórán kívüli') DEFAULT 'óratervi',
  `rovidNev` varchar(30) NOT NULL,
  `leiras`   varchar(255) NOT NULL,
  `jelenlet` enum('kötelező','nem kötelező') NOT NULL,
  `regisztralando` enum('igen','nem') DEFAULT NULL,
  `hianyzasBeleszamit` enum('igen','nem') DEFAULT NULL,
  PRIMARY KEY (`tankorTipusId`),
  KEY (`rovidNev`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci ;

INSERT INTO tankorTipus VALUES (1,'óratervi','óratervi','Óratervi (képzési hálóban kötelező) tanóra','kötelező','igen','igen');
INSERT INTO tankorTipus VALUES (2,'óratervi','első nyelv','Óratervi (képzési hálóban kötelező) tanóra - első nyelv','kötelező','igen','igen');
INSERT INTO tankorTipus VALUES (3,'óratervi','második nyelv','Óratervi (képzési hálóban kötelező) tanóra - második nyelv','kötelező','igen','igen');
INSERT INTO tankorTipus VALUES (4,'tanórán kívüli','tanórán kívüli','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás','nem kötelező','igen','nem');
INSERT INTO tankorTipus VALUES (5,'tanórán kívüli','szakkör','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - szakkör','nem kötelező','igen','nem');
INSERT INTO tankorTipus VALUES (6,'tanórán kívüli','edzés','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - edzés','nem kötelező','igen','nem');
INSERT INTO tankorTipus VALUES (7,'tanórán kívüli','kórus','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - kórus','nem kötelező','igen','nem');
INSERT INTO tankorTipus VALUES (8,'tanórán kívüli','tanulószoba','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - tanulószoba','nem kötelező','igen','nem');
INSERT INTO tankorTipus VALUES (9,'tanórán kívüli','napközi','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - napközi','nem kötelező','igen','nem');
INSERT INTO tankorTipus VALUES (10,'tanórán kívüli','egyéni foglalkozás','Nem óratervi (képzési hálóban nem szerepló) óra jellegű foglalkozás - egyéni foglalkozás','nem kötelező','igen','nem');

ALTER TABLE tankor ADD tankorTipusId int(10) unsigned NULL DEFAULT NULL;

UPDATE tankor SET tankorTipusId=1 WHERE tankorTipus='tanórai';
UPDATE tankor SET tankorTipusId=4 WHERE tankorTipus='tanórán kívüli';
UPDATE tankor SET tankorTipusId=2 WHERE tankorTipus='első nyelv';
UPDATE tankor SET tankorTipusId=3 WHERE tankorTipus='második nyelv';
UPDATE tankor SET tankorTipusId=10 WHERE tankorTipus='egyéni foglalkozás';
UPDATE tankor SET tankorTipusId=4 WHERE tankorTipus='délutáni';

