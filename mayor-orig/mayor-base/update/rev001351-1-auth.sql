
alter table accounts modify policy ENUM('private','parent','public') NOT NULL;
alter table accounts modify userAccount VARCHAR(32) NOT NULL;
