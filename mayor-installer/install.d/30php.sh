#!/bin/bash
#

cat <<EOF
A PHP memória limit beállítása

Az alapértelmezett 16MB-os memória a rendszer futtatásához a
legtöbb esetben elég, de egyes esetekben (például a nyomtatványok
generálásakor) kevésnek bizonyul. Ezért a telepítő ezt a küszöböt
256MB-ra emeli. Az eredeti php.ini állományról másolat készül
php.ini.mayor néven.

EOF

read -n 1 -p "A php.ini-t módosíthatom? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nNem módosítom a php.ini-t.\n"; exit 1; fi


if [[ "x${RELEASE}" =~ ^x9.* ]]; then

	if [ ! -e /etc/php/7.0/apache2/php.ini.mayor ]; then
        	cp /etc/php/7.0/apache2/php.ini /etc/php/7.0/apache2/php.ini.mayor
	fi
	cat /etc/php/7.0/apache2/php.ini.mayor | sed "s/memory_limit/memory_limit = 256M ; old value: /" > /etc/php/7.0/apache2/php.ini
	echo -e "256M"

else

	echo -n "  A PHP memória limit beállítása: "
	if [ ! -e /etc/php5/apache2/php.ini.mayor ]; then
	    cp /etc/php5/apache2/php.ini /etc/php5/apache2/php.ini.mayor
	fi

	cat /etc/php5/apache2/php.ini.mayor | sed "s/memory_limit/memory_limit = 256M ; old value: /" > /etc/php5/apache2/php.ini
	echo -e "256M"
fi

echo "  Az web szerver újraindítása"
/etc/init.d/apache2 restart

