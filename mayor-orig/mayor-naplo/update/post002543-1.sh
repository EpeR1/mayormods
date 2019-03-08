#!/bin/sh

echo 'Településlista hozzáadása a mayor_naplo adatbázishoz'

FILE="www/policy/private/naplo/tanev/helyettesites.php"

FILENAME="${BASEDIR}/install/module-naplo/mysql/telepulesLista.sql"
    if [ -e "$FILENAME" ]; then
	cat $FILENAME | mysql -p$MYSQL_PW -u$MYSQL_USER mayor_naplo
    else
	echo 'nincs meg a file!'
    fi
