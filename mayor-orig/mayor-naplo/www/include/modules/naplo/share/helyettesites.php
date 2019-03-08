<?php

    function getHelyettesitendoOrak($SET = array('tolDt'=>'','igDt'=>'','tankorIdk'=>'','telephelyId'=>'')) {

    	//találjuk ki valahogy a telephelyet... (osztály, terem, tanár alapján???)
	if ($SET['telephelyId']!='') {
	    $telephelyId=$SET['telephelyId'];
	    $WT = " AND (`terem`.`telephelyId` = $telephelyId OR terem.teremId IS NULL) ";
	}

	$igDt = $SET['igDt']; $tolDt=$SET['tolDt']; $osztalyId = $SET['osztalyId'];
	$W = '';
	if (is_array($SET['tankorIdk']) && count($SET['tankorIdk']) > 0) {
	    $W = ' AND tankorId IN ('.implode(',', array_fill(0, count($SET['tankorIdk']), '%u')).')';
	    $v = mayor_array_join(array($tolDt, $igDt), $SET['tankorIdk']);
	} else { $v = array($tolDt, $igDt); }
        $q = "SELECT ora.oraId,ora.dt,ora.ora,ora.ki,ora.kit,ora.tankorId,ora.teremId,ora.leiras,ora.tipus,ora.eredet, ora.feladatTipusId
              FROM ora
		LEFT JOIN ".__INTEZMENYDBNEV.".terem USING (teremId)
              WHERE dt>='%s' AND dt<='%s'
                    AND (eredet!='órarend' OR kit!='')
		    $W
		    $WT
              ORDER BY dt,ora";
//                    AND (tipus NOT LIKE '%máskor') 
	$RESULT['indexed'] = db_query($q, array('fv' => 'getHelyettesitendoOrak', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
	for ($i = 0; $i < count($RESULT['indexed']); $i++) {
	    $TANAROK[] = $RESULT['indexed'][$i]['ki'];
	    $TANAROK[] = $RESULT['indexed'][$i]['kit'];
	    $TANKOROK[] = $RESULT['indexed'][$i]['tankorId'];
	    /* ezt egyelőre nem használjuk semmire! Sima plusz óra */
	    //if ($RESULT['indexed'][$i]['eredet'] == 'plusz') 
	    //	$RESULT['plusz'][] = $RESULT['indexed'][$i]['oraId'];
	}
	if (is_array($TANKOROK))
	    $RESULT['tanarok'] = array_unique($TANAROK);
	$RESULT['tankorok'] = $TANKOROK;
	return $RESULT;
    }


?>
