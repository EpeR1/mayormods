<?php

    if (_RIGHTS_OK !== true) die();
    if (!__NAPLOADMIN) {
        $_SESSION['alert'][] = 'page:insufficient_access';
    }

        require_once('include/modules/naplo/share/tanar.php');
        require_once('include/modules/naplo/share/osztaly.php');
        require_once('include/modules/naplo/share/tankor.php');
        require_once('include/modules/naplo/share/diak.php');
        require_once('include/share/date/names.php');
        require_once('include/modules/naplo/share/osztalyzatok.php');
        require_once('include/modules/naplo/share/dolgozat.php');
        require_once('include/modules/naplo/share/ora.php');
        require_once('include/modules/naplo/share/szemeszter.php');
        require_once('include/modules/naplo/share/zaroJegyModifier.php');
        require_once('include/modules/naplo/share/kepzes.php');
        require_once('include/modules/naplo/share/intezmenyek.php');

        $ADAT['szemeszterId'] = $szemeszterId = readVariable($_POST['szemeszterId'],'id');

	$ADAT['szemeszterAdat'] = getSzemeszterAdatById($szemeszterId);
	$ADAT['tanev'] = $ADAT['szemeszterAdat']['tanev'];
        $Osztalyok = getOsztalyok($ADAT['szemeszterAdat']['tanev'],array('result' => 'indexed', 'minden'=>false, 'telephelyId' => $telephelyId));
	if (isset($_POST['osztalyId']) && $_POST['osztalyId'] != '') { $osztalyId = $ADAT['osztalyId'] = readVariable($_POST['osztalyId'],'id'); }
        elseif (__OSZTALYFONOK && !isset($_POST['osztalyId'])) { $osztalyId = $ADAT['osztalyId'] = $_POST['osztalyId'] = $_OSZTALYA[0]; }

        if (isset($_POST['tolDt']) && $_POST['tolDt'] != '') $tolDt = readVariable($_POST['tolDt'],'date');
                elseif (isset($szemeszterKezdesDt)) $tolDt = $szemeszterKezdesDt;
                else $tolDt = $_TANEV['kezdesDt'];

                if (isset($_POST['igDt']) && $_POST['igDt'] != '') $igDt = readVariable($_POST['igDt'],'date');
                elseif (isset($szemeszterZarasDt)) $igDt = $szemeszterZarasDt;
                else $igDt = $_TANEV['zarasDt'];
	               // a tankör diákjainak lekérdezése
	$ADAT['diakok'] = getDiakokByOsztalyId(array($osztalyId), $SET = array('tanev' => $tanev, 'tolDt' => null, 'igDt' => null, 'result' => '', 'statusz' => array('jogviszonyban van','magántanuló','egyéni munkarend')));
/*	for ($i=0; $i<count($ADAT['diakok']); $i++) {
	    $DIAKIDS[] = $ADAT['diakok'][$i]['diakId'];
	}
*/
//-----------
	if (isset($osztalyId)) {
	    $ADAT['evfolyam'] = getEvfolyam($osztalyId,$ADAT['tanev']);
	    $ADAT['evfolyamJel'] = getEvfolyamJel($osztalyId,$ADAT['tanev']);

	    if ($action=='modosit') {
		$ZAROJEGYIDS = readVariable($_POST['zaroJegyId'],'id');
		$lr = db_connect('naplo_intezmeny');
		for ($i=0; $i<count($ZAROJEGYIDS); $i++) {
		    $_z = $ZAROJEGYIDS[$i];
		    $q = "UPDATE zaroJegy SET evfolyamJel='%s' WHERE zaroJegyId = %u";
		    $v = array($ADAT['evfolyamJel'],$_z);
		    db_query($q, array('modul'=>'naplo_intezmeny','values'=>$v),$lr);
		    $q = "UPDATE vizsga SET evfolyamJel='%s' WHERE zaroJegyId = %u";
		    db_query($q, array('modul'=>'naplo_intezmeny','values'=>$v),$lr);
		}
		db_close($lr);
	    }
	    $q = "SELECT osztalyId, leiras,kezdoTanev,vegzoTanev,jel,telephelyId,osztalyJellegId, elokeszitoEvfolyam,
		    osztalyJelleg.osztalyJellegNev, osztalyJelleg.vegzesKovetelmenye, kovOsztalyJellegId
		    FROM osztaly LEFT JOIN mayor_naplo.osztalyJelleg USING (osztalyJellegId) WHERE osztalyId=%u";
	    $v = array($osztalyId);
	    $ADAT['osztalyok'] = db_query($q,array('values'=>$v,'result'=>'record','modul'=>'naplo_intezmeny'));

	    $q = "SELECT * FROM zaroJegy LEFT JOIN osztalyDiak ON (osztalyDiak.diakId=zaroJegy.diakId AND beDt<=hivatalosDt AND (kiDt IS NULL or kiDt>=hivatalosDt)) WHERE osztalyId=%u AND hivatalosDt>='%s' AND hivatalosDt<='%s' ORDER BY hivatalosDt,zaroJegy.diakId,evfolyam,evfolyamJel";
	    $v = array($osztalyId,$ADAT['szemeszterAdat']['kezdesDt'],$ADAT['szemeszterAdat']['zarasDt']);
	    $ADAT['zaroJegyek'] = db_query($q,array('values'=>$v,'result'=>'indexed','modul'=>'naplo_intezmeny'));
	}
//-----------
//       $TOOL['telephelySelect'] = array('tipus' => 'cella','paramName'=>'telephelyId');
        $TOOL['szemeszterSelect'] = array('tipus'=>'cella','paramName' => 'szemeszterId', 'statusz' => array('aktív','lezárt') , 'post' => array('sorrendNev', 'osztalyId', 'telephelyId'));
        if (!__DIAK) {
            $TOOL['osztalySelect'] = array('tipus'=>'cella','paramName' => 'osztalyId', 'tanev' => $ADAT['szemeszterAdat']['tanev'], 'post' => array('szemeszterId', 'sorrendNev', 'telephelyId'));
        }
        if (isset($osztalyId) && isset($szemeszterId))
            $TOOL['nyomtatasGomb'] = array('titleConst' => '_NYOMTATAS','tipus'=>'cella', 'url'=>'index.php?page=naplo&sub=nyomtatas&f=ertesito','post' => array('osztalyId','szemeszterId','sorrendNev', 'telephelyId'));
        getToolParameters();



?>