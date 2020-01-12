#!/bin/bash
#
DEBIAN=false;
UBUNTU=false;
if [ `which lsb_release || echo "notinstalled"` == "notinstalled" ]
then
    ISSUE=`cat /etc/issue | cut -d " " -f 1`
    if [ "x${ISSUE}" == "xDebian" ]; then
	DEBIAN=true;
	RELEASE=`cat /etc/issue | cut -d " " -f 3`
    elif [ "x${ISSUE}" == "xUbuntu" ]; then
	UBUNTU=true;
	RELEASE=`cat /etc/issue | cut -d " " -f 2`
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

TEST=`grep contrib /etc/apt/sources.list`
if [ "$TEST" == "" ]
then
    echo "!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!"
    echo "Az apt sources.list módosítása szükséges!"
    if [ DEBIAN ]; then
	echo "Debian Jessie (8) esetén például:
    deb http://ftp.hu.debian.org/debian/ jessie main contrib non-free
    deb http://security.debian.org/ jessie/updates main contrib non-free
    deb http://ftp.hu.debian.org/debian/ jessie-updates main contrib non-free
	"
    fi
    exit 255;
fi

if [[ "x${RELEASE}" =~ ^x9.* ]] 
then
    PKGS="apache2 php php-json php-mysql php-ldap php-mbstring php-mcrypt php-curl mariadb-server-10.1 recode texlive texlive-fonts-extra texlive-latex-extra texlive-binaries texlive-xetex ntp wget ssl-cert ssh pwgen texlive-lang-european"
elif [[ "x${RELEASE}" =~ ^x10.* ]]
then
    ## PHP 7.2-től php-mcrypt deprecated --> kivettük, de a kódban még van...
    PKGS="apache2 php php-json php-mysql php-ldap php-mbstring php-curl php-bcmath mariadb-server-10.3 recode texlive texlive-fonts-extra texlive-latex-extra texlive-binaries texlive-xetex ntp wget ssl-cert ssh pwgen texlive-lang-european"
else
    PKGS="apache2 php5 php5-json php5-mysqlnd php5-ldap php5-mcrypt php5-curl mysql-server recode texlive texlive-fonts-extra texlive-latex-extra texlive-binaries texlive-xetex ttf-mscorefonts-installer ntp wget ssl-cert ssh pwgen texlive-lang-european texlive-lang-hungarian"
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
    STAT=`dpkg -l $pkg | grep $pkg | cut -f 1 -d ' '`
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
    apt-get update
    apt-get -m -f install $MISSING
fi
