<?php

    /*
	A loadFile() függvény a paraméterül kapott $ADAT['fileName'] nevű file-t megnyitja, majd a benne lévő adatokat feldolgozza,
	és berakja a $OrarendiOra és $OrarendiOraTankor globális változóba. Ebben már a betöltéshez szükséges összes adatnak bent kell lennie

	----------

	MIT IS CSINÁL EZ A VÁLTOZAT?

	Az aSc-vel voltak teremhozzárendelési problémáink. Ezért a termhozzárendeléseket a felületen módosítottuk (Tanév adatok/Teremmodosítás).
	Ezeket a módosításokat az új órarend betöltéskor nem szeretnénk elveszteni (kisebb módosításról van szó, ami azért érint blokkokat).
	Lementjük az orarendiOra tábla tartalmát az _orarendiOra táblába:

	USE naplo_vmg_2008;
	DROP TABLE IF EXISTS _orarendiOra;
	CREATE TABLE _orarendiOra SELECT * FROM orarendiOra;

	Majd betöltjük az újat ezzel a convert modullal. Ekkor a betöltés előtt lekérdezi a régi órarendből, hogy volt-e ugyanabban a pozícióban
	a betöltendő órával egyező tanarId,osztalyJel,targyJel hármas, és ha van, akkor átveszi ennek teremId-jét.

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
	// A <?xml ... sorok kiiktatása
        $xmlArray = explode("\n", $xmlStr);
        while (substr($xmlArray[0], 0, 5) == '<?xml') array_shift($xmlArray);
        $xmlStr = implode("\n", $xmlArray); unset($xmlArray);

	$dom = new DOMDocument();
	$dom->loadXML( $xmlStr );
	if (!$dom) {
	    $_SESSION['alert'][] = 'message:wrong_data:Hibás XML file='.$ADAT['fileName'];
	    return false;
	}

	// Tankörök adatainak lekérdezése
        $Tankorok = getTankorok(array("tanev=$tanev"));
	$mayorTankor = array();
        for ($i = 0; $i < count($Tankorok); $i++) {
	    $tankorId = $Tankorok[$i]['tankorId'];
            $Tankorok[$i]['osztaly'] = getTankorOsztalyaiByTanev($tankorId, $tanev, array('tagokAlapjan' => true, 'result' => 'id'));
//            $Tankorok[$i]['tanar'] = getTankorTanaraiByInterval($tankorId, array('tanev' => $tanev, 'result' => 'csakId'));
    	    $Tankorok[$i]['tanar'] = getTankorTanaraiByInterval(
                $tankorId, array('tanev' => $tanev, 'tolDt' => $ADAT['tolDt'], 'igDt' => $ADAT['igDt'], 'result' => 'csakId')
            );
if ($tankorId == 104) {
var_dump($Tankorok[$i]['tanar']);
}
	    $mayorTankor[$tankorId] = $Tankorok[$i];
	}
var_dump($ADAT['tolDt']);
var_dump($ADAT['igDt']);
	unset($Tankorok);

	// A targyJel mező hosszának lekérdezése (32)
        $q = "SHOW FIELDS FROM `%s`.orarendiOraTankor LIKE 'targyJel'";
        $ret = db_query($q, array('fv' => 'loadFile/targyJel típusa', 'modul' => 'naplo', 'result' => 'record', 'values' => array($ADAT['tanevDb'])));
	$targyJelHossz = intval(substr($ret['Type'], 8, -1));

	// A már felvett orarendiOraTankor bejegyzések lekérdezése
	$ret = getOrarendiOraTankor($tanev);
	$tankorOrarendiOra = array();
	for ($i = 0; $i < count($ret); $i++) {
	    $kulcs = $ret[$i]['tanarId'].':'.$ret[$i]['osztalyJel'].':'.$ret[$i]['targyJel'];
	    $tankorOrarendiOra[ $ret[$i]['tankorId'] ][] = $kulcs;
	}


	// ====================== Alapadatok lekérdezése az XML-ből =========================== //

	$teachers = $dom->getElementsByTagName( 'teacher' );
	foreach ($teachers as $teacher) {
	    $id = $teacher->getAttribute( 'id' );
	    $name = $teacher->getAttribute( 'name' );
	    $ascTanar[$id] = $name;
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
	    if (substr($id,0,1) == 'b') $ascBlokkTargy[substr($id, 1)] = $name;
	    else $ascTargy[$id] = array('name' => $name, 'short' => $short);
	}
	$classrooms = $dom->getElementsByTagName( 'classroom' );
	foreach ($classrooms as $classroom) {
	    $id = $classroom->getAttribute( 'id' );
	    $name = $classroom->getAttribute( 'name' );
	    $ascTerem[$id] = $name;
	}

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
		    continue;
		}
		if (!in_array("$tanarId:$osztalyId:$targyJel", $tankorOrarendiOra[$tankorId])) {
		    $tankorOrarendiOra[$tankorId][] = "$tanarId:$osztalyId:$targyJel";
		    $OrarendiOraTankor[] = array(
			$tanarId, $osztalyId, $targyJel, $tankorId
		    );
		}
	    }

	} // foreach - lesson

	// Kártyák feldolgozása
	$cards = $dom->getElementsByTagName( 'card' );
	foreach ($cards as $card) {
	    $lessonid = $card->getAttribute( 'lessonid' );
//	    $periodspercard = $lAdat['periodspercard'];
	    $periodspercard = $card->getAttribute( 'durationperiods' );
	    $day = ($card->getAttribute( 'day' ));
	    $period = $card->getAttribute( 'period' );
	    $classroomids = explode(',', ($card->getAttribute( 'classroomids' )));
	    $tanarIds = explode(',', $ascLesson[$lessonid]['teacherids']);

	    list($jel, $ids) = explode('-', $lessonid);
	    if (substr($jel,0,1) == 't') $tipus = 'tankor';
	    elseif (substr($jel,0,1) == 'b') $tipus = 'blokk';
	    else {
		$_SESSION['alert'][] = "message:wrong_data:Hibás lesson:lessonid=$lessonid";
		$return = $ADAT['force'];
		if ($ADAT['force']) { // felvesszük az órarendi óra bejegyzést - még tankörhöz kell majd rendelni
		    $osztalyIds = explode(',', $ascLesson[$lessonid]['classids']);
		    foreach ($tanarIds as $i => $tanarId) {
//			$targyJel = substr($lessonid, 0, $targyJelHossz);
			$targyJel = $ascTargy[ $ascLesson[$lessonid]['subjectid'] ]['short'].'-'.$lessonid;
			$teremId = readVariable($classroomids[$i],'numeric unsigned','NULL'); // feltételezzük, hogy a tanárok és termek sorrendje megfelel
			if (count($osztalyIds) == count($tanarIds)) $osztalyJel = $ascOsztaly[ $osztalyIds[$i] ];
			else $osztalyJel = $ascOsztaly[ $osztalyIds[0] ];
			$OrarendiOra[] = array(
			    $ADAT['orarendiHet'],$day,intval($period),$tanarId,$osztalyJel,$targyJel,$teremId,$ADAT['tolDt'],$ADAT['igDt']
			);			
		    }
		}
		continue;
	    }
//	    $oraszam = intval(substr($jel, 1, 1));
	    $oraszam = $periodspercard;
	    if ($oraszam == 0) $oraszam = 1;
//	    if ($oraszam != $periodspercard) $_SESSION['alert'][] = "message:wrong_data:Óraszám=$oraszam - periodspercard=$periodspercard";
	    $tankorIds = explode('_', $ids);
//	    $targyJel = $ascLesson[$lessonid]['subjectid'];
	    $targyJel = substr($lessonid, 0, $targyJelHossz);
	    for ($i = 0; $i < count($tankorIds); $i++) {

		$tankorId = $tankorIds[$i];
		$tanarId = readVariable($mayorTankor[$tankorId]['tanar'][0], 'numeric unsigned', null);
		list($index) = array_keys($tanarIds, $tanarId);
		$osztalyId = readVariable($mayorTankor[$tankorId]['osztaly'][0], 'numeric unsigned', null);
		$teremId = readVariable($classroomids[$index],'numeric unsigned','NULL'); // feltételezzük, hogy a tankor-ök és termek sorrendje megfelel
		for ($j = 0; $j < $oraszam; $j++) {
		    if (isset($tanarId) && isset($osztalyId)) {

$q = "SELECT teremId FROM `%s`._orarendiOra WHERE het=%u AND nap=%u AND ora=%u AND tanarId=%u AND osztalyJel='%s' AND targyJel='%s' AND tolDt<='%s' AND '%s' <= igDt";
$v = array($ADAT['tanevDb'], $ADAT['orarendiHet'], $day, intval($period+$j), $tanarId, $osztalyId, $targyJel, $ADAT['tolDt'], $ADAT['tolDt']);
$ret = db_query($q, array('fv' => 'terem update', 'modul' => 'naplo', 'result' => 'value', 'values' => $v));
if ($ret != '') {
    $teremId = $ret;
}
else {
    $teremId = 'NULL';
}
			$OrarendiOra[] = array(
			    $ADAT['orarendiHet'],$day,intval($period+$j),$tanarId,$osztalyId,$targyJel,$teremId,$ADAT['tolDt'],$ADAT['igDt']
			);
		    }
		}
	    }

	}

	return $return;

    }


?>
