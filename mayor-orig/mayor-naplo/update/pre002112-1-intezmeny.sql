-- telephely collate bug javítása

alter table mayor_naplo.intezmeny CONVERT TO CHARACTER SET 'utf8' COLLATE 'utf8_hungarian_ci';
alter table mayor_naplo.intezmeny MODIFY `rovidNev` varchar(16) character set 'utf8' collate 'utf8_hungarian_ci' NOT NULL;
alter table mayor_naplo.intezmeny MODIFY    `nev` varchar(128) character set  'utf8' collate 'utf8_hungarian_ci' NOT NULL;
alter table mayor_naplo.intezmeny MODIFY    `cimHelyseg` varchar(16) character set 'utf8' collate 'utf8_hungarian_ci' default NULL;
alter table mayor_naplo.intezmeny MODIFY    `cimIrsz` varchar(8) character set  'utf8' collate 'utf8_hungarian_ci'  default NULL;
alter table mayor_naplo.intezmeny MODIFY    `cimKozteruletNev` varchar(32) character set  'utf8' collate 'utf8_hungarian_ci'  default NULL;
        
insert into telephely (rovidNev,nev,alapertelmezett,cimHelyseg,cimIrsz,cimKozteruletNev,cimKozteruletJelleg,cimHazszam,telefon,fax,email,honlap)                                 
select intezmeny.rovidNev,intezmeny.nev,intezmeny.alapertelmezett,intezmeny.cimHelyseg,intezmeny.cimIrsz,intezmeny.cimKozteruletNev,                                             
intezmeny.cimKozteruletJelleg,intezmeny.cimHazszam,intezmeny.telefon,intezmeny.fax,intezmeny.email,intezmeny.honlap                                                              
from mayor_naplo.intezmeny left join telephely ON (mayor_naplo.intezmeny.nev COLLATE utf8_hungarian_ci = telephely.nev)                                                          
where intezmeny.rovidNev='%INTEZMENY%' collate utf8_hungarian_ci and telephely.nev collate utf8_hungarian_ci is null;
