
DELIMITER $$
 DROP PROCEDURE IF EXISTS upgrade_database_3396c $$
 CREATE PROCEDURE upgrade_database_3396c()
 -- READS SQL DATA
 BEGIN
    DECLARE done INT DEFAULT FALSE;
    DECLARE DB VARCHAR(255);
    DECLARE statement TEXT;
    DECLARE cur1
        CURSOR FOR
	SELECT DISTINCT TABLE_SCHEMA FROM information_schema.TABLES WHERE TABLE_SCHEMA LIKE 'mayor\_%';
    DECLARE CONTINUE HANDLER FOR NOT FOUND SET done = TRUE;

    OPEN cur1;
    lo: LOOP
	IF done = TRUE THEN
	    LEAVE lo;
	END IF;

        FETCH cur1 INTO DB;

	IF NOT EXISTS (
	    SELECT * FROM information_schema.TABLES WHERE TABLE_SCHEMA=DB COLLATE utf8_general_ci AND TABLE_NAME='mayorUpdateLog'
	) THEN
	    SET @statement = CONCAT('CREATE TABLE ',DB,'.`mayorUpdateLog` (
	      `scriptFile` varchar(255) COLLATE utf8_hungarian_ci NOT NULL DEFAULT \'\',
	      `dt` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
	      PRIMARY KEY (`scriptFile`,`dt`)
	    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_hungarian_ci;');
	    PREPARE command FROM @statement;
	    EXECUTE command;
	    DEALLOCATE PREPARE  command;
	END IF;

    END LOOP;
    CLOSE cur1;
 END; $$
DELIMITER ; $$

CALL upgrade_database_3396c();
