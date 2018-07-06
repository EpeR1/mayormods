#!/bin/bash


FILES="www/policy/parent/naplo/tanev/helyettesites-pre.php
www/policy/parent/naplo/tanev/helyettesites.php
www/policy/parent/naplo/tanev/orarend-pre.php
www/policy/parent/naplo/tanev/orarend.php
www/policy/public/naplo/tanev/orarend-pre.php
www/policy/public/naplo/tanev/orarend.php
www/policy/public/naplo/tanev/szabadTerem-pre.php
www/policy/public/naplo/tanev/szabadTerem.php
www/policy/public/naplo/tanev/helyettesites-pre.php
www/policy/public/naplo/tanev/helyettesites.php
www/include/modules/naplo/tanev/ascExport.php
www/include/modules/naplo/tanev/convert-Microsoft%Excel%XML.php
www/include/modules/naplo/tanev/convert-RoPaSoft.php
www/include/modules/naplo/tanev/convert-aSc%Timetables%XML%(tankör%nélkül).php
www/include/modules/naplo/tanev/convert-aSc%Timetables%XML%update.php
www/include/modules/naplo/tanev/convert-aSc%Timetables%XML.php
www/include/modules/naplo/tanev/convert-csv.php
www/include/modules/naplo/tanev/convert-default.php
www/include/modules/naplo/tanev/orarendBetolto.php
www/include/modules/naplo/tanev/orarendEllenorzes.php
www/include/modules/naplo/tanev/orarendModosito.php
www/include/modules/naplo/tanev/orarendTankor.php
www/include/modules/naplo/tanev/orarendTeremModositas.php
www/include/modules/naplo/tanev/tanarOrarend.php
www/lang/hu_HU/module-naplo/tanev/ascExport.php
www/lang/hu_HU/module-naplo/tanev/orarend.php
www/lang/hu_HU/module-naplo/tanev/orarendBetolto.php
www/lang/hu_HU/module-naplo/tanev/orarendEllenorzes.php
www/lang/hu_HU/module-naplo/tanev/orarendModosito.php
www/lang/hu_HU/module-naplo/tanev/orarendTankor.php
www/lang/hu_HU/module-naplo/tanev/orarendTeremModositas.php
www/lang/hu_HU/module-naplo/tanev/szabadTerem.php
www/lang/hu_HU/module-naplo/tanev/tanarOrarend.php
www/policy/private/naplo/tanev/ascExport-pre.php
www/policy/private/naplo/tanev/ascExport.php
www/policy/private/naplo/tanev/helyettesites-pre.php
www/policy/private/naplo/tanev/helyettesites.php
www/policy/private/naplo/tanev/orarend-pre.php
www/policy/private/naplo/tanev/orarend.php
www/policy/private/naplo/tanev/orarendBetolto-pre.php
www/policy/private/naplo/tanev/orarendBetolto.php
www/policy/private/naplo/tanev/orarendEllenorzes-pre.php
www/policy/private/naplo/tanev/orarendEllenorzes.php
www/policy/private/naplo/tanev/orarendModosito-pre.php
www/policy/private/naplo/tanev/orarendModosito.php
www/policy/private/naplo/tanev/orarendTankor-pre.php
www/policy/private/naplo/tanev/orarendTankor.php
www/policy/private/naplo/tanev/orarendTeremModositas-pre.php
www/policy/private/naplo/tanev/orarendTeremModositas.php
www/policy/private/naplo/tanev/orarendUtkozes-pre.php
www/policy/private/naplo/tanev/orarendUtkozes.php
www/policy/private/naplo/tanev/szabadTerem-pre.php
www/policy/private/naplo/tanev/szabadTerem.php
www/policy/private/naplo/tanev/tanarOrarend-pre.php
www/policy/private/naplo/tanev/tanarOrarend.php
www/skin/ajax/module-naplo/html/tanev/orarend.phtml
www/skin/ajax/module-naplo/css/tanev/orarend.css
www/skin/classic/module-naplo/css/tanev/ascExport.css
www/skin/classic/module-naplo/css/tanev/helyettesites.css
www/skin/classic/module-naplo/css/tanev/orarend.css
www/skin/classic/module-naplo/css/tanev/orarendBetolto.css
www/skin/classic/module-naplo/css/tanev/orarendEllenorzes.css
www/skin/classic/module-naplo/css/tanev/orarendModosito.css
www/skin/classic/module-naplo/css/tanev/orarendTankor.css
www/skin/classic/module-naplo/css/tanev/orarendTeremModositas.css
www/skin/classic/module-naplo/css/tanev/szabadTerem.css
www/skin/classic/module-naplo/css/tanev/tanarOrarend.css
www/skin/classic/module-naplo/html/tanev/ascExport.phtml
www/skin/classic/module-naplo/html/tanev/helyettesites.phtml
www/skin/classic/module-naplo/html/tanev/orarend.phtml
www/skin/classic/module-naplo/html/tanev/orarendBetolto.phtml
www/skin/classic/module-naplo/html/tanev/orarendEllenorzes.phtml
www/skin/classic/module-naplo/html/tanev/orarendLoad.phtml
www/skin/classic/module-naplo/html/tanev/orarendModosito.phtml
www/skin/classic/module-naplo/html/tanev/orarendTankor.phtml
www/skin/classic/module-naplo/html/tanev/orarendTeremModositas.phtml
www/skin/classic/module-naplo/html/tanev/szabadTerem.phtml
www/skin/classic/module-naplo/html/tanev/tanarOrarend.phtml"

echo -e "\n          Elavult állományok törlése:\n"
for FILE in $FILES; do
    FILE=`echo $FILE | sed -e "s/%/ /g"`
    echo -n "          $BASEDIR/$FILE ... "
    if [ -e "$BASEDIR/$FILE" ]; then
        rm -f "$BASEDIR/$FILE"
        echo "törölve."
    else
        echo "nincs."
    fi
done
