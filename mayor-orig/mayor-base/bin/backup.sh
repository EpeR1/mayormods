#!/bin/bash

OPT_SPEC="hf:d::"
LONG_OPT_SPEC="help,file:,backup-file:,dir:,backup-dir::"
PARSED_OPTIONS=$(getopt -n "$0" -a -o $OPT_SPEC --long $LONG_OPT_SPEC -- "$@")
OPTIONS_RET=$?
eval set -- "$PARSED_OPTIONS"

help_usage() {
cat <<EOF

Backup használata: mayor backup [opciók]

A parancs segítségével menthetjük a MaYoR rendszer adatbázisait, aktuális forrását és 
beállításait. A mentés alapértelmezetten az aktuális dátum alapján lesz elnevezve 
"YYYYmmdd.tgz" alakban (pl. $DATE.tgz).

A mentési könyvtár, a szükséges jelszavak és egyéb mentési paraméterek beállításait a 
/etc/mayor/main.conf állományban kell megadni.

Opciók:
    -h, --help:                A parancs leírása (amit most olvasol...)
    -f, --file, --backup-file: A mentési állomány neve
    -d, --dir, --backup-dir:   A mentési könyvtár elérési útja

EOF
}

#if [ $OPTIONS_RET -ne 0 ] || [ $# -le 1 ]; then  help_usage; exit; fi
if [ $OPTIONS_RET -ne 0 ]; then  help_usage; exit; fi
                                
BACKUPFILE="$DATE"
while [ $# -ge 1 ]; do
    case $1 in
        --help | -h )           	help_usage
                            		exit
                            		;;

        --backup-file | --file | -f )	shift
                            		BACKUPFILE="$1"
                            		echo "Backup fájl: $BACKUPFILE"
                            		;;
    
        --backup-dir | --dir | -d ) 	shift
                            		BACKUPDIR="$1"
                            		echo "Backup könyvtár: $BACKUPDIR"
                            		;;

        -- )                        	shift
                            		break
                            		;;

        * )                     	echo "HIBA: ismeretlen opció: $1" # ide elvileg sose jutunk, mert a getopts már kiszűrte a hibás paramétereket...
                            		exit
                            		;;
    esac
    shift
done

###########################

if [ -z $BACKUPDIR ]; then
    if [ -f ../config/backup.conf ]; then
        echo "WARNING: elavult konfigurációs állomány! (backup.conf)"
        echo "    A frissítési rendszer a korábbi backup.conf és update.conf állományok helyett"
        echo "    egy új, rögzített helyen lévő állományt használ: /etc/mayor/main.conf"
        echo "    Most - ennek hiányában - a régebbi állományt használjuk"
        . ../config/backup.conf
    else
        echo -e "\n\nERROR: Hiányzó konfigurációs file: ../config/backup.conf"
	pwd
        exit 11
    fi
fi

##
# A könyvtárak létrehozása
##

if [ ! -e  $BACKUPDIR ]; then
    mkdir $BACKUPDIR > /dev/null 2>&1
    if [ $? -ne 0 ]; then
	echo "Nem sikerült a ${BACKUPDIR} könyvtárat létrehozni!"
	echo "MaYoR Backup failure!"
	exit 1
    fi
fi
chown $WEB_SERVER_USER $BACKUPDIR
chmod 700 $BACKUPDIR

if [ -e $BACKUPDIR/$BACKUPFILE.tgz ]; then
    echo -e "\n\nERROR: már volt mentés: $BACKUPDIR/$BACKUPFILE.tgz\n"
    exit 1
fi
mkdir $BACKUPDIR/$BACKUPFILE
chown $WEB_SERVER_USER $BACKUPDIR/$BACKUPFILE
chmod 700 $BACKUPDIR/$BACKUPFILE

##
# mysql adatbázis mentése
##

if [ "$MYSQL_HOST" == "" ]; then
    MYSQL_HOST="localhost"
fi

if [ -f $MYSQL ]; then
    DATABASES=''
    for DB in `echo 'SHOW DATABASES' | mysql -h$MYSQL_HOST -p$MYSQL_PW -u$MYSQL_USER`; do
	if [[ ! $EXCLUDED_DBS =~ .*$DB.* ]] && { [[ $DB =~ ^mayor.* ]] || [[ $DB =~ ^naplo.* ]] || [[ $DB =~ ^intezmeny.* ]]; } then 
	    DATABASES="$DATABASES $DB"
	fi
    done

else
    echo -e "\n\nERROR: A mysql kliens nem található: $MYSQL\n"
    exit 2
fi

for DATABASE in $DATABASES; do
    mysqldump -R -h$MYSQL_HOST -p$MYSQL_PW -u$MYSQL_USER $DATABASE >> $BACKUPDIR/$BACKUPFILE/$DATABASE.sql
done

##
# A honlap mentése
##

mkdir $BACKUPDIR/$BACKUPFILE/log
cp -a $BASEDIR/log/revision $BACKUPDIR/$BACKUPFILE/log/revision
cp -a $BASEDIR/www $BACKUPDIR/$BACKUPFILE/www
cp -a $BASEDIR/config $BACKUPDIR/$BACKUPFILE/config


if [ "$SAVELDAP" == 1 ]; then

    ##
    # Az LDAP adatbázis
    ##

    /etc/init.d/slapd stop
    sleep 1

    slapcat -b $BASEDN -l $BACKUPDIR/$BACKUPFILE/ldap.ldif

    cp -a $LDAPDBDIR $BACKUPDIR/$BACKUPFILE/ldap

    /etc/init.d/slapd start

    ##
    # LDAP konfig file-ok mentése (schema)
    ##

    mkdir $BACKUPDIR/$BACKUPFILE/etc
    cp -a  $LDAPCONFDIR $BACKUPDIR/$BACKUPFILE/etc/

fi

##
# Becsomagolás
##

cd $BACKUPDIR
## Ez a korábbi szerintem hibás:
## tar cfz ${BACKUPFILE}.tgz ${DATE}
tar cfz ${BACKUPFILE}.tgz ${BACKUPFILE}
rm -rf $BACKUPFILE
#Debian6 inkompatibilis: tar cfz ${BACKUPFILE}.tgz --remove-files ${BACKUPFILE}

##
# Mentés átmásolása másik szerverre
# rsync # Losonci János kiegészítése (losy@agymk.sulinet.hu)
##

if [ "$RSYNC" == 1 ]; then
    RSYNCBIN=`which rsync`
    if [ "$RSYNCBIN" != "" ]; then
       echo $RSYNCBIN -auvE $BACKUPDIR/   $RUSER@$RHOST:$RPATH/
       $RSYNCBIN -auvE $BACKUPDIR/   $RUSER@$RHOST:$RPATH/
       if [ $? -ne 0 ]; then
	    echo "rsync error!"
       fi
    fi
fi

##
# Elavult mentés törlése
##

declare -i BDAYS=BACKUPDAYS
if [ $BDAYS -gt 0 ]; then
    find $BACKUPDIR -mtime +$BDAYS -exec rm {} \;
fi
