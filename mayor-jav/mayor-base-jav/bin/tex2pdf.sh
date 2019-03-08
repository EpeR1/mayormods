#!/bin/bash
#
# A script segítségével tesztelhetjük, hogy mi történik, mikor a program a haladásinaplót, illetve osztályozónaplót generálja...
#
# Paraméter: a feldolgozandó UTF-8 kódolású TeX állomány "-u8.tex" végződés nélküli neve.
#

. /etc/mayor/main.conf

HOME="/tmp"
export HOME

# Make sure this exists
# cd $BASEDIR/print/module-naplo/tex/
# fmtutil-sys --cnffile $BASEDIR/print/module-naplo/tex/mayor.cnf --fmtdir $BASEDIR/print/module-naplo/ --byfmt mayor
        
cat $1-u8.tex | recode u8..T1 > $1.tex

tex -fmt $BASEDIR/print/module-naplo/tex/mayor $1.tex

dvips $1.dvi

ps2pdf -sPAPERSIZE=a4 -dAutoRotatePages=/None $1.ps

