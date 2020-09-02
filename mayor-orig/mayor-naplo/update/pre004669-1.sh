#!/bin/sh

#BASEDIR="/var/mayor"

DIR="$BASEDIR/download/private/naplo/nyomtatas/torzslap/"
mkdir -p $DIR
chown -R www-data $DIR
echo "$DIR Kész."

DIR="$BASEDIR/download/private/naplo/upload/"
mkdir -p $DIR
chown -R www-data $DIR
echo "$DIR Kész."
