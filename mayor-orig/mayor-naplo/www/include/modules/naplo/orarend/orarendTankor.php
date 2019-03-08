<?php

    function orarendiOraTankorAssoc() {

        $lr = db_connect('naplo');

        foreach( array_unique($_POST['ORARENDKULCSOK']) as $_k => $_v) {
            list($_tanarId,$_osztalyJel,$_targyJel) = explode('%',$_v);
            $q = "DELETE FROM orarendiOraTankor WHERE tanarId=%u AND osztalyJel='%s' AND targyJel='%s'";
	    $v = array($_tanarId, $_osztalyJel, $_targyJel);
            db_query($q, array('fv' => 'orarendiOraTankorAssoc', 'modul' => 'naplo', 'values' => $v), $lr);
        }

        for ($i = 0; $i < count($_POST['ORARENDKEY']); $i++) {
            if ($_POST['ORARENDKEY'][$i]!='') {
                list($_tanarId,$_osztalyJel,$_targyJel,$_tankorId) = explode('%',$_POST['ORARENDKEY'][$i]);
                if (!is_null($_tanarId)) {
                    // bugfix 152->153. Muszáj kitörölni, mert előtte már lehet hogy egy másik tankörhöz rögzítettük ugyanazt a kulcsot... ????
                    // ez így persze egy sor felesleges query.
                    $q = "DELETE FROM orarendiOraTankor WHERE tanarId=%u AND osztalyJel='%s' AND targyJel='%s'";
		    $v = array($_tanarId, $_osztalyJel, $_targyJel);
                    $db = db_query($q, array('fv' => 'orarendTankor', 'modul' => 'naplo', 'result' => 'affected rows', 'values' => $v), $lr);
                    $q = "REPLACE INTO orarendiOraTankor (tanarId,osztalyJel,targyJel,tankorId) VALUES (%u, '%s', '%s', %u)";
		    $v = array($_tanarId, $_osztalyJel, $_targyJel, $_tankorId);
                    db_query($q, array('fv' => 'orarendTankor', 'modul' => 'naplo', 'values' => $v), $lr);
                }
            }
        }
        db_close($lr);

    }

?>
