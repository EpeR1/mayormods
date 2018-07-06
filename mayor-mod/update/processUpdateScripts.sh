#!/bin/bash

source $BASEDIR/update/linkme.sh

# Setting Mysql Connection Parameters
echo -e "[mysqld]
character-set-server = utf8
collation-server = utf8_hungarian_ci
[client]
default-character-set=utf8
host=$MYSQL_HOST
user=$MYSQL_USER
password=$MYSQL_PW
" > $BASEDIR/config/my.cnf
 
PRECHARSET="SET NAMES 'utf8' COLLATE 'utf8_hungarian_ci'; " ## fontos, hogy a ";" ott legyen a végén!!
#PRECHARSET="$PRECHARSET SET collation_connection = utf8_hungarian_ci; " ## ha a mysql server esetleg régebbi lenne.

MYSQL_CONFIG="--defaults-extra-file=$BASEDIR/config/my.cnf"
MYSQL_PARAMETERS=""
TEST=$($MYSQL -h$MYSQL_HOST -u$MYSQL_USER -p$MYSQL_PW -e exit 2>&1 >/dev/null)
if [ $? == "0" ]; then
    echo "SQL-connect test #2 OK"
    MYSQL_PARAMETERS="-h$MYSQL_HOST -u$MYSQL_USER -p$MYSQL_PW"
else
    echo "Hibás SQL csatlakozás(2)"
fi
TEST=$($MYSQL $MYSQL_CONFIG -e exit 2>&1 >/dev/null)
if [ $? == "0" ]; then 
    echo "SQL-connect test #1 OK"
    MYSQL_PARAMETERS=$MYSQL_CONFIG
else
    echo "Hibás SQL csatlakozás(1)"
fi
echo "Karakterkódolás ellenőrzése"
echo "SHOW VARIABLES LIKE 'character%';" | $MYSQL $MYSQL_PARAMETERS
# patch by Acsai Gabor <acsi@c3.hu> - FreeBSD
# Kiegészítve: Miklós Gergő <gergo@bmrg.hu> - Baár-Madas
# gondolatmenet: 
# A "default-character-set" beállítása után a "character_set_client" változóból 1db kell legyen, és értéke pontosan "utf8" kell legyen! 
### TEST=`echo "SHOW VARIABLES LIKE 'character_set_client'" | $MYSQL $MYSQL_PARAMETERS | grep utf8 | wc -l | tr -d " "`
TEST=$(echo "SHOW VARIABLES LIKE '%character_set_client%'" | $MYSQL $MYSQL_PARAMETERS | tail -n+2 | cut -f 2)
if [ "$TEST" == "utf8" ]; then
    echo "SQL-UTF8 test OK"
else
    echo -e "\n\nERROR: Hibás - nem utf8 - MySQL kliens karakterkódolás!\n"
    echo -e "Ellenőrizd a beállításokat és kérj segítséget a support@mayor.hu -n!\n"
    exit 12
fi
# --

