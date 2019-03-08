-- ha a 2086-os nem vette volna fel a telephelyet...

-- insert into telephely (rovidNev,nev,alapertelmezett,cimHelyseg,cimIrsz,cimKozteruletNev,cimKozteruletJelleg,cimHazszam,telefon,fax,email,honlap) 
--  select intezmeny.rovidNev,intezmeny.nev,intezmeny.alapertelmezett,intezmeny.cimHelyseg,intezmeny.cimIrsz,intezmeny.cimKozteruletNev,
--         intezmeny.cimKozteruletJelleg,intezmeny.cimHazszam,intezmeny.telefon,intezmeny.fax,intezmeny.email,intezmeny.honlap 
--  from mayor_naplo.intezmeny left join telephely using (nev)
--  where intezmeny.rovidNev='%INTEZMENY%' and telephely.nev is null;

-- insert into telephely (rovidNev,nev,alapertelmezett,cimHelyseg,cimIrsz,cimKozteruletNev,cimKozteruletJelleg,cimHazszam,telefon,fax,email,honlap)
-- select intezmeny.rovidNev,intezmeny.nev,intezmeny.alapertelmezett,intezmeny.cimHelyseg,intezmeny.cimIrsz,intezmeny.cimKozteruletNev,
-- intezmeny.cimKozteruletJelleg,intezmeny.cimHazszam,intezmeny.telefon,intezmeny.fax,intezmeny.email,intezmeny.honlap
-- from mayor_naplo.intezmeny left join telephely ON (mayor_naplo.intezmeny.nev COLLATE utf8_hungarian_ci = telephely.nev)
-- where intezmeny.rovidNev='%INTEZMENY%' collate utf8_hungarian_ci and telephely.nev collate utf8_hungarian_ci is null;

-- ezt később javítjuk