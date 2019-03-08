DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3787 $$
CREATE PROCEDURE upgrade_database_3787()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='tovabbkepzes' AND COLUMN_NAME='kategoria'
) THEN
    alter table tovabbkepzes ADD kategoria ENUM('diploma', 'szakvizsga', 'akkreditált', 'egyéb') DEFAULT 'egyéb';
    update tovabbkepzes set kategoria='akkreditált' WHERE akkreditalt=1;
    update tovabbkepzes set kategoria='egyéb' WHERE akkreditalt=0;
END IF;
END $$
DELIMITER ; $$
CALL upgrade_database_3787();
