#!/bin/bash

if [ -e "/etc/mayor/main.conf" ]; then
    echo 'OK'
else
    echo "          - Konfigurációs állományok és naplózási beállítások átszervezése..."

    echo "            * Új konfigurációs állomány létrehozása"
    echo -e "\n#====================#\n#  Main paraméterek  #\n#====================#\n\n" > $BASEDIR/config/main.conf
    grep -e '^BASEDIR' -e 'WEB_SERVER_USER' -e '^MYSQL=' -e 'MYSQL_USER' -e 'MYSQL_PW' -e 'SVN' -e 'SHELL' $BASEDIR/config/update.conf >> $BASEDIR/config/main.conf
    echo -e "\n\n#====================#\n# Update paraméterek #\n#====================#\n\n" >> $BASEDIR/config/main.conf
    echo -e "UPDATELOG=\"\$BASEDIR/log/update.log\"\nSQLLOG=\"\$BASEDIR/log/sql-update.log\"\nREVISION_FILE=\"\$BASEDIR/log/revision\"\nLOCKFILE=\"/var/run/mayor.lock\"\nVERSION=\"radyx\"" >> $BASEDIR/config/main.conf
    grep -v -e '^BASEDIR' -e 'WEB_SERVER_USER' -e '^MYSQL=' -e 'MYSQL_USER' -e 'MYSQL_PW' -e 'SVN' -e 'SHELL' -e 'REVISION_FILE' $BASEDIR/config/update.conf >> $BASEDIR/config/main.conf
    echo -e "\n\n#====================#\n# Backup paraméterek #\n#====================#\n\n" >> $BASEDIR/config/main.conf
    grep -v -e '^BASEDIR' -e 'WEB_SERVER_USER' -e '^MYSQL=' -e 'MYSQL_USER' -e 'MYSQL_PW' -e 'WWWDIR' -e 'DATE' $BASEDIR/config/backup.conf >> $BASEDIR/config/main.conf

    echo "            * Korábbi konfigurációs állományok mentése: "
    mv $BASEDIR/config/update.conf $BASEDIR/config/update.conf.old
    echo "                update.conf --> update.conf.old"
    mv $BASEDIR/config/backup.conf $BASEDIR/config/backup.conf.old
    echo "                backup.conf --> backup.conf.old"

    echo "            * Naplózás előkészítése"
    if [ ! -e $BASEDIR/log ]; then mkdir $BASEDIR/log; fi
    if [ ! -e /var/log/mayor ]; then ln -s $BASEDIR/log /var/log/mayor; fi

    echo "            * update.rev áthelyezése"
    mv $BASEDIR/config/update.rev $BASEDIR/log/revision
    mv $BASEDIR/config/main-config.php $BASEDIR/config/main-config.php.old
    cat $BASEDIR/config/main-config.php.old | \
	sed -e "s/define('_CONFIGDIR',\(.*\));/define('_CONFIGDIR',\\1);\\ndefine('_LOGDIR',_MAYOR_DIR\.'\/log');\\ndefine('_LOCKFILE','\/var\/run\/mayor.lock');/g" \
	> $BASEDIR/config/main-config.php

    if [ "$HTTP_SERVER" != '' ]; then
	cd $TMPDIR
	FILE=`grep mayor-base md5sum | cut -d ' ' -f 3`
	tar xfz $FILE -C $BASEDIR ./bin
	cd $BASEDIR/bin
    fi

    echo "            * Konfigurációs könyvtár linkelése a /etc-be"
    ln -s $BASEDIR/config /etc/mayor

    echo "            * Jogosultságok állítása"
    chmod +x $BASEDIR/bin/mayor

    echo "
#*****************************************************************#
#                                                                 #
#  A konfigurációs beállítások átalakítása befejeződött.          #
#                                                                 #
#  Ellenőrizze a /etc/mayor/main.conf állomány tartalmát, vala-   #
#  mint a /var/log/mayor/revision állományban található számot,   #
#  aminek 1582-nél kisebbnek kell még lennie.                     #
#                                                                 #
#  Ha mindent rendben talál futtassa újra a mayor update paran-   #
#  csot! Kövesse nyomon az üzeneteket, melyek mostantól a képer-  #
#  nyő mellett a /var/log/mayor/update.log állományban is megje-  #
#  lennek.                                                        #
#                                                                 #
#*****************************************************************#
"

exit 1

fi