#!/bin/sh
##
# Az elektronikus visszatöltése (Version 1.0)
##

    NAPLO_BACKUP_BASE_DIR='/backup/naplo'
    NAPLO_BACKUP_TMP_DIR="$NAPLO_BACKUP_BASE_DIR/tmp"

    DATABASE='%MYSQL_NAPLO_DB%'
    MYSQL_PW='%MYSQL_ROOT_PW%'
    MYSQL_USER='root'

    WWW_DIR='%BASEDIR%'
    WEB_SERVER_USER='%WEB_SERVER_USER%'
    BASEDN='%BASEDN%'
    DUMP_FILE="$DATABASE.sql"
    LDIF_FILE='ldap.ldif'
    LDAP_DB_DIR='%LDAP_DB_DIR%/mayor'

##
# A paraméter ellenőrzése
##

    if [ -z $1 ]; then
	exit 1
    else
	if [ -e $NAPLO_BACKUP_BASE_DIR/$1 ]; then
	    FILE=$1
	    DATE=`echo $FILE | cut -d . -f 1`
	else
	    exit 2
	fi
    fi

echo "PARAMÉTER: $1"

##
# TMP Könyvtár ellenőrzése, létrehozása
##

    if [ ! -e $NAPLO_BACKUP_TMP_DIR ]; then
	/bin/mkdir $NAPLO_BACKUP_TMP_DIR
    fi
    /bin/chown $WEB_SERVER_USER $NAPLO_BACKUP_TMP_DIR
    /bin/chmod 700 $NAPLO_BACKUP_TMP_DIR

##
# Adatfile kicsomagolása
##

    cd $NAPLO_BACKUP_TMP_DIR
    /bin/tar xfz $NAPLO_BACKUP_BASE_DIR/$FILE
    if [ ! -e $NAPLO_BACKUP_TMP_DIR/$DATE/$DUMP_FILE ]; then
	exit 3
    fi
    if [ ! -e $NAPLO_BACKUP_TMP_DIR/$DATE/$LDIF_FILE ]; then
	exit 4
    fi

##
# Az LDAP adatbázis visszatöltése/felülírása
##

    /etc/init.d/slapd stop
    /bin/sleep 1

    /bin/rm -rf $LDAP_DB_DIR/*    
    /usr/sbin/slapadd -c -b $BASEDN -l $NAPLO_BACKUP_TMP_DIR/$DATE/$LDIF_FILE

    /etc/init.d/slapd start

##
# mysql adatbázis visszatöltése
##

(cat <<EOF 
DROP DATABASE IF EXISTS $DATABASE;
CREATE DATABASE $DATABASE;
EOF
) | mysql -u$MYSQL_USER -p$MYSQL_PW

cat $NAPLO_BACKUP_TMP_DIR/$DATE/$DUMP_FILE | mysql -u$MYSQL_USER -p$MYSQL_PW $DATABASE
