#!/bin/bash
#
# Example: mayor crypt-backup --backup-file=/tmp/wiki.tgz
# Az eredmény a $BACKUPDIR/$DATE-crypt.tgz állományba kerül
# (Vagy jobb lenne, ha a file nevéből venné a nevét?)
#
# TODO: ellenőrizni kell még, hogy van-e openssl és aespipe
#

OPT_SPEC="hf:d::"
LONG_OPT_SPEC="help,file:,backup-file:,dir:,backup-dir::"
PARSED_OPTIONS=$(getopt -n "$0" -a -o $OPT_SPEC --long $LONG_OPT_SPEC -- "$@")
OPTIONS_RET=$?
eval set -- "$PARSED_OPTIONS"

help_usage() {
cat <<EOF

Crypt-backup használata: mayor crypt-backup [opciók]

A parancs segítségével titkosíthatjuk meglévő mentési állományunkat. Az így kapott
állomány akár nyilvános tárhelyen is tárolható, kibontásához az intézmény privát
kulcsa szükséges. 
A script alapértelmezetten az aktuális dátum alapján elnevezett mentési állományt
keres: "YYYYmmdd.tgz" alakban (pl. $DATE.tgz), ebből készít "YYYYmmdd-crypt.tgz"
nevű állományt (pl. ${DATE}-crypt.tgz).

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
        --help | -h )                   help_usage
                                        exit
                                        ;;

        --backup-file | --file | -f )   shift
                                        BACKUPFILE="$1"
                                        echo "Backup fájl: $BACKUPFILE"
                                        ;;

        --backup-dir | --dir | -d )     shift
                                        BACKUPDIR="$1"
                                        echo "Backup könyvtár: $BACKUPDIR"
                                        ;;

        -- )                            shift
                                        break
                                        ;;

        * )                             echo "HIBA: ismeretlen opció: $1" # ide elvileg sose jutunk, mert a getopts már kiszűrte a hibás paraméterek
                                        exit
                                        ;;
    esac
    shift
done

############################

# mysql bináris ellenőrzése
if [ ! -f $MYSQL ]; then
    echo -e "\n\nERROR: A mysql kliens nem található: $MYSQL\n"
    exit 1
fi

# Login adatbázis eléréséhez szükséges paraméterek lekérdezése a konfig-ból...
DB=`grep db $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`
USER=`grep user $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`
PW=`grep pw $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`

# Ellenőrizzük, hogy van-e publikus kulcs - regisztrált intézmény-e
COUNT=`$MYSQL -e 'SELECT count(publicKey) FROM mayorSsl' -p$PW -u$USER $DB | grep -i -v count`
if [ "$COUNT" -ne "1" ]; then
    echo "Publikus kulcsok száma: $COUNT"
    echo "Nem regisztrált intézmény? A mentés titkosítása csak regisztrált intézmények számára érhető el!"
    exit 2
fi

# Backup könyvtár létrehozása
if [ ! -e  $BACKUPDIR ]; then
    mkdir $BACKUPDIR > /dev/null 2>&1
    if [ $? -ne 0 ]; then
        echo "Nem sikerült a ${BACKUPDIR} könyvtárat létrehozni!"
        echo "MaYoR Backup failure!"
        exit 3
    fi
fi
CRYPTDIR=$BACKUPDIR/$DATE
if [ ! -e  $CRYPTDIR ]; then
    mkdir $CRYPTDIR
fi
chown $WEB_SERVER_USER $BACKUPDIR
chmod 700 $BACKUPDIR

if [ ! -f $BACKUPFILE ]; then
    # Próbáljuk meg a mai dátum szerinti backup-ot (alapértelmezés)
    echo -e "    HIBA: Hiányzik a kódolandó mentési állomámny: (${BACKUPFILE})\n"
    BACKUPFILE=$BACKUPDIR/${BACKUPFILE}
    echo "Próbáljuk a mentéi könyvtáron belül: ${BACKUPFILE}"
    if [ ! -f $BACKUPFILE ]; then
	echo -e "    HIBA: Hiányzik a kódolandó mentési állomámny: (${BACKUPFILE})\n"
	BACKUPFILE=$BACKUPDIR/${PREFIX}${DATE}.tgz
	echo "Próbáljuk az alapértelmezett állománynevet: ${BACKUPFILE}"
	if [ ! -e $BACKUPFILE ]; then
	    echo -e "    HIBA: Hiányzik a kódolandó mentési állomámny: (${BACKUPFILE})\n"
	    exit 3
	fi
    fi
fi
BASENAME=`basename $BACKUPFILE`

# Publikus kulcs lekérdezése
echo 'SELECT publicKey FROM mayorSsl' | $MYSQL -p$PW -u$USER $DB | grep -v publicKey | sed -e 's/\\n/\n/g' > $CRYPTDIR/id_rsa.pub

# Véletlen kulcs generálás a szimmetrikus AES kódoláshoz
pwgen -nc 50 1 > $CRYPTDIR/key.txt

# AES kódolás
aespipe -P $CRYPTDIR/key.txt -e aes256 < $BACKUPFILE > $CRYPTDIR/$BASENAME.aes

# A kulcsok RSA kódolása a publikus kulccsal
openssl rsautl -encrypt -inkey $CRYPTDIR/id_rsa.pub -pubin -in $CRYPTDIR/key.txt -out $CRYPTDIR/key.rsa

# Kulcsok törlése
rm $CRYPTDIR/id_rsa.pub
rm $CRYPTDIR/key.txt

cd $BACKUPDIR
tar cfz ${DATE}-crypt.tgz ${DATE}
rm -rf $DATE

echo -e "\nBecsomagolva: $BACKUPDIR/${DATE}-crypt.tgz\n\n"
