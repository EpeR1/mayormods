
# Munin monitorozó plugin a mayorhoz

Grafikonokat készít a "munin" program segítségével a Mayor pillanatnyi állapotáról.
Egyelőre csak debian 9+ rendszeren tesztelt, de pár apró módosításokkal életre kelthető más/régebbi rendszereken is.

Rögzíti az:  

* Egyidejűleg (pillanatnyi) bejelentkezett felhasználók számát.  
* IP-cím alapján a lokális (192.168.*.*, 10.*.*.*, 172.16.*.*, fd**::/8) és külön a külső tartományokat.  
* Policy alapján a 'private' és/vagy 'parent' -ben belépett felhasználókat.  
* Aktivitás alapján az Xperc (pl:10perc) ideje, a 2*Xperc ideje aktív felhasználókat és a tétleneket.  


## Beállítása: (debian)

1. lépés: Telepítsük fel a Munin monitorozó rendszert!  
  >>> apt-get install munin  
2. lépés: a **/etc/munin/munin.conf** fájl szerkesztése a következő módon:   

* Adjuk hozzá az alábbi sorokat, a napló-szerver gépnevének megfelelően:  
  (itt az FQDN (teljes elérési út szükséges)) és állítsuk "no"-ra az "use_node_name"-t.    

        [gépnév.iskolaneve.hu]
          address 127.0.0.1
          use_node_name no

3. Ezután módoítanunk kell a **/etc/munin/plugin-conf.d/munin-node** fájlt,   

* (a.) Adjuk hozzá a következőket:  

        [mayor_munin]
          user munin
		  timeout 60
          env.host naplo.iskolaneve.hu
          env.db_user mayor-munin
          env.db_pass <;erős_jelszó>
          env.db_host  localhost
          env.t_active 10

        [mayor_munin]
          user >>A felhasználó, amelyik neve alatt a munin futtatja a scriptet<<
		  timeout >>A munin ennyi ideig várjon a script lefutására<<
          env.host >>A napló web-címe<<
          env.db_user >>Az adatbázishoz használt felhasználónév<< 
          env.db_pass >>Az adatbázishoz használt jelszó<<
          env.db_host >>Ahol a mayor mysql szervere elérhető<<
          env.t_active >>Mennyi percet számoljon a tétlenségnek<<

* (b.) Vagy lehetőségünk van mindezeket a script fejlécében is beáálítani,  
  akkor nem szükséges a **/etc/munin/plugin-conf.d/munin-node** fájl szerkesztése.  

* (c.) Debian 10-től lehetőség van a mysql-nél úgynevezett **unix-shocket** hitelesítésre is.   
   Ez alapból a **root**-nak van bekapcsolva, előnye, hogy sehol sem kell kiolvasható jelszót   
   használni, a mysql egyszerűen az oprendszerhez hitelesíti a bejelentkező felhasználót.  
  (Értelemszerűen ez csak "locahost"-ra működik.)  

  * Az unix_shocket használtaához állítsuk be az alábbiakat:  
  (Ekkor a root lesz a mysql felhasználó, számára nem kell külön engedélyezni a mayor_login  
  táblából való olvasást, viszont ekkor a munin-nak is root-ként kell futtatnia a scriptet.)  

        [mayor_munin]
          user root
          env.host naplo.iskolaneve.hu
          env.db_user root
          #env.db_pass 
          env.db_host  localhost
          env.t_active 10

4. Utána másoljuk a **mayor_munin.php** az:  
 az **/usr/share/munin/plugins/** mappába vagy  
 az **/etc/munin/** könyvtárba vagy  
 bárhova, ahonnan a munin eléri.  

   * FONTOS!!  
  A "mayor_munin.php"-nak állítsuk be a "root" tulajdonost és 700-as jogokat!  
  (A Munin-nak futtatási jog kell, illetve nehogy valami kiolvassa a jelszót)  


5. Végül el kell helyezni egy simlink-et a **/etc/munin plugins**  
  könyvtárba, ami a bemásolt fájlra mutat.  
  >>> ln -s /usr/share/munin/plugins/mayor_munin.php /etc/munin/plugins/munin/mayor_munin


6. Befejezésképpen pedig indítsuk újra a munin-t az alábbi parancsokkal:  
  >>> /etc/init.d/munin-node restart  
  >>> /etc/init.d/munin restart  

7. A beálítás végeztével a következő paranccsal ellenőrizhetjük, hogy mindent jól csináltunk-e:  
 >>> munin-run mayor_munin

