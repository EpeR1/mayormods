<?php 

//////////////////////// Figyelem! Az alábbi config érvényes a Mayor-Nextcloud scripthez, nem a "mayor-nextcloud.php" fejléce!!  ////////////////////////////
$cfg = array();

$cfg['db_host'] = "localhost";
$cfg['db_port'] = "3306";
$cfg['db_user'] = "root";
$cfg['db_pass'] = "";
$cfg['db_m2n_db'] = "mayor_to_nextcloud";
$cfg['db_m2n_prefix'] = "m2n_";
$cfg['db_nxt_dbname'] = "Nextcloud";
$cfg['db_nxt_prefix'] = "oc_";
//$cfg['db_mayor_host'] = ""; 
//$cfg['db_mayor_port'] = "";
//$cfg['db_mayor_user'] = ""; 
//$cfg['db_mayor_pass'] = "";

$cfg['min_evfolyam'] = 7;
$cfg['isk_rovidnev'] = "rovid";  
$cfg['csoport_prefix'] = "(tk) ";
$cfg['default_email'] = "indulo@iskola.hu";
$cfg['default_passw'] = "EHYmGktzrdfS7wxJR6DFqxjJ";
$cfg['always_set_diak_quota'] = false;
$cfg['default_quota'] = "10GB";
$cfg['diak_quota']    = "2GB"; 
$cfg['min_osztalyok'] =  array(); 	//pl:  array('9.a','11.a');
$cfg['csoportnev_hossz'] = 40;
$cfg['felhasznalo_hossz'] = 45;
$cfg['default_lang']  = "hu";
$cfg['manage_groups'] = false;
$cfg['manage_groupdirs'] = false;   //Foglalkozzon-e a script a tankörmappákkal
$cfg['groupdir_users'] = array("naplo_robot","123abcd");  //Ha a tömb üres, akkor az összes tanárral dolgozik.
$cfg['groupdir_prefix'] = "Iskolai Távoktatás";
$cfg['mindenki_csop'] = "naplós_felhasználók";
$cfg['mindenki_tanar'] = "naplós_tanárok";
$cfg['mindenki_diak'] = "naplós_diákok";
$cfg['allapot_tartas'] =  "2018-06-14";	// Ha nem kell, akkor állítsd át "1970-01-01"-re.
$cfg['megfigyelo_user'] = "naplo_robot";
$cfg['kihagy'] = array();   //pl:  array('Trap.Pista', 'Ebeed.Elek', '22att')
$cfg['verbose'] = 3 ;  


$cfg['ldap_server'] = "ldaps://windows.iskola.hu:636";      //Jelszóváltoztatást csak TLS/SSL porton enged a windows!
$cfg['ldap_reqCert'] = "allow";                             // Ellenőrizze-e a certet: "true" "allow" "never"
$cfg['ldap_baseDn']   =   "DC=ad,DC=iskola,DC=hu";
$cfg['ldap_rootBindDn'] = "CN=LDAP_ADATCSERE_ADMIN,CN=Users,DC=ad,DC=iskola,DC=hu";
$cfg['ldap_rootBindPw'] = "<password>";
$cfg['ldap_pageSize']   = 100;
$cfg['ld_username']       = "sAMAccountName";
$cfg['ld_oId']            = "serialNumber";
$cfg['ld_employeeId']     = "employeeNumber";
$cfg['ld_osztalyJel']     = "department";
$cfg['ld_viseltNevElotag'] = "initials";
$cfg['ld_viseltCsaladinev'] = "sn";
$cfg['ld_viseltUtonev']   = "givenName";
$cfg['ld_lakhelyOrszag']  = "co";
$cfg['ld_lakhelyHelyseg'] = "l";
$cfg['ld_lakhelyIrsz']    = "postalCode";
$cfg['ld_lakHely']        = "streetAddress";
$cfg['ld_telefon']        = "homePhone";
$cfg['ld_mobil']          = "mobile";
$cfg['ld_statusz']        = "company";
$cfg['ld_beoszt']         = "title";
$cfg['ld_nxtQuota']       = "description";

$occ_path = "/var/www/nextcloud/"; 
$occ_user = "www-data";


