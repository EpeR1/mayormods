CREATE TABLE `audit` (
    `auditId` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
    `dt` DATETIME NOT NULL,
    `userAccount` VARCHAR(32) NULL,
    `psf` VARCHAR(255) NULL,
    `params` VARCHAR(255) NULL,
    `fejlec` VARCHAR(255) NULL,
    `szoveg` TEXT NULL,
    `felelosCsoport` VARCHAR(64) NULL,
    `felelos` VARCHAR(64) NULL,
    `lezarasDt` DATETIME NULL,
     PRIMARY KEY  (`auditId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;