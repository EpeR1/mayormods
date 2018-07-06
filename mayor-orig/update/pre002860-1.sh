#!/bin/sh

FILE="www/skin/vakbarat/base/javascript/base.js"

echo " Felesleges állományok törlése:"
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e "$BASEDIR/$FILE" ]; then
        rm -f "$BASEDIR/$FILE"
        echo "törölve."
    else
        echo "nincs."
    fi
echo " Kész."