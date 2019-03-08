ALTER TABLE vizsga ADD felev tinyint unsigned null AFTER `evfolyam`;
-- default a második félév
UPDATE vizsga SET felev=(SELECT felev FROM zaroJegy where zaroJegyId=vizsga.zaroJegyId);