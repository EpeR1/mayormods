#!/bin/bash
#

cat <<EOF
Intézmény létrehozása

A telepítő segítségével létrehozhatjuk az intézmény adatbázisát. 
(Ezt a lépést a webes felületen is végezhetnénk.)

A telepítő létrehozza az 
 * intézmény konfigurációs állományát, 
 * az intézmény adatbázisát és 
 * bejegyzését a mayor_naplo.intezmeny táblába.

Ehhez meg kell adnia az intézmény OM kódját, nevét és rövid nevét
(mint vmg, njszki, fasori, stb).

EOF

read -n 1 -p "Létrehozzam az intézményt? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nNem hoztam létre az intézményt.\n"; exit; fi

if [ "$MAYORDIR" = "" ]; then echo "MAYORDIR változó üres. Kilépek!"; exit 1; fi

echo -e "\n"
OMKOD=""
while [ "$OMKOD" = "" ]; do
    read -p "Kérem az intézmény OM kódját: " OMKOD
    OMKOD=$(echo ${OMKOD} | sed "s/[^[0-9]]*//g")
done
read -p "Kérem az intézmény nevét: " INTEZMENYNEV
ROVID=""
while [ "$ROVID" = "" ]; do
    read -p "Kérem az intézmény rövid nevét (néhány karakteres rövidítés, mint \"vmg\", \"illyes\"...: " ROVID
    ROVID=$(echo ${ROVID} | sed "s/[^[:alnum:]]*//g" | sed "y, űáéúőóüöíŰÁÉÚŐÓÜÖÍ,_uaeuoouoiUAEUOOUOI," | sed 's/\(.*\)/\L\1/')
done
echo -e "\nOM: ${OMKOD}; Rövidnév: ${ROVID}\n"

echo -n "  Az intézmény konfigurációs állománya: "
cp "$MAYORDIR/config/module-naplo/config-pl.php.example" "$MAYORDIR/config/module-naplo/config-$ROVID.php"
echo "$MAYORDIR/config/module-naplo/config-$ROVID.php"

DB=$(grep db $MAYORDIR/config/module-naplo/config.php | grep naplo_base | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")
USER=$(egrep 'userWrite.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")
USERREAD=$(egrep 'userRead.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g")

echo -e "\nAz adatbázis létrehozása\n"
cat <<EOF > $TMPDIR/mysql/intezmeny.sql

insert into $DB.intezmeny (OMKod, rovidNev, nev, alapertelmezett) VALUES ('$OMKOD','$ROVID','$INTEZMENYNEV',1);
create database intezmeny_$ROVID character set utf8 collate utf8_hungarian_ci;
grant select,execute on intezmeny_$ROVID.* to '$USERREAD'@'localhost';
grant all on intezmeny_$ROVID.* to '$USER'@'localhost';
use intezmeny_$ROVID;

EOF

if [ "$MYSQLROOTPW" == "" ]; then
    read -p " A mysql root jelszó -ha van!- (a begépelt szöveg nem látszik!): " -s MYSQLROOTPW
fi
#Ezt miért is akarom kiírni?
#echo $MYSQLROOTPW - a jelszó
if [ "$MYSQLROOTPW" = "" ];
then
    MYSQLROOTPWSTR="";
else
    MYSQLROOTPWSTR="-p$MYSQLROOTPW --user=root"
fi

cat $TMPDIR/mysql/intezmeny.sql $MAYORDIR/install/module-naplo/mysql/intezmeny.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8 $INTEZMENYDB
