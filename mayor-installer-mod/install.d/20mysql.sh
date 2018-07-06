#!/bin/bash
#

cat <<EOF
MySQL szerver beállításai

A rendszer adatbázisai UTF-8 kódolásúak, ennek megfelelően kell
beállítani a MySQL-t is. A beállításokat a /etc/mysql/conf.d
alá helyezi el a telepítő utf8.cnf néven, majd újraindítja az
adatbázis szervert.

EOF

read -n 1 -p "A MySQL szerver beállításait módosíthatom? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nA MySQL beállításait nem módosítottam.\n"; exit 1; fi

echo -e "\nMySQL beállítások"
echo -n "  utf8.cnf ... "
cp -f $MAYORDIR/install/base/mysql/utf8.cnf /etc/mysql/conf.d
echo ok
/etc/init.d/mysql restart

if [ "x${RELEASE}" == "x9" ]
then
    echo -e "\nMariaDB/MySQL beállítások"
    echo -n "  futtatom a mysql_Secure_installation scriptet: "
    mysql_secure_installation
fi
