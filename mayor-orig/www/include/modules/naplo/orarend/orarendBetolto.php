<?php

    function orarendBetoltes($ADAT, $OrarendiOra, $OrarendiOraTankor) {


        $ok = true;
        $lr = db_connect('naplo', array('fv' => 'orarendBetoltes'));
            db_start_trans($lr);
	    if ($ADAT['lezaras']) {
                // tol-ig között töröljük az adott hét órarendi bejegyzéseit
                $q = "DELETE FROM `%s`.orarendiOra WHERE het=%u AND '%s'<=tolDt AND igDt<='%s'";
		$v = array($ADAT['tanevDb'], $ADAT['orarendiHet'], $ADAT['tolDt'], $ADAT['igDt']);
                $ok = $ok && db_query($q, array('fv' => 'orarendBetoltes/közti törlés', 'modul' => 'naplo', 'values' => $v), $lr);
                // A keresztülnyúlókat ketté kell vágni
                $q = "INSERT INTO `%s`.orarendiOra
                        SELECT het,nap,ora,tanarId,osztalyJel,targyJel,teremId,'%s' + INTERVAL 1 DAY AS tolDt,igDt
                        FROM `%s`.orarendiOra WHERE het=%u AND tolDt<'%s' AND igDt>'%s'";
		$v = array($ADAT['tanevDb'], $ADAT['igDt'], $ADAT['tanevDb'], $ADAT['orarendiHet'], $ADAT['tolDt'], $ADAT['igDt']);
                $ok = $ok && db_query($q, array('fv' => 'orarendBetoltes/keresztülnyúló kettévágása', 'modul' => 'naplo', 'values' => $v), $lr);
                // a "balról" benyúlóakat tolDt-ig levágjuk
                $q = "UPDATE `%s`.orarendiOra SET igDt='%s' - INTERVAL 1 DAY WHERE tolDt<'%s' AND '%s'<=igDt";
		$v = array($ADAT['tanevDb'], $ADAT['tolDt'], $ADAT['tolDt'], $ADAT['tolDt']);
                $ok = $ok && db_query($q, array('fv' => 'orarendBetoltes/balról levág', 'modul' => 'naplo', 'values' => $v), $lr);
                // a "jobbról" benyúlóakat igDt-től levágjuk
                $q = "UPDATE `%s`.orarendiOra SET tolDt='%s' + INTERVAL 1 DAY WHERE '%s'<tolDt AND '%s'<igDt";
		$v = array($ADAT['tanevDb'], $ADAT['igDt'], $ADAT['tolDt'], $ADAT['igDt']);
                $ok = $ok && db_query($q, array('fv' => 'orarendBetoltes/jobbról levág', 'modul' => 'naplo', 'values' => $v), $lr);
	    }

            // felvesszük az új bejegyzéseket
            $v = $V = array();
            for ($i = 0; $i < count($OrarendiOra); $i++) {
                if ($OrarendiOra[$i][3] != 'NULL') {
		    // Nem hibás, de jobb lenne a %s-t csak sztring-ekre hasznáni, a NULL-ra nem...
		    if ($OrarendiOra[$i][6] == '') $OrarendiOra[$i][6] = 'NULL';
		    if ($OrarendiOra[$i][6] == 'NULL')
			$q =  "INSERT INTO `%s`.orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt)
                		    VALUES (%u, %u, %u, %u, '%s', '%s', %s, '%s', '%s')";
		    else 
			$q = "INSERT INTO `%s`.orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt)
                		    VALUES (%u, %u, %u, %u, '%s', '%s', %u, '%s', '%s')";
		    $v = mayor_array_join(array($ADAT['tanevDb']), $OrarendiOra[$i]);
        	    $ok = $ok && db_query($q, array('fv' => 'orarendBetoltes/orarendiOra beszúrás #'.$i, 'modul' => 'naplo', 'values' => $v), $lr);
		}
	    }
/*
            $v = $V = array();
            for ($i = 0; $i < count($OrarendiOra); $i++)
                if ($OrarendiOra[$i][3] != 'NULL') {
		    // Nem hibás, de jobb lenne a %s-t csak sztring-ekre hasznáni, a NULL-ra nem...
		    if ($OrarendiOra[$i][6] == 'NULL') $V[] = "(%u, %u, %u, %u, '%s', '%s', %s, '%s', '%s')";
		    else $V[] = "(%u, %u, %u, %u, '%s', '%s', %u, '%s', '%s')";
		    $v = array_merge($v, $OrarendiOra[$i]);
		}
            $q = "INSERT INTO `%s`.orarendiOra (het,nap,ora,tanarId,osztalyJel,targyJel,teremId,tolDt,igDt)
                    VALUES ".implode(',', $V);
	    array_unshift($v, $ADAT['tanevDb']);
            $ok = $ok && db_query($q, array('fv' => 'orarendBetoltes/orarendiOra beszúrás', 'modul' => 'naplo', 'values' => $v), $lr);
*/
            // orarendiOraTankor tábla feltöltése
            if (is_array($OrarendiOraTankor) && count($OrarendiOraTankor) > 0) {
                $v = $V = array();
                for ($i = 0; $i < count($OrarendiOraTankor); $i++) {
                    $V[] = "(%u, '%s', '%s', %u)";
		    $v = mayor_array_join($v, $OrarendiOraTankor[$i]);
		}
		array_unshift($v, $ADAT['tanevDb']);
                $q = "INSERT INTO `%s`.orarendiOraTankor (tanarId, osztalyJel, targyJel, tankorId) VALUES ".implode(',', $V);
                $ok = $ok && db_query($q, array('fv' => 'orarendBetoltes/orarendiOraTankor beszúrás', 'modul' => 'naplo', 'values' => $v), $lr);
            }

            if ($ok) {
                db_commit($lr);
                $_SESSION['alert'][] = 'info:success';
            } else {
                db_rollback($lr);
            }
        db_close($lr);
	return $ok;
    }

?>
