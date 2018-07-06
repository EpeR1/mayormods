<?php

    /* INTEZMENY */

    function getKosziEsemenyek() {

	$q = "SELECT * FROM kosziEsemeny ORDER BY kosziEsemenyNev";
	$r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed'));

	return $r;

    }


    function getKosziPont($kosziEsemenyId) {

	if (!is_numeric($kosziEsemenyId)) return false;

	$q = "SELECT * FROM kosziPont WHERE kosziEsemenyId = %u";
	$v = array($kosziEsemenyId);
	$r = db_query($q, array('modul'=>'naplo_intezmeny','result'=>'indexed', 'values'=>$v));

	return $r;

    }


    /* TANEV */

    function getKoszi($kosziEsemenyId) {

	if (!is_numeric($kosziEsemenyId)) return false;

	$q = "SELECT * FROM koszi WHERE kosziEsemenyId = %u ORDER BY dt,tanev,felev";
	$v = array($kosziEsemenyId);
	$r = db_query($q, array('modul'=>'naplo','result'=>'indexed', 'values'=>$v));

	return $r;
	
    }

    function getKosziLista() {

	$q = "SELECT * FROM koszi LEFT JOIN ".__INTEZMENYDBNEV.".kosziEsemeny USING (kosziEsemenyId) 
		WHERE tolDt IS NULL OR (tolDt<=NOW() AND (igDt is NULL OR igDt>=NOW()))
		ORDER BY dt,tanev,felev";
	$r = db_query($q, array('modul'=>'naplo','result'=>'indexed'));

	return $r;
	
    }


    function getKosziEsemenyIdByKosziId($kosziId) {

	$q = "SELECT kosziEsemenyId FROM koszi WHERE kosziId = %u";
	$v = array($kosziId);
	$r = db_query($q, array('modul'=>'naplo','result'=>'value', 'values'=>$v));

	return $r;
    }


    function getKosziDiakIgazolandoLista($tipus='',$SET = array('tanarId'=>null, 'diakId'=>null)) {

	if (__KOSZIADMIN ===true) {
	    $q = "SELECT *, IF (jovahagyasDt!='0000-00-00',1,0) AS jovahagyva FROM kosziDiak
		LEFT JOIN kosziIgazoloTanar USING (kosziId)
		LEFT JOIN kosziIgazoloOf USING (kosziId)
		LEFT JOIN koszi USING (kosziId) 
		LEFT JOIN ".__INTEZMENYDBNEV.".kosziEsemeny USING (kosziEsemenyId)
		";
//	    $v = array($SET['diakId']);
	    $ret = db_query($q, array('modul'=>'naplo','result'=>'indexed'));
	} elseif (is_numeric($SET['diakId'])) { // ide jön még a dök csoport!
	    $q = "SELECT *, IF (jovahagyasDt!='0000-00-00',1,0) AS jovahagyva FROM kosziDiak
		LEFT JOIN kosziIgazoloDiak USING (kosziId)
		LEFT JOIN koszi USING (kosziId) 
		LEFT JOIN ".__INTEZMENYDBNEV.".kosziEsemeny USING (kosziEsemenyId)
		WHERE jovahagyasDt='0000-00-00 00:00:00' AND kosziIgazoloDiak.diakId=%u
		ORDER BY rogzitesDt";
	    $v = array($SET['diakId']);
	    $ret = db_query($q, array('modul'=>'naplo','result'=>'indexed', 'values'=>$v));
	} elseif (is_numeric($SET['tanarId'])) {
	    $q = "SELECT *, IF (jovahagyasDt!='0000-00-00',1,0) AS jovahagyva FROM kosziDiak
		LEFT JOIN kosziIgazoloTanar USING (kosziId)
		LEFT JOIN kosziIgazoloOf USING (kosziId)
		LEFT JOIN koszi USING (kosziId) 
		LEFT JOIN ".__INTEZMENYDBNEV.".kosziEsemeny USING (kosziEsemenyId)
		WHERE jovahagyasDt='0000-00-00 00:00:00' AND 
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
		ORDER BY rogzitesDt";

	    $v = array($SET['tanarId'],$SET['tanarId'],$SET['tanarId'],$SET['tanarId']);
	    $ret = db_query($q, array('modul'=>'naplo','result'=>'indexed', 'values'=>$v));

	} else {
	    $ret = false;
	}
	return $ret;
    }


    /* DIAK */

    function getKosziDiakLista($diakId) {

	if ($diakId=='') return false;

	$q = "SELECT *, IF (jovahagyasDt!='0000-00-00',1,0) AS jovahagyva FROM kosziDiak LEFT JOIN koszi USING (kosziId)
		LEFT JOIN ".__INTEZMENYDBNEV.".kosziEsemeny USING (kosziEsemenyId)
		WHERE diakId = %u ORDER BY rogzitesDt,jovahagyasDt";
	$v = array($diakId);
	$r = db_query($q, array('modul'=>'naplo','result'=>'indexed', 'values'=>$v));

	return $r;
    }



?>
