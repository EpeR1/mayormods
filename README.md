## MaYoR Elektornikus napló    

Ez a tároló, a MaYoR elektornikus napló (© [www.mayor.hu](http://www.mayor.hu)), és a hozzá készített apróbb javítások/módosítások/kiegészítések gyüjteménye.  

**Az eredeti repó elérhető itt: [http://git.bmrg.hu/mayormods.git](http://git.bmrg.hu/mayormods.git/)**   


A fenti könyvtárak struktúrája:


**mayor-jav**   >               :       Az elvégzett javítások ki/egybegyűjtve. (mysql-utf8, update, texgen hibajavítás)  
**mayor-orig**  >               :       Az eredeti forrásállományok (base, naplo, portal, stb..) gyűjteménye, rev4284-től kezdve.

**mayor-installer/mayor-installer-jav**  >    : A mayor telepítő javított változata. (debian9, php memlimit, basedir pontosítása)  
**mayor-installer/mayor-instller-for-fcgi** > : Az eredeti telepítő kiegészítve az Apache2/mod-fcgid féle php-értelmezővel való használathoz (biztonsági szeparáció*, bővebben: lejjebb)   
**mayor-installer/mayor-installer-orig** >    : Az eredeti installer forrásállományok gyűjteménye, rev4284-től.  

**egyéb/munin**         >       :       Munin monitorozó plugin a  MaYoR-hoz.  
**egyéb/nextcloud**     >       :       Nextcloud felhasználó/csoport betöltő-menedzser script a MaYoR-ból.  


.


## Munin Plugin

(A MaYor-napló aktuális leterheltségének monitorozása munin segítségével.)

![munin](http://git.bmrg.hu/mayormods.git/pict/mayor_munin-day.png)

.



## Nextcloud-MaYor script

(Felhasználókat (tanár, diák), és csoportokat (tankörök) tölthetünk be vele a mayorból, a nextcloudba. )  
(Csoportokat hoz létre a mayor tankörei alapján, majd ezen csoportokba belépteti az adott tankör diákjait, és a tanárait. )

![munin](http://git.bmrg.hu/mayormods.git/pict/nextcloud.png)

.

---------------------------------------------------------------------

### *Szeparációs lehetőségek:

Biztonsági szempontból nem javasolt a MaYoR-t és egy másik weboldalt ugyanazon Apache szerver és ugyanazon "DocumentRoot" könyvtára alól futtatni!
Szétválasztásukra többféle lehetőség van:
1. külön fizikai szerver mindegyiknek.
2. külön virtuális szerver mindegyiknek.
3. külön "DOCKER konténer" mindegyiknek.
4. külön felhasználónévvel/jogokkal futtatott php-értelmező egy webszerveren belül.

Természetesen a legerősebb szeparációt az **1.** megoldás jelenti, de ugyanakkor előjöhetnek ennek hátrányai is, például, hogy dupla akkora adminisztrációs teher a rendszergazdának. Ugyanakkor foglalkoznunk kell azzal a kérdéssel is, hogy ha az intézmény vásárol egy komolyabb szervergépet, (ma már)tíz gigabájt RAM-mal.  Ekkor felesleges pazarlás fizikailag is külön szervergépre telepíteni az egyes webszervereket, különösen, ha figyelembe vesszük a weboldalak memóriaigényét is.


Ekkor jöhet képbe a **2.** és **3.** lehetőség, ahol már ugyanazon fizikai gép alól fut mindegyik, ez már ésszerű elosztást biztosít, de, ha tovább vizsgáljuk, akkor szóba jöhet az a kérdés is, hogy ekkora fizikai memória (RAM) esetén miért futtassunk több, különálló mysql-szervert, web-szervert? Ahelyett, hogy egy, központi mysql-szerverünk, web-szerverünk lenne, aminek kiosztunk több gigabájt ramot, így az jóval gyorsabb kiszolgálást tud biztosítani.
(Különösen annak fényében, hogy egy ilyen "felturbózott" mysql sokszorosára növeli a mayor-napló sebességét is.)

Erre a megoldásra születtek a "php külön felhasználónévvel futtatva" típusú ( **4.** ) lehetőségek.
Ezek közül is a legegyszerűbb, és legbiztonságosabb az **Apache2**  **mod_suexec** és **mod_fcgid** segítségével futtatott php-értelmező.
Ekkor egy, közös Apache2 (és persze MySQL) szerver van, ahol az egyes weboldalak (apache virtualhost-ok) mind, külön-külön "felhasználónévvel" futnak, külön-külön, saját "DocumentRoot" könyvtárból, (és persze külön a www-data felhasználótól is) ahol a felhasználónév váltást az Apache2 indulásakor, a mod_suexec modul végzi, a php futtatását pedig az Apache2 mod_fcgid modulja, a már meghatározott felhasználó nevében, és jogaival.
(Természetesen vannak még más megoldások is, mint pl. a php_fpm, de azokat nem javaslom, mert több vele az adminisztráció, és a bonyodalom a különálló szerver-processzek miatt. )
