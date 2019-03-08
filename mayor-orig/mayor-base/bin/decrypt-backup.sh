#!/bin/bash
#
# Example: mayor decrypt-backup --crypted-file=/home/backup/20110515-crypt.tgz
# Az eredmény a $BACKUPDIR alá kerül az eredetileg elkódolt állomány nevéből
# származtatott néven: file.kit --> file-decrypt.kit
#
# TODO: ellenőrizni kell még az openssl és az aespipe meglétét
#

OPT_SPEC="hf:d::"
LONG_OPT_SPEC="help,file:,crypted-file:,dir:,backup-dir::"
PARSED_OPTIONS=$(getopt -n "$0" -a -o $OPT_SPEC --long $LONG_OPT_SPEC -- "$@")
OPTIONS_RET=$?
eval set -- "$PARSED_OPTIONS"

help_usage() {
cat <<EOF

Decrypt-backup használata: mayor decrypt-backup [opciók]

A parancs segítségével a korábban titkosított (ld. mayor help crypt-backup) mentést 
csomagolhatjuk ki. A kicsomagoláshoz szükséges az intézmény privát kulcsa, amit a 
program alapesetben az adatbázisban keres. Ha nincs más megadva, akkor a szkript
"YYYYmmdd-crypt.tgz" néven keresi a kicsomagolandó állományt (pl. ${DATE}-crypt.tgz)

A mentési könyvtár, a szükséges jelszavak és egyéb paraméterek beállításait a 
/etc/mayor/main.conf állományban kell megadni.

Opciók:
    -h, --help:			A parancs leírása (amit most olvasol...)
    -f, --file, --crypted-file: A kódolt állomány neve
    -d, --dir, --backup-dir:	A mentési könyvtár elérési útja

EOF
}

if [ $OPTIONS_RET -ne 0 ]; then  help_usage; exit; fi

FILE="${DATE}-crypt.tgz"
while [ $# -ge 1 ]; do
    case $1 in
        --help | -h )                   help_usage
                                        exit
                                        ;;

        --crypted-file | --file | -f )   shift
                                        FILE="$1"
                                        echo "Kódolt állomány: $FILE"
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




# A mysql bináris ellenőrzése
if [ ! -f $MYSQL ]; then
    echo -e "\n\nERROR: A mysql kliens nem található: $MYSQL\n"
    exit 1
fi

# A login adatbázis eléréséhez szükséges adatok a konfig-ból
DB=`grep db $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`
USER=`grep user $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`
PW=`grep pw $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`

# Ellenőrizzük, hogy van-e privát kulcs - regisztrált intézmény-e
COUNT=`$MYSQL -e 'SELECT count(privateKey) FROM mayorSsl' -p$PW -u$USER $DB | grep -i -v count`
if [ "$COUNT" -ne "1" ]; then
    echo "Privát kulcsok száma: $COUNT"
    echo "Nem regisztrált intézmény? A titkosított mentések kezelése csak regisztrált intézmények számára érhető el!"
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
chown $WEB_SERVER_USER $BACKUPDIR
chmod 700 $BACKUPDIR

# bejövő paraméterek feldolgozása (crypted-file)
if [ ! -f $FILE ]; then
    echo -e "    HIBA: Hiányzik a dekódolandó mentési állomámny: ${FILE}\n"
    FILE=$BACKUPDIR/${FILE}
    echo "Próbáljuk a mentési könyvtárban (${FILE})"
    if [ ! -f $FILE ]; then
	echo -e "    HIBA: Hiányzik a dekódolandó mentési állomámny: ${FILE}\n"
	FILE=$BACKUPDIR/${PREFIX}${DATE}-crypt.tgz
	echo "Próbáljuk az alapértelmezett állományt (${FILE})"
	if [ ! -e $FILE ]; then
    	    echo "    HIBA: Hiányzik a dekódolandó mentési állomámny: ${FILE}"
    	    exit 4
	fi
    fi
fi
BASENAME=`basename $FILE`

cd $BACKUPDIR
tar xfz $FILE
TARFILES=`tar tf $FILE`
SUBDIR=`for f in $TARFILES; do echo $f; break; done`
AESFILE=`echo $TARFILES | sed -e 's/ /\n/g' | grep '.aes'`
AESBASE=`basename $AESFILE | sed -e 's/.aes//g'`
DECRYPTFILE=`echo $AESBASE | sed -e 's/\.\([a-z]*\)$/-decrypt\.\1/'`

# A privát kulcs lekérdezése
echo 'SELECT privateKey FROM mayorSsl' | $MYSQL -p$PW -u$USER $DB | grep -v privateKey | sed -e 's/\\n/\n/g' > $BACKUPDIR/$SUBDIR/id_rsa

# AES kulcs dekódolása
openssl rsautl -decrypt -inkey $BACKUPDIR/$SUBDIR/id_rsa -in $BACKUPDIR/$SUBDIR/key.rsa -out $BACKUPDIR/$SUBDIR/key.txt

# AES dekódolás (feltételezzük, hogy az eredmény tgz
aespipe -P $BACKUPDIR/$SUBDIR/key.txt -d -e aes256 < $BACKUPDIR/$AESFILE > $BACKUPDIR/$DECRYPTFILE

# Törlés
rm -rf $BACKUPDIR/$SUBDIR

echo -e "\nKicsomagolva: $BACKUPDIR/$DECRYPTFILE\n\n"
