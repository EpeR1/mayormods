ALTER TABLE `groups` CHANGE groupId groupCn varchar(32);
ALTER TABLE `groups` CHANGE groupName groupDesc varchar(32);
ALTER TABLE `members` DROP `type`;
