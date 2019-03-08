#!/bin/bash

echo "          - Az esetleg létező skin/classic/config* átmozgatása"
if [ `ls $BASEDIR/www/skin/classic/config* | wc -l` -gt 2 ]; then
    echo -n "             $BASEDIR/www/skin/classic/config* --> "
    mv "$BASEDIR/www/skin/classic/config*" "$BASEDIR/config/skin-classic/"
    echo "$BASEDIR/config/skin-classic/"
else
    echo "              nincs ilyen file"
fi
