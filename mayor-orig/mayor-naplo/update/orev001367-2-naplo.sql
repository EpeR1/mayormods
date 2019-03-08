alter database collate utf8_hungarian_ci;

ALTER TABLE nap 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE dolgozat
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE csere 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE osztalyNaplo 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE targySorszam 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE bejegyzes 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE tankorDolgozat 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE tankorNaplo 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE ora 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE orarendiOraTankor
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE hianyzas 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE orarendiOra 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE csoport 
CONVERT TO CHARACTER SET utf8 COLLATE utf8_hungarian_ci;

ALTER TABLE `uzeno` DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
ALTER TABLE `uzeno` ENGINE = InnoDB;
