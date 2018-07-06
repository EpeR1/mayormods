<?php

    if (_RIGHTS_OK !== true) die();

    global $ADAT;

    function exportKerdoiv($ADAT) {

	formBegin();

        echo '<textarea name="export" style="width: 80%; height: 200px;">';                                                                                                 
        foreach ($ADAT['stat']['kerdes'] as $kerdesId => $kAdat) {                                                                                                          
            echo $kAdat['kerdes']."\n";                                                                                                                                     
            foreach ($kAdat['valasz'] as $valaszId => $valasz) {                                                                                                            
                echo $valasz."\n";                                                                                                                                          
            }                                                                                                                                                               
            echo "\n";                                                                                                                                                      
        }                                                                                                                                                                   

	echo '</textarea>'."\n";
	formEnd();

    }

    exportKerdoiv($ADAT);

?>
