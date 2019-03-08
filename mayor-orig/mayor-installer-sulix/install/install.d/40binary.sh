#!/bin/bash
#

cat <<EOF
Karbantartást segítő szkriptek

A telepítő szimbolikus linket készít a /usr/local/sbin-be, hogy
a "mayor update" illettve "mayor backup" parancsokat bárhonnan
kiadhassuk, majd az /etc/cron.daily könyvtár alá is link készül,
hogy a mentések és frissítések rendszeresen lefuthassanak.

EOF

if [ "$MAYORDIR" = "" ]; then echo "MAYORDIR változó üres. Kilépek!"; exit 1; fi

    # A karbantartást segítő scriptek:
    if [ ! -e /usr/local/sbin/mayor ]; then 
	ln -s $MAYORDIR/bin/mayor /usr/local/sbin/mayor; 
	echo -e "\n  Az /usr/local/sbin/ alá létrejött a mayor szimbolikus link.";
    fi
# Majd meglátjuk mi lesz a frissítéssel / mentéssel...
#    if [ ! -e /etc/cron.daily/mayor ]; then 
#	ln -s $MAYORDIR/bin/etc/cron.daily/mayor /etc/cron.daily; 
#	echo -e "\n  Az /etc/cron.daily/ alá létrejött a mayor szimbolikus link.";
#    fi
