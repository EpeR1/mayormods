<?php

    // credits: rpetya (rakolcza.peter@gmail.com)

    if (_RIGHTS_OK !== true) die();
    if (__DIAK!==true && __TANAR!==true && __NAPLOADMIN !== true && __TITKARSAG !== true && __VEZETOSEG !== true) {
	$_SESSION['alert'][] = 'page:insufficient_access';
	exit;
    }

    require_once('include/modules/naplo/share/jegyzet.php');
    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/osztaly.php');
    require_once('include/modules/naplo/share/munkakozosseg.php');

    $jegyzetId = readVariable($_POST['jegyzetId'], 'id', '-1');
    $_JSON['refJegyzetDt'] = readVariable($_POST['refJegyzetDt'],'date');

    // REMOVE
    if ($action=='delJegyzet') {
	delJegyzet($jegyzetId); // szigoríthatjuk is
	$_JSON['jegyzetId'] = $jegyzetId; // a frontenden szukseges
	return ;
    // SET (CREATE)
    } elseif ($action!='') {
	//$_JSON['debug'] = $_POST;
	$jegyzetId = setJegyzetAdat($_POST); // szigoríthatjuk is, de a mentő fv gondoskodik erről
    }

    // Kiegészítő adatok
    if (__JEGYZETSZEREPTIPUS == 'diak') {
	$ADAT['tankorok'] = getTankorByDiakId(__JEGYZETSZEREPID);
	$ADAT['osztalyok'] = getDiakOsztalya(__JEGYZETSZEREPID,array('tanev'=>$tanev,'tolDt'=>$dt,'igDt'=>$dt));
    } elseif (__JEGYZETSZEREPTIPUS == 'tanar') {
	$ADAT['munkakozossegek'] = getMunkakozossegByTanarId(__JEGYZETSZEREPID, array('idonly'=>false));
	if (is_array($_OSZTALYA) && count($_OSZTALYA)>0) $ADAT['osztalyok'] = getOsztalyok(null,array('osztalyIds'=>$_OSZTALYA));
	$ADAT['tankorok'] = getTankorByTanarId(__JEGYZETSZEREPID);
    } else {

    }

    // GET
    $_JSON['adat'] = $ADAT['jegyzetAdat'] = getJegyzetAdat($jegyzetId);

    define('__READONLY',($jegyzetId>0 
	&& (__JEGYZETSZEREPTIPUS!==$ADAT['jegyzetAdat']['userTipus'] 
	|| __JEGYZETSZEREPID!==$ADAT['jegyzetAdat']['userId'])));
    if ($jegyzetId<=0) { 
	$dt = $_JSON['refJegyzetDt'];
	$leiras = "Új jegyzet";
    } else { 
	$dt = $ADAT['jegyzetAdat']['dt']; 
	$leiras = "Jegyzet";
    }

    $_JSON['jegyzetId'] = $jegyzetId;
    $_JSON['visibleData'] = true;
    $_JSON['leiras'] = $leiras;

    $_CHK[$ADAT['jegyzetAdat']['publikus']] = ' checked="checked" ';

    // generate HTML Template
