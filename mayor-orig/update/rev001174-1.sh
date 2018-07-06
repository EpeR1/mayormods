#!/bin/bash

echo "          - A main-config.php áthelyezése"
if [ -f $BASEDIR/www/include/config/main-config.php ]; then
    echo -n "             $BASEDIR/www/include/config/main-config.php --> "
    mv $BASEDIR/www/include/config/main-config.php $BASEDIR/config/main-config.php
    echo "$BASEDIR/config/main-config.php"
else
    echo "             $BASEDIR/www/include/config/main-config.php -- nincs!!"
fi

echo "          - Az esetleg létező main-config.php.example törlése"
if [ -f $BASEDIR/www/include/config/main-config.php.example ]; then
    echo -n "             $BASEDIR/www/include/config/main-config.php.example -- "
    rm $BASEDIR/www/include/config/main-config.php.example
    echo "törölve"
else
    echo "             $BASEDIR/www/include/config/main-config.php.example -- nincs"
fi

echo "          - A config/base könyvtár létrehozása"
if [ ! -d $BASEDIR/config/base ]; then
    echo -n "             A könyvtár létrehozása: "
    mkdir $BASEDIR/config/base
    echo "$BASEDIR/config/base"
fi

echo "          - A modulok konfig álloményainak áthelyezése - ha volnának"
if [ `ls -l $BASEDIR/www/include/config/module* 2>/dev/null | wc -l` \> 0 ]; then
    echo -n "             A modulok konfigurációs állományainak áthelyezése ... "
    cp $BASEDIR/www/include/config/module* $BASEDIR/config/base/
    rm $BASEDIR/www/include/config/module*
    echo "kész."
fi

echo "          - A _MAYOR_DIR és _CONFIGDIR konstansok bevezetése a main-config.php-ben"
if [ -f $BASEDIR/config/main-config.php ]; then
    sed -r -i.rev1174  \
	-e "s#_BASEDIR','(.*)/www.*#_MAYOR_DIR','\1');\ndefine('_BASEDIR',_MAYOR_DIR.'/www');\ndefine('_CONFIGDIR',_MAYOR_DIR.'/config');#" \
	-e "s#_DATADIR','.*'#_DATADIR',_MAYOR_DIR.'/data'#" \
	-e "s#_DOWNLOAD_DIR','.*'#_DOWNLOADDIR',_MAYOR_DIR.'/download'#" \
	-e "s#'include/config/'#_CONFIGDIR.'/base/'#" $BASEDIR/config/main-config.php
    echo "             kész."
else
    echo -e "\n\nERROR: Hiányzó konfigurációs file: $BASEDIR/config/main-config.php\n"
    exit 1
fi

echo "          - A policy-k konfigurációs állományainak áthelyezése"
for policy in private parent public; do
    if [ -f $BASEDIR/www/include/config/$policy-conf.php ]; then
	echo -n "             $BASEDIR/www/include/config/$policy-conf.php --> "
	mv $BASEDIR/www/include/config/$policy-conf.php $BASEDIR/config/$policy-conf.php
	echo "$BASEDIR/config/$policy-conf.php"
    else
	echo "             $BASEDIR/www/include/config/$policy-conf.php -- nincs"
    fi
done

echo "          - A policy-k konfigurációs állományainak minta állományait töröljük (.example)"
for policy in private parent public; do
    if [ -f $BASEDIR/www/include/config/$policy-conf.php.example ]; then
	echo -n "             $BASEDIR/www/include/config/$policy-conf.php.example -- "
	rm $BASEDIR/www/include/config/$policy-conf.php.example
	echo "törölve"
    else
	echo "             $BASEDIR/www/include/config/$policy-conf.php.example -- nincs"
    fi
done

echo "          - A modulok base/config.php-inek áthelyezése"
for module in naplo portal forum felveteli honosito; do
    if [ -f $BASEDIR/www/include/modules/$module/base/config.php ]; then
	if [ ! -d $BASEDIR/config/module-$module ]; then
	    echo -n "             A könyvtár létrehozása: "
	    mkdir $BASEDIR/config/module-$module
	    echo "$BASEDIR/config/module-$module"
	fi
	echo -n "             $BASEDIR/www/include/modules/$module/base/config.php --> "
	mv $BASEDIR/www/include/modules/$module/base/config.php $BASEDIR/config/module-$module/config.php
	echo "$BASEDIR/config/module-$module/config.php"
    else
	echo "             $BASEDIR/www/include/modules/$module/base/config.php -- nincs"
    fi
