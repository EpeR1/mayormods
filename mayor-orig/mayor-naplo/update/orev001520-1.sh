#!/bin/bash


FILES="update/rev1517-1-intezmeny.sql update/rev1517-1-mayor_naplo.sql"

echo -e "\n          Hibás update szkriptek törlése:\n"
for FILE in $FILES; do
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e $BASEDIR/$FILE ]; then
        rm -f $BASEDIR/$FILE
        echo "törölve."
    else
        echo "nincs."
    fi
done
