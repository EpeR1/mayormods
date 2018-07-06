#!/bin/bash

# Ha telepítve van a XeTeX csomag, akkor ahhoz is generálunk *.fmt-t
if [ -e /usr/bin/xetex ]; then
    echo -e "\n          * Új XeTeX formátum file generálása (szükséges a texlive-xetex csomag, telepítsük!)"
    apt-get --yes --force-yes install texlive-xetex
    echo -e "\n          * Új XeTeX formátum file generálása (system-wide, az /usr - nek írhatónak kell lennie)"                                                                                         
    cd $BASEDIR/print/module-naplo/xetex/
    fmtutil-sys --cnffile $BASEDIR/print/module-naplo/xetex/mayor-xetex.cnf --fmtdir $BASEDIR/print/module-naplo/ --byfmt mayor-xetex
else
    echo 'Nincs telepítve a TeXLive XeTeX csomagja (texlive-xetex és ttf-mscorefonts-installer). A __NYOMTATAS_XETEX=true opció, csak a csomag telepítése után használható.
Az opció használatával lehetővé válik speciális karakterek, pl. cirill betűk használata a haladási és osztályozónapló nyomtatványokban.'
fi
