#!/bin/bash
#
# Example: mayor remote-backup --backup-file=/tmp/wiki.tgz
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

Remote-backup használata: mayor remote-backup [opciók]

A parancs segítségével menthetjük egy központi szerverre a MaYoR rendszer adatbázisait, 
aktuális forrását és beállításait titkosított formában. A távoli szerverre való bejele-
lentkezéshez szükséges az intézmény privát kulcsa, amit a program alapesetben az adat-
bázisban keres.
A szkript, ha nincs más megadva, akkor az aktuális dátumnak megelelő, 
"YYYYmmdd-crypt.tgz" alkú néven keresi a feltöltendő mentési állományt.

A mentési könyvtár, a szükséges jelszavak és egyéb mentési paraméterek beállításait a 
/etc/mayor/main.conf állományban kell megadni.

Opciók:
    -h, --help:                A parancs leírása (amit most olvasol...)
    -f, --file, --backup-file: A mentési állomány neve
    -d, --dir, --backup-dir:   A mentési könyvtár elérési útja

EOF
}

if [ $OPTIONS_RET -ne 0 ]; then  help_usage; exit; fi

FILE="${DATE}-crypt.tgz"
while [ $# -ge 1 ]; do
    case $1 in
        --help | -h )                   help_usage
                                        exit
                                        ;;

        --backup-file | --file | -f )   shift
                                        FILE="$1"
                                        echo "Backup fájl: $FILE"
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



SSH_PORT="8023"
SSH_HOST="backup.mayor.hu"

# mysql bináris ellenőrzése
if [ ! -f $MYSQL ]; then
    echo -e "\n\nERROR: A mysql kliens nem található: $MYSQL\n"
    exit 1
fi

# Login adatbázis eléréséhez szükséges paraméterek lekérdezése a konfig-ból...
DB=`grep db $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`
USER=`grep user $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g" | sed 's/^ *//g'`
PW=`grep pw $BASEDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g" | sed 's/^ *//g'`

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

if [ ! -f $FILE ]; then
    echo -e "    HIBA: Hiányzik a kódolandó mentési állomámny: ${FILE}"
    FILE=$BACKUPDIR/${FILE}
    echo "Próbáljuk a mentési könyvtárban! (${FILE})"
    if [ ! -f $FILE ]; then
	echo -e "    HIBA: Hiányzik a kódolandó mentési állomámny: ${FILE}"
	FILE=$BACKUPDIR/${PREFIX}${DATE}.tgz
	echo "Próbáljuk az alapértelmezett állományt! (${FILE})"
	if [ ! -e $FILE ]; then
	    echo -e "    HIBA: Hiányzik a kódolandó mentési állomámny: ${FILE}"
	    exit 3
	fi
    fi
fi
BASENAME=`basename $FILE`

# Publikus kulcs lekérdezése
echo 'SELECT publicKey FROM mayorSsl' | $MYSQL -p$PW -u$USER $DB | grep -v publicKey | sed -e 's/\\n/\n/g' > $CRYPTDIR/id_rsa.pub

# Véletlen kulcs generálás a szimmetrikus AES kódoláshoz
pwgen -nc 50 1 > $CRYPTDIR/key.txt

# AES kódolás
aespipe -P $CRYPTDIR/key.txt -e aes256 < $FILE > $CRYPTDIR/$BASENAME.aes

# A kulcsok RSA kódolása a publikus kulccsal
openssl rsautl -encrypt -inkey $CRYPTDIR/id_rsa.pub -pubin -in $CRYPTDIR/key.txt -out $CRYPTDIR/key.rsa

# Kulcsok törlése
rm $CRYPTDIR/id_rsa.pub
rm $CRYPTDIR/key.txt

cd $BACKUPDIR
tar cfz ${DATE}-crypt.tgz ${DATE}
rm -rf $DATE

echo -e "\nBecsomagolva: $BACKUPDIR/${DATE}-crypt.tgz\n\n"

# A privát kulcs lekérdezése, elhelyezése
if [ ! -d $BASEDIR/ssh ]; then
    echo "Létrehozzuk a $BASEDIR/ssh könyvtárat, amibe belerakjuk a privát kulcsot..."
    mkdir $BASEDIR/ssh
    chmod 700 $BASEDIR/ssh
fi
if [ ! -f $BASEDIR/ssh/id_rsa ]; then
    echo 'SELECT privateKey FROM mayorSsl' | $MYSQL -p$PW -u$USER $DB | grep -v privateKey | sed -e 's/\\n/\n/g' > $BASEDIR/ssh/id_rsa
    chmod 700 $BASEDIR/ssh/id_rsa
fi

# Az intézmény OM kódjának lekérdezése

# A mayor_naplo adatbázis eléréséhez szükséges paraméterek lekérdezése a konfig-ból...
DB=`grep db $BASEDIR/config/module-naplo/config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g"`
USER=`grep userRead $BASEDIR/config/module-naplo/config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g" | sed 's/^ *//g'`
PW=`grep pwRead $BASEDIR/config/module-naplo/config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g" | sed 's/^ *//g'`

OMKOD=`echo 'SELECT SUBSTR(OMKod,-6) FROM intezmeny WHERE alapertelmezett = 1' | $MYSQL -p$PW -u$USER $DB | grep -v OMKod`
SSH_USER="om$OMKOD"
echo $SSH_USER

# A kódolt adatállomány másolása
scp -i $BASEDIR/ssh/id_rsa -P $SSH_PORT $BACKUPDIR/${DATE}-crypt.tgz $SSH_USER@$SSH_HOST:/home/$SSH_USER/
