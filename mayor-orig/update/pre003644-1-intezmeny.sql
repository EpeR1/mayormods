DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3644 $$
CREATE PROCEDURE upgrade_database_3644()
BEGIN

SET NAMES utf8 COLLATE utf8_hungarian_ci;

IF EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() and TABLE_NAME='kepzesOraterv' AND COLUMN_NAME='tipus'
) THEN

    alter table `kepzesOraterv` modify `tipus` 
    enum('első nyelv','második nyelv','kötelezően választható','szabadon választható','mintatantervi',
	    'kötelezően választható 1.','kötelezően választható 2.','szabadon választható 1.','szabadon választható 2.','művészetek') 
    CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'mintatantervi';

    update `kepzesOraterv` set tipus=concat(tipus,' 1.') where tipus in ('kötelezően választható','szabadon választható');

    alter table `kepzesOraterv` modify `tipus` 
    enum('első nyelv','második nyelv','mintatantervi','kötelezően választható 1.','kötelezően választható 2.',
	'szabadon választható 1.','szabadon választható 2.','művészetek') CHARACTER SET utf8 COLLATE utf8_hungarian_ci DEFAULT 'mintatantervi';

END IF;

END $$
DELIMITER ;
CALL upgrade_database_3644();

