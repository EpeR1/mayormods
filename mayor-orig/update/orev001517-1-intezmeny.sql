-- Házszám: 8 --> 20 karakter
alter table szulo modify cimHazszam varchar(20);
alter table diak modify lakhelyHazszam varchar(20);
alter table diak modify tartHazszam varchar(20);
-- Ajtó: smallint --> varchar(5)
alter table szulo modify cimAjto varchar(5);
alter table diak modify lakhelyAjto varchar(5);
alter table diak modify tartAjto varchar(5);
-- közterületJelleg: új tíőusok
alter table szulo modify cimKozteruletJelleg ENUM('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány',
'dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor',
'határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','lakónegyed','lépcső','liget',
'major','mélykút','ösvény','park','parkja','part','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya',
'telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') NULL;
alter table diak modify lakhelyKozteruletJelleg ENUM('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány',
'dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor',
'határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','lakónegyed','lépcső','liget',
'major','mélykút','ösvény','park','parkja','part','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya',
'telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') NULL;
alter table diak modify tartKozteruletJelleg ENUM('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány',
'dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor',
'határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','lakónegyed','lépcső','liget',
'major','mélykút','ösvény','park','parkja','part','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya',
'telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') NULL;
-- Új mezők
alter table diak add column mobil VARCHAR(64) NULL after telefon;
alter table diak add column tartozkodasiOkiratSzam VARCHAR(16) NULL after szemelyiIgazolvanySzam;
alter table szulo add column munkahely VARCHAR(128) NULL after foglalkozas;
