Ez a tároló, a MaYoR elektornikus napló (© [www.mayor.hu](http://www.mayor.hu))-hoz készített apróbb javítások/módosítások/kiegészítések gyüjteménye.  


A fenti könyvtárak struktúrája:


**mayor-mod**   >               :       Az elvégzett javítások ki/egybegyűjtve. (mysql-utf8, update, texgen hibajavítás)  
**mayor-orig**  >               :       Az eredeti forrásállományok (base, naplo, portal egybegyúrva) gyűjteménye, rev4284-től.

**mayor-installer-mod**  >      :       A mayor telepítő javított változata. (debian9, php memlimit, basedir/server-user pontosítása)  
**mayor-instller-fcgi-mod** >   :       Az Apache2/mod-fcgid-es php-értelmezőhöz készült telepítő kiegészítve,  a "hagyományos" telepítővel.  
**mayor-installer-orig** >      :       Az eredeti installer forrásállományok gyűjteménye, rev4284-től.  

**egyéb/munin**         >       :       Munin monitorozó plugin a  MaYoR-hoz.  
**egyéb/nextcloud**     >       :       Nextcloud felhasználó/csoport betöltő-menedzser script a MaYoR-ból.  

---------------------------------------------------------------------

**Munin Plugin** :

(A MaYor-napló aktuális leterheltségének monitorozása munin segítségével.)

![munin](http://git.bmrg.hu/mayormods.git/pic/mayor_munin-day.png)

.


**Nextcloud-MaYor script** :

(Felhasználókat (tanár, diák), és csoportokat (tankörök) tölthetünk be vele a mayorból, a nextcloudba. )  
(Csoportokat hoz létre a mayor tankörei alapján, majd ezen csoportokba belépteti az adott tankör diákjait, és a tanárait. )

![munin](http://git.bmrg.hu/mayormods.git/pic/nextcloud.png)


.
