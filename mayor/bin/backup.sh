#!/bin/bash

OPT_SPEC="hnlf:d::"
LONG_OPT_SPEC="help,now,skip-lock,file:,backup-file:,dir:,backup-dir::"
PARSED_OPTIONS=$(getopt -n "$0" -a -o $OPT_SPEC --long $LONG_OPT_SPEC -- "$@")
OPTIONS_RET=$?
eval set -- "$PARSED_OPTIONS"

help_usage() {
cat <<EOF

Backup használata: mayor backup [opciók]

A parancs segítségével menthetjük a MaYoR rendszer adatbázisait, aktuális forrását és 
beállításait. A mentés alapértelmezetten az aktuális dátum alapján lesz elnevezve 
"YYYYmmdd.tgz" alakban (pl. $DATE.tgz).

Lehetőség van azonnali mentésre, akkor is, ha már volt aznap mentés, 
ehhez használjuk a --now kapcsolót.

A mentési könyvtár, a szükséges jelszavak és egyéb mentési paraméterek beállításait a 
/etc/mayor/main.conf állományban kell megadni.

Újdonság:
  Ezentúl a backup folyamat egyben a napló Lock-file -vel történő ideiglenes zárolásával jár,
  ha ezt el szeretnénk kerülni, akkor használjuk a --skip-lock kapcsolót.

Opciók:
    -h, --help:			A parancs leírása (amit most olvasol...)
    -f, --file, --backup-file:	A mentési állomány neve
    -d, --dir, --backup-dir:	A mentési könyvtár elérési útja
    -l, --skip-lock:		A mellőzi a mayor zárolását a backup futása alatt.
    -n,	--now:			Mentést készít most/azonnal.

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

        --skip-lock | -l )		SKIPLOCK=1
					;;

        --now | -n )			shift
        				DATE=$(date "+%Y%m%d_%H%M%S")
					BACKUPFILE="$DATE"
                                        #echo "Backup fájl: $BACKUPFILE.tgz"
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

### A lockfile beállítása

LOCK_MARAD=0
if [ "$SKIPLOCK" != "1" ]; then
    if [ ! -z $LOCKFILE ] && [ ! -f $LOCKFILE ]; then
	if [ "$VERBOSE" -gt 0 ]; then
            echo -e "* A web-es elérés letiltása:"
            echo -n "- Lock-file létrehozása... "
            echo -e "kész."
	fi
	echo "$DATE: Backup fut... " > $LOCKFILE
#        echo -n "    - Aktív munkamenetek törlése... "	### erre elvileg itt nincs szükség
#        $MYSQL $MYSQL_PARAMETERS -e"DELETE FROM mayor_login.session"
#        echo "kész."
    else
	if [ $VERBOSE -gt 1 ]; then   echo -e "* A web-es elérés már le van tiltva...";	fi
	LOCK_MARAD=1
    fi
else
	if [ $VERBOSE -gt 1 ]; then  echo -e "* A lock-olást a kérésedre kihagytam...";	fi
fi

function freeup_lock {
if [ -e $LOCKFILE ]; then
    if [ ! -z $LOCKFILE ] && [ $LOCK_MARAD != 1 ]; then
	if [ $VERBOSE -gt 1 ]; then echo -e "-"; echo -n "* A web-es hozzáférés engedélyezése:..."; fi
    rm $LOCKFILE
	if [ $VERBOSE -gt 1 ]; then echo -e "kész."; echo -e "-"; fi
    fi
fi
}


##
# A könyvtárak létrehozása
##

if [ ! -e  $BACKUPDIR ]; then
    mkdir $BACKUPDIR > /dev/null 2>&1
    if [ $? -ne 1 ]; then
	echo "*** Nem sikerült a $BACKUPDIR könyvtárat létrehozni!"
	echo "**** MaYoR Backup failure! ****"
	freeup_lock
	exit 1
    fi
fi
chown $WEB_SERVER_USER $BACKUPDIR
chmod 700 $BACKUPDIR

if [ -e $BACKUPDIR/$BACKUPFILE.tgz ]; then
    echo -e "\n**** ERROR: már volt mentés: $BACKUPDIR/$BACKUPFILE.tgz ****\n"
    freeup_lock
    exit 1
fi
mkdir $BACKUPDIR/$BACKUPFILE
chown $WEB_SERVER_USER $BACKUPDIR/$BACKUPFILE
chmod 700 $BACKUPDIR/$BACKUPFILE

##
# mysql adatbázis mentése
##
if [ $VERBOSE -gt 0 ]; then
    echo -e "-"
    echo -e "* Backup fájl: $BACKUPFILE.tgz"
fi

if [ "$MYSQL_HOST" == "" ]; then
    MYSQL_HOST="localhost"
fi

DB_INTEZMENYEK=''
if [ -f $MYSQL ] && [ -f $MYSQLDUMP ]; then
   
    if [ -f "$BASEDIR/config/my.cnf" ]; then
        MYSQL_CONFIG="--defaults-extra-file=$BASEDIR/config/my.cnf" ##Vigyázat! a 'mysql' kliens rossz! Néha beleveszi a $(pwd) tartamát is!
	TEST=$(echo "SHOW VARIABLES LIKE '%character_set_client%'" | $MYSQL "$MYSQL_CONFIG" | tail -n+2 | cut -f 2) 	
