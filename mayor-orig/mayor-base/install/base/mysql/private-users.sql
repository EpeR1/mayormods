USE %MYSQL_PRIVATE_DB%;

INSERT INTO accounts 
    (policy, userAccount, userCn, userPassword, shadowLastChange, shadowMin, shadowMax, shadowWarning, shadowInactive, shadowExpire)
    VALUES ('private','mayoradmin','MaYoR Adminisztrátor',SHA('jelszo'),(TO_DAYS(now())-TO_DAYS("1970-01-01"))-80,2,80,10,0,NULL);
SET @uid = (SELECT uid FROM accounts WHERE userAccount='mayoradmin');
INSERT INTO groups (groupCn,groupDesc,policy) VALUES ('useradmin','Adminisztrátor','private'),('tanar','Tanárok','private'),
	    ('diak','Diákok','private'),('titkarsag','Titkárság','private'),('egyeb','Egyéb','private');
SET @gid = (SELECT gid FROM groups WHERE groupCn='useradmin');
INSERT INTO members (uid,gid) VALUES (@uid,@gid);
SET @gid = (SELECT gid FROM groups WHERE groupCn='egyeb');
INSERT INTO members (uid,gid) VALUES (@uid,@gid);
