<?php

    function ujSzempontRendszer($ADAT) {

	$kepzesId = readVariable($ADAT['kepzesId'], 'numeric unsigned', 'NULL');
	$targyId = readVariable($ADAT['targyId'], 'numeric unsigned', 'NULL');
	// Az új szempontRendszer felvétele
	$q = "INSERT INTO szempontRendszer (tanev,szemeszter,evfolyamJel,kepzesId,targyId,targyTipus) VALUES (%u, %u, '%s', ";
	$v = array($ADAT['szemeszter']['tanev'], $ADAT['szemeszter']['szemeszter'], $ADAT['evfolyamJel']);
	if ($kepzesId == 'NULL') $q .= "NULL, ";
	else { $q .= "%u, "; $v[] = $kepzesId; }
	if ($targyId == 'NULL') $q .= "NULL, ";
	else { $q .= "%u, "; $v[] = $targyId; }
	if ($ADAT['targyTipus'] == '') $q .= "NULL)";
	else { $q .= "'%s')"; $v[] = $ADAT['targyTipus']; }

	$szrId = db_query($q, array('fv' => 'ujSzempontRendszer', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v));
	if (!$szrId) return false;

	// A szempontok és minősítések rögzítése
        $Szempont = $aktSz = array();
        for ($i = 0; $i < count($ADAT['txt']); $i++) {
	    $txt = trim($ADAT['txt'][$i]);
            if ($txt != '') {
                if (!isset($aktSz['szempont'])) {
                    $aktSz['szempont'] = $txt;
                    $q = "INSERT INTO szrSzempont (szrId, szempont) VALUES (%u, '%s')";
		    $v = array($szrId, $txt);
                    $szempontId = db_query($q, array('fv' => 'ujSzempontRendszer/szempont', 'modul' => 'naplo_intezmeny', 'result' => 'insert', 'values' => $v));
                } else {
                    $aktSz['minosites'][] = $txt;
                    $q = "INSERT INTO szrMinosites (szempontId, minosites) VALUES (%u,'%s')";
		    $v = array($szempontId, $txt);
                    db_query($q, array('fv' => 'ujSzempontRendszer/minősítés', 'modul' => 'naplo_intezmeny', 'values' => $v));
                }
            } else {
                if (isset($aktSz['szempont'])) {
                    $Szempont[] = $aktSz;
                    $aktSz = array();
                }
            }
        }

	return true;
	
    }

    function szempontRendszerTorles($ADAT) {

	// cascade-olás miatt törli a hozzá tartozó értékeléseket is!
	$q = "DELETE FROM szempontRendszer WHERE szrId=%u";
	return db_query($q, array('fv' => 'szempontRendszerTorles', 'modul' => 'naplo_intezmeny', 'values' => array($ADAT['szrId'])));

    }

?>
