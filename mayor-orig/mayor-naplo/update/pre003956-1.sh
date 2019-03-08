#!/bin/sh

FILES="lang/hu_HU/module-naplo/admin/regisztracio.php
policy/private/naplo/admin/regisztracio-pre.php
policy/private/naplo/admin/regisztracio.php
skin/classic/module-naplo/css/admin/regisztracio.css
skin/classic/module-naplo/html/admin/regisztracio.phtml
static/hu_HU/naplo/regisztracio/readme.html"

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


