#!/bin/sh

DIRS="script testdata tex vmg2006-data vmg2007"

echo -e "\n          Elavult könyvtárak törlése:\n"
for DIR in $DIRS; do
    echo -n "          $BASEDIR/install/module-naplo/$DIR ... "
    if [ -d $BASEDIR/install/module-naplo/$DIR ]; then
	rm -rf $BASEDIR/install/module-naplo/$DIR
	echo "törölve."
    else
	echo "nincs."
    fi
done

echo -ne "\n          A download könyvtár tulajdonosának beállítása ... "
chown -R www-data.www-data $BASEDIR/download
echo "kész."

echo -ne "\n          A /usr/local/sbin/update.sh ... "
(cat <<EOF
#!/bin/sh

BASEDIR=$BASEDIR

cd \$BASEDIR/bin
. ./update.sh

EOF
) > /usr/local/sbin/update.sh
echo "kész."

