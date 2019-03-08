#!/bin/bash
#

cat <<EOF
Az Apache (2) webszerver beállítása

Az elektronikus napló használata feltétlenül HTTPS protokollt igényel.
Ehhez engedélyeznie kell az ssl modul használatát, és tanusítványra is
szükség van. A telepítő "self-signed" tanusítványt generál (ehhez meg kell
adnia a szerver teljes domain nevét), valamint engedélyezi a rewrite modul
használatát is, végül létrehoz és engedélyez mayor néven egy site
konfigurációt is.

EOF

read -n 1 -p "Az Apache web-szerver módosítható? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nAz Apache web szerver beállításait nem módosítottam.\n"; exit 1; fi

if [ "$MAYORDIR" = "" ]; then echo "A MAYORDIR változó üres. Kilépek."; exit 1; fi

echo -e "\nApache beállítások"


echo "    Tanusítvány generálás"
if [ ! -e /etc/apache2/ssl ]; then 
    mkdir /etc/apache2/ssl
fi
make-ssl-cert /usr/share/ssl-cert/ssleay.cnf /etc/apache2/ssl/apache.pem

echo "  Szükséges modulok ellenőrzése:"
echo -n "    - ModSSL engedélyezése ... "
if [ ! -e /etc/apache2/mods-enabled/ssl.load ]; then
    a2enmod ssl > /dev/null
    echo ok
else
    echo "már engedélyezett"
fi
    echo -n "    - ModReWrite engedélyezése ... "
if [ ! -e /etc/apache2/mods-enabled/rewrite.load ]; then
    a2enmod rewrite > /dev/null
    echo ok
else
    echo "már engedélyezett"
fi
echo "ok."

SERVERNAME=""
while [ "$SERVERNAME" = "" ]
do
    read -p "  A web szerver teljes domain neve (pl: mayor.tesztsuli.hu): " SERVERNAME
done

#cat $MAYORDIR/install/base/apache2/mayor.conf | sed "s/ServerName your.mayor.server.hu/ServerName $SERVERNAME/" > /etc/apache2/sites-available/mayor.conf

# A 2.4-es apache esetén módosítani kell a konfig állományt!
APACHE_VERSION=$(dpkg -l apache2 | grep apache2 | tr -s ' ' | cut -d ' ' -f 3 | cut -d '-' -f 1 | sed 's/\..$//')
cat $MAYORDIR/install/base/apache2/mayor.conf | sed \
    -e "s/ServerName your.mayor.server.hu/ServerName $SERVERNAME/" > /etc/apache2/sites-available/mayor.conf


if [ ! -e /etc/apache2/sites-enabled/mayor.conf ]; then
    echo "  A mayor site engedélyezése"
    a2ensite mayor.conf > /dev/null
fi

echo "  A web-szerver újraindítása"
/etc/init.d/apache2 restart
