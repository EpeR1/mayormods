#!/bin/sh
##
# Az elektronikus mentése (Version 1.0)
##

    DATE=`date +%Y%m%d`
    NAPLO_BACKUP_BASE_DIR='/backup/naplo'
    NAPLO_BACKUP_DIR="$NAPLO_BACKUP_BASE_DIR/$DATE"

    DATABASES='mayor_parent mayor_private mayor_naplo intezmeny_fasori naplo_fasori_2007'
    #MYSQL_DB='%MYSQL_NAPLO_DB%'
    MYSQL_USER='%MYSQL_NAPLO_USER%'
    MYSQL_PW='%MYSQL_NAPLO_PW%'

    WWW_SSL_DIR='%BASEDIR%'
    WEB_SERVER_USER='%WEB_SERVER_USER%'
    EXPIRE_DATE=`date -d '30 days ago' +%Y%m%d`

    BASEDN='%BASEDN%'
    LDAP_CONF_DIR='%LDAP_CONF_DIR%'
    LDAP_DB_DIR='%LDAP_DB_DIR%'

##
# A könyvtárak létrehozása
##
    if [ ! -e  $NAPLO_BACKUP_BASE_DIR ]; then
	/bin/mkdir $NAPLO_BACKUP_BASE_DIR
    fi
    /bin/chown $WEB_SERVER_USER $NAPLO_BACKUP_BASE_DIR
    /bin/chmod 700 $NAPLO_BACKUP_BASE_DIR

    if [ -e $NAPLO_BACKUP_DIR.tgz ]; then
	exit 1
    fi
    /bin/mkdir $NAPLO_BACKUP_DIR
    /bin/chown $WEB_SERVER_USER $NAPLO_BACKUP_DIR
    /bin/chmod 700 $NAPLO_BACKUP_DIR

##
# mysql adatbázis mentése
##

for DATABASE in $DATABASES; do
    /usr/bin/mysqldump -p$MYSQL_PW -u$MYSQL_USER $DATABASE >> $NAPLO_BACKUP_DIR/$DATABASE.sql
done

##
# mysql adatbázis mentése
##

#    /usr/bin/mysqldump -p$MYSQL_PW -u$MYSQL_USER $MYSQL_DB >> $NAPLO_BACKUP_DIR/$MYSQL_DB.sql

##
# A honlap mentése
##

    mkdir $NAPLO_BACKUP_DIR/html

    /bin/cp -a $WWW_SSL_DIR/* $NAPLO_BACKUP_DIR/html/

##
# Az LDAP adatbázis
##

    /etc/init.d/slapd stop
    /bin/sleep 1

    /usr/sbin/slapcat -b $BASEDN -l $NAPLO_BACKUP_DIR/ldap.ldif

    /bin/cp -a $LDAP_DB_DIR $NAPLO_BACKUP_DIR/ldap

    /etc/init.d/slapd start

##
# LDAP konfig file-ok mentése (schema)
##

    /bin/mkdir $NAPLO_BACKUP_DIR/etc
    /bin/cp -a  $LDAP_CONF_DIR $NAPLO_BACKUP_DIR/etc/

##
# Becsomagolás
##

    cd $NAPLO_BACKUP_BASE_DIR
    /bin/tar cfz $DATE.tgz $DATE
    /bin/rm -rf $NAPLO_BACKUP_DIR

##
# Elavult mentés tölése
##

    if [ -e $NAPLO_BACKUP_BASE_DIR/$EXPIRE_DATE.tgz ]; then
        rm -rf $NAPLO_BACKUP_BASE_DIR/$EXPIRE_DATE.tgz
    fi