/*
Nextcloud(13+) - Mayor script

Ezen script segítségével a Mayor-naplóból tudunk felhasználókat és csoportokat importálni a Nextcloud felhőbe,
létrehozva ezzel a saját, iskolai felhőszolgáltatásunkat.

Ez a script lényegében annyit csinál, hogy a mysql-ből lekérdezi a mayor diák-tankör-tanár kapcsolatokat,
majd a tankörök nevei alapján létrehozza a csoportokat és a felhasználókat a Nextcloud szerveren,
majd belépteti ezen csoportokba a diákokat, és tanárokat,  megspórolva ezzel a kézi (egyesével történő) feltöltést,
és egy csomó időt az adminisztrátornak.

Támogatja a külön, illetve az egy, közös szerverre történő telepítését a mayornak és a nextcloud-nak. **(lásd: Szeparációs lehetőségek rész.)
A Nextcloud 13-mas és újabb verzióival használható.

FONTOS!
  Legalább "php7.0" és "Apache 2.4" kell hozzá!
 


Beállítása az alábbiak szerint: (egy lehetséges elrendezés)

-(I.)   Először telepítsünk föl egy Nextcloud(legalább 13-mas verzió) szervert egy Debian (9-es vagy magasabb verzió) szerverre,
        a Nextcloudnak szüksége van e-mail küldés (smtp) szolgáltatásra is. (ez lehet külső, pl.: google)
        Bővebb leírást a telepítésről az interneten "Nextcloud Admin Manual"-ra keresve találunk.

-(II.)  Helyezzük el a "mayor-nextcloud.php"-t biztos, védett helyre,a nextcloud szerveren, akár a /etc/ mappába, akár a /root könyvtárba,
        ezt később "root"-ként kell majd futtatnunk, és mysql jelszó is lehet/van benne, 
        ezért ennek megfelelően védeni kell. Állítsuk be a tulajdonost, és korlátozzuk a jogosultságokat! (chown root; chmod 600)

-(III.) Majd töltsük ki a konfigurációs fájlt (mayor-nextcloud.cfg.php) az alább felsorolt beállítások szerint!
        (Ennek is állísunk (ugyanolyan)szigorú jogosultságokat, mint a fenti esetben! )

-(IV.)  Ezután, a script, az első futtatásnál automatikusan telepíti magát. 
        Ez azt jelenti, hogy létrehoz egy mysql adatbázist saját magának, ahol később nyilvántartja, hogy melyek azoka felhasználók, amelyeket ő hozott létre. 
        Így később, a folyamatos nyilvántartás révén a script a saját maga által létrehozott felhasználókat veszi csak figyelembe,
        a többi, a más által létrehozott Nextcloud felhasználókhoz alapvetően nem nyúl.
        Ez később felülbírálható, ha a script nyilvántartási adatbázisába kézzel felvesszük az adott felhasználó nevét.




-(V.)  	A mayor-nextcloud script működése néhány mondatban:
	
	Első lépésben lekérdezi a feltételeknek megfelelő tanköröket a mayorból, (tankör-osztály évfolyama, tankör aktív-e a dátumok apaján)
	  majd ez alapján ellenőrzi, hogy a Nextcloud-ban már szerepelnek-e ezen tankörneveknek megfelelő csoportok.
	  Ha új tankör van a mayorban, akkor azt a Nextcloud-ban is létrehozza, ha egy tankört töröltek a mayorból, akkor azt a csoportot Nextcloud-ból is törli.
	Fontos!
	A csoport prefix-szel, például: "(tk) "-val kezdődő csoportokat magáénak tekinti, és ha nem találja a mayorban, akkor automatikusan töröli!
	
	Második lépésben lekérdezi a felhasználókat a mayorból, (jogviszony státusz, évfolyam, kapcsolódó tankörök)
	  csak azokat a tanárokat, illetve diákokat veszi figyelembe, akinek a státusza nem "jogviszonya lezárva", vagy nem "felvételt nyert".
 	Ha az illető még nem rendelkezik felhasználónévvel a Nextcloud-ban, akkor létrehozza, 
	- ha a mayorban, az "intezmeny_xxx.diak", illetve az "intezmeny_xxx.tanar" táblákon van beállított e-mail címe, akkor azt használja,
	- ha nincs, akkor az alapértelmezettet állítja be a Nextcloud-ba, valamint ekkor állítódik be az alapértelmezett qvóta, és a felhasználó valódi neve is.
	  (Ezeket később Rendszergazdai, és Felhasználói oldalról változtatni lehet, értelemszerűen.)
	Ha az illető már rendelkezik felhasználónévvel, de az le volt tiltva, akkor újra engedélyezi.

	Ezután egyezteti a tankör-csoport összerendeléseket, ha az illető új tankörbe került be, akkor belépteti a megfelelő csportba, 
	  ha kikerült egy tankörből, akkor a csoportból is kilépteti.

	Végül ellenőrzi a kiléptetndő felhasználókat.
	Ha velekinek a státusza a mayorban "jogviszonya lezárva"-ra változott, vagy a felhasználónevét a mayorból törölték, akkor 	
	- ha még nem lépett be soha a Nextcloudba, akkor a felhasználót fizikailag törli.
	- ha már használta a fiókját, akkor csak letiltja, azért, hogy a fájljai ne vesszenek el automatikusan. 
	(Ez, utóbbi esetben a Rendszergazdának kell külön elvégezni a törlést.)
 



- (VI.)  További Információk:

	Esetleg érdemes lehet a scriptet betenni a "cron"-ba (éjszakára), így naponta lefut, és követi napló változásait.
	(Ez esetben figyelni kell arra, hogy mayorban a tankör-diák, tankör-tanár összerendelések az év végén lejárhatnak, (pl. júni. 15-én)
	így a script futtatása júni. 16-án kitörli, letiltja az összes létrehozott mayor-os csoportot, és felhasználót a nextcloud-ból, 
	amely csak a script szept. 1-je után történő futtatásával hozható vissza, 
	ezért érdemes lehet a script automatikus futtatását átmenetileg júni. 15. és szept. 1. között felfüggeszteni.	)
	(pl: /etc/crontab -ban
		01  3   * * *     root    php -f /root/mayor-nextcloud.php						)


	A mayor által ajánlott "Vezetéknév.Keresztnév" típusú felhasználónév formátum, bár hasznos, mert könnyen megjegyezhető,
	viszont (sajnos) egyáltalán nem POSIX kompatibilis (amely csak az angol ABC betűit +pár kiegészítő karaktert engedélyez)
	így a legtöbb rendszerrel nem hozható összhangba, ezért szükség van az ékezetes karakterek lecserélésére.

	A karakterek cseréje az alábbi módon történik:
	
		á --> aa 	Á --> Aa
		ä --> ae	Ä --> Aae
		é --> ee	É --> Ee	Például:
		í --> ii	Í --> Ii	Vezetéknév.Keresztnév --> Vezeteekneev.Keresztneev
		ó --> oo	Ó --> Oo	Bőrönd.Ödön	      --> Booeroend.Oedoen
		ö --> oe	Ö --> Oe
		ő --> ooe	Ő --> Ooe	Ha a mayor felhasználónév nem tartalmaz ékezetes betűt,
		ú --> uu	Ú --> Uu	akkor nem történik csere, a felhasználónév marad az eredeti.
		ü --> ue	Ü --> Ue
		ű --> uue	Ű --> Uue

	Ezeket a karakter cseréket maga a script végzi, futás közben, a mayor felhasználónevekből,
	a létrejövő Nextcloud felhasználónév már a lecserélt változat lesz.




-(VII.)  További Paraméterek / Kapcsolók:

      --help                      :  Help kiírása. 
      --debug                     :  Ugyanaz mint a "--loglevel 100" 
      --loglevel x                :  A bőbeszédűséget/logolást tudjuk ezzel szabályozni, ekkor ez az érték érvényesül, nem a configban megadott.
      --config-file               :  Konfig fájl elérési útvonala.
      --config-print              :  A betöltött konfig kiírása.
      --set-diak-quota            :  Az összes diák qvótáját átállítja az "$cfg['diak_quota'] = x" -nél megadott értékre,  
                                       csak kézzel futtatva működik, az automatikus, napi futtatásban nicns benne.
      --create-groupdir <username>:  A távoktatást segítő könyvtárstruktúrát csak az <username> felhasználónak  hozza létre, 
                                       egyébként kapcsoló nélküli híváskor, (pl: automatikusan, a napi futásban éjjel), az összes tanárnak egyszerre.
      --manage-groups <1/0>       :  Ha 1: A felhasználókat csoportokba rendezi a MaYor tankörök szerint, ha 0, nem foglalkozik a csoportokkal.
      --manage-groupdirs <1/0>    :  Ha 1: tankörmappákat hoz létre a tankör-csoportokhoz, ha 0, nem foglalkozik vele. (kell hozzá a --manage-groups is!)
    

  FONTOS!! 
    A Nextcloud szerver "occ" parancsa elérhető kell legyen a script számára!
    Az "occ" parancs működését pl. az: (>>> sudo -u www-data php /var/www/nextcloud/occ ) kiadásával ellenőrizzük!




-(VIII.) A "mayor-nextcloud.cfg.php" fajl elején találhatóak a konfigurációs adatok, ezeket a következőképpen módosíthatjuk:


    $cfg['db_host'] = "localhost";              //Ez a nextcloud alatt futó mysql elérhetősége.
                                            // (Csak localhost lehet, a scriptet a nextcloud-szerveren kell futtatnunk.)

    $cfg['db_port'] = "3306";                   //nextcloud-mysql port

    $cfg['db_user'] = "root";                   //nextcloud-mysql felhasználónév
                                            // HA nem a root-ot használjuk, akkor, saját kezűleg kell létrehozni a script saját, nyilvántartó adatbázisát, és a fenti jogokat beállítani rá, 
                                            //  valamint Ha a Nextcloud verziószáma kisebb 14-nél, akkor a használt felhasználónak írási-olvasási-törlési
                                            //  (insert,select,update,delete) joggal kell rendelkeznie a nextcloud adatbázis "..groups" tábláján.
                                            // Ha a Debian-on alapértelmezett root-ot használjuk, akkor mindez automatikusan történik.

    $cfg['db_pass'] = "";                       //A nextcloud-mysql jelszó    (A fenti felhasználónévhez tartozó)
                                            // érdemes a debian 9.x-en, a root-hoz alapértelmezett "unix_socket" bejelentkezési módot 
                                            // használnunk, ez biztonságosabb, mert ekkor nem kell jelszó, más módon hitelesít, így biztonságosabb.



    $cfg['db_nxt_dbname'] = "nextcloud";        //A Nextcloud által használt adatbázis neve.
    $cfg['db_nxt_prefix'] = "oc_";              //A Nextcloud által használt adatbázisban a táblák prefix-je. (ha van)


    $cfg['db_m2n_db'] = "mayor_to_nextcloud";   //A mayor->nextcloud script adatbázisa (nyilvántartás). 
                                            // Ennek az adatbázisnak a Nextcloud mysql szerverén kell lennie!
                                            //
                                            // Ebben az adatbázisban könyveli le a script által létrehozott felhasználókat, azért,
                                            //  hogy így meg tudja különböztetni, a saját maga által létrehozottakat, az Adminisztrátor
                                            //  által, kézzel létrehozott felhasználóktól, hogy azokat ne birizgálja.

    $cfg['db_m2n_prefix'] = "m2n_";             //A nyilvántartó adatbázisban használt prefix, ha van. (ha nincs, akkor "üres string"-re kell állítani ($cfg['db_m2n_prefix'] = "";))


    //$cfg['db_mayor_host'] = "";               //Akkor használatos, ha a mayor alatti mysql szerver egy másik szerveren van, mint a Nextcloud által használt.
    //$cfg['db_mayor_port'] = "";               // ekkor ki kell venni kommentből, és ki kell tölteni a mayor-mysql serverre érvényes adatokkal.
    //$cfg['db_mayor_user'] = "";
    //$cfg['db_mayor_pass'] = "";               //A kiválasztott felhaználónak olvasnia (GRANT SELECT) kell tudnia a mayor-mysql serveren a(z):
                                            // intezmeny_xxx, mayor_parent, naplo_xxx_yyyy adatbázisokból.
	
                                            //Ha a mayor-mysql sezvere fizikailag (vagy virtuálisan) másik szerveren van, akkor érdemes egy "ssh-tunnel"-lel 
                                            // áthozni a portját a nextcloud-serverre. (ez a legbiztonságossabb)


    $cfg['isk_rovidnev'] = "rovid";         //A mayor-ban használt "iskola rövidneve" megnevezés.

    $cfg['min_evfolyam'] = 10;              //A minimális évfolyam, amelytől fölfelé engedélyezzük a felhő használatát a diákoknak.

    $cfg['csoport_prefix'] = "(tk) ";       //A Nextcloud-ban ezzel az előtaggal jelennek majd meg mayor-ból importált csoportok, a jobb átláthatóság érdekében.



    $cfg['default_email'] = "rendszergazda@iskola.hu";  //Ha a mayor napló "intezmeny_xxx.diak", vagy az "intezmeny_xxx.tanar" táblákban nincs kitöltve az 
                                                        // e-mail, akkor ezt használja alapértelmezetten. 
                                                        // (ide megy a jelszó-emlékeztető, amíg a felhasználó birtokba nem veszi a Nextcloud fiókját, és ben nem állít sajátot)

    $cfg['default_passw'] = "EHYmGktzrdfS7wxJR6DF11jJ"; //Az induló jelszó a Nextcloud-ban a felhasználóknak. (érdemes erőset/hosszút megadni, a botnet-ek/hackerek miatt)

    $cfg['default_quota'] = "10GB";                     //Az általános indulási fájl-kvóta a Nextcloud-ban. (A wbes felületen később módosítható.)
                                                        // Amikor új felhasználót ad hozzá, akkor az itt megadott méretre állítja a qvótáját.
                                                        // Midel a diákoknak külön qvótát lehet beállítani, ezért ez értelemszerűen már csak a tanárokra vonatkozik.
    
    $cfg['diak_quota'] = "2GB";                         //A diákok! indulási kvótája
                                                        // amikor új diákot ad hozzá, ezt a qvótát állítja be a számára.
                                                        //Lehetőség van az összes diák kvótáját egyszerre, az itt beállított értékre állítani,
                                                        // de ez nem fut automatikusan éjjelente, ezt kézzel kell, --set-diak-qupta kapcsoló kiadásával.

    $cfg['default_lang']  = "hu";                       //Az alapértelmezett nyelv (később minden felhasználó átállíthatja magának)

    $cfg['min_osztalyok'] =  array( );                  //Ide lehet felsorolni az osztályokat, ha konkrét osztályokat akaruni importálni,
                                                        // ez logikai (megengedő) VAGY kapcsolatban van a $cfg['min_evfolyam'] -mal.
                                                        //  Tehát ha beállítunk egy minimális évfolyamot, a listában felsorolt osztályok akkor is importálódnak,
                                                        //  ha a min_évfolyam-nál kisebbek.   //pl:  array('9.a','11.a');

    $cfg['csoportnev_hossz'] = 40;                      //Formázott kimenet: Kiegészíti "space"-kkel a kimenetet, ha rövidebb lenne a csoport neve. 
                                                        // (csak a script kinézete/átláthatósága végett)

    $cfg['felhasznalo_hossz'] = 45;                     //Formázott kimenet: Kiegészíti "space"-kkel a kimenetet, ha rövidebb lenne a felhasználó valódi neve. 
                                                        // (Ssak a script szép kinézete, átláthatósága végett.)

    $cfg['mindenki_csop'] = "naplós_felhasználók";      //Legyen egy olyan csoport, amiben "mindenki benne van".
                                                        // ebbe a "mindenki" csoportba minden, a script által létrehozott felhasználó bekerül.

    $cfg['mindenki_tanar'] = "naplós_tanárok";          //Legyen egy olyan csoport, amiben minden tanár benne van
                                                        // ebbe a "minden_tanár" csoportba a naplóbeli tanárok kerülnek.

    $cfg['mindenki_diak'] = "naplós_diákok";            //Legyen egy olyan csoport, amiben "minden diák" benne van.
                                                        // ebbe a "minden_diák" csoportba a naplóbeli diákok kerülnek.

    $cfg['allapot_tartas'] =  "2018-06-19";             //A jelölt nap állapotának megtartása/betöltése minden futtatáskor. (hasznos lehet a nyári szütnetben)
                                                        // Csak az AKTÍV vagy LEZÁRT állapotú szemeszereket nézi.
                                                        //A szerveren lévő legelső, vagy legutolsó, (aktív vagy lezárt) szemeszter előtti, illetve utáni nap nem állítható be.
                                                        //Ha már nem kell, vagy, ha mindíg az aktuális (aznapi) dátumot szeretnéd használni, 
                                                        // akkor állítsd üresre, vagy  "1970-01-01"-ra!;

    $cfg['manage_groupdirs'] = false;                   //Ha szerenénk, hogy a script létrehozza a távoktatást segítő tankörmappákat, állítsuk "true"-re!

    $cfg['groupdir_users'] = array("123abcd", );        //Ha csak egyes tanároknak szeretnénk bekapcsolni a tankörmappákat,
                                                        // ha minden tanárnak, akkor állítsuk üresre a tömböt!   / = array();/

    $cfg['groupdir_prefix'] = "Iskolai Távoktatás";     //A távoktatást segítő mappák gyüjtőmappája/gyökérkönyvtára.

    $cfg['manage_groups'] = true;                       //Foglalkozzon-e a tankörökkel/csoportokkal?  Ha true -> igen, ha false, akkor csak a felhasználókat 
                                                        // egyezteti a mayorral
    $cfg['manage_groupdirs'] = false;                   //A tankör-cspoprt mappákat kezelje-e vagy sem.  
 
    $cfg['verbose'] = 3                                 //Log bőbeszédűség      (A leg informatívabb(tömörebb), talán a 3-mas fokozat.)
                                                        // 0: csak fatális hibák, 1: fontosabbak, 2: csop./felh. elvétel, 3: csop./felh. hozzáadás, 
                                                        // 4: csop./felh. állapot, 5: részletesebben, 6: sql query + bash parancsok kiírása is


    $occ_path = "/var/www/nextcloud/";                  //A Nextcloud-server fájljainak elérési útja. (DocumentRoot)
                                                        // Erre szükség van a nextcloud "occ" parancsának eléréséhez.

    $occ_user = "www-data";                             //A Nextcloud-servert futtató (Apache által használt) felhasználónév

    $cfg['kihagy'] = array();                           //Lehetőség van egy-egy felhasználó kezelésének letiltására, ezt felsorolásként tehetjük meg.
                                                        // Ekkor a script nem fog foglalkozni, az adott felhasználóval a továbbiakban.
                                                        //pl:  array('Trap.Pista', 'Ebeed.Elek', '22att')
                                                        
    $cfg['megfigyelo_user'] = "naplo_robot";            //Lehetőség van egy úgymond "megfigyelő" felhasználó létrehozására.
                                                        // ez a felhasználó be lesz léptetve az összes csoportba, így az összes üzenetet megkapja, 
                                                        // és az összes tankörben megosztott fájlt látja, amit a csoportokkal/tankörökkel megosztottak.
                                                        //EZ a felhasználó nem egyezik meg a MaYor "mayoradmin" felhasználójával (nem lesz automatikusan rendszergazda)!
                                                        // de megadható ugyanazon felhasználónév, és lejszó, mint a MaYor "mayoradmin"-nak


    CONFIG FILE: "mayor-nextcloud.cfg.php";             //Lehetőség van a konfig exportálására egy külön fájlba, 
                                                        // így a mayor-nextcloud scriptet nem kell szerkeszteni, ha frissítés érkezik hozzá.
                                                        // Ez alapértelmezetten a maxor-nextcloud.php -val kell egy könyvtárba legyen.
                                                        


		




	** Szeparációs lehetőségek:
		Biztonsági megfontolásokból nem javasolt a Nextcloud-ot és a mayort ugyanazon Apache szerver és ugyanazon "DocumentRoot" alól futtatni.
		Szétválasztásukra többféle lehetőség van:
			- külön fizikai szerver mindegyiknek
			- külön virtuális szerver mindegyiknek
			- külön "DOCKER konténer" mindegyiknek
			- külön felhasználónévvel futtatott php

		Természetesen a legerősebb szeparációt az 1. megoldás jelenti, de ugyanakkor előjöhetnek ennek hátrányai is, például, hogy dupla
		akkora adminisztrációs teher a rendszergazdának.
		Ugyanakkor foglalkoznunk kell azzal a kérdéssel is, hogy ha az iskola vásárol egy komolyabb szervergépet, (ma már) több-tíz gigabájt RAM-mal,
		ekkor felesleges pazarlás fizikailag is külön szervergépre telepíteni a kettőt, különösen, ha figyelembe vesszük a mayor (ma már kicsinek számító)
		memória igényét is.

		Ekkor jöhet képbe a 2. és 3. lehetőség, amely már ugyanazon fizikai gépre is telepíthető egyszerre, ez már ésszerű elosztást biztosít.
		Ám, ha tovább gondoljuk, akkor szóba jöhet az a kérdés is, hogy ekkora fizikai memória (RAM) esetén 
		miért futtassunk több, különálló mysql-szervert, ahelyett, hogy egy, központi mysql-serverünk lenne, 
		aminek kiosztunk néhányszor-tíz gigabájt ramot?
		(Különösen annak fényében, hogy egy ilyen "felturbózott" mysql sokszorosára növeli a mayor-napló sebességét.)

		Erre a megoldásra születtek a "php külön felhasználónévvel futtatva" típusú lehetőségek.
		Ezek közül is a legésszerűbb, és legbiztonságosabb az Apache2 mod_suexec és mod_fcgid segítségével futtatott php.
		(Ekkor egy, közös Mysql és Apache2 szerver van, ahol az egyes weboldalak (apache virtualhost-ok) mind, 
		külön "rendszer felhasználónévvel" futnak, külön "DocumentRoot" könyvtárból, (és külön a www-data felhasználótól)
		ahol a felhasználónév váltást a mod_suexec modul végzi, a php futtatását pedig a mod_fcgid. )
		(Természetesen vannak még más megoldások is, mint pl. a php_fpm, de azokat nem javaslom. )

*/





