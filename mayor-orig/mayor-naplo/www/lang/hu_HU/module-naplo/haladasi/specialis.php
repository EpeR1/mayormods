<?php
/*
    module:	naplo
    file:	specialis.php
    nyelv:	hu_HU (magyar)
*/

    define('__PAGETITLE','Speciális tanítási nap');
    define('__PAGEHELP','Speciális napot üres "tanítási" vagy "speciális tanítási" napra lehet összerakni. Ha van olyan munkaterv, melyben a megadott nap
nem "tanítási nap", akkor a hozzá tartozó osztályok tanköreinek órái nem fognak betöltődni. Ez a művelet tehát figyelembe veszi a munkaterveket. A többi
- órák törlése és nap típusának állítása, órák betöltése és órarendi hét beállítása - mindig minden munkatervre vonatkozik. 

A "Beállítás az órák törlésével" minden adott napi órát töröl és beállítja minden munkatervben a megadott nap típust (ha nincs megadva nap típus, akkor nem módosít!).
Az "Órák betöltése" minden munkatervben beállítja a megadott órarendi hetet és a nap típsát is "tanítási nap"-ra változtatja.
A "Foglalt sávok törlése" is az összes érintett sávban lévő órát törli és minden munkatervben "speciális tanítási nap"-ot állít be.');
    define('_NAP_TIPUSA','A nap típusa');
    define('_ORAK_TORLESE','Beállítás az órák törlésével');
    define('_TORLES_BIZTOS_E','Biztosan törölni akarod az adott nap összes óráját?');
    define('_ORAK_BETOLTESE','Órarendi órák betöltése');
    define('_ORARENDIHET','Órarendi hét');
    define('_BETOLTES_BIZTOS_E','Biztosan betöltsük az órarendnek megfelelő órákat és beállítsuk a nap típusát tanítási nap-ra?');
    define('_FOGLALT_SAVOK_TORLESE','Foglalt sávok törlése');
    define('_TORLENDO','Törlendő');
    define('_FOGLALT_ORA','Foglalt sáv');
    define('_TORLES','Töröl!');
    define('_ORA_TORLES_BIZTOS_E','Biztosan törölni akarod véglegesen a kijelölt sávok óráit?');
    define('_HET','hét');
    define('_NAP','nap');
    define('_OK','OK');
    define('_SZABAD_ORA','szabad sáv');
    define('_ORA','óra');
    define('_SPECIALIS_NAP','Speciális órarendű nap összeállítása');
    define('_ORAREND_OSSZEALLITASA','Órarend összeállítása');
    define('_MUNKATERV','Munkaterv');
    define('_TIPUS','Típus');
    define('_MEGJEGYZES','Megjegyezés');


?>
