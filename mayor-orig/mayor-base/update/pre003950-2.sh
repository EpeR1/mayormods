#!/bin/sh

FILES="mayor-base/www/policy/public/portal/rpc/rpc.php
mayor-portal-mayor/www/policy/public/portal/regisztracio/regisztracio.php
mayor-portal-mayor/www/skin/mayor/module-portal/css/regisztracio
mayor-portal-mayor/www/skin/mayor/module-portal/css/regisztracio/regisztracio.css
mayor-portal-mayor/www/skin/mayor/module-portal/html/regisztracio
mayor-portal-mayor/www/skin/mayor/module-portal/html/regisztracio/regisztracio.phtml"

echo " Felesleges állományok törlése:"
for FILE in $FILES; do
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e "$BASEDIR/$FILE" ]; then
        rm -f "$BASEDIR/$FILE"
        echo "törölve."
    else
        echo "nincs."
    fi
done
echo " Kész."