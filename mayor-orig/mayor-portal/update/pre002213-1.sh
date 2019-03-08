#!/bin/bash

FILES="install/module-portal/mysql/hirek.sql"

echo -e "\n          * Felesleges állományok törlése:"
  for FILE in $FILES
  do
    echo -n "            $BASEDIR/$FILE ... "
    if [ -e "$BASEDIR/$FILE" ]; then
        rm -f "$BASEDIR/$FILE"
        echo "törölve."
    else
        echo "nincs."
    fi
  done
