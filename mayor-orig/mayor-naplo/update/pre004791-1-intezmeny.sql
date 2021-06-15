DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_4791 $$
CREATE PROCEDURE upgrade_database_4791()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='kepzesOraterv' AND COLUMN_NAME='tipus'
) THEN

    alter table `kepzesOraterv` modify `tipus` 
    enum('első nyelv','második nyelv','mintatantervi','kötelezően választható 1.','kötelezően választható 2.',
	'szabadon választható 1.','szabadon választható 2.','művészetek','természettudomány') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'mintatantervi';

END IF;

END $$
DELIMITER ;
CALL upgrade_database_4791();

-- | Warning | 3719 | 'utf8' is currently an alias for the character set UTF8MB3, but will be an alias for UTF8MB4 in a future release. Please consider using UTF8MB4 in order to be unambiguous. |
-- | Warning | 3778 | 'utf8_hungarian_ci' is a collation of the deprecated character set UTF8MB3. Please consider using UTF8MB4 with an appropriate collation instead.      
