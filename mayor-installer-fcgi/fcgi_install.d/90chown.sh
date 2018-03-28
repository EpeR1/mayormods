#!/bin/bash

echo -e "A könyvtárak jogosultságainak beállítása\n\n"
read -n 1 -p "beállíthatom? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nNem.\n"; exit 1; fi


if [ $MAYORUSER == "" ] || [ $MAYORDIR == "" ]; then echo -e "A MAYOR(DIR/USER) változó üres! .....kilépek\n" ; exit 1 ; fi

chown -R "$MAYORUSER:$MAYORUSER" /home/$MAYORUSER/  ## Ez esetben a MAYORDIR a /home/xxx/mayor-ra mutat

find /home/$MAYORUSER/ -type f -print0 | xargs -0 chmod 0644
find /home/$MAYORUSER/ -type d -print0 | xargs -0 chmod 0751
find /home/$MAYORUSER/ -name '*.php'  -print0 | xargs -0 chmod 0640
find /home/$MAYORUSER/mayor/ -name '*.sh'  -print0 | xargs -0 chmod 0600
find /home/$MAYORUSER/mayor/bin/ -name '*.sh'  -print0 | xargs -0 chown root:root
find /home/$MAYORUSER/mayor/ -name '*.conf'  -print0 | xargs -0 chmod 0600

# a csomagoló jogainak visszaállítása
chmod -R 600 "/home/$MAYORUSER/mayor/www-bin/"
chmod +x "/home/$MAYORUSER/mayor/www-bin/php7.fcgi"

## de azért idemásolom az eredetit is, biztos-ami-biztos
chmod 700 $MAYORDIR/config/
# A main.conf védelme
chown root:root $MAYORDIR/config/main.conf
chmod 600 $MAYORDIR/config/main.conf


echo -e "...kész.\n"