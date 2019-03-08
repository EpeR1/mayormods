ALTER TABLE tankorDiakFelmentes DROP PRIMARY KEY;
ALTER TABLE tankorDiakFelmentes ADD PRIMARY KEY (tankorId,diakId,beDt,felmentesTipus);
ALTER TABLE tankorDiakFelmentes ADD nap tinyint unsigned NULL;
ALTER TABLE tankorDiakFelmentes ADD ora tinyint unsigned NULL;
