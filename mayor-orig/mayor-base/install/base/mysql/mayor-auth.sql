
DROP DATABASE IF EXISTS %MYSQL_AUTH_DB%;
CREATE DATABASE %MYSQL_AUTH_DB% CHARACTER SET utf8 DEFAULT COLLATE utf8_hungarian_ci;

GRANT ALL ON %MYSQL_AUTH_DB%.* TO '%MYSQL_AUTH_USER%'@'localhost' IDENTIFIED BY '%MYSQL_AUTH_PW%';

USE %MYSQL_AUTH_DB%;

CREATE TABLE `mayorUpdateLog` (
  `scriptFile` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT '',
  `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`scriptFile`,`dt`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;

CREATE TABLE accounts (
    uid INT UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    policy ENUM('private','parent','public') NOT NULL,
    userAccount VARCHAR(32) NOT NULL,
    userCn VARCHAR(64),
    userPassword VARBINARY(40) DEFAULT NULL,
    studyId VARCHAR(12),
    mail VARCHAR(64),
    telephoneNumber VARCHAR(32),
    shadowLastChange INT UNSIGNED,
    shadowMin TINYINT UNSIGNED,
    shadowMax TINYINT UNSIGNED,
    shadowWarning TINYINT UNSIGNED,
    shadowInactive TINYINT UNSIGNED,
    shadowExpire INT UNSIGNED,
    UNIQUE KEY (userAccount,policy)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE groups (
    gid INT UNSIGNED PRIMARY KEY AUTO_INCREMENT NOT NULL,
    groupCn VARCHAR(32),
    groupDesc VARCHAR(64),
    policy VARCHAR(10)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;

CREATE TABLE members (
    uid INT UNSIGNED NOT NULL,
    gid INT UNSIGNED NOT NULL,
  INDEX members_uid(uid),
  INDEX members_gid(gid),
  FOREIGN KEY(uid)
    REFERENCES accounts(uid)
      ON DELETE CASCADE
      ON UPDATE CASCADE,
  FOREIGN KEY(gid)
    REFERENCES groups(gid)
      ON DELETE CASCADE
      ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

