#!/bin/bash
#

cat <<EOF
Tanév megnyitása

A telepítő segítségével létrehozhatunk egy aktív tanévet. 
(Ezt a lépést a webes felületen is végezhetnénk.)

A telepítő létrehozza az 
 * a tanév adatbázisát
 * módosítja a szemeszter tábla megfelelő bejegyzéseit.

A program feltételezi, hogy jelenleg nincsenek még osztályok definiálva!

A művelethez meg kell adnia a megnyitandó tanév kezdetének évét!

EOF

if [ "$MAYORDIR" = "" ]; then echo "MAYORDIR változó üres. Kilépek!"; exit 1; fi

TANEV=$(grep SCHOOLYEAR $SCHOOLSERVERCONF | sed -e 's/SCHOOLYEAR="//' -e 's/"//')
if [ "$TANEV" = "" ]; then
    TANEV=2012
    echo "Nincs megadva tanév - az alapértelemzett: ${TANEV}"
fi

ROVID=$(grep SCHOOLSHORTNAME $SCHOOLSERVERCONF | sed -e 's/SCHOOLSHORTNAME="//' -e 's/"//')
if [ "$ROVID" = "" ]; then
    echo "Nincs rövid név - kilépek!"
    exit 1
fi
echo -e "\nTanév: ${TANEV}; Rövidnév: ${ROVID}\n"

DB="naplo_${ROVID}_${TANEV}"
USER=`egrep 'userWrite.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
USERREAD=`egrep 'userRead.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
INTEZMENYDB="intezmeny_${ROVID}"

echo -e "\nAz adatbázis létrehozása\n"
cat <<EOF > $TMPDIR/mysql/tanev.sql

create database $DB character set utf8 collate utf8_hungarian_ci;
grant select,execute on $DB.* to '$USERREAD'@'localhost';
grant all on $DB.* to '$USER'@'localhost';

update $INTEZMENYDB.szemeszter SET statusz='aktív' where tanev=$TANEV;

use $DB;

EOF

#if [ "$MYSQLROOTPW" == "" ]; then
#    read -p " A mysql root jelszó (a begépelt szöveg nem látszik!): " -s MYSQLROOTPW
#fi

if [ "$MYSQLROOTPW" != "" ]; then MYSQLROOTPW="-p$MYSQLROOTPW"; fi

cat $TMPDIR/mysql/tanev.sql $MAYORDIR/install/module-naplo/mysql/tanev.sql | sed -e s/%DB%/$INTEZMENYDB/g | mysql $MYSQLROOTPW $INTEZMENYDB --default-character-set=utf8