run_script() {
    FILEELEJE=$(echo $FILE | cut -f 1 -d '-')
    FILEREV=${FILEELEJE: -6}
    LOGSQL=""
    if [ "$FILEREV" != "" ]; then
	if [ "$FILEREV" -gt 3399 ]; then
	    LOGSQL="INSERT IGNORE mayorUpdateLog (scriptFile) VALUES ('${FILE}')"
	fi
    fi
    if [ ! "${FILEREV}" \< "${REVSTR}" ]; then
	FILEEXT=$(echo $FILE | cut -f 2 -d '.')
	if [ "$FILEEXT" == "sh" ]; then
	    echo "    - Szkript futtatása ($FILE)..."
	    . $FILE
	    if [ $? != 0 ]; then exit 13; fi
	elif [ "$FILEEXT" == "sql" ]; then
	    echo "    - SQL utasítások végrehajtása ($FILE)..."
	    FILEDB=$(echo $FILE | cut -f 1 -d '.' | cut -f 3 -d '-')
	    case "$FILEDB" in
	        naplo)
		    DBS=$DB_NAPLO
	        ;;
	        intezmeny)
		    DBS=$DB_INTEZMENY
	        ;;
	        auth)
		    DBS=$DB_AUTH
	        ;;
	        *)
		    DBS=$FILEDB
	        ;;
	    esac
	    for DB in $DBS; do
		echo -e "\n        $DB..."

		if [ "$FILEREV" != "" ]; then
		    if [ "$FILEREV" -gt 3399 ]; then
			TEST=$((echo $PRECHARSET && echo "SELECT count(*) as db FROM information_schema.TABLES WHERE TABLE_SCHEMA='${DB}' and TABLE_NAME='mayorUpdateLog'") | $MYSQL $MYSQL_PARAMETERS $DB | grep -v "db")
			if [ "$TEST" == "0" ]; then 
			    echo "      Hiányzik a mayorUpdateLog tábla... tovább..."
			    continue
			fi
		    fi
		fi

		case "$FILEDB" in
		    naplo)
			INTEZMENYDB=$(echo $DB | sed -e 's/\(naplo_\)\(.*\)\(_.*\)/intezmeny_\2/')
			INTEZMENYROVIDNEV=$(echo $DB | cut -f 2 -d '_')
			TANEV=$(echo $DB | cut -f 3 -d '_')
			(echo $PRECHARSET && cat $FILE && echo "${LOGSQL}") | sed -e "s/%INTEZMENYDB%/$INTEZMENYDB/g" | sed -e "s/%INTEZMENY%/$INTEZMENYROVIDNEV/g" | \
				    sed -e "s/%TANEV%/$TANEV/g" | $MYSQL $MYSQL_PARAMETERS $DB
		    ;;
		    auth)
			(echo $PRECHARSET && cat $FILE && echo "${LOGSQL}") | sed -e "s/%MYSQL_ENCODE_STR%/$MYSQL_ENCODE_STR/g" | $MYSQL $MYSQL_PARAMETERS $DB
		    ;;
		    intezmeny)
			INTEZMENYROVIDNEV=$(echo $DB | cut -f 2 -d '_')
			(echo $PRECHARSET && cat $FILE && echo "${LOGSQL}") | sed -e "s/%INTEZMENY%/$INTEZMENYROVIDNEV/g" | $MYSQL $MYSQL_PARAMETERS $DB
		    ;;
		    *)
			(echo $PRECHARSET && cat $FILE && echo "${LOGSQL}") | $MYSQL $MYSQL_PARAMETERS $DB
		    ;;
		esac
		if [ $? != 0 ]; then 
		    exit 13; 
		fi
	    done
	    [ ! -z $SQLLOG ] && echo $FILE >> $SQLLOG; 
	fi
    fi
}

echo "   Az új változat verziószáma: $UJ_REV"

REVSTR=$(printf "%06d" $REV)
MYSQL_ENCODE_STR=$(grep _MYSQL_ENCODE_STR $BASEDIR/config/main-config.php | sed -e "s/define('_MYSQL_ENCODE_STR','\(.*\)');/\\1/")

# mysql host beállítása
if [ "$MYSQL_HOST" == "" ]; then
    MYSQL_HOST="localhost"
fi
echo "   A MySQL backend a következő lesz: $MYSQL_HOST"
echo "   Figyelem! MySQL Master-Slave architekturát az upgrade script jelenleg nem támogat!!!"

echo -e "\n * Adatbázisok lekérdezése..."
if [ -f $MYSQL ]
then
    DB_MAYOR=$($MYSQL $MYSQL_PARAMETERS -e"$PRECHARSET SHOW DATABASES LIKE 'mayor\_%'" | grep -e '^mayor\_[^_]*$')	## ide elvileg nem kellene precharset, mert itt minden ASCCI/2
    DB_NAPLO=$($MYSQL $MYSQL_PARAMETERS -e"$PRECHARSET SHOW DATABASES LIKE 'naplo\_%\_%'" | grep -e '^naplo\_[^\_]*\_20[0-9][0-9]$')
    DB_INTEZMENY=$($MYSQL $MYSQL_PARAMETERS -e"$PRECHARSET SELECT CONCAT('intezmeny_',rovidNev) FROM mayor_naplo.intezmeny" | grep -e '^intezmeny\_[^\_]*$')
#    DB_INTEZMENY=`$MYSQL $MYSQL_PARAMETERS -e"SHOW DATABASES LIKE 'intezmeny\_%'" | grep -e '^intezmeny\_[^\_]*$'`
    if [ $? != 0 ]; then exit 12; fi
    DB_AUTH=""
    for DB in $DB_MAYOR; do
	if [ "$DB" == "mayor_parent" -o "$DB" == "mayor_private" -o "$DB" == "mayor_public" ]; then
	    DB_AUTH="$DB_AUTH $DB"
	fi
    done
else
    echo -e "\n\nERROR: A mysql kliens nem található: $MYSQL\n"
    exit 12
fi

if [ "$SKIPLOCK" != "1" ]; then
    if [ ! -z $LOCKFILE ]; then
	echo -e "\n * A web-es elérés letiltása:"
	echo -n "    - Lock-file létrehozása... "
	echo "$DATE: Update process runing... " > $LOCKFILE
	echo "kész."

	echo -n "    - Aktív munkamenetek törlése... "
	$MYSQL $MYSQL_PARAMETERS -e"DELETE FROM mayor_login.session"   ##itt se kell, jó az ASCII
	echo "kész."
    fi
