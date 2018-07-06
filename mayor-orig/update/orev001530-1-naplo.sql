alter table uzeno engine=InnoDB;
CREATE TABLE `uzenoFlagek` (
  `mId` int(10) unsigned NOT NULL,
  `Id` int(10) unsigned NOT NULL,
  `Tipus` enum('diak','szulo','tanar') default NULL,
  `flag` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`mId`,`Id`,`Tipus`),
  FOREIGN KEY(mId)
    REFERENCES uzeno(mId)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
