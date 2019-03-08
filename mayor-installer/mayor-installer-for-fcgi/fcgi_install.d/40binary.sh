#!/bin/bash
#

cat <<EOF
Karbantartást segítő szkriptek

A telepítő szimbolikus linket készít a /usr/local/sbin-be, hogy
a "mayor update" illettve "mayor backup" parancsokat bárhonnan
kiadhassuk, majd az /etc/cron.daily könyvtár alá is link készül,
hogy a mentések és frissítések rendszeresen lefuthassanak.

EOF

read -n 1 -p "Karbantartást segítő scriptek telepítése mehet? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nNem telepítjük a szkripteket.\n"; exit; fi

if [ "$MAYORDIR" = "" ]; then echo -e "\nMAYORDIR változó üres. Kilépek!"; exit 1; fi
echo -e "\n * Webszerver userének ellenőrzése, beállítása"
if [ "$WEB_SERVER_USER" == "" ]; then
    source /etc/apache2/envvars
    WEB_SERVER_USER=$APACHE_RUN_GROUP
    WEB_SERVER_USER=$MAYORUSER		### hiszen pont ezért csináltuk :)
fi
if [ "$WEB_SERVER_USER" == "" ]; then
    echo -e "!!! Fatális hiba !!! A WEB_SERVER_USER változó üres. Kilépek!"; exit 1;
else
    echo -e " * WEB_SERVER_USER="$WEB_SERVER_USER
fi

    # A karbantartást segítő scriptek:
    if [ ! -e /usr/local/sbin/mayor ]; then 
	ln -s $MAYORDIR/bin/mayor /usr/local/sbin/mayor; 
	echo -e "\n  Az /usr/local/sbin/ alá létrejött a mayor szimbolikus link.";
    fi
    if [ ! -e $MAYORDIR/config/main.conf ]; then cp $MAYORDIR/config/main.conf.example $MAYORDIR/config/main.conf; fi
    # A webserver_user lecserélése, ha nem www-data lenne
    sed -e "s/WEB_SERVER_USER=\"www-data\"/WEB_SERVER_USER=\"$WEB_SERVER_USER\"/g" -i $MAYORDIR/config/main.conf
    # A konfig könyvtár védelme
    chown -R $WEB_SERVER_USER $MAYORDIR/config/
    chmod 700 $MAYORDIR/config/
    # A main.conf védelme
    chown root $MAYORDIR/config/main.conf
    chmod 600 $MAYORDIR/config/main.conf
    if [ ! -e /etc/cron.daily/mayor ]; then 
	ln -s $MAYORDIR/bin/etc/cron.daily/mayor /etc/cron.daily; 
	echo -e "\n  Az /etc/cron.daily/ alá létrejött a mayor szimbolikus link.";
    fi

# BASEDIR és  és BACKUPDIR javítása a main.conf-ban
sed -e "s/BASEDIR=\"\/var\/mayor\"/BASEDIR=\"\/home\/$MAYORUSER\/mayor\"/g" -i $MAYORDIR/config/main.conf
sed -e "s/BACKUPDIR=\/home\/backup/BACKUPDIR=\/home\/$MAYORUSER\/mayor_backup/g" -i $MAYORDIR/config/main.conf

