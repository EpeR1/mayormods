ALTER TABLE terem ADD telephely varchar(64) null;

UPDATE tankor SET kovetelmeny='jegy' WHERE kovetelmeny IS NULL;
ALTER TABLE tankor MODIFY kovetelmeny ENUM('jegy','féljegy','aláírás','százalékos',
'három szintű','egyedi felsorolás','szöveges','magatartás','szorgalom');
ALTER TABLE targy ADD evkoziKovetelmeny ENUM('jegy','féljegy','aláírás','százalékos',
'három szintű','egyedi felsorolás','szöveges','magatartás','szorgalom') DEFAULT 'féljegy';
ALTER TABLE targy ADD zaroKovetelmeny ENUM('jegy','féljegy','aláírás','százalékos',
'három szintű','egyedi felsorolás','szöveges','magatartás','szorgalom') DEFAULT 'jegy';
UPDATE targy SET evkoziKovetelmeny='féljegy';
UPDATE targy SET zaroKovetelmeny='jegy';
UPDATE targy SET targyJelleg='nyelv' WHERE targyJelleg IS NULL AND targyNev like '%nyelv%' AND targyNev NOT LIKE '%magyar%';