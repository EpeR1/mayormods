<?php

    /*
	A loadFile() függvény a paraméterül kapott $ADAT['fileName'] nevű file-t megnyitja, majd a benne lévő adatokat feldolgozza,
	és berakja a $OrarendiOra és $OrarendiOraTankor globális változóba. Ebben már a betöltéshez szükséges összes adatnak bent kell lennie

    */

    function loadFile($ADAT) {

	global $OrarendiOra, $OrarendiOraTankor;

	$OrarendiOra = $OrarendiOraTankor = array();
	$return = true;
	$tanev = $ADAT['tanev'];

	// A file beolvasása
        $fp = fopen($ADAT['fileName'], 'r');
	$size = filesize($ADAT['fileName']);
        if ($size > 0) $xmlStr = mb_convert_encoding(fread($fp, $size), 'UTF-8', 'ISO-8859-2');
        fclose($fp);
	// A <?xml sor(ok) kiiktatása
        $xmlArray = explode("\n", $xmlStr);
        while (substr($xmlArray[0], 0, 5) == '<?xml') array_shift($xmlArray);
        $xmlStr = implode("\n", $xmlArray); unset($xmlArray);

	$dom = new DOMDocument();
	$dom->loadXML( $xmlStr );
	if (!$dom) {
	    $_SESSION['alert'][] = 'message:wrong_data:Hibás XML file='.$ADAT['fileName'];
	    return false;
	}

	// A targyJel mező hosszának lekérdezése (32)
	$q = "SHOW FIELDS FROM `%s`.orarendiOraTankor LIKE 'targyJel'";
	$ret = db_query($q, array('fv' => 'loadFile/targyJel típusa', 'modul' => 'naplo', 'result' => 'record', 'values' => array($ADAT['tanevDb'])));
	$targyJelHossz = intval(substr($ret['Type'], 8, -1));

	// Tanárok - MaYoR
        $cTanar = array(); $Tanarok = getTanarok();
        for ($i = 0; $i < count($Tanarok); $i++) $cTanar[ $Tanarok[$i]['tanarId'] ] = $Tanarok[$i];


	// ====================== Alapadatok lekérdezése az XML-ből =========================== //

	$teachers = $dom->getElementsByTagName( 'teacher' );
	foreach ($teachers as $teacher) {
	    $id = $teacher->getAttribute( 'id' );
	    $ascTanar[$id]['name'] = $teacher->getAttribute( 'name' );
	    $ascTanar[$id]['short'] = $teacher->getAttribute( 'short' );
	    if (!is_array($cTanar[$id])) {
		$_SESSION['alert'][] = 'message:Hibás tanár ID!:'.$id.':'.$ascTanar[$id]['name'].':'.$ascTanar[$id]['short'];
	    }
	}
	$classes = $dom->getElementsByTagName( 'class' );
	foreach ($classes as $class) {
	    $id = $class->getAttribute( 'id' );
	    $name = $class->getAttribute( 'name' );
	    $ascOsztaly[$id] = $name;
	}
	$subjects = $dom->getElementsByTagName( 'subject' );
	foreach ($subjects as $subject) {
	    $id = $subject->getAttribute( 'id' );
	    $name = $subject->getAttribute( 'name' );
	    $short = $subject->getAttribute( 'short' );
	    $ascTargy[$id] = array('name' => $name, 'short' => $short);
	}
	$classrooms = $dom->getElementsByTagName( 'classroom' );
	foreach ($classrooms as $classroom) {
	    $id = $classroom->getAttribute( 'id' );
	    $name = $classroom->getAttribute( 'name' );
	    $short = $classroom->getAttribute( 'name' );
	    $ascTerem[$id] = array('name' => $name, 'short' => $short);
	}

	//  =============== Lesson tábla feldolgozása ======================== //
	// két hetes órarend --> egy hét
	$kihagyandoWeek = ($ADAT['orarendiHet'] == 1) ? '01':'10';
	$lessons = $dom->getElementsByTagName( 'lesson' );
	foreach ($lessons as $lesson) {
	    $id = $lesson->getAttribute( 'id' );
	    $weeks = $lesson->getAttribute( 'weeks' );
	    if ($weeks === $kihagyandoWeek) $kihagyandoLesson[$id] = true;
	}
/*
	//  =============== Lesson tábla feldolgozása ======================== //

	$OrarendiOraTankor = array();
	$lessons = $dom->getElementsByTagName( 'lesson' );
	foreach ($lessons as $lesson) {
	    $id = $lesson->getAttribute( 'id' );
	    $name = $lesson->getAttribute( 'name' );
	    $periodspercard = $lesson->getAttribute( 'periodspercard' );
	    $periodsperweek = $lesson->getAttribute( 'periodsperweek' );
	    $subjectid = $lesson->getAttribute( 'subjectid' );
	    $classids = $lesson->getAttribute( 'classids' );
	    $teacherids = $lesson->getAttribute( 'teacherids' );

	    $ascLesson[$id] = array(
		'lessonid' => $id,
		'name' => $name,
		'periodspercard' => $periodspercard,
		'periodsperweek' => $periodsperweek,
		'subjectid' => $subjectid,
		'classids' => $classids,
		'teacherids' => $teacherids,
	    );

	    list($jel, $ids) = explode('-', $id);
	    $tankorIds = explode('_', $ids);
	    $tanarIds = explode(',', $teacherids);
	    $osztalyIds = explode(',', $classids);
//	    $targyJel = $subjectid;
	    $targyJel = substr($id, 0, $targyJelHossz);
	    for ($i = 0; $i < count($tankorIds); $i++) { // feltételezzük, hogy a tanárok és tankörök sorrendje megfelel egymásnak!
		$tankorId = $tankorIds[$i];
		if (!is_array($tankorOrarendiOra[$tankorId])) $tankorOrarendiOra[$tankorId] = array();
		$tanarId = $mayorTankor[$tankorId]['tanar'][0];
		if (!isset($tanarId)) {
		    $_SESSION['alert'][] = "message:wrong_data:Hiányzó tanár hozzárendelés:tankorId=$tankorId - tanarId NINCS! - teacherids=$teacherids - lessonid=$id";
		    $return = $ADAT['force'];
		    continue;
		} elseif(!in_array($tanarId, $tanarIds)) {
		    $_SESSION['alert'][] = "message:wrong_data:Hibás tanár hozzárendelés:tankorId=$tankorId - tanarId=$tanarId - teacherids=$teacherids - lessonid=$id";
		    $return = $ADAT['force'];
		    continue;
		}
		$osztalyId = $mayorTankor[$tankorId]['osztaly'][0];
		if (!in_array($osztalyId, $osztalyIds)) {
		    $_SESSION['alert'][] = "message:wrong_data:Hibás osztály hozzárendelés:tankorId=$tankorId - osztalyId=$osztalyId - classids=$classids";
		    $return = $ADAT['force'];
		    if (!$return) continue;
		}
		if (!in_array("$tanarId:$osztalyId:$targyJel", $tankorOrarendiOra[$tankorId])) {
		    $tankorOrarendiOra[$tankorId][] = "$tanarId:$osztalyId:$targyJel";
		    $OrarendiOraTankor[] = array(
			$tanarId, $osztalyId, $targyJel, $tankorId
		    );
		}
	    }

	} // foreach - lesson
*/
	// Kártyák feldolgozása
	$cards = $dom->getElementsByTagName( 'card' );
	if (count($cards)==0) $_SESSION['alert'][] = '::nincsenek kártyák az adott xml file-ban';
	foreach ($cards as $card) {

	    $lessonid = $card->getAttribute( 'lessonid' );
	    if ($kihagyandoLesson[$lessonid]) continue;
	    $classids = explode(',', ($card->getAttribute( 'classids' )));
	    $subjectids = explode(',', ($card->getAttribute( 'subjectids' )));
	    $teacherids = explode(',', ($card->getAttribute( 'teacherids' )));
	    $classroomids = explode(',', ($card->getAttribute( 'classroomids' )));
	    $day = ($card->getAttribute( 'day' ));
	    $period = $card->getAttribute( 'period' );
	    $periodspercard = $card->getAttribute( 'durationperiods' );

	    $weeks = $card->getAttribute( 'weeks' );
	    if ($weeks === $kihagyandoWeek) continue;

	    if ($periodspercard == 0) $periodspercard = 1;

	    for ($j = 0; $j < $periodspercard; $j++) {
		foreach ($teacherids as $i => $tanarId) {
			if (count($subjectids) == count($teacherids)) $targyJel = $ascTargy[ $subjectids[$i] ]['short'];
			else $targyJel = $ascTargy[ $subjectids[0] ]['short']; // Csak az első subjectid-t vesszük figyelembe

			if (count($classroomids) == count($teacherids)) $teremId = readVariable($classroomids[$i],'numeric unsigned','NULL');
			else $teremId = readVariable($classroomids[0],'numeric unsigned','NULL'); // feltételezzük, hogy a tanárok és termek sorrendje megfelel

			if (count($classids) == count($teacherids)) $osztalyJel = $ascOsztaly[ $classids[$i] ];
			else $osztalyJel = $ascOsztaly[ $classids[0] ];

			$OrarendiOra[] = array(
			    $ADAT['orarendiHet'],$day,intval($period+$j),$tanarId,$osztalyJel,$targyJel,$teremId,$ADAT['tolDt'],$ADAT['igDt']
			);			
		}
	    }

	}

	return $return;

    }


?>
