#!/bin/bash

FILES="www/include/modules/naplo/share/haladasi.php www/include/modules/naplo/intezmeny/diak.php www/include/modules/naplo/hianyzas/hianyzas.php"

echo -e "\n          Felesleges állományok törlése:\n"
for FILE in $FILES; do
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e $BASEDIR/$FILE ]; then
        rm -f $BASEDIR/$FILE
        echo "törölve."
    else
        echo "nincs."
    fi
done
