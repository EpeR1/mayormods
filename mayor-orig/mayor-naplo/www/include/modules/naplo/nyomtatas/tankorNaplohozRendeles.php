<?php

    function tankorNaploInit($torlessel = false) {

	// Az eddigi ejegyzések törlése - induljunk tiszta lappal!
	if ($torlessel === true) {
	    $q = "DELETE FROM tankorNaplo";
	    db_query($q, array('fv' => 'tankorNaploInit/delete', 'modul' => 'naplo'));
	}
	// Kérdezzük le, hogy melyik osztályhoz nincs még bejegyzés
	$q = "SELECT osztalyId FROM osztalyNaplo LEFT JOIN tankorNaplo USING (osztalyId) GROUP BY osztalyId HAVING COUNT(tankorId) = 0";
	$osztalyIds = db_query($q, array('fv' => 'tankorNaploInit', 'modul' => 'naplo', 'result' => 'idonly'));
	// Ezen osztályok hozzárendelése a csak hozzájuk tartozó tankörökhöz
	if (is_array($osztalyIds) && count($osztalyIds) > 0) {
    	    $q = "REPLACE INTO tankorNaplo (tankorId,osztalyId)
                SELECT tankorId,osztalyId FROM ".__INTEZMENYDBNEV.".tankorOsztaly
                WHERE osztalyId IN (".implode(',', $osztalyIds).")
		AND tankorId IN (
                    SELECT DISTINCT tankorId FROM ".__INTEZMENYDBNEV.".tankorSzemeszter WHERE tanev=".__TANEV."
                ) GROUP BY tankorId HAVING COUNT(*)=1";
    	    return db_query($q, array('fv' => 'tankorNaploInit', 'modul' => 'naplo'));
	} else { return true; }

    }

    function tankorNaplohozRendeles($osztalyId, $T) {
        $v = $V = array();
        for ($i = 0; $i < count($T); $i++) {
            list($tankorId, $naplo) = explode('/',$T[$i]);
	    array_push($v, $tankorId, $naplo);
            $V[] = "(%u, %u)";
        }
        if (count($V) > 0) {
            $q = "DELETE FROM tankorNaplo WHERE osztalyId=%u";
            db_query($q, array('fv' => 'tankorNaplohozRendeles', 'modul' => 'naplo', 'values' => array($osztalyId)));
            $q = "REPLACE INTO tankorNaplo (tankorId,osztalyId) VALUES ".implode(',', $V);
            db_query($q, array('fv' => 'tankorNaplohozRendeles', 'modul' => 'naplo', 'values' => $v));
        }
    }

    function getTankorokNaploja() {

	$q = "SELECT tankorId,osztalyId FROM tankorNaplo";
        return db_query($q, array('fv' => 'tankorokNaploja', 'modul' => 'naplo', 'result' => 'keyvaluepair'));

    }

?>
