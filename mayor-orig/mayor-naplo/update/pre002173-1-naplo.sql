drop procedure if exists update2173;
DELIMITER //
create procedure update2173()
BEGIN
    DECLARE done INT DEFAULT 0;
    SELECT count(`table_schema`) AS darab from `information_schema`.`columns` where `table_name`='uzeno' and `column_name`='cimzettTipus' 
	and `table_schema` = (SELECT database())
	and `column_type`!="enum('diak','szulo','tanar','tankor','tankorSzulo','munkakozosseg','osztaly','osztalySzulo','osztalyTanar')"
    INTO done;
	IF done = 0 THEN 
	    SELECT "nothing to do here",database();
	ELSE
	    ALTER TABLE `uzeno` MODIFY `cimzettTipus` enum('diak','szulo','tanar','tankor','tankorSzulo','munkakozosseg','osztaly','osztalySzulo','osztalyTanar') NULL;
	    UPDATE `uzeno` SET `cimzettTipus` = "osztalyTanar" WHERE `cimzettTipus`='';
	END IF;
    SELECT done;
END; //
DELIMITER ; //
CALL update2173();
-- A script egyébként felesleges bonyolult, későbbi felhasználás tesztje
drop procedure if exists update2173;
