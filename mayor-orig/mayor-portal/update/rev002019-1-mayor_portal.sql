create table `kerdoivSzavazott` (`kerdoivId` int unsigned not null, `policy` ENUM('private','parent'), `userAccount` varchar(60))
ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE utf8_hungarian_ci;
alter table `kerdesek` add `policy` set('private','parent','public') DEFAULT 'public';
