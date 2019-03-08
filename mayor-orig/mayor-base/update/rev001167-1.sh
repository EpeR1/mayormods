#!/bin/bash

echo -ne "\n          Az update.conf file jogosultságainak beállítása ... "
# nem kell ellenőrizni, mert ilyen állomány biztosan van - ha eljutottunk idáig
chmod 0600 $BASEDIR/config/update.conf
chown root.root $BASEDIR/config/update.conf
echo "kész."

