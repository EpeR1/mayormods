alter table tankorDiakFelmentes drop primary key;
alter table tankorDiakFelmentes add tankorDiakFelmentesId int unsigned not null primary key auto_increment;
alter table tankorDiakFelmentes add KEY (`tankorId`,`diakId`,`beDt`,`felmentesTipus`);