$cfg['infotxt_szöveg'] = <<<EOT

                    FONTOS INFORMÁCIÓK!

Ez a mappa, és a benne lévő mappák, a távoktatás segítésére, 
egyszerűsítésére szolgálnak.

Fontos tudni, hogy ha megosztunk egy, a mi mappánkon belüli fájlt, 
vagy mappát, egy másik felhasználóval a felhőben, akkor a megosztott 
fájl, vagy mappa, a másik felhasználónak a kezdőoldalán jelenik meg, 
a fájlok között.

Egy idő után, ha az illető, már nagyon sok megosztást fogadott, 
a rengeteg fájl már számára egy átláthatatlan mappa-tengert fog képezni,
és nagyon nehéz lesz benne eligazodnia.


Ezen probléma kivédésére született ez, az alábbi megoldás:

A tanátoknak a szerver automatikusan létrehoz egy, a távoktatásra 
használatos gyűjtőmappát, utána ebbe a mappába létrehozza a tanár által 
tanított (e-napló szerint) tanköröknek megfelelő mappákat automatikusan.



A működési szabályok:

1) A tanár ezekbe a mappákba helyezheti el a diákoknak szánt fájlokat.
   (Ha akarja, ez nem kötelező, csak segítség.)

2) A tanár ezeket a mappákat osztja meg a diákcsoportokkal.

