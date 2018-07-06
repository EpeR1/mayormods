<?php

    function checkStatus() {
        if ($olr == '') $lr = db_connect('naplo');                                                                                                                               
        else $lr = $olr;                                                                                                                                                         

        // A munkaterv meglétének ellenőrzése                                                                                                                                    
        $q = "SELECT COUNT(*) AS db FROM `nap`";                                                                                                                                         
        $R['napokSzama'] = db_query($q, array('fv' => 'checkStatus/1', 'modul' => 'naplo', 'result' => 'value'), $lr);
                                                                                                                                                                                 
        $q = "SELECT COUNT(*) AS db FROM orarendiOra WHERE tolDt <= curdate() AND igDt >= curdate()";                                                                                  
        $R['orakSzama'] = db_query($q, array('fv' => 'checkStatus/2', 'modul' => 'naplo', 'result' => 'value'), $lr);                                                            
                                                                                                                                                                                 
        $q = "SELECT DISTINCT orarendiOra.tanarId, orarendiOra.targyJel, orarendiOra.osztalyJel
                FROM orarendiOra LEFT JOIN orarendiOraTankor USING(tanarId, targyJel, osztalyJel)                                                                                
                WHERE tankorId IS NULL";                                                                                                                                         

        $R['hianyzoTankor'] = db_query($q, array('fv' => 'checkStatus/3', 'modul' => 'naplo', 'result' => 'indexed'),$lr);

        $q = "SELECT DISTINCT orarendiOra.tanarId, orarendiOra.targyJel, orarendiOra.osztalyJel
                FROM orarendiOraTankor LEFT JOIN orarendiOra USING(tanarId, targyJel, osztalyJel)                                                                                
                WHERE tanarId IS NULL";                                                                                                                                         

        $R['hianyzoOra'] = db_query($q, array('fv' => 'checkStatus/3', 'modul' => 'naplo', 'result' => 'indexed'),$lr);

	$R['vizsgaltDt'] = date('Y-m-d');
                                                                                                                                                                                 
        if ($olr == '') db_close($lr);                                                                                                                                           
        
	return $R;
                                                                                                                                                                      
    }
?>
