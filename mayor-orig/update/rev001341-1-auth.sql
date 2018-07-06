-- Auth adatbázis módosítása --

alter table accounts modify userPassword varbinary(40);
update accounts set userPassword=sha(decode(userPassword,'%MYSQL_ENCODE_STR%'));
