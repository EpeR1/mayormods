#!/bin/bash
#

cat <<EOF
A forrás csomagok letöltése és kicsomagolása

Most letöltheti és kicsomagolhatja a forrás állományokat.
A telepítendő rendszer gyökérkönyvtára:
        $MAYORDIR 
lesz.

A telepítő mindig az aktuális legfrissebb változatot tölti le.

Amennyiben egy korábbi változatot szeretne telepíteni (pl. korábbi
mentés helyreállításához), úgy töltse le a szükséges állományokat
(mayor-base-rev????.tgz, mayor-naplo-rev????.tgz) és helyezze el
őket a $TMPDIR könyvtárban. Ezek után folytassa a telepítést a 
csomagok letöltésének kihagyásával.

EOF

#read -n 1 -p "Letöltsem a forrás csomagokat? (i/N)" -s DO

if [ "$MAYORDIR" = "" ]; then echo "A MAYORDIR változó üres. Kilépek."; exit 1; fi
if [ "$TMPDIR" = "" ]; then echo "A TMPDIR változó üres. Kilépek."; exit 1; fi


MENU=$( /bin/ls -1 $TMPDIR/mayor-base-*.tgz 2>/dev/null | sed 's/.*-\([^-]*\)\.tgz$/\1/' )

DO=n
if [ "$MENU" == '' ]; then
	DO=i
else
	echo "     0  Letöltés mindenképpen"
	echo "${MENU}" | sed 's/.*-\([^-]*\)\.tgz$/\1/' | nl
	read -n1 -p 'Melyiket telepítsem? ' SEL; echo

	if [ "$SEL" == 0 ]; then
		DO=i
	else
		SELECT=$( echo "${MENU}" | sed -n ${SEL}p )

		MAYORBASE="$TMPDIR/mayor-base-$SELECT.tgz"
		MAYORNAPLO="$TMPDIR/mayor-naplo-$SELECT.tgz"
	fi
fi

if [ "$DO" == "i" ]; then
	MAYORBASE="$TMPDIR/mayor-base-current.tgz"
	MAYORNAPLO="$TMPDIR/mayor-naplo-current.tgz"

	echo -e "\nForrások letöltése:"
	cd $TMPDIR
	rm -f mayor-base-current.tgz
	rm -f mayor-naplo-current.tgz
	wget "http://www.mayor.hu/download/$VERSION/mayor-base-current.tgz"
	wget "http://www.mayor.hu/download/$VERSION/mayor-naplo-current.tgz"
else
	echo -e "\nA forráscsomagok letöltését kihagytam.\n"
fi

read -n 1 -p "Telepítsem a forrás csomagokat? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nA forráscsomagok telepítését kihagytam.\n"; exit 1; fi

echo -n -e "\nRendszer könyvtár: "
if [ ! -e $MAYORDIR ]; then
    mkdir -p $MAYORDIR
fi
if [ ! -e "/var/mayor" ]; then
        ln -s $MAYORDIR /var/mayor
fi
echo $MAYORDIR

echo -e -n "Források kicsomagolása... "
cd $MAYORDIR
tar xfz "$MAYORBASE"
tar xfz "$MAYORNAPLO"
echo "ok."