done

echo "          - A modulok base/config.php.example-inek törlése"
for module in naplo portal forum felveteli honosito; do
    if [ -f $BASEDIR/www/include/modules/$module/base/config.php.example ]; then
	echo -n "             $BASEDIR/www/include/modules/$module/base/config.php.example -- "
	rm $BASEDIR/www/include/modules/$module/base/config.php.example
	echo "törölve"
    else
	echo "             $BASEDIR/www/include/modules/$module/base/config.php.example -- nincs"
    fi
done

echo "          - A napló intézményi config file-jainak (és mintáinak) áthelyezése"
if [ `ls -l $BASEDIR/www/include/modules/naplo/config-* 2>/dev/null | wc -l` \> 0 ]; then
    echo -n "             Az intézményi konfigurációs állományok áthelyezése ... "
    cp $BASEDIR/www/include/modules/naplo/config-* $BASEDIR/config/module-naplo
    rm $BASEDIR/www/include/modules/naplo/config-*
    echo "kész."
fi

echo "          - A classic skin configurációs könyvtárának létrehozása"
if [ ! -d $BASEDIR/config/skin-classic ]; then
    echo -n "             A könyvtár létrehozása: "
    mkdir $BASEDIR/config/skin-classic
    echo "$BASEDIR/config/skin-classic"
fi

echo "A pda skin configurációs könyvtárának létrehozása"
if [ ! -d $BASEDIR/config/skin-pda ]; then
    echo -n "             A könyvtár létrehozása: "
    mkdir $BASEDIR/config/skin-pda
    echo "$BASEDIR/config/skin-pda"
fi

echo "          - A skin config áthelyezése. Csak a classic skinhez!"
if [ -f $BASEDIR/www/skin/classic/config.php ]; then 
    echo -n "             $BASEDIR/www/skin/classic/config.php --> "
    mv $BASEDIR/www/skin/classic/config.php $BASEDIR/config/skin-classic/config.php
    echo "$BASEDIR/config/skin-classic/config.php"
else
    echo "             $BASEDIR/www/skin/classic/config.php -- nincs"
fi

echo "          - A skin config mintafile törlése"
if [ -f $BASEDIR/www/skin/classic/config.php.example ]; then 
    echo -n "             $BASEDIR/www/skin/classic/config.php.example -- "
    rm $BASEDIR/www/skin/classic/config.php.example
    echo "törölve"
else
    echo "             $BASEDIR/www/skin/classic/config.php.example -- nincs"
fi

echo "          - A modulok skin beállításai - csak classic skin-re!!"
for module in naplo portal forum; do
    if [ -f $BASEDIR/www/skin/classic/module-$module/config.php ]; then
	echo -n "             $BASEDIR/www/skin/classic/module-$module/config.php --> "
	mv $BASEDIR/www/skin/classic/module-$module/config.php $BASEDIR/config/skin-classic/$module-config.php
	echo "$BASEDIR/config/skin-classic/$module-config.php"
    else
	echo "          $BASEDIR/www/skin/classic/module-$module/config.php -- nincs"
    fi
done

echo "          - A modul skin beállítás minta állományok törlése"
for module in naplo portal forum; do
    if [ -f $BASEDIR/www/skin/classic/module-$module/config.php.example ]; then
	echo -n "             $BASEDIR/www/skin/classic/module-$module/config.php.example -- "
	rm $BASEDIR/www/skin/classic/module-$module/config.php.example
	echo "törölve"
    else
	echo "             $BASEDIR/www/skin/classic/module-$module/config.php.example -- nincs"
    fi
done

echo "          - /var/mayor/data/base/huhyphn.tex törlése"
if [ -f $BASEDIR/data/base/huhyphn.tex ]; then
    echo -n "             $BASEDIR/data/base/huhyphn.tex -- "
    rm $BASEDIR/data/base/huhyphn.tex
    echo "törölve"
else
    echo "             $BASEDIR/data/base/huhyphn.tex -- nincs"
fi
