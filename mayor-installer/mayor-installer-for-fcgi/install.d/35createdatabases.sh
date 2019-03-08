#!/bin/bash
#

cat <<EOF
Adatbázisok létrehozása

A felhasználói azonosítók és csoportok adatainak tárolásához, a 
munkamenetek adminisztrálásához és a napló modul adatainak tárolásához
szükséges adatbázisok létrehozása következik.

Ez a lépés csak a szükséges konfigurációs állományok megléte
esetén fut le helyesen!

A telepítő először elkészíti a betöltendő SQL utasításokat tartalmazó
állományokat a $TMPDIR/mysql alá, majd - amennyiben engedélyezzük - 
be is tölti az állományokat.

EOF

read -n 1 -p "A konfigurációs állományok alapján létrehozhatom az adatbázisokat? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nAz adatbázisok létrehozását kihagyjuk.\n"; exit 1; fi

if [ "$MAYORDIR" = "" ]; then echo "MAYORDIR változó üres. Kilépek!"; exit 1; fi

echo -n -e "\n Config fileok ellenőrzése..."

if [ ! -e "$MAYORDIR/log" ]; then mkdir $MAYORDIR/log; fi
if [ ! -e /var/log/mayor ]; then ln -s $MAYORDIR/log /var/log/mayor; fi
if [ ! -e /etc/mayor ]; then ln -s $MAYORDIR/config /etc/mayor; fi
chmod +x $MAYORDIR/bin/mayor

FILES="$MAYORDIR/config/parent-conf.php $MAYORDIR/config/private-conf.php $MAYORDIR/config/main-config.php $MAYORDIR/config/module-naplo/config.php $MAYORDIR/install/base/mysql/mayor-login.sql $MAYORDIR/install/base/mysql/mayor-auth.sql $MAYORDIR/install/base/mysql/private-users.sql $MAYORDIR/install/module-naplo/mysql/naplo-users.sql"
for f in $FILES
do
    if [ ! -e $f ]; then echo -e "\n\r Nincs meg a szükséges ${f} config file!"; exit 1; else echo -n '.'; fi
done;
echo " kész.";

if [ ! -e $MAYORDIR/log/mayor-base.rev ]; then 
    echo " Hiányzó mayor-base.rev file"; 
    if [ -e $MAYORDIR/log/revision ]; 
    then 
	IREV=$(cat $MAYORDIR/log/revision)
    else
	IREV="";
    fi
else
    cp $MAYORDIR/log/mayor-base.rev $MAYORDIR/log/revision
    IREV=$(cat $MAYORDIR/log/revision)
fi

echo " Az aktuális revision: ${IREV}"

if [ "$IREV" = "" ]
then
    echo " Nincs revision file és helyreállítani sem tudom. Kilépek!"
    exit 1;
fi

echo " SQL fileok létrehozása... "
mkdir -p $TMPDIR/mysql
cd $TMPDIR/mysql
DB=$(grep db $MAYORDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g")
USER=$(grep user $MAYORDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g")
PW=$(grep pw $MAYORDIR/config/main-config.php | sed -e "s/$.*=\ *['|\"]//g" -e "s/['|\"];//g")
cat $MAYORDIR/install/base/mysql/mayor-login.sql | sed -e "s/%MYSQL_LOGIN_DB%/$DB/g" -e "s/%MYSQL_LOGIN_USER%/$USER/g" \
        -e "s/%MYSQL_LOGIN_PW%/$PW/g" > mayor-login.sql
DB=$(grep db $MAYORDIR/config/parent-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")
USER=$(grep user $MAYORDIR/config/parent-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")
PW=$(grep pw $MAYORDIR/config/parent-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")
cat $MAYORDIR/install/base/mysql/mayor-auth.sql | sed -e "s/%MYSQL_AUTH_DB%/$DB/g" -e "s/%MYSQL_AUTH_USER%/$USER/g" \
        -e "s/%MYSQL_AUTH_PW%/$PW/g" > mayor-parent.sql
DB=$(grep db $MAYORDIR/config/private-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")
USER=$(grep 'mysql user' $MAYORDIR/config/private-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")
PW=$(grep pw $MAYORDIR/config/private-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")
cat $MAYORDIR/install/base/mysql/mayor-auth.sql | sed -e "s/%MYSQL_AUTH_DB%/$DB/g" -e "s/%MYSQL_AUTH_USER%/$USER/g" \
        -e "s/%MYSQL_AUTH_PW%/$PW/g" > mayor-private.sql
DB=$(grep db $MAYORDIR/config/module-naplo/config.php | grep naplo_base | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")
USER=$(egrep 'userWrite.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")
USERREAD=$(egrep 'userRead.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")
PW=$(egrep 'pwWrite.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")
PWREAD=$(egrep 'pwRead.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")
cat $MAYORDIR/install/module-naplo/mysql/base.sql | sed -e "s/%MYSQL_NAPLO_DB%/$DB/g" -e "s/%MYSQL_NAPLO_USER%/$USER/g" \
        -e "s/%MYSQL_NAPLO_PW%/$PW/g"  -e "s/%MYSQL_NAPLO_USER_READ%/$USERREAD/g" \
        -e "s/%MYSQL_NAPLO_PW_READ%/$PWREAD/g" > base.sql
DB=$(grep db $MAYORDIR/config/private-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")

cat $MAYORDIR/install/base/mysql/private-users.sql | sed -e "s/%MYSQL_PRIVATE_DB%/$DB/g" > private-users.sql
cat $MAYORDIR/install/module-naplo/mysql/naplo-users.sql | sed -e "s/%MYSQL_PRIVATE_DB%/$DB/g" > naplo-users.sql

echo " A létrejött sql fileok:"
FILES=$(ls *.sql)
for f in $FILES
do
    echo " * ${f}";
done;
read -n 1 -p "Telepíthetem? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo " ok, kiléptem..."; exit 1; fi
echo -e "\n"

read -p " A mysql root jelszó (a begépelt szöveg nem látszik!): " -s MYSQLROOTPW


if [ ! -e $MAYORDIR/config/main.conf ]; then 
    cat $MAYORDIR/config/main.conf.example | sed s/%SQLPW%/$MYSQLROOTPW/ > $MAYORDIR/config/main.conf
#    BDIR=$(echo $MAYORDIR | sed -e 's:\/:\\\/:g')
#    sed -e "s/BASEDIR=\"\/var\/mayor\"/BASEDIR=\"$BDIR\"/g" -i $MAYORDIR/config/main.conf
    chmod 600 $MAYORDIR/config/main.conf
fi

if [ "$MYSQLROOTPW" = "" ]; 
then 
    MYSQLROOTPWSTR=""; 
else 
    MYSQLROOTPWSTR="-p$MYSQLROOTPW --user=root"
fi

cat /tmp/mysql/mayor-login.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8
cat /tmp/mysql/mayor-parent.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8
cat /tmp/mysql/mayor-private.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8
cat /tmp/mysql/base.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8
cat /tmp/mysql/private-users.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8
cat /tmp/mysql/naplo-users.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8

echo -e "\n"
echo "A telepítés végeztével beléphetsz a webes felületen!
=========================================================
        user: mayoradmin
=========================================================
	jelszó: jelszo
=========================================================
"
