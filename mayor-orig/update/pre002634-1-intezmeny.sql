ALTER TABLE kosziPont ADD kosziPontId int(10) unsigned not null FIRST;
ALTER TABLE kosziPont ADD KEY (kosziPontId);
ALTER TABLE kosziPont MODIFY kosziPontId int(10) unsigned not null auto_increment;