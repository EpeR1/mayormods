#!/bin/sh

#BASEDIR="/var/mayor"

DIR="$BASEDIR/download/private/naplo/felveteli/level/"
mkdir -p $DIR
chown -R www-data $DIR
echo "$DIR Kész."

DIR="$BASEDIR/download/public/naplo/felveteli/level/"
mkdir -p $DIR
chown -R www-data $DIR
echo "$DIR Kész."
