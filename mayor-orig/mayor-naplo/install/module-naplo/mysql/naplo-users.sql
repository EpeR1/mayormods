USE %MYSQL_PRIVATE_DB%;

INSERT INTO groups (groupCn,groupDesc,policy) VALUES
('naploadmin','Napló adminisztrátorok','private'),('vezetoseg','Vezetőség','private'),
                ('diakadmin','Diák adminisztrátorok','private'),('uzenoadmin','Üzenő adminisztrátorok','private');
SET @uid = (SELECT uid FROM accounts WHERE userAccount='mayoradmin');
SET @gid = (SELECT gid FROM groups WHERE groupCn='naploadmin');
INSERT INTO members (uid,gid) VALUES (@uid,@gid);
