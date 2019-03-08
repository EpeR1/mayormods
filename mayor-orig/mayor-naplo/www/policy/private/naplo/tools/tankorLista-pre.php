<?php
    /*
     * Az oldal különböző bejövő paraméterek alapján ad vissza ajax kérésre egy tankör listát...
     */

    if (_RIGHTS_OK !== true) die();
    if (__NAPLOADMIN !== true && __VEZETOSEG !== true) $_SESSION['alert'][] = 'page:insufficient_access';

    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/targy.php');

    $diakId = readVariable($_POST['diakId'], 'id');
    $osztalyId = readVariable($_POST['osztalyId'], 'id');
    $tanarId = readVariable($_POST['tanarId'], 'id');
    $targyId = readVariable($_POST['targyId'], 'id');
    $mkId = readVariable($_POST['mkId'], 'id');
    $tolDt = readVariable($_POST['tolDt'], 'date');
    $igDt = readVariable($_POST['igDt'], 'date');
    $tanev = readVariable($_POST['tanev'], 'numeric unsigned', __TANEV);
    $bontasIds = readVariable($_POST['bontasIds'],'id'); //,            array(101,102,103,104,105,106));


    // tankörök lekérdezése - lényegében a tankorSelect-nek megfelelően
        if (isset($diakId) && $diakId!='') { // diák tankörei
                $tankorok = getTankorByDiakId($diakId, $tanev, array('tolDt'=>$tolDt, 'igDt'=>$igDt));
        } elseif (isset($osztalyId) && $osztalyId!='') { // osztály tankörei
                $tankorok = getTankorByOsztalyId($osztalyId, $tanev, array('tolDt'=>$tolDt, 'igDt'=>$igDt));
        } elseif (isset($tanarId) && $tanarId!='') { // tanár tankörei
                $tankorok = getTankorByTanarId($tanarId, $tanev, array('tolDt'=>$tolDt, 'igDt'=>$igDt));
        } else { // általános tankörlekérdző
                $WHERE = array();
                if (isset($targyId) && $targyId != '') { // leszűkítés adott tárgyra
                    $WHERE[] = 'targyId='.$targyId;
                } elseif (isset($mkId) && $mkId != '') { // leszűkítés adott munkaközösségre
                    $TARGYAK = getTargyakByMkId($mkId);
                    for ($i = 0; $i < count($TARGYAK); $i++) $T[] = $TARGYAK[$i]['targyId'];
                    if (count($T) > 0) $WHERE[] = 'targyId IN ('.implode(',', $T).')';
                }
                
                if (isset($tanev)) // szűkítés adott tanévre
                    $WHERE[] = 'tankorSzemeszter.tanev='.$tanev;

    	        $tankorok = getTankorok($WHERE);
	}
	$tankorIds = array();
	for ($i=0; $i<count($tankorok); $i++) $tankorIds[] = $tankorok[$i]['tankorId'];

    // speciális szűrések, tagolások
    if (is_array($bontasIds) && count($bontasIds)>0) {

	$bontasTankorIds = getTankorByBontasIds($bontasIds);
	$tankorOraszamok = getTankorTervezettOraszamok($tankorIds);
	
	for ($i=0; $i<count($tankorok); $i++) {
	    $tankorok[$i]['kiemelt'] = (in_array($tankorok[$i]['tankorId'], $bontasTankorIds));
	    $tankorok[$i]['oraszam'] = $tankorOraszamok[ $tankorok[$i]['tankorId'] ]['oraszam'];
	    $tankorok[$i]['bontasOraszam'] = $tankorOraszamok[ $tankorok[$i]['tankorId'] ]['bontasOraszam'];
	}
    }
//dump($tankorok);


    $_JSON['tankorok'] = $tankorok;
    $_JSON['post'] = $_POST;
//    $_JSON['bontasTankorIds'] = $bontasTankorIds;
?>