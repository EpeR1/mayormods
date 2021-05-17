#!/bin/bash

BASEDIR="/var/mayor"

DIR="$BASEDIR/download/public/portal/upload/"
mkdir -p $DIR
chown -R www-data $DIR
ln -s $DIR $BASEDIR/www/upload 
echo "$DIR Kész."

