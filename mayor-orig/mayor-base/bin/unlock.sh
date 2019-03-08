#!/bin/bash

help_usage() {
cat <<EOF

LOCK használata: mayor lock [options]

A parancs segítségével feloldhatjuk a MaYoR rendszert, ilyenkor a webes engedélyezésre kerül.
Hasznos lehet különböző Rendszergazdai műveletek elvégzésekor.

*** FIGYELEM! ***
   Veszélyes lehet, ha a feloldás, a frissítés, vagy a mentés vége előtt történik!!

        --help:         ^Ez a help szöveg.

AUTHOR: Miklós Gergő <gergo@bmrg.hu> - Baár-Madas Református Gimnázium

EOF
}

if [ $? -ne 0 ]; then  help_usage; exit; fi

while [ $# -ge 1 ]; do
    case $1 in
        --help | -h )           help_usage
                                exit
                                ;;

        -- )                    shift
                                break
                                ;;

        * )                     echo "HIBA: ismeretlen opció: $1"
                                exit
                                ;;
    esac
    shift
done

if [ "x$LOCKFILE" == "x" ]; then echo -e "Üres a LOCKFILE változó :("; exit 1; fi

if [ -e $LOCKFILE ]; then
    if [ ! -z $LOCKFILE ]; then
	if [ $VERBOSE -gt 0 ]; then echo -e "* A web-es hozzáférés engedélyezése:"; fi
	if [ $VERBOSE -gt 1 ]; then echo -e "- A Lock-file törlése..."; fi
	rm $LOCKFILE
	if [ $VERBOSE -gt 0 ]; then echo -e "kész.\n"; fi
    fi
else
    if [ $VERBOSE -gt 0 ]; then echo -e "* Már engedélyezett.\n"; fi
fi

