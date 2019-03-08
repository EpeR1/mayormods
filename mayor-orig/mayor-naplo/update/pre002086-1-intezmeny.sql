
-- intezmeny.telephely létrehozása

CREATE TABLE telephely (
  telephelyId TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
  rovidNev VARCHAR(16) COLLATE utf8_hungarian_ci NOT NULL,
  nev VARCHAR(128) COLLATE utf8_hungarian_ci NOT NULL,
  alapertelmezett TINYINT UNSIGNED NOT NULL DEFAULT '0',
  cimHelyseg VARCHAR(16) NULL,
  cimIrsz VARCHAR(8) NULL,
  cimKozteruletNev VARCHAR(32) NULL,
  cimKozteruletJelleg ENUM('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány','dűlő',
    'átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor',
    'határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','lakónegyed','lépcső',
    'liget','major','mélykút','ösvény','park','parkja','part','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor',
    'sugárút','sziget','tanya','telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') NULL,
  cimHazszam VARCHAR(20) NULL,
  telefon VARCHAR(64) NULL,
  fax VARCHAR(64) NULL,
  email VARCHAR(96) NULL,
  honlap VARCHAR(96) NULL,
  PRIMARY KEY(telephelyId)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

insert into telephely (rovidNev,nev,alapertelmezett,cimHelyseg,cimIrsz,cimKozteruletNev,cimKozteruletJelleg,cimHazszam,telefon,fax,email,honlap)
  select rovidNev,nev,alapertelmezett,cimHelyseg,cimIrsz,cimKozteruletNev,cimKozteruletJelleg,cimHazszam,telefon,fax,email,honlap from mayor_naplo.intezmeny
  where rovidNev='%INTEZMENY%';
