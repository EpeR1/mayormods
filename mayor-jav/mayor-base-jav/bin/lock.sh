#!/bin/bash

help_usage() {
cat <<EOF

LOCK használata: mayor lock [options]

A parancs segítségével zárolhatjuk a MaYoR rendszert, ilyenkor a webes elérés le van tiltva.
Hasznos lehet különböző Rendszergazdai műveletek elvégzésekor.

	--help: 	^Ez a help szöveg.

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

if [ ! -z $LOCKFILE ]; then
    if [ $VERBOSE -gt 0 ]; then echo -e "* A web-es elérés letiltása:"; fi
    if [ $VERBOSE -gt 1 ]; then echo -e "- Lock-file létrehozása... "; fi
    
    echo "$DATE: Rendszergazda dolgozik" > $LOCKFILE
    if [ $VERBOSE -gt 0 ]; then echo -e "kész.\n"; fi
fi

