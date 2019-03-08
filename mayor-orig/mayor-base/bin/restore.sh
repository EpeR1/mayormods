#!/bin/bash

OPT_SPEC="hkprf:d:t::"
LONG_OPT_SPEC="help,keep-old-passwords,restore-parent,restore-private,tmp-dir:,file:,backup-file:,dir:,base-dir::"
PARSED_OPTIONS=$(getopt -n "$0" -a -o $OPT_SPEC --long $LONG_OPT_SPEC -- "$@")
OPTIONS_RET=$?
eval set -- "$PARSED_OPTIONS"

help_usage() {
cat <<EOF

Restore használata: mayor restore [opciók]

A parancs segítségével korábbi mentésből állíthatjuk helyre a napló adaokat. A helyreállítás előtt
telepíteni kell egy új, lehetőleg a mentettel megegyező, vagy újabb verziójú rendszert, amibe a régi
adatokat betöltjük.
A mentést alapértelmezetten az aktuális dátum alapján keresi, "YYYYmmdd.tgz" alakban (pl. $DATE.tgz).

Amennyiben a paraméterként megadott alapkönyvtárban léteznek a configurációs állományok, úgy a program
ezeket használja, különben a mentésből állítja őket helyre. A jelszavak alapértelmezetten mindig az
új telepítés szerintiek maradnak.

Opciók:
    -h, --help:                A parancs leírása (amit most olvasol...)
    -f, --file, --backup-file: A visszatöltendő mentési állomány neve
    -d, --dir, --base-dir:     Az alapkönyvtár elérési útja
    -t, --tmp-dir:             Az ideiglenes könyvtár
    -k, --keep-old-passwords:  A mentett adatbázis-jelszavak megtartása akkor is, ha léteznek újak
    -p, --restore-parent:      A szülők felhasználói azonosítóinak helyreállítása
    -r, --restore-private:     A tanár/diák felhasználói azonosítóinak helyreállítása (csak MySQL-ből!)

EOF
}

cleartmp() {
    echo -e -n "\n\nAz ideiglenes könyvátrat töröljük: ${RTMPDIR}/restore/${DT}..."
    rm -rf ${RTMPDIR}/restore/${DT}
    echo "ok"
}

#if [ $OPTIONS_RET -ne 0 ] || [ $# -le 1 ]; then  help_usage; exit; fi
if [ $OPTIONS_RET -ne 0 ]; then  help_usage; exit; fi

# Alapéretelmezett értékek:        
RBACKUPFILE="${DATE}.tgz"
RBASEDIR="/var/mayor"
KEEP_OLD_PASSWORDS=0

while [ $# -ge 1 ]; do
    case $1 in
        --help | -h )           	help_usage
                            		exit
                            		;;

	--keep-old-passwords | -k )	KEEP_OLD_PASSWORDS=1
					echo "A mentett adatbázis-jelszavak használata..."
					;;

	--restore-parent | -p )  	RESTORE_PARENT=1
					echo "Szülői azonosítók betöltése..."
					;;

	--restore-private | -r )  	RESTORE_PRIVATE=1
					echo "Tanár/diák azonosítók betöltése..."
					;;

        --backup-file | --file | -f )	shift
                            		RBACKUPFILE="$1"
                            		echo "Backup fájl: $RBACKUPFILE"
                            		;;
    
        --base-dir | --dir | -d ) 	shift
                            		RBASEDIR="$1"
                            		echo "Alapkönyvtár: $RBASEDIR"
                            		;;

        --tmp-dir | -t )        	shift
                            		RTMPDIR="$1"
                            		echo "Ideiglenes könyvtár: $RTMPDIR"
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

##
# A könyvtárak létrehozása
##

RREVISIONFILE="${RBASEDIR}/log/revision"
declare -i INST_REV
if [ ! -e "${RREVISIONFILE}"  ]; then
    echo "Hiba: A megadott alapkönyvtárban (${RBASEDIR}) nem található telepített MaYoR rendszer!"
    echo -e "      A helyreállításhoz először telepíteni kell egy azonos, vagy újabb verziójú rendszert.\n"
    exit 1
fi
INST_REV=`cat $RREVISIONFILE`
echo "A telepített rendszer revision száma: ${INST_REV}"

if [ ! -e "${RBACKUPFILE}" ]; then
    echo "Hiba: A backup állomány nem található (${RBACKUPFILE})"
    echo -e "      A backup állományt teljes elérési úttal kell megadni!\n"
    exit 2
fi

[ -z "${RTMPDIR:-}" ] && RTMPDIR="/tmp"
if [ ! -e "${RTMPDIR}" ]; then
    echo "Az ideiglenes könyvtár (${RTMPDIR}) nem létezik."
    echo -n "Létrehozás ... "
    if mkdir -p "${RTMPDIR}" 2>/dev/null; then 
	echo "ok."
    else 
	echo "hiba." 	
	exit 3
    fi
fi

if [ ! -d "${RTMPDIR}/restore" ]; then
    if ! mkdir "${RTMPDIR}/restore" 2>/dev/null; then 
	echo "Hiba: Az ideiglenes könyvtár nem írható (${RTMPDIR})!"
	exit 4
    fi
fi
chmod 700 "${RTMPDIR}/restore"
cd "${RTMPDIR}/restore"
tar xvfz $RBACKUPFILE
DT=$(ls)
echo "A mentés dátuma: ${DT}"
cd ${DT}

RREVISIONFILE="${RTMPDIR}/restore/${DT}/log/revision"
declare -i BAK_REV
if [ ! -e "${RREVISIONFILE}"  ]; then
    echo "Hiba: A mentési állomány nem tartalmaz verzió információt!"
    echo -e "      A mentési állományon belül, a ${DT}/log/revision állományba írja be a revision számot (pl: 2512)!\n"
    cleartmp
    exit 5
