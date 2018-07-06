
alter table hianyzasOsszesites 
add column `gyakorlatIgazolt` smallint(5) unsigned DEFAULT NULL,
add column `gyakorlatIgazolatlan` smallint(5) unsigned DEFAULT NULL,
add column `gyakorlatKesesPercOsszeg` smallint(5) unsigned DEFAULT NULL,
add column `elmeletIgazolt` smallint(5) unsigned DEFAULT NULL,
add column `elmeletIgazolatlan` smallint(5) unsigned DEFAULT NULL,
add column `elmeletKesesPercOsszeg` smallint(5) unsigned DEFAULT NULL;
