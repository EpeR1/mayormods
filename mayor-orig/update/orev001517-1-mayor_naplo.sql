-- Házszám: 8 --> 20 karakter
alter table intezmeny modify cimHazszam varchar(20);
-- KözterületJelleg: új típusok
alter table intezmeny modify cimKozteruletJelleg ENUM('út','utca','útja','körút','tér','tere','körtér','köz','fasor','árok','lejtő','lakótelep','sétány',
'dűlő','átjáró','bástya','bástyája','domb','dűlőút','egyéb','elágazás','erdősor','fasora','forduló','főút','gát','hajóállomás','határsor',
'határút','hegy','helyrajzi szám','hídfő','játszótér','kapu','kert','kikötő','kilátó','körönd','körvasútsor','lakónegyed','lépcső','liget',
'major','mélykút','ösvény','park','parkja','part','piac','pihenő','puszta','rakpart','repülőtér','rét','sétaút','sor','sugárút','sziget','tanya',
'telep','udvar','üdülőpart','várkerület','vasútállomás','völgy','zug') NULL;
