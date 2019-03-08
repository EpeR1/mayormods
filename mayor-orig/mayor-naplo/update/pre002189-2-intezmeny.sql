-- érdemjegyet kap
ALTER TABLE `tankorDiak` ADD erdemjegyet enum('kap','nem kap') DEFAULT 'kap';
UPDATE `tankorDiak` SET `erdemjegyet`='kap';
