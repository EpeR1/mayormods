<?php

    function createTGZ($ADAT) {

	global $policy, $page, $sub, $f;

	define('TMP','/tmp/mayor2sulix');
	define('TGZ',_DOWNLOADDIR."/$policy/$page/$sub/$f/mayor2sulix.tgz");

	$D = getIntezmenyByRovidnev(__INTEZMENY);

        mkdir(TMP);
        file_put_contents(TMP.'/diak.csv', createCSV('diak', $ADAT));
        file_put_contents(TMP.'/tanar.csv', createCSV('tanar', $ADAT));
        file_put_contents(TMP.'/schoolserver', "SCHOOLOM=\"${D['OMKod']}\"\nSCHOOLNAME=\"${D['nev']}\"\nSCHOOLSHORTNAME=\"${D['rovidNev']}\"\n");

        system("cd ".TMP."; tar cfz ".TGZ." * >/dev/null");

	unlink(TMP.'/diak.csv');
	unlink(TMP.'/tanar.csv');
	unlink(TMP.'/schoolserver');
        rmdir(TMP);

	return true;
    }

    function getDiakAccounts() {

	$D = getDiakok(array('extraAttrs'=>'oId, viseltCsaladinev, viseltUtonev, szuletesiIdo'));
	for ($i = 0; $i < count($D); $i++) {
	    $oId = $D[$i]['oId'];
	    if (isset($oId)) {
		$U = searchAccount('studyId', $oId, array('userAccount','studyId'), 'private');
		for ($j = 0; $j < $U['count']; $j++) {
		    // Ha az oId nem valódi, akkor lehet egy valódi oId része. Az egyenlőséget vizsgálni kell!
		    if ($oId == $U[$j]['studyId'][0]) {
			$O = getDiakOsztalya($D[$i]['diakId']);
			$D[$i]['userAccount'] = ekezettelen($U[$j]['userAccount'][0]);
			$D[$i]['osztalyJel'] = str_replace('.','',$O[0]['osztalyJel']);
			$ret[] = $D[$i];
		    }
		}
	    }
	}
	
	return $ret;

    }

    function getTanarAccounts() {

	$D = getTanarok(array('tanev'=>__TANEV,'result'=>'indexed','extraAttrs'=>'oId, viseltCsaladinev, viseltUtonev, szuletesiIdo'));

	for ($i = 0; $i < count($D); $i++) {
	    $oId = $D[$i]['oId'];
	    if (isset($oId)) {
		$U = searchAccount('studyId', $oId, array('userAccount','studyId'), 'private');
		for ($j = 0; $j < $U['count']; $j++) {
		    // Ha az oId nem valódi, akkor lehet egy valódi oId része. Az egyenlőséget vizsgálni kell!
		    if ($oId == $U[$j]['studyId'][0]) {
			$D[$i]['userAccount'] = ekezettelen($U[$j]['userAccount'][0]);
			$Osztalya = getOsztalyByTanarId($D[$i]['tanarId'], array('csakId'=>false));
			$D[$i]['osztalyJel'] = str_replace('.','',$Osztalya[0]['osztalyJel']);
			if ($D[$i]['szuletesiIdo'] == '') $D[$i]['szuletesiIdo'] = '0000-00-00';
			$ret[] = $D[$i];
		    }
		}
	    }
	}
	
	return $ret;
	
    }

    function getEgyebAccounts() {

    }

    function getAlapadatok($fileName) {

	$D = getIntezmenyByRovidnev(__INTEZMENY);
//var_dump($D);
	$fp = fopen(_DOWNLOADDIR.'/private/naplo/export/sulix/'.$fileName,'w');
	if (!$fp) {
            $_SESSION['alert'][] = 'message:file_open_failure:'.$fileName;
            return false;
        }

	fputs($fp, "SCHOOLOM=\"${D['OMKod']}\"\n");
	fputs($fp, "SCHOOLNAME=\"${D['nev']}\"\n");
	fputs($fp, "SCHOOLSHORTNAME=\"${D['rovidNev']}\"\n");

	fclose($fp);
	return true;
    }

    function myImplode($v) { return implode(':', $v); } // az alábbi array_map-hez

    function createCSV($csoport, $ADAT) {

	if (!is_array($ADAT[$csoport]) || count($ADAT[$csoport])==0) return false;

	$fileName = $csoport.'.csv';
	$title = ':';
	$mayor2sulix = array(
	    'userAccount' => 'AZONOSITO', 'viseltCsaladinev' => 'CSALADI_NEV', 'viseltUtonev' => 'KERESZTNEV',
	    'szuletesiIdo' => 'SZULETESNAP', 'osztalyJel' => 'OSZTALY', 'oId' => 'OKTATASI_AZONOSITO' // ':UTF-8
	);
	$Attrs = array();
	foreach ($ADAT[$csoport][0] as $attr => $value) {
	    if (isset($mayor2sulix[$attr])) { 
		$title .= $mayor2sulix[$attr].':';
		$Attrs[] = $attr;
	    }
	}
	$title .= 'UTF-8';
	$Table = array();
	for ($i = 0; $i < count($ADAT[$csoport]); $i++) {
	    $Table[$i] = array(0=>'');
	    for ($j = 0; $j < count($Attrs); $j++) {
		$Table[$i][$j+1] = $ADAT[$csoport][$i][ $Attrs[$j] ];
	    }
	    $Table[$i][] = ''; // $Table[$i][] = '';
	}
	return $title."\n".implode("\n",array_map('myImplode', $Table));

    }

?>