ALTER TABLE kepzesOraterv ADD kepzesId smallint unsigned not null AFTER kepzesOratervId;
ALTER TABLE kepzesOraterv ADD FOREIGN KEY(kepzesId) REFERENCES kepzes(kepzesId) ON DELETE CASCADE ON UPDATE CASCADE;
alter table kepzes ADD kezdoEvfolyam tinyint unsigned null;
alter table kepzes ADD zaroEvfolyam tinyint unsigned null;
