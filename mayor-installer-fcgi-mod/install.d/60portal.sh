#!/bin/bash
#

cat <<EOF
A MaYoR Portal modul telepítése

A keretrendszer és az elektronikus napló modul mellett
telepíthető a portál modul, ami egy egyszerű, testreszabható
nyitóoldalt ad az egyes hozzáférési szintekhez. A személyes
kezdőlapon megjeleníthető a napi órarend, a legutóbbi üzenetek,
az aktuális kérelmek listája, név- és születésnap információk,
illetve kiírhatunk híreket is.

EOF

read -n 1 -p "Telepítsem a portál modult? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nA portál modult nem telepítem.\n"; exit 1; fi

echo -e "\nA main.conf módosítása ..."
if [ ! -e $MAYORDIR/config/main.conf ]; then
	echo -e "\nHiányzó konfigurációs állomány: $MAYORDIR/config/main.conf"
	exit 1;
fi

sed -r -i.60portal \
	-e "s#mayor-naplo#mayor-naplo mayor-portal#" $MAYORDIR/config/main.conf

echo -e "\nA telepítőcsomag letöltése ..."

cd $TMPDIR
rm -f mayor-portal-current.tgz
wget "http://www.mayor.hu/download/$VERSION/mayor-portal-current.tgz"

echo -e -n "Források kicsomagolása... "
cd $MAYORDIR
tar xfz "$TMPDIR/mayor-portal-current.tgz"
echo "ok."

echo -e "\nA porál modul konfigurációs állományának létrehozása"
file="module-portal/config.php"
if [ -e "${MAYORDIR}/config/${file}" ]; then echo "  $file létezik."; else
	echo -n "  $file.example --> "
	PW=$(pwgen -s1 32)
	if [ "$ROVID" = "" ]; then
		ROVID="demo"
	fi
	cat "$MAYORDIR/config/$file.example" | sed -e "s/%SQLPW%/$PW/" -e "s/demo/${ROVID}/" > "$MAYORDIR/config/$file"
	echo $file
fi
						
echo -e "\n SQL file létrehozása... "
if [ ! -d $TMPDIR/mysql ]; then
	mkdir -p $TMPDIR/mysql
fi
cd $TMPDIR/mysql
PDB=$(grep db $MAYORDIR/config/$file | sed -e "s/.*=\ *['\"]//g" -e "s/['\"];//g")
USER=$(grep user $MAYORDIR/config/$file | sed -e "s/.*=\ *['\"]//g" -e "s/['\"];//g")
PW=$(grep pw $MAYORDIR/config/$file | sed -e "s/.*=\ *['\"]//g" -e "s/['\"];//g")

cat $MAYORDIR/install/mayor-portal/mysql/mayor-portal.sql | sed \
	-e "s/%MYSQL_PORTAL_DB%/$PDB/g" \
	-e "s/%MYSQL_PORTAL_USER%/$USER/g" \
	-e "s/%MYSQL_PORTAL_PW%/$PW/g" > mayor-portal.sql

DB=$(grep db $MAYORDIR/config/private-conf.php | sed -e "s/^.*>\ *['|\"]//g" -e "s/['|\"],//g")
cat $MAYORDIR/install/mayor-portal/mysql/portal-init.sql | sed \
	-e "s/%MYSQL_PRIVATE_DB%/$DB/g" \
	-e "s/%MYSQL_PORTAL_DB%/$PDB/g" > portal-init.sql

read -n 1 -p "Telepíthetem? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo " ok, kiléptem..."; exit 1; fi
echo -e "\n"

if [ "$MYSQLROOTPW" == "" ]; then
    read -p " A mysql root jelszó -ha van- (a begépelt szöveg nem látszik!): " -s MYSQLROOTPW
fi

if [ "$MYSQLROOTPW" = "" ];
then
    MYSQLROOTPWSTR="";
else
    MYSQLROOTPWSTR="-p$MYSQLROOTPW --user=root"
fi


cat /tmp/mysql/mayor-portal.sql /tmp/mysql/portal-init.sql | mysql $MYSQLROOTPWSTR --default-character-set=utf8

read -n 1 -p "Tegyük a portál oldalt a rendszer kezdőlapjává minden hozzáférési szinten? (i/N)" -s DO
if [ "$DO" != "i" ]; then 
	echo -e "\nA portál oldalt nem teszem kezdőlappá.\n"; 
	cat <<EOF 

Az egyes hozzáférési szintek konfigurációs állományaiban
(private-conf.php, parent-conf.php, public-conf.php) állítható
be, hogy mi legyen a kezdőlap a \$DEFAULT_PSF tömb módosításával.

EOF

else
	echo ""
	for file in private-conf.php parent-conf.php public-conf.php; do
		echo -n "    $file ... "
		sed  -i.60portal -e "s/^[ \t]*\$DEFAULT_PSF\[\(.*\)\]\(.*\)/\/\/\t\$DEFAULT_PSF\[\1\]\2\n\t\$DEFAULT_PSF\[\1\] = array('page'=>'portal', 'sub' => 'portal', 'f' => 'portal');/" $MAYORDIR/config/$file
		echo  ok
	done
fi

if [ "$ROVID" != "demo" ]; then
	echo -e "\nEgyedi kezdőoldalak létrehozása:"
	for policy in private public parent; do
		echo -n "  $policy "
		for skin in classic pda; do
			cp $MAYORDIR/www/policy/$policy/portal/portal/portal_demo.$skin.php $MAYORDIR/www/policy/$policy/portal/portal/portal_$ROVID.$skin.php
			echo -n "... "
		done
		echo "ok"
	done
fi

