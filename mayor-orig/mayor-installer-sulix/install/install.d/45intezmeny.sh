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

if [ "$MAYORDIR" = "" ]; then echo "MAYORDIR változó üres. Kilépek!"; exit 1; fi

echo -e "\n"
OMKOD=$(grep SCHOOLOM $SCHOOLSERVERCONF | sed -e 's/SCHOOLOM="//' -e 's/"//')
if [ "$OMKOD" = "" ]; then
    echo "Nincs OM kód - kilépek!"
    exit 1
fi

INTEZMENYNEV=$(grep SCHOOLNAME $SCHOOLSERVERCONF | sed -e 's/SCHOOLNAME="//' -e 's/"//')
if [ "$INTEZMENYNEV" = "" ]; then
    echo "Nincs intézménynév - kilépek!"
    exit 1
fi

ROVID=$(grep SCHOOLSHORTNAME $SCHOOLSERVERCONF | sed -e 's/SCHOOLSHORTNAME="//' -e 's/"//')
if [ "$ROVID" = "" ]; then
    echo "Nincs rövid név - kilépek!"
    exit 1
fi
echo -e "\nOM: ${OMKOD}; Név: ${INTEZMENYNEV}; Rövidnév: ${ROVID}\n"

echo -n "  Az intézmény konfigurációs állománya: "
cp "$MAYORDIR/config/module-naplo/config-pl.php.sulix" "$MAYORDIR/config/module-naplo/config-$ROVID.php"
echo "$MAYORDIR/config/module-naplo/config-$ROVID.php"

DB=`grep db $MAYORDIR/config/module-naplo/config.php | grep naplo_base | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
USER=`egrep 'userWrite.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`
USERREAD=`egrep 'userRead.*=' $MAYORDIR/config/module-naplo/config.php | sed -e "s/^.*=\ *['|\"]//g" -e "s/['|\"];//g"`

echo -e "\nAz adatbázis létrehozása\n"
cat <<EOF > $TMPDIR/mysql/intezmeny.sql

insert into $DB.intezmeny (OMKod, rovidNev, nev, alapertelmezett) VALUES ('$OMKOD','$ROVID','$INTEZMENYNEV',1);
create database intezmeny_$ROVID character set utf8 collate utf8_hungarian_ci;
grant select,execute on intezmeny_$ROVID.* to '$USERREAD'@'localhost';
grant all on intezmeny_$ROVID.* to '$USER'@'localhost';
use intezmeny_$ROVID;

EOF

#if [ "$MYSQLROOTPW" == "" ]; then
#    read -p " A mysql root jelszó (a begépelt szöveg nem látszik!): " -s MYSQLROOTPW
#fi

if [ "$MYSQLROOTPW" != "" ]; then MYSQLROOTPW="-p$MYSQLROOTPW"; fi

cat $TMPDIR/mysql/intezmeny.sql $MAYORDIR/install/module-naplo/mysql/intezmeny.sql | mysql $MYSQLROOTPW $INTEZMENYDB --default-character-set=utf8

