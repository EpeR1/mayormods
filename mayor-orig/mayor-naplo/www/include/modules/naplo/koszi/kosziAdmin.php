<?php

    function kosziJovahagyhatoByAdmin($kosziId,$diakId) {
	return true;
    }


    function kosziJovahagyhatoByDiakId($kosziId,$diakId) {

	$q = "SELECT count(*) AS c FROM kosziIgazoloDiak WHERE kosziId=%u AND diakId=%u";
        $v = array($kosziId,$diakId);
        $r = db_query($q, array('modul'=>'naplo','fv'=>'koszi_jovahagyhato','values'=>$v, 'result'=>'value'));
	return ($r==1);

    }

    function kosziJovahagyhatoByTanarId($kosziId,$tanarId) {


	$q = "SELECT count(*) AS c FROM kosziIgazoloTanar WHERE kosziId=%u AND tanarId=%u";
        $v = array($kosziId,$tanarId);
        $r1 = db_query($q, array('modul'=>'naplo','fv'=>'koszi_jovahagyhato','values'=>$v, 'result'=>'value'));

	$q = "SELECT count(*) AS c FROM kosziIgazoloOf WHERE kosziId=%u AND tanarId=%u";
        $v = array($kosziId,$tanarId);
        $r2 = db_query($q, array('modul'=>'naplo','fv'=>'koszi_jovahagyhato','values'=>$v, 'result'=>'value'));

	/* Ez sajnos jóval bonyolultabb lett v2.0: ilyen: szinkronizáld a share/koszi.php - ben levő függvénnyel, ami HASONLÓ */

           $q = "SELECT count(*) AS c FROM kosziDiak
                LEFT JOIN kosziIgazoloTanar USING (kosziId)
                LEFT JOIN kosziIgazoloOf USING (kosziId)
                LEFT JOIN koszi USING (kosziId) 
                LEFT JOIN ".__INTEZMENYDBNEV.".kosziEsemeny USING (kosziEsemenyId)
                WHERE kosziDiak.kosziId=%u AND jovahagyasDt='0000-00-00 00:00:00' AND 
                (
                  (
                    (kosziIgazoloTanar.tanarId=%u AND kosziIgazoloOf.tanarId IS NULL) Or
                    (kosziIgazoloTanar.tanarId IS NULL AND kosziIgazoloOf.tanarId=%u)
                  ) OR (
                    koszi.igazolo LIKE '%%osztályfőnök%%' AND
                    diakId IN (
                        SELECT diakId FROM ".__INTEZMENYDBNEV.".osztalyTanar
                        LEFT JOIN ".__INTEZMENYDBNEV.".osztalyDiak USING (osztalyId)
                        WHERE tanarId=%u AND osztalyTanar.beDt<=NOW() AND (osztalyTanar.kiDt IS NULL OR osztalyTanar.kiDt>=NOW())
                    )
                  ) OR (
                    koszi.igazolo LIKE  '%%tanár%%' AND
                    diakId IN (
                        SELECT diakId FROM ".__INTEZMENYDBNEV.".tankorTanar
                        LEFT JOIN ".__INTEZMENYDBNEV.".tankorDiak USING (tankorId)
                        LEFT JOIN ".__INTEZMENYDBNEV.".tankor USING (tankorId)
                        WHERE tanarId=%u AND ".__INTEZMENYDBNEV.".tankor.targyId = koszi.targyId
                    )
                  )
                )    
                ";                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         
	$v = array($kosziId, $tanarId, $tanarId,$tanarId,$tanarId,$tanarId);
        $r3 = db_query($q, array('debug'=>false,'modul'=>'naplo','result'=>'value', 'values'=>$v));
	return ($r1==1 || $r2==1 || $r3==1);

    }


    function kosziElutasit($kosziId,$diakId) {

	// delete helyett update elutasítva flag állítása?

	$q = "DELETE FROM kosziDiak WHERE kosziId=%u AND diakId=%u";
        $v = array($kosziId,$diakId);
	db_query($q, array('modul'=>'naplo','fv'=>'koszi_torol','values'=>$v, 'result'=>'delete'));

    }

    function kosziJovahagy($kosziId,$diakId) {
	$q = "UPDATE kosziDiak SET jovahagyasDt=NOW() WHERE kosziId=%u AND diakId=%u";
        $v = array($kosziId,$diakId);
	db_query($q, array('modul'=>'naplo','fv'=>'koszi_jovahagy','values'=>$v, 'result'=>'update'));
    }


?>
