ALTER TABLE kerelem ADD telephelyId tinyint unsigned default null;
ALTER TABLE kerelem ADD jovahagyasAccount varchar(32) default null AFTER jovahagyasDt;