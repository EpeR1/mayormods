#!/bin/bash

FILES="www/include/modules/portal/share/sql.php"

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

