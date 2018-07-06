#!/bin/bash

if [ -f $BASEDIR/config/backup.conf ]; then
    . $BASEDIR/config/backup.conf
else
    echo -e "\n\nERROR: hiányzó konfigurációs file: $BASEDIR/config/backup.conf\n"
    exit 1
fi

if [ ! -d $BACKUPDIR ]; then
    echo -e "\n\nERROR: hibás mentési könyvtár: $BACKUPDIR\n"
    exit 1
fi

if [ ! -d $BACKUPDIR/rev-$REV ]; then
    echo -n "          - $REV. változat mentési könyvtára: "
    mkdir $BACKUPDIR/rev-$REV
    echo $BACKUPDIR/rev-$REV
fi

FILES='eNaploBackup.sh eNaploRestore.sh update.sh'
echo '          - Az elavult /usr/local/sbin elavult scriptjeinek törlése...'
for FILE in $FILES; do
    echo -n "            /usr/local/sbin/$FILE "
    if [ -f /usr/local/sbin/$FILE ]; then
	mv /usr/local/sbin/$FILE $BACKUPDIR/rev-$REV/$FILE
	echo "--> $BACKUPDIR/rev-$REV/$FILE"
    else
	echo "-- nincs"
    fi
done

echo -n "          - Az új mayor szkript telepítése... "
if [ ! -f $BASEDIR/bin/mayor ]; then
    echo -e "\n          !! WARNING !!: Az új script még nincs letöltve\n"
    echo -n "            Letöltés ... "
    $SVN --force export https://svn.mayor.hu/svn/trunk/mayor-base/bin "$BASEDIR/bin" > /dev/null
    echo -n "mayor-base/bin ... "
    $SVN --force export https://svn.mayor.hu/svn/trunk/mayor-naplo/bin "$BASEDIR/bin" > /dev/null
    echo -e "mayor-naplo/bin\n"
fi
chmod +x $BASEDIR/bin/mayor
ln -s $BASEDIR/bin/mayor /usr/local/sbin
echo "kész."

if [ -f /etc/cron.daily/eNaplo ]; then
    echo "          - Az elavult cron script törlése"
    echo -n "            /etc/cron.daily/eNaplo --"
    mv /etc/cron.daily/eNaplo $BACKUPDIR/rev-$REV/eNaplo
    echo "> $BACKUP/rev-$REV/eNaplo"
fi

if [ -f $BASEDIR/bin/etc/cron.daily/mayor ]; then 
    echo -n "          - Az új cron script telepítése ... "
    cp $BASEDIR/bin/etc/cron.daily/mayor /etc/cron.daily/mayor
    echo "kész."
fi

