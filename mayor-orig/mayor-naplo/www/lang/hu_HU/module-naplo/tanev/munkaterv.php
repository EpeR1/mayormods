<?php
/*
    Module:	napló
    File:	munkaterv.php
    Nyelv:	hu_HU (magyar)
*/

    define('__PAGETITLE','Éves munkaterv');
    define('__PAGEHELP','Éves munkaterv inicializálásához és módosításához használt oldal.');
    define('_EVES_MUNKATERV','Éves munkaterv');
    define('_OK','OK');
    define('_OSSZES_MUNKANAP','összes munkanap');
    define('_MODOSIT', 'módosít &gt;&gt;');
    define('_ORARENDI_HET', 'órarendi hét');

    define('_NAPOKINIT','Munkaterv inicializálása');
    define('_MAGYARAZAT','FIGYELEM! A művelet törli az esetleg már meglévő munkatervet/munkaterveket és létrehoz egy új alapértelmezettet!</p>
	<p>
	Adja meg, hogy hány hetes órarendet használnak a tanév során!
	Ha minden héten azonos az órarend, akkor válassza az "1" értéket, ha A és B hét
	váltakozik, akkor a "2" a helyes érték, de lehet, hogy akár hat különböző órarendű
	hét ismétlődik ciklikusan, hiszen egy-két óra eltérése esetén is különbözőnek
	számítanak az órarendek...
	</p><p>
	Adja meg a tanítási napok és tanítás nélküli munkanapok számát (a rendeletben meghatározottak szerint),
	valamint - középiskola esetén - a végzősök utolsó tanítási napjának dátumát!>
    ');
    define('_TANITASI_NAPOK_SZAMA','Tanítási napok száma');
    define('_TANITAS_NELKULI_MUNKANAPOK_SZAMA','Tanításnélküli munkanapok száma');
    define('_VEGZOS_ZARAS_DT','Végzősök utolsó tanítási napja');
    define('_NAPOK_SZAMA','Napok száma');
    define('_ELTERO_MUNKATERVEK','Eltérő munkatervek');
    define('_MUNKATERV_OSZTALY','Munkaterv-osztály összerendelés');
    define('_MO_MAGYARAZAT','Az osztályokat hozzá kell rendelnünk egy-egy munkatervhez. Kezdetben minden osztály az alapértelmezett mukatervhez van rendelve.');
    define('_HETHOZZARENDELES','Órarendi hetek napokhozrendelése');
    define('_HH_MAGYARAZAT','Adja meg, hogy hány hetes órarendet használnak az adott időszak során!
	Ha minden héten azonos az órarend, akkor válassza az "1" értéket, ha A és B hét
	váltakozik, akkor a "2" a helyes érték, de lehet, hogy akár hat különböző órarendű
	hét ismétlődik ciklikusan, hiszen egy-két óra eltérése esetén is különbözőnek
	számítanak az órarendek... A program csak az órarendi hetek napokhozrendelését 
	változtatja meg, a napok típusát, egyéb paramétereit nem módosítja. A hetek hozzárendelése
	mindig az egyes héttől indul. A módosítás mindig az összes munkatervre vonatkozik!
    ');
    define('_UJ_MUNKATERV','Új munkaterv létrehozása');
    define('_UM_MAGYARAZAT','Az aktuális munkaterv másolataként új munkatervet készíthetünk. A munkaterveket 
	osztályokhoz rendelhetjük, minden osztályhoz pontosan egyet.');
    define('_MUNKATERV_NEVE','Munkaterv neve');
    define('_ORARENDIHETEKSZAMA','Órarendi hetek száma');
    define('_DATUM','Módosítandó időszak');
    define('_ELOIRT','Előírt');
    define('_TENYLEGES','Tényleges');

/*
    define('_TANITASI_NAP','tanítási nap');
    define('_SPECIALIS_TANITASI_NAP','speciális tanítási nap');
    define('_TANITAS_NELKULI_MUNKANAP','tanítás nélküli munkanap');
    define('_TANITASI_SZUNET','tanítási szünet');
*/
?>