3) A diák ezekben a mappákban helyezi el a nyilvánosan visszaküldendő 
   fájlokat, képeket. (Ezt a mappát, a tankör összes tagja látja,
   amelyik tankörrel, vagy csoporttal megosztottuk.
   A megosztást nekünk, kézzel kell elvégezni, az nem történik 
   meg automatikusan.)

4) A diák, a privát módon visszaküldendő fájlokat, a tanárral való 
   megegyezés szerint, lehetőleg ne ebbe a mappába helyezze el,
   hanem azt egyszerűen ossza meg a tanárával, vagy valamilyen 
   más módon juttassa vissza.

5) A Tankörmappákban, a fájlok rendjét a tanár határozhatja meg,
   viszont, amelyik mappára engedélyezve van a diák(ok) részére a 
   feltöltés/szerkesztés/törlés, ott már értelemszerűen nem csak a 
   tanár dönt a fájlok sorsáról.

6) A tankörmappák gyűjtőmappájában a rendet a szerver tartja, 
   oda egyéb fájl, mappa nem helyezhető.  
   Ha mégis kerülne oda egyéb fájl akkor, azt a szerver, átnevezi,
   és a tanárnak értesítést küld, egészen addig, amíg az "idegen" 
   fájlok tekintetében helyre nem áll a rend.
   Az átnevezéskor, a szerver, a fájl nevét kiegészíti egy
   "számsorozat.please-remove" utótaggal. Ezt az utótagot, a fájl,
   a gyűjtőmappából történő áthelyezése után már kézzel eltávolíthatjuk,
   így visszakapjuk az eredeti fájlunkat, mappáinkat. 
   (Az "Átnevezés" gomb segítségével. (Jobb-Egér kattintással.))

