#!/bin/bash
#

cat <<EOF
A rendszer telepítéséhez szükséges környezet kialakítása

Létrehozzuk a szükséges könyvtárakat, kicsomagoljuk a forrásokat...
EOF

if [ ! -d "$MAYORDIR" ]; then
    echo Telepítési könyvtár: $MAYORDIR
    mkdir -p $MAYORDIR
fi
if [ ! -d "$BACKUPDIR" ]; then
    echo Mentési könyvtár: $BACKUPDIR
    mkdir -p $BACKUPDIR
fi