### 	Csak character_set_client=utf8 engedélyezett
	if [ "$TEST" == "utf8" ]; then
	    if [ $VERBOSE -gt 2 ]; then echo -e "-   MySQL-connect OK (my.cnf)" ; fi
	else
	    MYSQL_CONFIG="-h$MYSQL_HOST -u$MYSQL_USER -p$MYSQL_PW --default-character-set=utf8"
	fi
    else
	MYSQL_CONFIG="-h$MYSQL_HOST -u$MYSQL_USER -p$MYSQL_PW --default-character-set=utf8"	    
    fi

### Ellenőrizzük
    TEST=$(echo "SHOW VARIABLES LIKE '%character_set_client%'" | $MYSQL $MYSQL_CONFIG | tail -n+2 | cut -f 2)
    if [ "$TEST" == "utf8" ]; then
	if [ $VERBOSE -gt 2 ]; then  echo -e "-   MySQL-connect OK (utf8+user/passw)"; fi
    else
	echo -e "*** MySQL-connect ERROR (nem utf8 vagy hibás user/passw)"
	exit 2
    fi

    DATABASES=''
    for DB in $(echo 'SHOW DATABASES' | $MYSQL $MYSQL_CONFIG); do
	if [[ ! $EXCLUDED_DBS =~ .*$DB.* ]] && { [[ $DB =~ ^mayor.* ]] || [[ $DB =~ ^naplo.* ]] || [[ $DB =~ ^intezmeny.* ]]; } then 
	    DATABASES="$DATABASES $DB"
	fi
    done
    DB_INTEZMENYEK=$($MYSQL $MYSQL_CONFIG -e "SET NAMES 'utf8'; SELECT rovidNev FROM mayor_naplo.intezmeny; " | tail -n+2 ) ### Elvileg itt sem lehetne ékezetes

else
    echo -e "*** MySQL ERROR: A mysql kliens nem található: $MYSQL\n"
    freeup_lock
    exit 2
fi


##
# Adatbázisok mentése
##

if [ $VERBOSE -gt 2 ]; then echo -e "* Adatbázisok mentése:"; fi

for DATABASE in $DATABASES; do
    if [ ! -z $MYSQLDUMP ]; then
        ### SET NAMES-hez: --set-charset 
        ### Szebb: --result-file=file_name
#       mysqldump $MYSQL_CONFIG -R --set-charset --result-file="$BACKUPDIR/$BACKUPFILE/$DATABASE.sql" $DATABASE
        $MYSQLDUMP $MYSQL_CONFIG -R --set-charset --result-file="$BACKUPDIR/$BACKUPFILE/$DATABASE.sql" $DATABASE
    else
	 mysqldump $MYSQL_CONFIG -R --set-charset --result-file="$BACKUPDIR/$BACKUPFILE/$DATABASE.sql" $DATABASE
    fi
    if [ $VERBOSE -gt 3 ]; then echo -e "-   $DATABASE"; fi
done


##
# A honlap mentése
##
if [ $VERBOSE -gt 2 ]; then echo -e "* Fájlok mentése:"; fi
mkdir $BACKUPDIR/$BACKUPFILE/log
cp -a $BASEDIR/log/revision $BACKUPDIR/$BACKUPFILE/log/revision
if [ $VERBOSE -gt 3 ]; then echo -e "-   revision"; fi
cp -a $BASEDIR/www $BACKUPDIR/$BACKUPFILE/www
if [ $VERBOSE -gt 3 ]; then echo -e "-   www/*"; fi
cp -a $BASEDIR/config $BACKUPDIR/$BACKUPFILE/config
if [ $VERBOSE -gt 3 ]; then echo -e "-   config/*"; fi

if [ $VERBOSE -gt 2 ]; then echo -e "* Templétek mentése:"; fi
for RN in $(echo $DB_INTEZMENYEK); do	## a nyomtatási templétek is legyenek benne a mentésben
if [ -d "$BASEDIR/print/module-naplo/templates/$RN" ]; then
   mkdir -p $BACKUPDIR/$BACKUPFILE/print/module-naplo/templates/
   cp -a $BASEDIR/print/module-naplo/templates/$RN $BACKUPDIR/$BACKUPFILE/print/module-naplo/templates/$RN
   if [ $VERBOSE -gt 3 ]; then  echo -e "-   $RN/*"; fi
fi
done

if [ "$SAVELDAP" == 1 ]; then

    ##
    # Az LDAP adatbázis
    ##
    if [ $VERBOSE -gt 1 ]; then  echo -e "* LDAP mentése"; fi

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

if [ $VERBOSE -gt 2 ]; then echo -e "* Becsaomagolás"; fi
cd $BACKUPDIR
#### Ez a korábbi szerintem hibás:
#### tar cfz ${BACKUPFILE}.tgz ${DATE}
#tar cfz ${BACKUPFILE}.tgz ${BACKUPFILE}   ##Ez is néha...

# ez már jó
tar -czf "$BACKUPFILE.tgz" "$BACKUPFILE/"
rm -rf $BACKUPFILE
#Debian6 inkompatibilis: tar cfz ${BACKUPFILE}.tgz --remove-files ${BACKUPFILE}

if [ $VERBOSE -gt 0 ]; then echo -e "* Takarítás"; fi
##
# Mentés átmásolása másik szerverre
# rsync # Losonci János kiegészítése (losy@agymk.sulinet.hu)
##

if [ "$RSYNC" == 1 ]; then
    if [ $VERBOSE -gt 2 ]; then echo -e "* RSYNC küldés"; fi
    RSYNCBIN=$(which rsync)
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

###
## Végül a lock-olás feloldása
#
freeup_lock
if [ $VERBOSE -gt 1 ]; then echo -e "* Backup-script vége.\n"; fi

###