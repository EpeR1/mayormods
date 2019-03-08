#!/bin/bash
#

cat <<EOF
A konfigurációs állományok létrehozása

A MaYoR konfigurációs állományait a minták alapján készítheti el. 
A konfigurációs állományok nélkül a telepítés nem fut le helyesen, ezért ezt 
a lépést akkor hagyja csak ki, ha ezeket már sajátkezűleg elkészítette!

EOF

if [ "$MAYORDIR" = "" ]; then echo "A MAYORDIR változó üres. Kilépek."; exit 1; fi

echo -e "\nKonfigurációs állományok létrehozása:"
for file in main-config.php parent-conf.php private-conf.php public-conf.php
do
    if [ -e "${MAYORDIR}/config/${file}" ]; then echo "  $file létezik."; else
	echo -n "  $file.sulix --> "
	PW=`mypwgen`
#	echo "$MAYORDIR/config/$file.sulix" sed -e "s/%SQLPW%/${PW}/" -e "s/%BASEDN%/${BASEDN}/" -e "s#%MAYORDIR%#${MAYORDIR}#"
	cat "$MAYORDIR/config/$file.sulix" | sed -e "s/%SQLPW%/${PW}/" -e "s/%BASEDN%/${BASEDN}/" -e "s#%MAYORDIR%#${MAYORDIR}#" > "$MAYORDIR/config/$file"
	echo $file
    fi
done

echo -n "  module-naplo/config.php.sulix --> "
PW=`mypwgen`
PWREAD=`mypwgen`
if [ -e "$MAYORDIR/config/module-naplo/config.php" ]; then echo "  module-naplo/config.php létezik."; else
    cat "$MAYORDIR/config/module-naplo/config.php.sulix" | sed -e "s/%SQLPW%/${PW}/" -e "s/%SQLPWREAD%/${PWREAD}/" > "$MAYORDIR/config/module-naplo/config.php"
    echo "module-naplo/config.php"
fi

if [ -e "$MAYORDIR/config/skin-classic/naplo-config.php" ]; then echo "  skin-classic/naplo-config.php létezik."; else
    echo -n "  skin-classic/naplo-config.php.sulix --> "
    cp $MAYORDIR/config/skin-classic/naplo-config.php.sulix $MAYORDIR/config/skin-classic/naplo-config.php
    echo "config/skin-classic/naplo-config.php"
fi

# A private menü átalakítása (nincs Kilépés, Felhasználói adatok - ahol nem kell...)
if [ -e "$MAYORDIR/config/menu/private/menu-hu_HU.php" ]; then echo "  menu/private/menu-hu_HU.php"; else
    echo -n "  menu/private/menu-hu_HU.php.sulix --> "
    cp $MAYORDIR/config/menu/private/menu-hu_HU.php.sulix $MAYORDIR/config/menu/private/menu-hu_HU.php
    echo "menu/private/menu-hu_HU.php"
fi

# A public menü átalakítása (szülői bejelentkezés)
if [ -e "$MAYORDIR/config/menu/public/menu-hu_HU.php" ]; then echo "  menu/public/menu-hu_HU.php"; else
    echo -n "  menu/public/menu-hu_HU.php.sulix --> "
    cp $MAYORDIR/config/menu/public/menu-hu_HU.php.sulix $MAYORDIR/config/menu/public/menu-hu_HU.php
    echo "menu/public/menu-hu_HU.php"
fi

# A parent menü átalakítása (utolsó két menüpont törlése)
if [ -e "$MAYORDIR/config/menu/parent/menu-hu_HU.php" ]; then echo "  menu/parent/menu-hu_HU.php"; else
    echo -n "  menu/parent/menu-hu_HU.php.sulix --> "
    cp $MAYORDIR/config/menu/parent/menu-hu_HU.php.sulix $MAYORDIR/config/menu/parent/menu-hu_HU.php
    echo "menu/parent/menu-hu_HU.php"
fi

# IFRAME-be ágyazás engedélyezése
if [ -e "$MAYORDIR/config/skin-sulix/config.php" ]; then echo "  skin-sulix/config.php"; else
    echo -n "  skin-sulix/config.php.sulix --> "
    cp $MAYORDIR/config/skin-sulix/config.php.sulix $MAYORDIR/config/skin-sulix/config.php
    echo "skin-sulix/config.php"
fi

