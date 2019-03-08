#!/bin/bash

OPT_SPEC="hes:lb:r::"
LONG_OPT_SPEC="http-server:,skip-lock,basedir:,backup-dir:,from-revision:,exec-only,help:"
PARSED_OPTIONS=$(getopt -n "$0" -a -o $OPT_SPEC --long $LONG_OPT_SPEC -- "$@")
OPTIONS_RET=$?

eval set -- "$PARSED_OPTIONS"

help_usage() {
cat <<EOF

UPDATE használata: mayor update [opciók] [modulok]

A parancs segítségével frissíthetjük a MaYoR rendszert, a program forrását és 
az adatbázis-szerkezetet. A frissítések letöltése és kicsomagolása mellett
a szkript egyéb módosításokat is végrehajthat, ezért alapértelmezett működés
esetén a frissítés idejére letiltjuk a napló elérését. A zárolás a 
/var/run/mayor.lock állomány létrehozásával történik.

A HTTP szervert, a frissítendő modulokat, a szükséges jelszavak és egyéb beál-
lításokat a /etc/mayor/main.conf állományban kell megadni.

Opciók:
    -h, --help:           A parancs leírása (amit most olvasol...)
    -s, --http-server:    A szerver ahonnan a frissítéseket letöltjük
    -b, --basedir:        A telepítési könyvtár
    -l, --skip-lock:      A frissítés idejére se zároljuk a program működését
    -r, --from-revision:  A frissítés indítása a megadott revision számtól - függetlenül a 
                          jelenleg eltárolt aktuális számtól
    -e, --exec-only:      A frissítő szkriptek futtatása új állományok letöltése nélkül.
                          Csak a --from-revision opcióval együtt használható!

Modulok:
    A frissítendő modulok listája, pl: mayor-base mayor-naplo mayor-portal

EOF
}

if [ $OPTIONS_RET -ne 0 ]; then  help_usage; exit; fi

declare -i REV
while [ $# -ge 1 ]; do
    case $1 in
        --help | -h )           help_usage
                                exit
                                ;;

        --skip-lock | -l )	SKIPLOCK=1
				export SKIPLOCK
                                ;;

        --exec-only | -e )	EXECONLY=1
				export EXECONLY
                                ;;

        --http-server | -s )	shift
                                HTTP_SERVER="$1"
                                echo "HTTP szerver: $HTTP_SERVER"
                                ;;

        --basedir | -b )	shift
                                BASEDIR="$1"
                                echo "Alapkönyvtár: $BASEDIR"
                                ;;

        --from-revision | -r )  shift
                            	REV="$1"
                            	echo "Revision: $REV"
                            	;;

        -- )                    shift
                                break
                                ;;

        * )                     echo "HIBA: ismeretlen opció: $1" # ide elvileg sose jutunk, mert a getopts már kiszűrte a hibás paraméterek
                                exit
                                ;;
    esac
    shift
done