7) A szerver, a tankörök egyeztetését, a naplóval, minden nap egyszer,
   éjfél és hajnali 5 óra között végzi el. Tehát, az iskolatitkárok 
   által elvégzett módosítások, így a tankör-csoportok összerendezése,
   a tankörök, valamint a tanárok és diákok jogviszonyában beálló 
   változás, csak másnap reggelre lép érvénybe.




Legyen egy gyakorlati példa:

A tanár mappalistájában létrejön a gyűjtőmappa mondjuk: 
"Iskolai Távoktatás" névvel.
Ezen, a mappán belül létrejön mondjuk, egy "(tk) 10.b fizika" 
és egy "(tk) 10.b osztályfőnöki" tankörmappa.
(Vagyis, a tanár, az e-naplóban ezen tankörök tagja.)

Ezen mappák mellé létrejön még a "(tk) 10.b fizika_beadás" és a
"(tk) 10.b osztályfőnöki_beadás" mappa is. Ezek a mappák a diákok 
által visszaküldendő fájlok gyűjtésére szolgálhatnak.


A tanár szabadon használhatja a   "(tk) 10.b fizika"   és a   
"(tk) 10.b osztályfőnöki" mappákat, azokat szabadon megoszthatja, 
bele fájlokat helyezhet, belőlük fájlokat törölhet, stb., 
ha engedélyezi a szerkesztést, akkor a diák is helyezhet bele 
fájlokat, vagy szerkesztheti*** a benne lévő fájlokat.

