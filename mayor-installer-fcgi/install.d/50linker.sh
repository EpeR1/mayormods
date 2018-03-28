#!/bin/bash
#
cat <<EOF
Linkek létrehozása a parent és public policyben

EOF

read -n 1 -p "Létrehozzam a linkeket? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nNem hoztam létre.\n"; exit; fi
source $MAYORDIR/config/main.conf
source $MAYORDIR/update/linkme.sh

echo -e " * Webszerver userének ellenőrzése, beállítása"
if [ "$WEB_SERVER_USER" == "" ]; then 
    source /etc/apache2/envvars
    WEB_SERVER_USER=$APACHE_RUN_GROUP
fi
if [ "$WEB_SERVER_USER" == "" ]; then 
    echo -e "!!! Fatális hiba !!! A WEB_SERVER_USER változó üres. Kilépek!"; exit 1; 
else
    echo -e " * WEB_SERVER_USER=" $WEB_SERVER_USER
fi
if [ "$MAYORDIR" == "" ]; then 
    echo "!!! MAYORDIR változó üres. Kilépek!"; exit 1; 
else
    echo -e "\n * A szükséges jogosultságok beállítása ($MAYORDIR/):"
    if [ -e "$MAYORDIR/download" ]; then chown -R $WEB_SERVER_USER $MAYORDIR/download; fi
    if [ -e "$MAYORDIR/www/wiki/conf" ]; then chown -R $WEB_SERVER_USER $MAYORDIR/www/wiki/conf; fi
    if [ -e "$MAYORDIR/www/wiki/data" ]; then chown -R $WEB_SERVER_USER $MAYORDIR/www/wiki/data; fi
fi

echo -e " * Szimbolikus linkek ellenőrzése/létrehozása"
POLICIES="parent public"
for POLICY in $POLICIES; do
    eval "LIST=\$${POLICY}Link"
    for f in $LIST; do
        DIR=$(echo $f | cut -d / -f 1-2)
        if [ ! -d $MAYORDIR/www/policy/$POLICY/$DIR ]; then
            echo "    Könyvtár: $MAYORDIR/www/policy/$POLICY/$DIR"
            mkdir -p $MAYORDIR/www/policy/$POLICY/$DIR
	else
	    echo "   [OK] A könyvtár már létezik: $MAYORDIR/www/policy/$POLICY/$DIR"
        fi
        FILES="$f-pre.php $f.php"
        for file in $FILES; do
            if [ ! -e $MAYORDIR/www/policy/$POLICY/$file ]; then
                if [ -f $MAYORDIR/www/policy/private/$file ]; then
                    echo "    $MAYORDIR/www/policy/private/$file --> $MAYORDIR/www/policy/$POLICY/$file"
                    ln -s $MAYORDIR/www/policy/private/$file $MAYORDIR/www/policy/$POLICY/$file
                else
                    echo "    Hiányzó file: $MAYORDIR/www/policy/private/$file"
                fi
	    else
		echo "   [OK] A file már létezik: " $file
            fi
        done
    done
done
