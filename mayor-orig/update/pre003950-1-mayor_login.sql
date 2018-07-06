
alter table mayorKeychain change `naploUrl` `url` varchar(255) COLLATE utf8_hungarian_ci NOT NULL;
alter table mayorKeychain add column `nodeId` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000' first;
alter table mayorKeychain change column `mayorTipus` `nodeTipus` enum('intézmény','fenntartó','backup','fejlesztői','controller','boss') COLLATE utf8_hungarian_ci DEFAULT 'intézmény' after `nodeId`;
update mayorKeychain set nodeTipus='controller',url='https://www.mayor.hu' where OMKod='09862967';
alter table mayorKeychain modify column `nodeTipus` enum('intézmény','fenntartó','backup','fejlesztői','controller') COLLATE utf8_hungarian_ci DEFAULT 'intézmény' after `nodeId`;

update mayorKeychain set `nodeId`=`OMKod` where `OMKod`<>'00000000' and `nodeId`='00000000';
-- duplikátumok kiszűrése, módosítása... hogyan?
alter table mayorKeychain drop primary key;
alter table mayorKeychain add primary key (`nodeId`);

alter table mayorSsl add column `nodeId` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000' after `sslId`;
-- valahogy be kell állítani a saját nodeId-t... Esetleg publicKey segítségével a reg. szervertől...



-- -- portal-mayor.regisztracio --

-- alter table regisztracio add column `nodeId` mediumint(8) unsigned zerofill NOT NULL DEFAULT '00000000' after `regId`;
-- alter table regisztracio add column `nodeTipus` enum('intézmény','fenntartó','backup','fejlesztői','controller') COLLATE utf8_hungarian_ci DEFAULT 'intézmény' after `nodeId`;
-- alter table regisztracio change `naploUrl` `url` varchar(255) COLLATE utf8_hungarian_ci NOT NULL;

-- update regisztracio set nodeTipus='controller',url='https://www.mayor.hu' where OMKod='09862967';
-- -- a valid mező nem tudom mire kell...
-- update regisztracio set `nodeId`=`OMKod` where `OMKod`<>'00000000' and `nodeId`='00000000';
-- alter table regisztracio add unique key (`nodeId`);
-- -- publicKey tördelése --
-- update regisztracio set publicKey = concat_ws('\n','-----BEGIN PUBLIC KEY-----',mid(publicKey,27,64),mid(publicKey,91,64),mid(publicKey,155,64),
-- mid(replace(publicKey,'-----END PUBLIC KEY-----',''),219,64),nullif(mid(replace(publicKey,'-----END PUBLIC KEY-----',''),283,64),''),
-- nullif(mid(replace(publicKey,'-----END PUBLIC KEY-----',''),347,64),''),nullif(mid(replace(publicKey,'-----END PUBLIC KEY-----',''),411,64),''),
-- '-----END PUBLIC KEY-----') where publicKey not like '%\n%';

