#!/bin/bash
                                                                                                                                            
FILES="print/module-naplo/mayor.fmt print/module-naplo/mayor.tex"                                                                           
                                                                                                                                            
echo -e "\n          * Felesleges állományok törlése:"                                                                                      
  for FILE in $FILES                                                                                                                        
  do                                                                                                                                        
    echo -n "            $BASEDIR/$FILE ... "                                                                                               
    if [ -e "$BASEDIR/$FILE" ]; then                                                                                                        
        rm -f "$BASEDIR/$FILE"                                                                                                              
        echo "törölve."                                                                                                                     
    else                                                                                                                                    
        echo "nincs."                                                                                                                       
    fi                                                                                                                                      
  done                                                                                                                                      
echo -e "\n          * Új formátum file generálása"                                                                                         
    cd $BASEDIR/print/module-naplo/tex/                                                                                                     
    fmtutil-sys --cnffile $BASEDIR/print/module-naplo/tex/mayor.cnf --fmtdir $BASEDIR/print/module-naplo/ --byfmt mayor
    # Ha az fmtutil nem a tex alkönyvtárba hozná létre a mayor.fmt-t, akkor áthelyezzük:
    if [ -e "$BASEDIR/print/module-naplo/mayor.fmt" ]; then
	mv $BASEDIR/print/module-naplo/mayor.fmt $BASEDIR/print/module-naplo/tex/mayor.fmt
    fi
echo " Kész."                                                                                                                               
