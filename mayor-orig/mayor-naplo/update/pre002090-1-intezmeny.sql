
alter table terem drop column `telephely`;
alter table terem add column `telephelyId` tinyint(3) unsigned DEFAULT NULL;
alter table terem add constraint `terem_telephely` FOREIGN KEY (`telephelyId`) REFERENCES `telephely` (`telephelyId`) ON DELETE SET NULL ON UPDATE SET NULL;
