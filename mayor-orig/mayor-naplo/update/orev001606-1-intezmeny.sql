-- Evkozi kovetelmeny
ALTER TABLE tankor MODIFY kovetelmeny ENUM('jegy','féljegy','aláírás','százalékos',
'három szintű','egyedi felsorolás','szöveges','magatartás','szorgalom','nincs');
ALTER TABLE targy MODIFY evkoziKovetelmeny ENUM('jegy','féljegy','aláírás','százalékos',
'három szintű','egyedi felsorolás','szöveges','magatartás','szorgalom','nincs') DEFAULT 'féljegy';
update targy SET evkoziKovetelmeny='nincs' WHERE targyJelleg='magatartás';
update targy SET evkoziKovetelmeny='nincs'  WHERE targyJelleg='szorgalom';
