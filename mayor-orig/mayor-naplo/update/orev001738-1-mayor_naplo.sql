DROP TABLE IF EXISTS `settings`;
CREATE TABLE `settings` (
  `userAccount` varchar(32) NOT NULL,
  `policy` enum('private','parent','public') DEFAULT 'private' NOT NULL,
  `intezmeny` varchar(16) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;