ALTER TABLE jegy MODIFY jegyTipus ENUM('jegy','féljegy','aláírás','százalékos',
'három szintű','egyedi felsorolás','szöveges','magatartás','szorgalom');
UPDATE jegy SET jegyTipus='féljegy' WHERE jegy IN ('1.5','2.5','3.5','4.5');