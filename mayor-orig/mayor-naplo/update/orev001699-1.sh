#!/bin/bash

FILE="www/include/modules/naplo/tanev/convert-aSc Timetables XML (tankor_nelkul).php"

echo -e "\n          Felesleges állományok törlése:\n"
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e "$BASEDIR/$FILE" ]; then
        rm -f "$BASEDIR/$FILE"
        echo "törölve."
    else
        echo "nincs."
    fi
