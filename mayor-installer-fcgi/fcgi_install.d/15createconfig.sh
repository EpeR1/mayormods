#!/bin/bash
#

cat <<EOF
A konfigurációs állományok létrehozása

A MaYoR konfigurációs állományait a minták alapján készítheti el. 
A konfigurációs állományok nélkül a telepítés nem fut le helyesen, ezért ezt 
a lépést akkor hagyja csak ki, ha ezeket már sajátkezűleg elkészítette!

EOF

read -n 1 -p "Létrehozzam a minták alapján a konfigurációs állományokat? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nA konfigurációs állományokat nem hoztam létre.\n"; exit 1; fi

if [ "$MAYORDIR" = "" ]; then echo "A MAYORDIR változó üres. Kilépek."; exit 1; fi
PWGEN=$(which pwgen)
if [ "${PWGEN}" = "" ]; then echo "A pwgen szoftver nincs telepítve."; exit 1; fi

echo -e "\nKonfigurációs állományok létrehozása:"
for file in main-config.php parent-conf.php private-conf.php public-conf.php
do
    if [ -e "${MAYORDIR}/config/${file}" ]; then echo "  $file létezik."; else
	echo -n "  $file.example --> "
	PW=$(pwgen -s1 32)
	cat "$MAYORDIR/config/$file.example" | sed s/%SQLPW%/$PW/ > "$MAYORDIR/config/$file"
	echo $file
    fi
done

echo -n "  module-naplo/config.php.example --> "
PW=$(pwgen -s1 32) 
PWREAD=$(pwgen -s1 32)
if [ -e "$MAYORDIR/config/module-naplo/config.php" ]; then echo "  module-naplo/config.php létezik."; else
    cat "$MAYORDIR/config/module-naplo/config.php.example" | sed -e s/%SQLPW%/$PW/ -e s/%SQLPWREAD%/$PWREAD/ > "$MAYORDIR/config/module-naplo/config.php"
    echo "module-naplo/config.php"
fi

if [ -e "$MAYORDIR/config/skin-classic/naplo-config.php" ]; then echo "  skin-classic/naplo-config.php létezik."; else
    echo -n "  skin-classic/naplo-config.php.example --> "
    cp $MAYORDIR/config/skin-classic/naplo-config.php.example $MAYORDIR/config/skin-classic/naplo-config.php
    echo "config/skin-classic/naplo-config.php"
fi


sed -e "s/\/var\/mayor/\/home\/$MAYORUSER\/mayor/g" -i "$MAYORDIR/config/main-config.php"
