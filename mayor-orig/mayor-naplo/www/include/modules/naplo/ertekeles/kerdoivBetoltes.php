<?php

    function ujKerdoiv($ADAT) {

        $q = "INSERT INTO kerdoiv (cim,tolDt,igDt,megjegyzes) VALUE ('%s', '%s', '%s', '%s')";
        $v = array($ADAT['cim'], $ADAT['tolDt'], $ADAT['igDt'], $ADAT['megjegyzes']);
        return db_query($q, array('fv' => 'ujKerdoiv', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v), $lr);

    }

    function kerdesValaszFelvetel($ADAT) {
        $kerdoivId = $ADAT['kerdoivId'];
        $kerdes = '';
        for ($i = 0; $i < count($ADAT['txt']); $i++) {
            if (trim($ADAT['txt'][$i]) != '') {
                if ($kerdes == '') {
                    $kerdes = chop(readVariable($ADAT['txt'][$i], 'string'));
                    $q = "INSERT INTO kerdoivKerdes (kerdoivId, kerdes) VALUES (%u, '%s')";
                    $v = array($kerdoivId, $kerdes);
                    $kerdesId = db_query($q, array('fv' => 'kerdesValaszFelvetel/kerdes', 'modul' => 'naplo', 'result' => 'insert', 'values' => $v), $lr);
                } else {
                    $mezok = explode('|', chop(readVariable($ADAT['txt'][$i],'string')));
		    if (count($mezok) == 1) {
			$valasz = $mezok[0];
			$pont = 0;
		    } else {
			$valasz = $mezok[1];
			$pont = $mezok[0];
		    }
                    $q = "INSERT INTO kerdoivValasz (kerdesId, valasz, pont) VALUES (%u, '%s', %d)";
                    $v = array($kerdesId, $valasz, $pont);
                    db_query($q, array('fv' => 'kerdesValaszFeltoltes/valasz', 'modul' => 'naplo', 'values' => $v), $lr);
                }
            } else {
                $kerdes = '';
            }
        }
    }

    function kerdoivCimzettFelvetel($kerdoivId, $cimzettId, $cimzettTipus) {
        // kerdoivCimzett
        $q = "INSERT INTO kerdoivCimzett (kerdoivId,cimzettId,cimzettTipus) VALUES (%u, %u, '%s')";
        $v = array($kerdoivId, $cimzettId, $cimzettTipus);
        db_query($q, array('fv' => 'kerdoivCimzett - cimzett', 'modul' => 'naplo', 'values' => $v));
        // kerdoivValaszSzam
        $q = "INSERT INTO kerdoivValaszSzam (valaszId,cimzettId,cimzettTipus,szavazat)
            SELECT valaszId, %u, '%s', 0 FROM kerdoivValasz LEFT JOIN kerdoivKerdes USING (kerdesId) WHERE kerdoivId=%u";
        $v = array($cimzettId, $cimzettTipus, $kerdoivId);
        return db_query($q, array('fv' => 'kerdoivCimzett - valaszSzam', 'modul' => 'naplo', 'values' => $v));

    }

    function kerdoivCimzettTorles($kerdoivId, $cimzettId, $cimzettTipus) {
	$return = true;
	$lr = db_connect('naplo');
	db_start_trans($lr);
        // kerdoivCimzett
        $q = "DELETE FROM kerdoivCimzett WHERE kerdoivId=%u AND cimzettId=%u AND cimzettTipus='%s'";
        $v = array($kerdoivId, $cimzettId, $cimzettTipus);
        $return = $return && db_query($q, array('fv' => 'kerdoivCimzettTorles - cimzett', 'modul' => 'naplo', 'values' => $v), $lr);
        // kerdoivMegvalaszoltKerdes
        $q = "DELETE FROM kerdoivMegvalaszoltKerdes WHERE cimzettId=%u AND cimzettTipus='%s' AND kerdesId IN
		(SELECT kerdesId FROM kerdoivKerdes WHERE kerdoivId=%u)";
        $v = array($cimzettId, $cimzettTipus, $kerdoivId);
        $return = $return && db_query($q, array('fv' => 'kerdoivCimzettTorles - cimzett', 'modul' => 'naplo', 'values' => $v), $lr);
        // kerdoivValaszSzam
        $q = "DELETE FROM kerdoivValaszSzam WHERE cimzettId=%u AND cimzettTipus='%s' AND valaszId IN
        	(SELECT valaszId FROM kerdoivValasz LEFT JOIN kerdoivKerdes USING (kerdesId) WHERE kerdoivId=%u)";
        $v = array($cimzettId, $cimzettTipus, $kerdoivId);
        $return = $return && db_query($q, array('fv' => 'kerdoivCimzettTorles - valaszSzam', 'modul' => 'naplo', 'values' => $v), $lr);

	if ($return) db_commit($lr);
	else db_rollback($lr);

	db_close($lr);
	return $return;

    }


?>