TMPMODS=""
while [ $# -ge 1 ]; do
    if [[ ! "${MODS[*]}" =~ .*$1.* ]]; then
        echo -e "Ismeretlen modul: $1"
    else
	TMPMODS="$1 $TMPMODS"
    fi
    shift
done
if [ "$TMPMODS" != '' ]; then
    MODULES=$TMPMODS
fi

###########################


if [ -z $UPDATEDIR ]; then
    if [ -f ../config/update.conf ]; then
        echo -e "\n\nWARNING: elavult konfigurációs állomány! (update.conf)"
        echo "    A frissítési rendszer a korábbi backup.conf és update.conf állományok helyett"
        echo "    egy új, rögzített helyen lévő állományt használ: /etc/mayor/main.conf"
        echo "    Most - ennek hiányában - a régebbi állományt használjuk"
        . ../config/update.conf
    else
        echo -e "\n\nERROR: Hiányzó konfigurációs file: ../config/update.conf"
        pwd
        exit 10
    fi
fi

# Ha paraméterknént nem volt megadva revision
if [ -z "${REV:-}" ]; then
    if [ "$EXECONLY" == "1" ]; then
	echo "Hiba: Az --exec-only kapcsoló csak a --from-revision paraméterrel együtt használható!"
	exit 9
    fi
    if [ -f $REVISION_FILE ]; then
	REV=$(cat $REVISION_FILE)
    else
	REV=0
    fi
fi

echo -e "\n%%%%%%%%%%%%%%%%% $DATETIME %%%%%%%%%%%%%%%%%"
echo -e "\nFrissítés $REV számú változatról."

if [ "$EXECONLY" != "1" ]; then
    echo -e "\n * Az frissítéshez szükséges állományok letöltése (eltarthat pár percig)... "

    if [ "$MODULES" == '' ]; then
	echo -e "\n\nERROR: Nincs megadva a frissítendő modulok listája (/etc/mayor/main.conf)!\n"
	exit 1
    else
	declare -i UJ_REV
	if [ "$HTTP_SERVER" == '' ]; then
	    if [ -f $SVN ]; then
		# frissítés SVN-ből
		$SVN --force export https://svn.mayor.hu/svn/trunk/mayor-base/bin "$BASEDIR/bin" > /dev/null
		for MODULE in $MODULES; do
		    echo -n "     $MODULE... "
		    chmod +x $BASEDIR/bin/mayor
		    if [ $? != 0 ]; then exit 2; fi
		    UJ_REV=$($SVN --force export https://svn.mayor.hu/svn/trunk/$MODULE/update "$BASEDIR/update" | grep revision | cut -d ' ' -f 3 | uniq | sed -e 's/\.//g')
		    if [ $? != 0 ]; then exit 3; fi
		    echo "kész."
		done
	    else
	        echo -e "\n\nERROR: A subversion kliens nem található: $SVN"
	        exit 4
	    fi
	else
	    # Munkakönyvtár létrehozása
	    if [ ! -d $TMPDIR ]; then
		mkdir $TMPDIR
		chown -R 0:0 $TMPDIR
		chmod -R 700 $TMPDIR
	    else
		rm -rf $TMPDIR/*
	    fi
	    cd $TMPDIR
	    # Az md5sum állomány leszedáse
	    if [ -z $VERSION ]; then
		wget "http://www.mayor.hu/download/md5sum"
	    else
		wget "http://www.mayor.hu/download/$VERSION/md5sum"
		if [[ ! $HTTP_SERVER =~ .*$VERSION.* ]]; then HTTP_SERVER="$HTTP_SERVER/$VERSION"; fi
	    fi
	    if [ $? != 0 ]; then exit 5; fi
	    UJ_REV=$(grep Revision md5sum | cut -d ' ' -f 2)
	    if [ "$REV" -lt "$UJ_REV" ]; then
		# csomagok leszedése
		for MODULE in $MODULES; do
		    echo -n "     $MODULE... "
		    MOD=$(echo $MODULE | sed "s#/#-#")
		    FILE=$(grep "$MOD-rev" md5sum | cut -d ' ' -f 3)
		    if [ "$FILE" != '' ]; then
			wget "$HTTP_SERVER/$FILE"
			if [ $? != 0 ]; then
			    echo -e "\n\n      ERROR: Az frissítés nem tölthető le: $FILE!\n"
			    exit 6 
			fi
			grep $FILE md5sum | md5sum -c --status
			if [ $? == 0 ]; then
			    tar xfz $FILE -C $BASEDIR ./update 
			else
			    echo -e "\n\n      ERROR: Az ellenörző összeg nem egyezik!\n"
			    exit 7
			fi
		    else
			echo 'Nincs ilyen modul!'
			exit 8 # nem szabad hiányzó csomagok mellett frissíteni!!
		    fi
		done
	    fi
	    cd $BASEDIR/bin
	fi
    fi
else # exec-only
    if [ -f $REVISION_FILE ]; then
	UJ_REV=$(cat $REVISION_FILE)
    else
	UJ_REV=0 # nem frissítünk semmit
    fi
fi

if [ "$REV" -ge "$UJ_REV" ]; then
    echo -e "\nNincs újabb változat.\n"
    echo -e "\n * A szükséges jogosultságok beállítása:"
    chmod +x $BASEDIR/bin/mayor
    echo 'kész.'
else
    echo 'kész.'
    . $BASEDIR/update/processUpdateScripts.sh
fi

PWDTEX=$(pwd)
echo -e "\nMaYoR TeX formátum állomány újragenerálása... "
cd $BASEDIR/print/module-naplo/tex/ && fmtutil-sys --cnffile $BASEDIR/print/module-naplo/tex/mayor.cnf --fmtdir $BASEDIR/print/module-naplo/ --byfmt mayor > /dev/null 2>&1
echo -e "\nMaYoR XeTeX formátum állomány újragenerálása... "
cd $BASEDIR/print/module-naplo/xetex/ && fmtutil-sys --cnffile $BASEDIR/print/module-naplo/xetex/mayor-xetex.cnf --fmtdir $BASEDIR/print/module-naplo/ --byfmt mayor-xetex > /dev/null 2>&1
cd $PWDTEX
echo 'kész.'

echo -e "\nFrissítés vége.\n"
exit 0
