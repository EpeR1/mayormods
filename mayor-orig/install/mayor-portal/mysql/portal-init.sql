
USE %MYSQL_PRIVATE_DB%;

SET @uid = (SELECT uid FROM accounts WHERE userAccount='mayoradmin');
DELETE FROM groups WHERE groupCn='hirekadmin';
INSERT INTO groups (groupCn,groupDesc,policy) VALUES ('hirekadmin','Hír adminisztrátor','private');
SET @gid = (SELECT gid FROM groups WHERE groupCn='hirekadmin');
DELETE FROM members WHERE uid=@uid AND gid=@gid;
INSERT INTO members (uid,gid) VALUES (@uid,@gid);

INSERT INTO %MYSQL_PORTAL_DB%.hirek (kdt,vdt,class,lang,cim,txt,owner,flag,csoport) VALUES (
	CURDATE(), CURDATE() + INTERVAL 7 DAY, 6, 'hu_HU', 
	'Köszöntünk a MaYoR-ban!','A MaYoR keretrendszer és elektronikus napló telepítése sikeresen lezajlott, 
	következhet az adatok betöltése és a rendszer konfigurálása. Ehhez segítséget a 
	<a href="http://wiki.mayor.hu/doku.php?id=hogyan:telepites#adatok_felvitele">MaYoR Wiki</a>-ben találsz.', 
	'mayoradmin', 1, 'egyéb'
);