Ha a tankörmappát megosztja a diákkal/ egész tankörrel,
akkor a diáknak így már nem egy csomó fájl, rendezetlenül, 
és nem is az "Iskolai Távoktatás" nevű mappa fog megjelenni 
a kezdőoldalán, hanem az adott tankörmappa. 

Tehát, ha a tanár a "(tk) 10.b fizika" mappát osztja meg, 
akkor a diák, a kezdőoldalán a "(tk) 10.b fizika" mappát fogja 
találni, és benne a tanár által hozzáadott fájlokat.


Viszont az "Iskolai Távoktatás" nevű mappában csak(!!) a  
"(tk) 10.b fizika"   és a   "(tk) 10.b osztályfőnöki" mappák lehetnek,
valamint a beadásra szolgáló társmappák, illetve az INFO.txt.
Más fájl, vagy mappa, nem! 

 
Ha mégis kerülne oda egyéb fájl, vagy mappa, akkor arról először 
értesíti a tanárt, majd, a nem odaillő fájlokat, vagy mappákat 
automatikusan eltávolításra megjelöli.

Ez szintén vonatkozik a korábban itt levő, de átnevezett mappákra is, 
tehát, ha a "(tk) 10.b fizika" mappát a tanár véletlenül átnevezné, 
valami másra, akkor onnantól azt is "idegen" mappának fogja tekinteni.

