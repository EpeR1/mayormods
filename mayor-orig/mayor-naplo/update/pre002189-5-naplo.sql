ALTER TABLE hianyzas ADD tankorTipusId int unsigned NULL DEFAULT NULL;
UPDATE hianyzas SET tankorTipusId=1 WHERE tankorTipus='tanórai';
UPDATE hianyzas SET tankorTipusId=4 WHERE tankorTipus='tanórán kívüli';
UPDATE hianyzas SET tankorTipusId=2 WHERE tankorTipus='első nyelv';
UPDATE hianyzas SET tankorTipusId=3 WHERE tankorTipus='második nyelv';
UPDATE hianyzas SET tankorTipusId=10 WHERE tankorTipus='egyéni foglalkozás';
UPDATE hianyzas SET tankorTipusId=4 WHERE tankorTipus='délutáni';