fi
BAK_REV=`cat $RREVISIONFILE`
echo "A mentett rendszer revision száma: ${BAK_REV}"

if [ ${BAK_REV} -gt ${INST_REV} ]; then
    echo "Hiba: A mentett rendszer újabb, mint a telepített!"
    echo -e "      A telepített rendszer revision száma nagyobb vagy egyenlő kell legyen a mentett rendszer revision számánál!\n      Telepítsen frissíebb rendszert!\n"
    cleartmp
    exit 6
fi

# A telepített rendszer beállításainsak betöltése
if [ -f "${RBASEDIR}/config/main.conf" ]; then
    . "${RBASEDIR}/config/main.conf"
else
    echo "Hiba: A telepített rendszer nincs beállítva!"
    echo -e "      Hiányzik a konfigurációs állomány: ${RBASEDIR}/config/main.conf\n"
    cleartmp
    exit 7
fi

# Az adatbázisok betöltése
NAPLOUSER=`egrep 'userWrite.*=' $BASEDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
NAPLOUSERREAD=`egrep 'userRead.*=' $BASEDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
DBS=`$MYSQL -h$MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PW -e"SHOW DATABASES"`
if [ "$RESTORE_PARENT" == "1" ]; then
    FILES="mayor_naplo.sql mayor_parent.sql intezmeny_*.sql naplo_*.sql"
else
    FILES="mayor_naplo.sql intezmeny_*.sql naplo_*.sql"
fi
if [ "$RESTORE_PRIVATE" == "1" ]; then
    FILES="$FILES mayor_private.sql"
fi
for SQLFILE in $FILES; do
    if [ -e "${SQLFILE}" ]; then
	DB=${SQLFILE%.sql}
	echo -n "${DB} ... "
	if [[ ! ${DBS} =~ .*$DB.* ]]; then
	    echo -n "... "
	    (cat <<EOF
	    create database ${DB} character set utf8 collate utf8_hungarian_ci;
	    grant select,execute on ${DB}.* to '$NAPLOUSERREAD'@'localhost';
	    grant all on ${DB}.* to '$NAPLOUSER'@'localhost';
EOF
) | $MYSQL -h$MYSQL_HOST -u $MYSQL_USER -p$MYSQL_PW
	fi
	echo -n '... '
	cat ${SQLFILE} | mysql -p${MYSQL_PW} --user=${MYSQL_USER} --default-character-set=utf8 ${DB}
	echo ok
    fi
done

# A mentésben szereplő user és jelszó adatok
bNCDIR="${RTMPDIR}/restore/${DT}/config/module-naplo"
iNCDIR="${BASEDIR}/config/module-naplo"
cd ${bNCDIR}
bDB=`grep db ${bNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | grep naplo_base | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
bUSER=`egrep 'userWrite.*=' ${bNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
bUSERREAD=`egrep 'userRead.*=' ${bNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
bPW=`egrep 'pwWrite.*=' ${bNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
bPWREAD=`egrep 'pwRead.*=' ${bNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
#echo "mentett: $bDB : $bUSER : $bUSERREAD : $bPW : $bPWREAD"
# Az új telepítés user és jelszó adatai
if [ -e "${iNCDIR}/config.php" ] && [ "${KEEP_OLD_PASSWORDS}" != "1" ]; then
    iDB=`grep db ${iNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | grep naplo_base | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
    iUSER=`egrep 'userWrite.*=' ${iNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
    iUSERREAD=`egrep 'userRead.*=' ${iNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
    iPW=`egrep 'pwWrite.*=' ${iNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
    iPWREAD=`egrep 'pwRead.*=' ${iNCDIR}/config.php | egrep -v '^[[:space:]]*(//|/\*.*\*/$)' | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
else
    iDB=$bDB
    iUSER=$bUSER
    iUSERREAD=$bUSERREAD
    iPW=$bPW
    iPWREAD=$bPWREAD
fi
#echo "installált: $iDB : $iUSER : $iUSERREAD : $iPW : $iPWREAD"
if [ -e "${iNCDIR}/config.php" ]; then
    if [ ! -e "${iNCDIR}/config.php.backup" ]; then
	echo "${iNCDIR}/config.php --> ${iNCDIR}/config.php.backup"
	mv "${iNCDIR}/config.php" "${iNCDIR}/config.php.backup"
    fi
fi

cat "${bNCDIR}/config.php" | sed \
	-e "s/db\(.*\)$bDB\(.*\)/db\1$iDB\2/g" \
	-e "s/userWrite\(.*\)$bUSER\(.*\)/userWrite\1$iUSER\2/g" \
	-e "s/userRead\(.*\)$bUSERREAD\(.*\)/userRead\1$iUSERREAD\2/g" \
	-e "s/$bPW/$iPW/g" -e "s/$bPWREAD/$iPWREAD/g" > "${iNCDIR}/config.php"

for FILE in config-*.php; do
    echo -n "$FILE ... "
    # mentés
    if [ -e "${iNCDIR}/$FILE" ]; then
	if [ ! -e "${iNCDIR}/$FILE.backup" ]; then
	    echo -n "... "
	    mv "${iNCDIR}/$FILE" "${iNCDIR}/$FILE.backup"
	fi
    fi
    cp $FILE "${iNCDIR}/$FILE"
    echo ok
done

cd ${BASEDIR}/bin
if [ -z $UPDATELOG ]; then
    . update.sh -e -r$BAK_REV -b${BASEDIR}
else
    . update.sh -e -r$BAK_REV -b${BASEDIR} 2>&1 | tee -a $UPDATELOG
fi

# Az ideiglenes könyvtár törlése
cleartmp

