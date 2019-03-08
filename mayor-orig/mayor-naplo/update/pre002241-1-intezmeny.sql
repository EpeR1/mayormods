-- A *Helyseg mezők megnövelése
ALTER TABLE `telephely` MODIFY `cimHelyseg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL;
ALTER TABLE `szulo` MODIFY `cimHelyseg`  varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL;
ALTER TABLE `diak` MODIFY `lakhelyHelyseg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL;
ALTER TABLE `diak` MODIFY `tartHelyseg` varchar(32) COLLATE utf8_hungarian_ci DEFAULT NULL;
