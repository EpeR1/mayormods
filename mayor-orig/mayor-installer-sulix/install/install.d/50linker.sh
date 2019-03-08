#!/bin/bash

cat <<EOF
Linkek létrehozása a parent és public policyben

EOF

source $MAYORDIR/config/main.conf
source $MAYORDIR/update/linkme.sh

if [ "$MAYORDIR" = "" ]; then echo "MAYORDIR változó üres. Kilépek!"; exit 1; fi
if [ "$WEB_SERVER_USER" = "" ]; then echo "WEB_SERVER_USER változó üres. Kilépek!"; exit 1; fi

# Ennek nem itt a helye
echo -e "\n * A szükséges jogosultságok beállítása"
if [ -e "$MAYORDIR/config" ]; then 
	chmod 700 $MAYORDIR/config/
	chown -R $WEB_SERVER_USER $MAYORDIR/config
fi
if [ -e "$MAYORDIR/config/main.conf" ]; then chown root $MAYORDIR/config/main.conf; fi
if [ -e "$MAYORDIR/download" ]; then chown -R $WEB_SERVER_USER $MAYORDIR/download; fi
if [ -e "$MAYORDIR/www/wiki/conf" ]; then chown -R $WEB_SERVER_USER $MAYORDIR/www/wiki/conf; fi
if [ -e "$MAYORDIR/www/wiki/data" ]; then chown -R $WEB_SERVER_USER $MAYORDIR/www/wiki/data; fi

ln -s $MAYORDIR/www /var/www/mayor

echo -e " * Szimbolikus linkek ellenőrzése/létrehozása"
POLICIES="parent public"
for POLICY in $POLICIES; do
    eval "LIST=\$${POLICY}Link"
    for f in $LIST; do
        DIR=`echo $f | cut -d / -f 1-2`
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
