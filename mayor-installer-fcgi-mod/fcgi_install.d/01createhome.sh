#!/bin/bash
#

cat <<EOF
		MAYOR telepítése Apache2 alá
	    mod_suexec/mod_fastcgi beállításásval

Ez a telepítési eljárás akkor hasznos, ha a webszerverünkön 
gazdaságossági okokból több weboldalt szeretnénk egyszerre futtatni, köztük a mayort.

Az alapvető cél, hogy a különböző (php-t futtató) weboldalak jogosultságaikban,
biztonsági okokból, el legyenek szeparálva. Ehhez az kell, hogy a különböző
php értelmezők, külön felhasználónév alatt, és külön 'docroot' könyvtárból legyenek futtatva.

Az apache2, a mod_suexec modulja segítségével végzi a felhasználónév-váltást, és
a mod_fcgid segítségével a php értelmezők (php-modulok) elérését.

Ez a felhsználó tulajdonképpen olyan, mint a www-data rendszerfelhasználó, 
de a jogosultstságait külön tudjuk szabályozni, hiszen az a cél, 
hogy ne mindent a www-data "futtasson". (Ha minden a www-data alól futna, 
akkor például a különböző weboldalak látnák, és elérnék egymás fájljait.)

Ez a gyakorlatban valahogy így néz(ne) ki:
Van 3 weboldalunk: mayor(napló), webmail, iskolai honlap
A "mayor-web" nevében fut a napló,
a "mail-web" nevében a webmail,
a "honlap-web" nevében pedig az iskolai honlap.

Első lépésként, létre kell hoszni egy felhasználót, akinek a neve alatt, 
és akinek a könyvtárából fut a php értelmező.
Ez általában "rendszer" felhasználót jelent, vagyis bejelentkezni vele nem lehetséges.
Az utolsó lépésben pedig módosítani kell az apache beállításokat a fentieknek megfelelően.

Az elrendezés valahogy így néz ki:

böngésző <---hálózat---> (apache2=fcgid): <==IPC==> :php-értelmező_apache_gyermekprocesszként_külön_userként


FONTOS!! 
A napló is ebből a könyvtárból fog futni, a /var/mayor/ egy symlink lesz
erre a könyvtárra. de ugyanúgy használható továbbra is, mint eddig.

Például: 
   A felhasználó neve: mayor-web
   A home könyvtára  : /home/mayor-web/
   A mayor könyvtára : /home/mayor-web/mayor/
   A mayor mantés(pl): /home/mayor-web/mayor_backup/
   (Megj: a /var/mayor/ továbbra is használható.)
   (/var/mayor/ --> /home/mayor-web/mayor/)

A telepítés végén pedig mindez a >> ps auxf parancs kiadásával személetesebben is látszódik.
EOF


echo -e "A php értelmező felhasználója: $MAYORUSER"
read -n 1 -p "Létrehozhatom? (i/N) " -s DO
if [ "$DO" != "i" ]; then echo -e "\nA $MAYORUSER létrehozását kihagytam.\n"; exit 1; fi

useradd -p "*"  -m --home "/home/$MAYORUSER" --shell "/bin/false" --system "$MAYORUSER"
chage --mindays 0 --maxdays 99999 --warndays 7 "$MAYORUSER"
chfn --full-name "$MAYORUSER" "$MAYORUSER"
echo -e "A $MAYORUSER (rendszer)felhasználó hozzáadva.\n"

echo -e "/home/$MAYORUSER/mayor és mayor_backup mappák létrehozása.\n"
mkdir -p "/home/$MAYORUSER/mayor_backup"
mkdir -p "/home/$MAYORUSER/mayor"

echo -e "linkelés a /var/mayor könyvtárra.\n"
ln -s "/home/$MAYORUSER/mayor" "/var/mayor"

echo -e "kész.\n\n"