if (__READONLY===false) {

    // formBegin!!!!!! a pre-ben nem létezik :(
    $jegyzetForm = '<form method="post" action="'.href('index.php?page=naplo&sub=tools&f=jegyzetAdat').'">
	<input class="salt" type="hidden" name="'.__SALTNAME.'" value="'.__SALTVALUE.'" />
	<input class="mayorToken" type="hidden" name="mayorToken" value="'.$_SESSION['mayorToken'].'" />
	<input type="hidden" name="action" value="jegyzetelo" />
	<input type="hidden" name="jegyzetId" value="'.$jegyzetId.'" />
	Határidő: <input type="date" name="dt" value="'.$dt.'"  />
	<textarea name="jegyzetLeiras" style="margin-top:8px; width:99%; height:100px;">'.supertext($_JSON['adat']['jegyzetLeiras']).'</textarea>';

    $jegyzetForm .='<h3>Láthatóság</h3>
	<ul style="list-style-type=none">
	<input type="radio" name="publikus" id="publikus0" value="0" '.$_CHK[0].'/><label for="publikus0">privát</label>
	<br/><input type="radio" name="publikus" id="publikus1" value="1" '.$_CHK[1].' /><label for="publikus1">látható a kapcsolódó csoportoknak</label>';
	//--@madas filter :( 
	if (__VEZETOSEG===true && __JEGYZETSZEREPTIPUS=='tanar') $jegyzetForm .= '<br/><input type="radio" name="publikus" id="publikus2" value="2" '.$_CHK[2].' /><label for="publikus2">publikus (vezetőség)</label>';
	else $jegyzetForm .= '<br/><input type="radio" name="publikus" id="publikus2" value="2" '.$_CHK[2].' disabled="disabled" /><label for="publikus2">publikus (csak a vezetőségnek elérhető)</label>';
    $jegyzetForm .= '</ul>';


    $jegyzetForm .= '<h3>Kapcsolódó csoportok</h3><ul>';

    if (count($ADAT['tankorok'])>0) {
        $jegyzetForm .= '<li>Tankörök: <select name="tankorId[]"><option>-</option>';
	for ($i=0; $i<count($ADAT['tankorok']); $i++) { $_D = $ADAT['tankorok'][$i];
	    $_SEL = (in_array($_D['tankorId'],$ADAT['jegyzetAdat']['tankorok']))? 'selected="selected"':'';
	    $jegyzetForm .= '<option value="'.$_D['tankorId'].'" '.$_SEL.'>'.$_D['tankorNev'].'</option>';
	}
	$jegyzetForm .= '</select></li>';
    }

    if (count($ADAT['osztalyok'])>0) {
	$jegyzetForm .= '<li>Osztályok: <select name="osztalyId[]"><option>-</option>';
	for ($i=0; $i<count($ADAT['osztalyok']); $i++) { 
	    $_D = $ADAT['osztalyok'][$i];
	    $_SEL = (in_array($_D['osztalyId'],$ADAT['jegyzetAdat']['osztalyok']))? 'selected="selected"':'';
	    $jegyzetForm .= '<option value="'.$_D['osztalyId'].'" '.$_SEL.'>'.$_D['osztalyJel'].'</option>';
	}
	$jegyzetForm .= '</select></li>';
    }

    if (count($ADAT['munkakozossegek'])>0) {
	$jegyzetForm .= '<li>Munkaközösségek: <select name="mkId[]"><option>-</option>';
	for ($i=0; $i<count($ADAT['munkakozossegek']); $i++) { 
	    $_D = $ADAT['munkakozossegek'][$i];
	    $_SEL = (in_array($_D['mkId'],$ADAT['jegyzetAdat']['munkakozossegek']))? 'selected="selected"':'';
	    $jegyzetForm .= '<option value="'.$_D['mkId'].'" '.$_SEL.'>'.$_D['munkakozossegNev'].'</option>';
	}
	$jegyzetForm .= '</select></li>';
    }

    //$jegyzetForm .= '<li>Órák: <select><option>-</option></select></li>';
    //$jegyzetForm .= '<li>Munkaközösségek: <select><option>-</option></select></li>';

    $jegyzetForm .= '</ul>';
    $jegyzetForm .= '<button type="button" class="setJegyzetAdat mentes" value="mentés" data-jegyzetid="'.$jegyzetId.'"><span class="icon-ok"></span> MENTÉS </button>';
    $jegyzetForm .= '</form>';

    // töröl
    $jegyzetForm .= '
	<form method="post" action="'.href('index.php?page=naplo&sub=tools&f=jegyzetAdat').'">
	<input class="salt" type="hidden" name="'.__SALTNAME.'" value="'.__SALTVALUE.'" />
	<input class="mayorToken" type="hidden" name="mayorToken" value="'.$_SESSION['mayorToken'].'" />
	<input type="hidden" name="action" value="delJegyzet">
	<input type="hidden" name="jegyzetId" value="'.$jegyzetId.'">
	<button type="button" class="delJegyzet torles" value="végleges törlés" data-jegyzetid="'.$jegyzetId.'"><span class="icon-remove"></span> TÖRLÉS </span></button>';
    $jegyzetForm .= '</form>';

} else {
    $jegyzetForm .= '<p style="font-size:16px; text-align:center">'.supertext($_JSON['adat']['jegyzetLeiras']).'</p>';
    $jegyzetForm .= '<ul>';
	for ($i=0; $i<count($ADAT['tankorok']); $i++) { $_D = $ADAT['tankorok'][$i];
	    if (in_array($_D['tankorId'],$ADAT['jegyzetAdat']['tankorok'])) {
		$jegyzetForm .= '<li class="tankorAdat" data-tankorid="'.$_D['tankorId'].'">'.$_D['tankorNev'].'</li>';
	    }
	}
	for ($i=0; $i<count($ADAT['osztalyok']); $i++) { 
	    $_D = $ADAT['osztalyok'][$i];
	    if (in_array($_D['osztalyId'],$ADAT['jegyzetAdat']['osztalyok'])) {
		$jegyzetForm .= '<li>'.$_D['osztalyJel'].'</li>';
	    }
	}
	for ($i=0; $i<count($ADAT['munkakozossegek']); $i++) { 
	    $_D = $ADAT['munkakozossegek'][$i];
	    if (in_array($_D['mkId'],$ADAT['jegyzetAdat']['munkakozossegek'])) {
		$jegyzetForm .= '<li>'.$_D['munkakozossegNev'].'</li>';
	    }
	}

    $jegyzetForm .= '</ul>';

    $jegyzetForm .= '<p style="border-top: solid 1px #ddd; padding:10px;">';
    $jegyzetForm .= 'Ezt a jegyzetet más felhasználó jegyezte be.
	Adatai egyelőre nem nyilvánosak. Addig is jegyezd meg, hogy ezeket így írjuk helyesen:
	<ul><li>tanár úr (fiú, tehát külön),</li><li>tanárnő (lány, tehát egybe),</li><li>muszáj (pontos jé)</li></ul> ;)
	</p>
    ';
}

    $_JSON['jegyzetForm'] = $jegyzetForm;

?>