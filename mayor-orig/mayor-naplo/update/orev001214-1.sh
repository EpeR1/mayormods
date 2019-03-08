#!/bin/bash

FILES="www/policy/private/naplo/intezmeny/diakAdmin.php
www/policy/private/naplo/intezmeny/diakAdmin-pre.php
www/lang/hu_HU/module-naplo/intezmeny/diakAdmin.php
www/include/modules/naplo/intezmeny/diakAdmin.php
www/skin/classic/module-naplo/html/intezmeny/diakAdmin.phtml
www/skin/classic/module-naplo/css/intezmeny/diakAdmin.css"

echo -e "\n          Elavult állományok törlése:\n"
for FILE in $FILES; do
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e $BASEDIR/$FILE ]; then
        rm -f $BASEDIR/$FILE
        echo "törölve."
    else
        echo "nincs."
    fi
done

