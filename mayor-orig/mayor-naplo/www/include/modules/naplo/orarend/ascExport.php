<?php

//
// A termek kezelése még GÁÁÁÁZ - szinte nulla...
//
	// groupsubject ~ tankörCsoport (de náluk osztályonként) - division ~ tankorBlokk

    function ascExport($ADAT) {


	$return = true;
	$tanev = $ADAT['tanev'];
	initTolIgDt($tanev, $ADAT['dt'], $ADAT['dt']);

	$file = _DOWNLOADDIR.'/private/orarend/ascExport.xml';
	$fp = fopen($file, 'w');
	if (!$fp) {
	    $_SESSION['alert'][] = 'message:file_open_failure:'.$file;
	    return false;
	}

	$Tanarok = getTanarok(array('tanev' => $tanev));
	$Osztalyok = getOsztalyok($tanev);
	// Nem tudható, hogy mely termekben lesz óra - ez intézménytől függő - exportáljuk mind...
	$Termek = getTermek(array('tipus'=>
	    array()
//	    array('tanterem','szaktanterem','osztályterem','labor','gépterem','tornaterem','tornaszoba','fejlesztőszoba','tanműhely','előadó','könyvtár','díszterem','egyéb') 
	)); $teremIds = array();
	for ($i = 0; $i < count($Termek); $i++) $teremIds[] = $Termek[$i]['teremId'];
	$Targyak = getTargyak();
	$Diakok = getDiakok(array('tanev' => $tanev)); $diakIds = array();
	for ($i = 0; $i < count($Diakok); $i++) $diakIds[] = $Diakok[$i]['diakId'];
	$diakokOsztalyai = getDiakokOsztalyai($diakIds, array('tanev' => $tanev, 'tolDt' => $ADAT['dt'], 'igDt' => $ADAT['dt']));
	foreach ($ADAT['bontas'] as $bontasId => $bontas) {
	    $bontasTomb = explode('+',$bontas);
	    for ($i = 0; $i < count($bontasTomb); $i++) {
		$Bontas[$bontasId][ $bontasTomb[$i] ]++;
	    }
	}
	if (isset($ADAT['exportalandoHet'])) $Orarend = getOrarendByDt($ADAT['dt'], array($ADAT['exportalandoHet']), $ADAT['tanev']);
	else $Orarend = array();
	// Tárgyak bontása
	if ($ADAT['targyBontas']) {
	    if (defined('__ASC_BONTANDO_TARGYAK')) $bontandoTargyak = explode(',', __ASC_BONTANDO_TARGYAK);
	}

	for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
	    $tankorId = $ADAT['tankorok'][$i]['tankorId'];	    
	    $ADAT['tankorok'][$i]['osztaly'] = getTankorOsztalyaiByTanev($tankorId, $tanev, array('tagokAlapjan' => true, 'result' => 'id'));
	    $ADAT['tankorok'][$i]['tanar'] = getTankorTanaraiByInterval($tankorId, array('tanev' => $tanev, 'result' => 'csakId'));
	    $tmpDiakok = getTankorDiakjaiByInterval($tankorId, $tanev);
	    $ADAT['tankorok'][$i]['diakIds'] = $tmpDiakok['idk'];
	    // Tárgyak bontása
	    if ($ADAT['targyBontas']) {
		$targyId = $ADAT['tankorok'][$i]['tankorAdat'][$tankorId][0]['targyId'];
		if (!is_array($bontandoTargyak) || in_array($targyId, $bontandoTargyak)) {
		    $tankorNev = $ADAT['tankorok'][$i]['tankorAdat'][$tankorId][0]['tankorNev'];
		    $csoportJel = substr($tankorNev, strrpos($tankorNev, ' ')+1);
		    if (!is_array($ADAT['alTargyak'][$targyId])) $ADAT['alTargyak'][$targyId] = array($csoportJel);
		    elseif (!in_array($csoportJel, $ADAT['alTargyak'][$targyId])) $ADAT['alTargyak'][$targyId][] = $csoportJel;
		    $ADAT['tankorok'][$i]['tankorAdat'][$tankorId][0]['targyId'] = $targyId.':'.$csoportJel;
		}
	    }

	}


	// Blokkok tanárai / diákjai / osztályai - és a blokk jelenlegi óráinak meghatározása
	$ADAT['blokkOra'] = array();
	$ADAT['tankorBlokkOra'] = array();
	if (is_array($ADAT['tankorBlokk']['exportOraszam'])) 
	foreach ($ADAT['tankorBlokk']['exportOraszam'] as $bId => $oraszam) if ($oraszam > 0) { // blokkonként

	    $ADAT['blokkTanarok'][$bId] = $ADAT['blokkDiakok'][$bId] = $ADAT['blokkOsztalyok'][$bId] = array();
	    for ($i = 0; $i < count($ADAT['tankorBlokk']['idk'][$bId]); $i++) { // az érintett tankörökön végigmenve
		$tankorId = $ADAT['tankorBlokk']['idk'][$bId][$i];
		$TA = $ADAT['tankorok'][ $ADAT['tankorIndex'][$tankorId] ];
		// tanárok
		for ($j = 0; $j < count($TA['tanar']); $j++) // elvileg nem lehet ütközés, hiszen blokk!!
		    if (!in_array($TA['tanar'][$j], $ADAT['blokkTanarok'][$bId])) {
			$ADAT['blokkTanarok'][$bId][] = $TA['tanar'][$j];
		    } else {
			$_SESSION['alert'][] = 'message:utkozes:blokk='.$bId.';tanarId='.$TA['tanar'][$j];
			$return = false;
		    }
		for ($j = 0; $j < count($TA['diakIds']); $j++) // elvileg nem lehet ütközés, hiszen blokk!!
		    if (!in_array($TA['diakIds'][$j], $ADAT['blokkDiakok'][$bId])) {
			$ADAT['blokkDiakok'][$bId][] = $TA['diakIds'][$j];
		    } else {
			$_SESSION['alert'][] = 'message:utkozes:blokk='.$bId.';diakId='.$TA['diakIds'][$j];
			$return = false;
		    }
		for ($j = 0; $j < count($TA['osztaly']); $j++) // Itt lehet ütközés
		    if (!in_array($TA['osztaly'][$j], $ADAT['blokkOsztalyok'][$bId])) $ADAT['blokkOsztalyok'][$bId][] = $TA['osztaly'][$j];
	    }

	    // A blokk jelenlegi óráinak lekérdezése
	    if (isset($ADAT['exportalandoHet'])) {
		$q = "SELECT het, nap, ora 
			FROM `%s`.orarendiOra LEFT JOIN `%s`.orarendiOraTankor USING (tanarId, osztalyJel, targyJel)
			WHERE tolDt<='%s' AND '%s'<=igDt 
			AND het=%u
			AND tankorId IN (".implode(',', array_fill(0, count($ADAT['tankorBlokk']['idk'][$bId]), '%u')).")
			GROUP BY het, nap, ora HAVING COUNT(*)=".count($ADAT['tankorBlokk']['idk'][$bId]);
		$v = mayor_array_join(array($ADAT['tanevDb'], $ADAT['tanevDb'], $ADAT['dt'], $ADAT['dt'], $ADAT['exportalandoHet']), $ADAT['tankorBlokk']['idk'][$bId]);
		$ret = db_query($q, array('fv' => 'blokkOrai', 'modul' => 'naplo', 'result' => 'indexed', 'values' => $v));
		if ($oraszam > count($ret)) {
		    $_SESSION['alert'][] = 'message:wrong_data:A beállított blokk óraszámnál kevesebb van az órarendben:'.$bId.':'.$oraszam.'/'.count($ret);
//		    $return = false;
		} else {
		    $db = $i = 0; 
		    while ($i < count($ret) && $db < $oraszam) {
			// Ellenőrizzük, hogy az összes tankörnek szabad-e még az adott órája
			$szabad = true;
			for ($j = 0; $j < count($ADAT['tankorBlokk']['idk'][$bId]); $j++) {
			    $tankorId = $ADAT['tankorBlokk']['idk'][$bId][$j];
			    if ($ADAT['tankorBlokkOra'][$tankorId][ $ret[$i]['het'] ][ $ret[$i]['nap'] ][ $ret[$i]['ora'] ]) {
				$szabad = false;
				break;
			    }
			}
			if ($szabad) {
			    // Lekérdezzük az érintett termeket
			    $q = "SELECT DISTINCT teremId FROM `%s`.orarendiOra 
				    LEFT JOIN `%s`.orarendiOraTankor USING (tanarId, osztalyJel, targyJel)
				    WHERE tolDt<='%s' AND '%s'<=igDt AND het=%u
				    AND tankorId IN (".implode(',', array_fill(0, count($ADAT['tankorBlokk']['idk'][$bId]), '%u')).")
				    AND het=%u AND nap=%u AND ora=%u";
			    $v = mayor_array_join(
				array($ADAT['tanevDb'], $ADAT['tanevDb'], $ADAT['dt'], $ADAT['dt'], $ADAT['exportalandoHet']), 
				$ADAT['tankorBlokk']['idk'][$bId],
				array($ret[$i]['het'], $ret[$i]['nap'], $ret[$i]['ora'])
			    );
			    $ret[$i]['teremIds'] = db_query($q, array('fv' => 'blokkTermei', 'modul' => 'naplo', 'result' => 'idonly', 'values' => $v));
			    // Ha szabad, akkor a blokk számára lefoglaljuk
			    $ADAT['blokkOra'][$bId][] = $ret[$i];

			    // Ha szabad, akkor minden tankör számára lefoglaljuk
			    for ($j = 0; $j < count($ADAT['tankorBlokk']['idk'][$bId]); $j++) {
				$tankorId = $ADAT['tankorBlokk']['idk'][$bId][$j];
				$ADAT['tankorBlokkOra'][$tankorId][ $ret[$i]['het'] ][ $ret[$i]['nap'] ][ $ret[$i]['ora'] ] = true;
			    }
			    $db++;
			}
			$i++;
		    }
		    if ($db < $oraszam) {
			$_SESSION['alert'][] = 'message:wrong_data:Az órarendben csak'.$db.' órát sikerült lefoglalni ('.$oraszam.' helyett) - blokkId='.$bId;
//			$return = false;
		    }
		}
	    }
	}

	$xml = '<timetable importtype="database" options="idprefix:MaYoR,daynumbering1">'."\r\n";


	// Tanárok
	$xml .= '   <teachers options="import:primarydb,silent" columns="id,name,short">'."\r\n";
	for ($i = 0; $i < count($Tanarok); $i++) {
	    $xml .= '      <teacher id="'.$Tanarok[$i]['tanarId'].'" name="'.$Tanarok[$i]['tanarNev'].'" short="'.$short.'"/>'."\r\n";
	}
	$xml .= '   </teachers>'."\r\n";
	// Osztályok
	$xml .= '   <classes options="import:primarydb,silent" columns="id,name">'."\r\n";
	for ($i = 0; $i < count($Osztalyok); $i++) {
	    $xml .= '      <class id="'.$Osztalyok[$i]['osztalyId'].'" name="'.$Osztalyok[$i]['osztalyJel'].'" />'."\r\n";
	}
	$xml .= '   </classes>'."\r\n";
	if ($ADAT['szeminariumkent']) {
	    // Diákok
	    $xml .= '   <students options="import:primarydb,silent" columns="id,name,classid">'."\r\n";
	    for ($i = 0; $i < count($Diakok); $i++) {
		$xml .= '      <student id="'.$Diakok[$i]['diakId'].'" name="'
			    .$Diakok[$i]['diakNev'].'" classid="'.$diakokOsztalyai[ $Diakok[$i]['diakId'] ][0].'"/>'."\r\n";
	    }
	    $xml .= '   </students>'."\r\n";
	    $studentidsStr = ',studentids';
	}
	// Tárgyak
	$xml .= '   <subjects options="import:primarydb,silent" columns="id,name,short">'."\r\n";
	if (is_array($ADAT['tankorBlokk']['exportOraszam'])) 
	foreach ($ADAT['tankorBlokk']['exportOraszam'] as $bId => $oraszam) { // blokkok - mint tárgyak
	    if ($oraszam > 0) $xml .= '      <subject id="b'.$bId.'" name="'.$ADAT['tankorBlokk']['blokkNevek'][$bId].'" short="" />'."\r\n";	    
	}
	for ($i = 0; $i < count($Targyak); $i++) { // igazi tárgyak
	    $xml .= '      <subject id="'.$Targyak[$i]['targyId'].'" name="'.$Targyak[$i]['targyNev'].'" short="" />'."\r\n";
	    // Tárgyak bontása
	    if (is_array($ADAT['alTargyak'][ $Targyak[$i]['targyId'] ])) {
		foreach ($ADAT['alTargyak'][ $Targyak[$i]['targyId'] ] as $index => $alTargyId) {
		    $xml .= '      <subject id="'.$Targyak[$i]['targyId'].':'.$alTargyId.'" name="'.$Targyak[$i]['targyNev'].' '.$alTargyId.'" short="" />'."\r\n";
		}
	    }
	}
	$xml .= '   </subjects>'."\r\n";
	// Termek
	$xml .= '   <classrooms options="import:primarydb,silent" columns="id,name,short">'."\r\n";
	for ($i = 0; $i < count($Termek); $i++) {
	    $xml .= '      <classroom id="'.$Termek[$i]['teremId'].'" name="'.$Termek[$i]['leiras'].'" short="'.$Termek[$i]['teremId'].'"/>'."\r\n";
	}
	$xml .= '   </classrooms>'."\r\n";
	// Órák
	$xml .= '   <lessons options="import:primarydb,silent" columns="id,name,subjectid,classids,teacherids,periodsperweek,periodspercard,classroomids,weeks,'.$studentidsStr.'">'."\r\n";
	// blokk --> lesson
	if (is_array($ADAT['tankorBlokk']['exportOraszam'])) foreach ($ADAT['tankorBlokk']['exportOraszam'] as $bId => $oraszam) if ($oraszam > 0) {
	    $tankorIds = $ADAT['tankorBlokk']['idk'][$bId];
	    $bNev = $ADAT['tankorBlokk']['blokkNevek'][$bId];
	    $subjectid = 'b'.$bId;
	    if (
		!isset($ADAT['exportalandoHet']) // Csak akkor exportálunk bontott órákat, ha meglevő órarendet nem
		&& is_array($Bontas['b-'.$bId]) && count($Bontas['b-'.$bId]) > 0
	    ) {
		foreach ($Bontas['b-'.$bId] as $periodsPerCard => $db) {
		    $xml .= '      <lesson id="b'.$periodsPerCard.'-'.implode('_', $tankorIds).'" name="'.$bNev.' ['.$periodsPerCard.']" subjectid="'.
			'b'.$bId.'" classids="'.implode(',', $ADAT['blokkOsztalyok'][$bId]).
			'" teacherids="'.implode(',', $ADAT['blokkTanarok'][$bId]).
			'" periodsperweek="'.($periodsPerCard*$db).
			'" periodspercard="'.$periodsPerCard.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="';
		    if ($ADAT['szeminariumkent']) $xml .= '" studentids="'.implode(',', $ADAT['blokkDiakok'][$bId]);
		    $xml .= '" />'."\r\n";    
		}
	    } elseif ($oraszam > ($egeszoraszam = floor($oraszam))) {
		if ($egeszoraszam > 0) {
		    // Ha tört szám az export óraszám, akkor két lessont gyártunk: egyet az egész óraszámhoz, egyet a törthöz...
		    $xml .= '      <lesson id="b1-'.implode('_', $tankorIds).'" name="'.$bNev.'-e" subjectid="'.
			'b'.$bId.'" classids="'.implode(',', $ADAT['blokkOsztalyok'][$bId]).
			'" teacherids="'.implode(',', $ADAT['blokkTanarok'][$bId]).
			'" periodsperweek="'.$egeszoraszam.
			'" periodspercard="1'.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="';
		    if ($ADAT['szeminariumkent']) $xml .=	'" studentids="'.implode(',', $ADAT['blokkDiakok'][$bId]);
		    $xml .=	'" />'."\r\n";    
		}
		// tört
		$xml .= '      <lesson id="b0-'.implode('_', $tankorIds).'" name="'.$bNev.'-t" subjectid="'.
			'b'.$bId.'" classids="'.implode(',', $ADAT['blokkOsztalyok'][$bId]).
			'" teacherids="'.implode(',', $ADAT['blokkTanarok'][$bId]).
			'" periodsperweek="1'.
			'" periodspercard="1'.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="10';
		if ($ADAT['szeminariumkent']) $xml .=	'" studentids="'.implode(',', $ADAT['blokkDiakok'][$bId]);
		$xml .=	'" />'."\r\n";    

	    } else {
		$xml .= '      <lesson id="b-'.implode('_', $tankorIds).'" name="'.$bNev.'" subjectid="'.
			'b'.$bId.'" classids="'.implode(',', $ADAT['blokkOsztalyok'][$bId]).
			'" teacherids="'.implode(',', $ADAT['blokkTanarok'][$bId]).
			'" periodsperweek="'.ceil($oraszam).
			'" periodspercard="1'.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="';
		if ($ADAT['szeminariumkent']) $xml .=	'" studentids="'.implode(',', $ADAT['blokkDiakok'][$bId]);
		$xml .=	'" />'."\r\n";    
	    }
	}
	// tankor --> lesson
	for ($i = 0; $i < count($ADAT['tankorok']); $i++) {
	    $TA = $ADAT['tankorok'][$i];
	    $tankorId = $TA['tankorId'];
	    if (count($TA['diakIds']) >= 0) {
		if (
		    !isset($ADAT['exportalandoHet']) // Csak akkor exportálunk bontott órákat, ha meglevő órarendet nem
		    && is_array($Bontas['t-'.$tankorId]) && count($Bontas['t-'.$tankorId]) > 0
		) {
		    foreach ($Bontas['t-'.$tankorId] as $periodsPerCard => $db) {
			$xml .= '      <lesson id="t'.$periodsPerCard.'-'.$tankorId.'" name="'.$TA['tankorNev'].' ['.$periodsPerCard.']" subjectid="'.
			$TA['tankorAdat'][$tankorId][0]['targyId'].'" classids="'.implode(',', $TA['osztaly']).
			'" teacherids="'.$TA['tanar'][0].
			'" periodsperweek="'.($db*$periodsPerCard).
			'" periodspercard="'.$periodsPerCard.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="';
			if ($ADAT['szeminariumkent']) $xml .= '" studentids="'.implode(',',$TA['diakIds']);
			$xml .= '" />'."\r\n";    
		    }
		} elseif ($TA['hetiOraszam'] > ($egeszoraszam = floor($TA['hetiOraszam']))) {
		    if ($egeszoraszam > 0) {
			// Ha tört szám az export óraszám, akkor két lessont gyártunk: egyet az egész óraszámhoz, egyet a törthöz...
			$xml .= '      <lesson id="t1-'.$tankorId.'" name="'.$TA['tankorNev'].'" subjectid="'.
			$TA['tankorAdat'][$tankorId][0]['targyId'].'" classids="'.implode(',', $TA['osztaly']).
			'" teacherids="'.$TA['tanar'][0].
			'" periodsperweek="'.$egeszoraszam.
			'" periodspercard="1'.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="';
			if ($ADAT['szeminariumkent']) $xml .= '" studentids="'.implode(',',$TA['diakIds']);
			$xml .= '" />'."\r\n";
		    }
		    $xml .= '      <lesson id="t0-'.$tankorId.'" name="'.$TA['tankorNev'].'" subjectid="'.
			$TA['tankorAdat'][$tankorId][0]['targyId'].'" classids="'.implode(',', $TA['osztaly']).
			'" teacherids="'.$TA['tanar'][0].
			'" periodsperweek="1'.
			'" periodspercard="1'.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="10';
			if ($ADAT['szeminariumkent']) $xml .= '" studentids="'.implode(',',$TA['diakIds']);
			$xml .= '" />'."\r\n";

		} else {
		    $xml .= '      <lesson id="t-'.$tankorId.'" name="'.$TA['tankorNev'].'" subjectid="'.
			$TA['tankorAdat'][$tankorId][0]['targyId'].'" classids="'.implode(',', $TA['osztaly']).
			'" teacherids="'.$TA['tanar'][0].
			'" periodsperweek="'.ceil($TA['hetiOraszam']).
			'" periodspercard="1'.
			'" classroomids="'.implode(',',$teremIds).
			'" weeks="';
			if ($ADAT['szeminariumkent']) $xml .= '" studentids="'.implode(',',$TA['diakIds']);
			$xml .= '" />'."\r\n";
		}
	    }
	}
	$xml .= '   </lessons>'."\r\n";

	if (isset($ADAT['exportalandoHet'])) {
	    // jelenleg betöltött órák
	    $xml .= '   <cards options="import:primarydb,silent,canadd,canupdate,canremove" columns="day,period,classroomids,teacherids,subjectids,classids,lessonid,durationperiods">'."\r\n";
	    for ($i = 0; $i < count($Orarend); $i++) {
		$tankorId = $Orarend[$i]['tankorId'];
		if ($ADAT['tankorBlokkOra'][$tankorId][ $Orarend[$i]['het'] ][ $Orarend[$i]['nap'] ][ $Orarend[$i]['ora'] ]!==true) {
		    $lessonId = 't-'.$tankorId;
		    $TA = $ADAT['tankorok'][ $ADAT['tankorIndex'][$tankorId] ];
		    if (is_array($TA)) // A szakkörök például nincsenek benne alap esetben...
			$xml .= '      <card day="'.$Orarend[$i]['nap']
			    .'" period="'.$Orarend[$i]['ora']
			    .'" classroomids="'.$Orarend[$i]['teremId']
			    .'" teacherids="'.$TA['tanar'][0]
			    .'" subjectids="'.$TA['tankorAdat'][$tankorId][0]['targyId']
			    .'" classids="'.implode(',', $TA['osztaly'])
			    .'" lessonid="'.$lessonId
			    .'" durationperiods="1"/>'."\r\n";
		}
	    }
	    // A blokk órák kiírása
	    if (is_array($ADAT['blokkOra'])) foreach ($ADAT['blokkOra'] as $bId => $Orak) {
		$tankorIds = $ADAT['tankorBlokk']['idk'][$bId];
		$lessonId = 'b-'.implode('_', $tankorIds);
		for ($i = 0; $i < count($Orak); $i++) {
		    $xml .= '      <card day="'.$Orak[$i]['nap']
			    .'" period="'.$Orak[$i]['ora']
			    .'" classroomids="'.implode(',',$Orak[$i]['teremIds'])
			    .'" teacherids="'.implode(',', $ADAT['blokkTanarok'][$bId])
			    .'" subjectids="b'.$bId
			    .'" classids="'.implode(',', $ADAT['blokkOsztalyok'][$bId])
			    .'" lessonid="'.$lessonId
			    .'" durationperiods="1"/>'."\r\n";
		}
	    }
	    $xml .= '   </cards>'."\r\n";
	}

	$xml .= '</timetable>'."\r\n";

	fputs($fp, mb_convert_encoding($xml, 'ISO-8859-2', 'UTF-8'));
	fclose($fp);

	return $return;

    }

    function blokkOraszamRogzites($blokkAdat, $tanevDb) {

	if (is_array($blokkAdat)) foreach ($blokkAdat as $bId => $bOraszam) {
	    $q = "UPDATE `%s`.blokk SET exportOraszam = %f WHERE blokkId = %u";
	    $v = array($tanevDb, $bOraszam, $bId);
	    $r = db_query($q, array('fv' => 'blokkOraszamRogzites', 'modul' => 'naplo', 'values' => $v));
	}

    }

    function ascBontasLekerdezes($tanevDb) {

	$v = array($tanevDb);
	// A szükséges tábla létrehozása - ha netán nem létezne
        $q = "CREATE TABLE IF NOT EXISTS `%s`._ascOraBontas (
	  tipus ENUM('blokk','tankör') NOT NULL DEFAULT 'tankör',
	  id INT UNSIGNED NOT NULL,
	  bontas VARCHAR(32),
	  PRIMARY KEY (tipus, id)
	) ENGINE InnoDb";
        db_query($q, array('fv' => 'ascBontasLekerdezes/createTable', 'modul' => 'naplo', 'values' => $v));

        $return = array();
        $q = "SELECT CONCAT(LEFT(tipus,1),'-',id) AS bontasId,bontas FROM `%s`._ascOraBontas";
        $ret = db_query($q, array('fv' => 'bontás lekérdezés', 'modul' => 'naplo', 'result' => 'assoc', 'keyfield' => 'bontasId', 'values' => $v));
        if ($ret !== false) foreach ($ret as $bontasId => $bAdat) $return[$bontasId] = $bAdat['bontas'];
        return $return;
    }

    function ascBontasModositas($ADAT) {


	$tipusStr = array('t' => 'tankör', 'b' => 'blokk');

        // Törlések elvégzése ($ADAT['bontas'] tömb)
            if (is_array($_POST['torlendoBontas']) && count($_POST['torlendoBontas']) > 0) {
                for ($i = 0; $i < count($_POST['torlendoBontas']); $i++) {
                    $bontasId = readVariable($_POST['torlendoBontas'][$i], 'emptystringnull', null, array_keys($ADAT['bontas']));
                    if (isset($bontasId)) unset($ADAT['bontas'][$bontasId]);
                }
            }
        // Új bontás felvétele
            $bontasId = readVariable($_POST['bontasId'], 'emptystringnull', null);
            if (isset($bontasId)) {
                list($tipus, $id) = explode('-', $bontasId);
                if ($tipus == 't') $oraszam = $ADAT['tankorok'][ $ADAT['tankorIndex'][$id] ]['hetiOraszam'];
                elseif ($tipus == 'b') $oraszam = $ADAT['tankorBlokk']['exportOraszam'][$id];
                $oraBontas = readVariable($_POST['oraBontas'], 'emptystringnull', '1');
                eval('$bontottOraszam='.$oraBontas.';');
                if ($bontottOraszam > $oraszam) {
                    $_SESSION['alert'][] = 'message:wrong_data:'."$tipus-$id - óraszám=$oraszam < $oraBontas=bontott óraszám";
                } else {
                    if ($bontottOraszam < $oraszam) $oraBontas .= str_repeat('+1', $oraszam-$bontottOraszam);
                    $ADAT['bontas']["$tipus-$id"] = $oraBontas;
                }
            }

        // A bontások kiírása
            $q = "DELETE FROM `%s`._ascOraBontas";
            db_query($q, array('fv' => 'ascExport/bontásokTörlése', 'modul' => 'naplo', 'values' => array($ADAT['tanevDb'])));
            $v = array($ADAT['tanevDb']); $V = array();
            foreach ($ADAT['bontas'] as $bontasId => $bontas) {
                list($tipus, $id) = explode('-', $bontasId);
                $V[] = "('%s', %u, '%s')";
                array_push($v, $tipusStr[$tipus], $id, $bontas);
            }
            if (count($V) > 0) {
                $q = "INSERT INTO `%s`._ascOraBontas (tipus, id, bontas) VALUES ".implode(',', $V);
                db_query($q, array('fv' => 'ascExport/bontásokRögzítése', 'modul' => 'naplo', 'values' => $v));
            }

    }

?>
