CREATE TABLE `zaroJegyr2055` SELECT * FROM `zaroJegy`;
ALTER TABLE `zaroJegy` ADD `jegySzemeszter` tinyint(3) unsigned DEFAULT NULL AFTER `evfolyam`;
UPDATE `zaroJegy` SET jegySzemeszter=szemeszter;
