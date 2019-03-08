<?php

    function getVizsgak($ADAT) {

	if (isset($ADAT['diakId'])) { $W[] = 'diakId=%u'; $v[] = $ADAT['diakId']; }
	if (isset($ADAT['targyId'])) { $W[] = 'targyId=%u'; $v[] = $ADAT['targyId']; }
	
	if (isset($ADAT['evfolyamJel'])) { $W[] = "(vizsga.evfolyamJel='%s' OR vizsga.evfolyam=%u)"; $v[] = $ADAT['evfolyamJel']; $v[] = $ADAT['evfolyam']; }
	else if (isset($ADAT['evfolyam'])) { $W[] = 'vizsga.evfolyam=%u'; $v[] = $ADAT['evfolyam']; }

	if (isset($ADAT['jelentkezesDt'])) { $W[] = "jelentkezesDt='%s'"; $v[] = $ADAT['jelentkezesDt']; }
	if (isset($ADAT['vizsgaDt'])) { $W[] = "vizsgaDt='%s'"; $v[] = $ADAT['vizsgaDt']; }

	if (!is_array($W) || $W == '') return false;

	$q = "SELECT *, vizsga.evfolyam AS evfolyam, vizsga.evfolyamJel AS evfolyamJel FROM vizsga 
		LEFT JOIN zaradek USING (zaradekId,diakId) 
		LEFT JOIN zaroJegy USING (zaroJegyId,diakId,targyId,felev)
		WHERE ".implode(' AND ', $W)." ORDER BY jelentkezesDt DESC,targyId,diakId";
	$ret = db_query($q, array(
            'modul' => 'naplo_intezmeny', 'fv' => 'getVizsgak', 'result' => 'indexed', 'values' => $v)
        );

	return $ret;

    }

    function getVizsgaAdatById($vizsgaId) {

	$q = "SELECT *, vizsga.evfolyam AS evfolyam, vizsga.evfolyamJel AS evfolyamJel FROM vizsga 
		LEFT JOIN zaradek USING (zaradekId,diakId) 
		LEFT JOIN zaroJegy USING (zaroJegyId,diakId,targyId,felev) 
		WHERE vizsgaId=%u";
	$v = array($vizsgaId);
	return db_query($q, array(
            'modul' => 'naplo_intezmeny', 'fv' => 'getVizsgaAdatById', 'result' => 'record', 'values' => $v)
        );

    }

?>