else
	echo -e "\n * A lock-olást a kérésedre kihagyjuk..."
fi

echo -e "\n * Az előzetes frissítő állományok feldolgozása (pre*, rev*):\n"
for FILE in $(ls $UPDATEDIR/rev* $UPDATEDIR/pre* | sort); do
    run_script
done

if [ "$EXECONLY" != "1" ]; then
    echo -e "\n * A modulok állományainak frissítése:\n"
    for MODULE in $MODULES; do
	echo -e "\n     $MODULE... \n"
	if [ "$HTTP_SERVER" == '' ]; then
	    $SVN --force --quiet export https://svn.mayor.hu/svn/trunk/$MODULE "$BASEDIR"
	    if [ $? != 0 ]; then exit 13; fi
	else
	    # Ha rpm csomag futtatja, akkor már ki van csomagolva minden és nincs md5sum
	    if [ -f $TMPDIR/md5sum ]; then 
		MOD=$(echo $MODULE | sed "s#/#-#")
		FILE=$(grep "$MOD-rev" $TMPDIR/md5sum | cut -d ' ' -f 3)
		if [ -f $TMPDIR/$FILE ]; then
    		    tar xfz $TMPDIR/$FILE -C $BASEDIR
		    if [ $? != 0 ]; then exit 13; fi
		fi
	    fi
	fi
    done
    if [ -d $TMPDIR ]; then rm -rf $TMPDIR; fi
    echo -e "\nkész.\n"
fi

echo -e "\n * Az utólagos frissítő állományok feldolgozása (post*):\n"
for FILE in $(ls $UPDATEDIR/post* | sort); do
    run_script
done


if [ -e $LOCKFILE ]; then
    if [ ! -z $LOCKFILE ]; then
    echo -e "\n * A web-es hozzáférés engedélyezése:"
    rm $LOCKFILE
    fi
fi

if [ "$EXECONLY" != "1" ]; then
    echo -e "\n * A szükséges jogosultságok beállítása:"
    chmod +x $BASEDIR/bin/mayor
    echo -n "  ... "
    chown -R $WEB_SERVER_USER $BASEDIR/download
    chown -R $WEB_SERVER_USER $BASEDIR/cache
    if [ -d $BASEDIR/www/wiki/conf ]; then
        chown -R $WEB_SERVER_USER $BASEDIR/www/wiki/conf $BASEDIR/www/wiki/data
    fi
    chown $WEB_SERVER_USER $BASEDIR/config
    chmod 700 $BASEDIR/config
    echo -e "... kész.\n"

    echo -e "\n * Szimbolikus linkek ellenőrzése/létrehozása"
    POLICIES="parent public"
    for POLICY in $POLICIES; do
        eval "LIST=\$${POLICY}Link"
        for f in $LIST; do
            DIR=$(echo $f | cut -d / -f 1-2)
            if [ ! -d $BASEDIR/www/policy/$POLICY/$DIR ]; then
                echo "    Könyvtár: $BASEDIR/www/policy/$POLICY/$DIR"
                mkdir -p $BASEDIR/www/policy/$POLICY/$DIR
            fi
            FILES="$f-pre.php $f.php"
            for file in $FILES; do
                if [ ! -e $BASEDIR/www/policy/$POLICY/$file ]; then
                    if [ -f $BASEDIR/www/policy/private/$file ]; then
                        echo "      $BASEDIR/www/policy/private/$file --> $BASEDIR/www/policy/$POLICY/$file"
                        ln -s $BASEDIR/www/policy/private/$file $BASEDIR/www/policy/$POLICY/$file
                    else
                        echo "      Hiányzó file: $BASEDIR/www/policy/private/$file"
                    fi
                fi
            done
        done
        eval "DLIST=\$${POLICY}Deny"
        for f in $DLIST; do
            FILES="$f-pre.php $f.php"
            for file in $FILES; do
                if [ -e $BASEDIR/www/policy/$POLICY/$file ]; then
                    echo "      $BASEDIR/www/policy/$POLICY/$file link(file) törlése"
                    rm "$BASEDIR/www/policy/$POLICY/$file"
                fi
            done
        done
    done
fi # execonly

echo -e "\n * Az frissített verziószám rögzítése ($UJ_REV)"
if [ "$UJ_REV" -gt 0 ]; then 
    echo $UJ_REV > $REVISION_FILE
else
    echo "      Hibás (nulla) verziószám! Nem rögzítjük."
fi

[ -x "$LOCAL_UPDATE_SCRIPT" ] && echo -e "\n * Helyi szkript futtatása: $LOCAL_UPDATE_SCRIPT...\n " && . "$LOCAL_UPDATE_SCRIPT"
