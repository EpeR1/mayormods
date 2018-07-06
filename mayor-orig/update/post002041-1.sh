#!/bin/bash
                                                                                                                                            
echo -e "\n          * Új formátum file generálása (system-wide, az /usr - nek írhatónak kell lennie)"                                                                                         
    cd $BASEDIR/print/module-naplo/tex/                                                                                                     
    fmtutil-sys --cnffile $BASEDIR/print/module-naplo/tex/mayor.cnf --fmtdir $BASEDIR/print/module-naplo/ --byfmt mayor
    # Ha az fmtutil nem a tex alkönyvtárba hozná létre a mayor.fmt-t, akkor áthelyezzük:
    if [ -e "$BASEDIR/print/module-naplo/mayor.fmt" ]; then
	mv $BASEDIR/print/module-naplo/mayor.fmt $BASEDIR/print/module-naplo/tex/mayor.fmt
    fi
echo " Kész."                                                                                                                               
