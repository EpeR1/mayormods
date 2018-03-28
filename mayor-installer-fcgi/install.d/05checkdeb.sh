#!/bin/bash
#
DEBIAN=false;
UBUNTU=false;
if [ $(which lsb_release || echo "notinstalled") == "notinstalled" ]
then
    ISSUE=$(cat /etc/issue | cut -d " " -f 1)
    if [ "x${ISSUE}" == "xDebian" ]; then
	DEBIAN=true;
	RELEASE=$(cat /etc/issue | cut -d " " -f 3)
    elif [ "x${ISSUE}" == "xUbuntu" ]; then
	UBUNTU=true;
	RELEASE=$(cat /etc/issue | cut -d " " -f 2)
    fi
else
    DISTRIBUTOR=$(lsb_release -i -s)
    RELEASE=$(lsb_release -r -s)
    if [ "x${DISTRIBUTOR}" == "xDebian" ]; then
	DEBIAN=true;
    elif [ "x${DISTRIBUTOR}" == "xUbuntu" ]; then
	UBUNTU=true;
    fi
fi
echo "Debian:" ${DEBIAN}
echo "Ubuntu:" ${UBUNTU}
echo "Version:" ${RELEASE}

TEST=$(grep contrib /etc/apt/sources.list)
if [ "$TEST" == "" ]
then
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo "Az apt sources.list módosítása szükséges!"
    if [ DEBIAN ]; then
	echo "Debian Jessie (8) esetén például:
    deb http://ftp.hu.debian.org/debian/ jessie main contrib non-free
    deb http://security.debian.org/ jessie/updates main contrib non-free
    deb http://ftp.hu.debian.org/debian/ jessie-updates main contrib non-free

Debian 9 esetén például:
    deb http://ftp.hu.debian.org/debian/ stretch main contrib non-free
    deb http://security.debian.org/debian-security stretch/updates main contrib non-free
    deb http://ftp.hu.debian.org/debian/ stretch-updates main contrib non-free    
        \n"

        read -n 1 -p "Hozzáadhatom? (egyelőre csak Debian9 esetén működik) (i/N)" -s DO
        if [ "$DO" != "i" ]; then echo -e "\nA hozzáadást kihagytam.\n"; exit 255; fi

        if [[ "x${RELEASE}" =~ ^x9.* ]]; then
                echo -e "#mayor miatt is:
deb http://ftp.hu.debian.org/debian/ stretch contrib non-free
deb http://security.debian.org/debian-security stretch/updates contrib non-free
deb http://ftp.hu.debian.org/debian/ stretch-updates contrib non-free"  >> /etc/apt/sources.list

        fi
        echo -e "\n ----  csomaglista frissítése ---- \n"
        apt update  ## frisítés
        echo -e "\n -------------  kész ------------- \n"
        
    fi
#    exit 255;
fi

if [[ "x${RELEASE}" =~ ^x9.* ]]
then
    PKGS="apache2 php libapache2-mod-php php-json php-mysql php-ldap php-mbstring php-mcrypt php-curl mariadb-server recode texlive texlive-fonts-extra texlive-latex-extra texlive-binaries texlive-xetex ntp wget ssl-cert ssh pwgen texlive-lang-european"
else
    PKGS="apache2 php5 libapache2-mod-php5 php5-json php5-mysqlnd php5-ldap php5-mcrypt php5-curl mysql-server recode texlive texlive-fonts-extra texlive-latex-extra texlive-binaries texlive-xetex ttf-mscorefonts-installer ntp wget ssl-cert ssh pwgen texlive-lang-european texlive-lang-hungarian"
fi

if [ "$1" == "--no-deb" ]; then
    exit 1
fi

cat <<EOF
A rendszer futásához szükséges csomagok ellenőrzése

Ebben a lépésben ellenőrizheti és telepítheti a rendszer működéséhez
szükséges Debian/Ubuntu csomagokat:

$PKGS

EOF

read -n 1 -p "Ellenőrizzem a deb csomagokat? (i/N)" -s DO
if [ "$DO" != "i" ]; then echo -e "\nA függőségek ellenőrzését kihagytam.\n"; exit 1; fi

echo -e "\nFüggőségek ellenőrzése (dpkg): "
MISSING=""
for pkg in $PKGS
do
    echo -n "  $pkg ... "
    STAT=$(dpkg -l $pkg | grep $pkg | cut -f 1 -d ' ')
    if [ "$STAT" == "ii" ]; then
	echo ok
    else
	echo "még nincs telepítve"
	MISSING="$MISSING $pkg"
    fi
done
if [ "$MISSING" != "" ]; then
    echo Még nem telepített csomagok: $MISSING
    read -n 1 -p "Telepítsem? (i/N)" -s DO
    if [ "$DO" != "i" ]; then echo " ok, kiléptem..."; exit 1; fi
    apt update
    apt -m -f install $MISSING
fi
