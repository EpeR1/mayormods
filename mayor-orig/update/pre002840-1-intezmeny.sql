ALTER TABLE tankorTipus ADD tankorJel varchar(3) DEFAULT NULL;
UPDATE tankorTipus SET tankorJel='I' WHERE tankorTipus.rovidNev='első nyelv';
UPDATE tankorTipus SET tankorJel='II' WHERE tankorTipus.rovidNev='második nyelv';