Ugyanez történik akkor is, ha a tanár, (a napló szerint) kikerül a 
tankörből, tehát nem tanítja tovább, vagy a tankör ugrik egyet 
évkezdéskor, és a "(tk) 10.b fizika"-ból "(tk) 11.b fizika" lesz, 
ekkor, a, még ottmaradó "(tk) 10.b fizika" mappát szintén 
idegennek fogja tekinteni.

Ilyenkor a tanárnak szükséges, kézzel, az idegennek minősített mappákat 
kiüríteni, és az egész mappát, vagy csak azok tartalmát 
az "Iskolai Távoktatás" mappán kívülre, a saját mappái közé áthelyezni, 
vagy az idegennek minősített mappák tartalmát egy másik, az "elfogadott" 
tankörmappák valamelyikébe átrakni.
Majd a kiürült, előző mappát kézzel kell törölnie!

(Ez utóbbit kell tenni, vagyis a tanárnak, kézzel áthelyezni a 
fájlokat az egyik tankörmappából, az új tankörmappába, amikor 
évváltás van, és a tankör ugrik egyet: 
"(tk) 10.b fizika"  --> "(tk) 11.b fizika". )





*** Tippek/Megjegyzések:

1)
Ha szeretnénk biztosra menni, és elkerülni, hogy a diák, 
a tanár által, az egész tankörnek küldött fájlokba véletlenül 
beleszerkesszen, vagy esetleg töröljön belőlük, akkor vonjuk meg a
szerkesztési/létrehozási/törlési jogokat, és ezt ellenőrizzük is 
minden megosztáskor, hogy valóban a megfelelő jogok vannak-e beállítva
a megosztott mappán!

