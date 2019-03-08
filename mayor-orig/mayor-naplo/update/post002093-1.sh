#!/bin/sh

echo 'A post update szkriptek tesztelése'
echo 'A post fut ($BASEDIR)!!' >> /tmp/mayor-update-test.log

FILE="www/policy/private/naplo/tanev/helyettesites.php"

    echo "$BASEDIR/$FILE"
    if [ -e "$BASEDIR/$FILE" ]; then
        echo "törölve."
    else
        echo "nincs."
    fi

