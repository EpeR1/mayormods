#!/bin/bash                                                                                                                                 
#                                                                                                                                           
cat <<EOF                                                                                                                                   
A mayor.fmt előllítása...
                                                                                                                                            
EOF

cd $MAYORDIR/print/module-naplo/tex/  
fmtutil-sys --cnffile $MAYORDIR/print/module-naplo/tex/mayor.cnf --fmtdir $MAYORDIR/print/module-naplo/ --byfmt mayor                     

# Ha az fmtutil nem a tex alkönyvtárba hozná létre a mayor.fmt-t, akkor áthelyezzük:                                                    
if [ -e "$MAYORDIR/print/module-naplo/mayor.fmt" ]; then                                                                                 
        mv $MAYORDIR/print/module-naplo/mayor.fmt $MAYORDIR/print/module-naplo/tex/mayor.fmt                                                  
fi                                                                                                                                      

# Ha telepítve van a XeTeX csomag, akkor ahhoz is generálunk *.fmt-t
if [ -e /usr/bin/xetex ]; then
    cd $MAYORDIR/print/module-naplo/xetex/  
    fmtutil-sys --cnffile $MAYORDIR/print/module-naplo/xetex/mayor-xetex.cnf --fmtdir $MAYORDIR/print/module-naplo/ --byfmt mayor-xetex
fi
