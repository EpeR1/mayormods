DELIMITER $$
DROP PROCEDURE IF EXISTS upgrade_database_3766 $$
CREATE PROCEDURE upgrade_database_3766()
BEGIN
IF NOT EXISTS (
    SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND COLUMN_NAME='hetiLekotottMaxOraszam'
) THEN
    alter table tanar 
	ADD hetiLekotottMinOraszam decimal(3,1) DEFAULT 0 ,
	ADD hetiLekotottMaxOraszam decimal(3,1) DEFAULT 0,
	ADD hetiKotottMaxOraszam decimal(3,1) DEFAULT 0;
END IF;

END $$
DELIMITER ; $$
CALL upgrade_database_3766();
