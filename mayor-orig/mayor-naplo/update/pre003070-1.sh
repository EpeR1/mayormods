#!/bin/sh

FILE="www/skin/classic/module-naplo/javascript/bejegyzesek/bejegyzesTipus.js"

echo " Felesleges állományok törlése:"
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e "$BASEDIR/$FILE" ]; then
        rm -f "$BASEDIR/$FILE"
        echo "törölve."
    else
        echo "nincs."
    fi
echo " Kész."