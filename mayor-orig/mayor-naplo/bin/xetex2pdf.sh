#!/bin/bash
#
# A script segítségével tesztelhetjük, hogy mi történik, mikor a program a haladásinaplót, illetve osztályozónaplót generálja
# __NYOMTATAS_XETEX===true beállítás mellett
#
# Paraméter: a feldolgozandó UTF-8 kódolású TeX állomány "-u8.tex" végződés nélküli neve.
#

. /etc/mayor/main.conf

HOME="/tmp"
export HOME

# Make sure this exists
# cd $BASEDIR/print/module-naplo/tex/
# fmtutil-sys --cnffile $BASEDIR/print/module-naplo/tex/mayor.cnf --fmtdir $BASEDIR/print/module-naplo/ --byfmt mayor
        
cat <<EOF > $1.tex
%\font\kicsi=ecrm0500
%\font\nagy=ecbx1200
%\font\vastag=ecsx0800
%\font\nagyss=ecsx1200
%\font\normal=ecss0800
%\font\dolt=ecsi0800

\font\kicsi="Linux Libertine O" at 5pt
\font\nagy="Linux Libertine O/B" at 12pt
\font\nagyss="Arial/B" at 12pt
\font\normal="Linux Biolinum O" at 8pt
\font\dolt="Linux Biolinum O/I" at 8pt
\normal

EOF
cat $1-u8.tex  >> $1.tex
xetex -fmt $BASEDIR/print/module-naplo/xetex/mayor-xetex $1.tex