A szerver létrehoz egy "_beadás", végződésű mappát is, a tankörmappák 
mellé, hogy ezeket használhassuk a visszaküldendő fájlok gyűjtésére, így
csak erre a "_beadás" mappára kell jogot adnunk a diáknak/tankörnek 
a szerkesztésre/módosításra/törlésre/stb..



2)
Ha videó fájlokat szeretnénk feltölteni nagy mennyiségben, 
akkor hozzunk létre az adott tankörhöz tartozó tankörmappában egy 
"Videók" nevű mappát, és abba helyezzük el a videó fájlokat. 
A könnyebb megkülönböztethetőség végett nevezzük át a videó fájlokat 
dátum szerinti fájlnévre, vagy sorszámozzuk őket, 
és a sorszámot tüntessük föl a fájl nevében!


3) A létrejövő mappák megosztási beállításait nekünk kell 
finomhangolnunk, mert nem jönnek automatikusan létre a 
feltöltési/szerkesztési/törlési/stb. engedélyek. 
A megosztás pillanatában, a fogadó félnek, csak az olvasási 
jogosultsága jön létre automatikusan.

Tehát, minután megosztottuk egy tankörrel, kattintsunk rá a 
fektetett "V" betűhöz hasonlító ikonra, és a megosztási beállításoknál,
a "..."-ra kattintva engedélyezzük a 
feltöltést/szerkesztést/törlést/továbbadást/stb. a csoport tagjainak,
igényünknek megfelelően! 

(Magát a megosztást is nekünk, kézzel kell megtennünk, 
mert azt sem állítja be a szerver automatikusan.)

Alapesetben, gyakorlatilag csak annyi a dolgunk, hogy például, 
a "(tk) 10.b fizika" mappát megosszuk a "(tk) 10.b fizika" tankörrel.
Ha a beadást is szeretnénk itt összegyűjteni, akkor 
a "(tk) 10.b fizika_beadás" mappát is meg kell osszuk 
a "(tk) 10.b fizika" tankörrel, ügyelve arra, hogy csak 
a "(tk) 10.b fizika_beadás" mappára kerüljön a diákok számára 
létrehozási/szerkesztési/törlési jogosultság.



4)
Ha egy rossz helyen lévő fájl miatt értesítést kapunk, akkor a 
legfontosabb, hogy a fájl onnan, a távoktatás gyűjtőmappájából,
elkerüljön, tehát ne másoljuk, hanem helyezzük át, a saját, egyéb
mappáinkba / egyéb fájlaink közé! 

Utána, ha ez megtörtént, nevezzük vissza, az eredeti nevére,
vagyis töröljük ki a nevéből a szerver által odarakott:
"számsorozat.please-remove" utótagot, a "Átnevezés" gomb segítségével!


5)
Az INFO.txt, vagyis, ez a leírás, minden éjjel frissítődik, illetve újra
létrehozódik a szerveren, hogy mindig a legfrissebb információkat 
tartalmazza, tehát nem szükséges törölni. Ám erre is igaz, hogy 
ha véletlenül átnevezzük, akkor már idegennek fogja tekinteni
a rendszer. Ekkor, az "idegen" változatát már törölnünk szükséges!


EOT;





?>

