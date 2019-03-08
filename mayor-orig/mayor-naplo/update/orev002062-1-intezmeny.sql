ALTER TABLE `zaroJegy` MODIFY `szemeszter` tinyint(3) unsigned DEFAULT NULL;
-- UPDATE `zaroJegy` SET szemeszter = NULL WHERE zaroJegyId IN (SELECT DISTINCT zaroJegyId FROM vizsga);
