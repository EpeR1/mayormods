#!/bin/bash
#

cat <<EOF
A PHP memória limit beállítása

Az alapértelmezett 16MB-os memória a rendszer futtatásához a
legtöbb esetben elég, de egyes esetekben (például a nyomtatványok
generálásakor) kevésnek bizonyul. Ezért a telepítő ezt a küszöböt
256MB-ra emeli. Az eredeti php.ini állományról másolat készül
php.ini.mayor néven.

Figyelem!!
A mod_fcgid miatt a mayor php.ini -je nem a /etc/php5/apache2 mappában,
hanem a php-csomagoló mellett a /home/$MAYORUSER/mayor/www-bin/ mappában kell legyen!!

EOF

read -n 1 -p "A php.ini-t módosíthatom? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nNem módosítom a php.ini-t.\n"; exit 1; fi

echo -n "  A PHP memória limit beállítása: "
if [ ! -e "/home/$MAYORUSER/mayor/www-bin/php.ini.mayor" ]; then
    cp "/home/$MAYORUSER/mayor/www-bin/php.ini" "/home/$MAYORUSER/mayor/www-bin/php.ini.mayor"
fi

sed -e  "s/memory_limit.*/memory_limit = 256M/g" -i "/home/$MAYORUSER/mayor/www-bin/php.ini"
echo "256M"
sed -e  "s/;cgi.force_redirect.*/cgi.force_redirect = 1/g" -i "/home/$MAYORUSER/mayor/www-bin/php.ini"
sed -e  "s/;cgi.fix_pathinfo.*/cgi.fix_pathinfo = 1/g" -i "/home/$MAYORUSER/mayor/www-bin/php.ini"
sed -e  "s/;date.timezone.*/date.timezone = Europe/Budapest/g" -i "/home/$MAYORUSER/mayor/www-bin/php.ini"

echo "  Az web szerver újraindítása"
/etc/init.d/apache2 restart

