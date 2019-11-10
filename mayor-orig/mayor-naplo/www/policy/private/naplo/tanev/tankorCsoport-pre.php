<?php

    if (_RIGHTS_OK !== true) die();

    if (!__NAPLOADMIN && !__VEZETOSEG) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    } else {

	require_once('include/modules/naplo/share/osztaly.php');
	require_once('include/modules/naplo/share/tankor.php');
	require_once('include/modules/naplo/share/diak.php');

	$tanev = __TANEV;

	if (isset($_POST['osztalyId']) && $_POST['osztalyId'] != '') $osztalyId = $_POST['osztalyId'];


	if ($action == 'tankorCsoportHozzarendesTorles') {

		    $HM = readVariable($_POST['hozzarendelesMegszuntetes'],'string');
		    for ($i=0; $i<count($HM); $i++) {
		    list($_csoportId,$_tankorId) = explode(':',$HM[$i]);
		    $_csoportId = readVariable($_csoportId, 'numeric unsigned');
		    $_tankorId = readVariable($_tankorId, 'numeric unsigned');

		    tankorCsoportHozzarendelesTorles($_csoportId, $_tankorId);

		    }
	}


	if (isset($osztalyId)) {

	    $Tankorok = getTankorByOsztalyId($osztalyId, $tanev);
	    $tankorIds = array();
	    for ($i = 0; $i < count($Tankorok); $i++) {
		$tankorIds[] = $Tankorok[$i]['tankorId'];
		$tankorAdat[ $Tankorok[$i]['tankorId'] ] = $Tankorok[$i];
	    }
	    $CsA = getTankorCsoportByTankorIds($tankorIds);
	    $Csoportok = $csTankorIds = $csoportIds = array();
	    foreach ($CsA as $csoportId => $csoportAdat) {
		$csoportIds[] = $csoportId;
		$Csoportok[$csoportId]['csoportNev'] = $csoportAdat[0]['csoportNev'];
		$Csoportok[$csoportId]['tankorok'] = array();
		for ($i = 0; $i < count($csoportAdat); $i++) {
		    $Csoportok[$csoportId]['tankorok'][] = $csoportAdat[$i]['tankorId'];
		    $csTankorIds[] = $csoportAdat[$i]['tankorId'];
		}
	    }
	    $szTankorIds = array_diff($tankorIds, $csTankorIds);

// ----------------- action --------------------- //
/*	    $min=0; $max=0;

		if ($min<$Tankorok[$i]['min']) $min= $Tankorok[$i]['min'];
		if ($Tankorok[$i]['max']!=0 && $max>$Tankorok[$i]['max']) $max= $Tankorok[$i]['max'];
*/

	    if ($action == 'ujTankorCsoport') {

		$csoportNev = $_POST['csoportNev'];
		$tankorId = $_POST['tankorId'];
		ujTankorCsoport($csoportNev, $tankorId);

	    } elseif ($action == 'tankorCsoportModositas') {

		$csoportId = readVariable($_POST['csoportId'], 'numeric unsigned', null, $csoportIds);
		if (isset($csoportId)) {
		    $csoportNev = $_POST['csoportNev'];
		    $tankorId = $_POST['tankorId'];
		    if (isset($_POST['tankorCsoportTorles'])) tankorCsoportTorles($csoportId, $tanev);
		    else tankorCsoportModositas($csoportId, $csoportNev, $tankorId);
		}

	    } elseif ($action == 'tankorCsoportokKeresese') {

		// Kérdezzük le a szabad tankörök diákjait
		foreach ($szTankorIds as $i => $tankorId) {
		    $tmp = getTankorDiakjaiByInterval($tankorId, $tanev);
		    $szTankorDiak[$tankorId] = $tmp['idk'];
		}
		// Azonos tagokkal rendelkező csoportok keresése
		$ujCsoportok = $voltMar = $Nevek = array();
		foreach ($szTankorIds as $i => $tankorId) {
		    if (!$voltMar[$tankorId]) {
			$voltMar[$tankorId] = true;
			$ujCsoport = array(
			    'ids' => array($tankorId),
//			    'diakIds' => $szTankorDiak[ $tankorId ]
			);
			foreach  ($szTankorIds as $j => $_tankorId) {
			    if (
				!$voltMar[ $_tankorId ]
				&& $szTankorDiak[ $tankorId ] == $szTankorDiak[ $_tankorId ]
			    ) {
				$ujCsoport['ids'][] = $_tankorId;
				$voltMar[ $_tankorId ] = true;
			    }
			}
			// csoportnév - a tárgynevekből
			$Targyak = array();
			for ($k = 0; $k < count($ujCsoport['ids']); $k++) {
			    $tankorId = $ujCsoport['ids'][$k];
			    $tankorNev = $tankorAdat[ $tankorId ]['tankorNev'];
			    $Targyak[] = substr($tankorNev, ($pos = strpos($tankorNev, ' ')+1), strrpos($tankorNev, ' ')-$pos);
			}
			$ujCsoport['nev'] = implode(' - ', $Targyak);			
			$ujCsoport['nev'] .= ' '.(++$Nevek[ $ujCsoport['nev'] ]).'.';
			$ujCsoportok[] = $ujCsoport;
		    }
		}
		// új csoportok felvétele
		for ($i = 0; $i < count($ujCsoportok); $i++) {
		    if (count($ujCsoportok[$i]['ids']) > 1) ujTankorCsoport($ujCsoportok[$i]['nev'], $ujCsoportok[$i]['ids']);
		}
	    }

	    if ($action != '') {



		$CsA = getTankorCsoportByTankorIds($tankorIds);
		$Csoportok = $csTankorIds = array();
		foreach ($CsA as $csoportId => $csoportAdat) {
		    $Csoportok[$csoportId]['csoportNev'] = $csoportAdat[0]['csoportNev'];
		    $Csoportok[$csoportId]['tankorok'] = array();
		    for ($i = 0; $i < count($csoportAdat); $i++) {
			$Csoportok[$csoportId]['tankorok'][] = $csoportAdat[$i]['tankorId'];
			$csTankorIds[] = $csoportAdat[$i]['tankorId'];
		    }
		}
		$szTankorIds = array_diff($tankorIds, $csTankorIds);

	    }




// ----------------- action --------------------- //

	}




	$ADAT['tankorCsoport'] = getTankorCsoport(); 
	$ADAT['tankorCsoportAdat'] = getTankorCsoportAdat();

	$TOOL['osztalySelect'] = array('tipus' => 'cella','paramName' => 'osztalyId', 'post' => array());
	getToolParameters();
    }

?>
