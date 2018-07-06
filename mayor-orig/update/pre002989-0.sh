#!/bin/sh

FILE="update/pre002989-1-intemzeny.sql"

echo " Felesleges állományok törlése:"
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e "$BASEDIR/$FILE" ]; then
        rm -f "$BASEDIR/$FILE"
        echo "törölve."
    else
        echo "nincs."
    fi
echo " Kész."