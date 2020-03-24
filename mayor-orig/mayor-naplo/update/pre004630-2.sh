#!/bin/sh

#BASEDIR="/var/mayor"

DIR="$BASEDIR/download/parent/naplo/haladasi/"
mkdir -p $DIR
chown -R www-data $DIR
echo "$DIR Kész."

DIR="$BASEDIR/download/private/naplo/haladasi/hazifeladat"
mkdir -p $DIR
chown -R www-data $DIR
echo "$DIR Kész."

LNDIR="$BASEDIR/download/parent/naplo/haladasi/"
ln -s $DIR $LNDIR
echo "$LNDIR Kész."
