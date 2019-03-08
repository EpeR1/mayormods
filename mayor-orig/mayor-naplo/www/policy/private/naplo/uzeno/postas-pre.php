<?php

    require_once('include/modules/naplo/uzeno/uzeno.php'); /* !!! */

    require_once('include/modules/naplo/share/kereso.php');
    include_once('include/modules/naplo/share/tankor.php');
    include_once('include/modules/naplo/share/diak.php');
    include_once('include/modules/naplo/share/szulo.php');
    include_once('include/modules/naplo/share/tanar.php');
    include_once('include/modules/naplo/share/osztaly.php');
    include_once('include/modules/naplo/share/munkakozosseg.php');

    /* ADMIN */
//    if (__UZENOADMIN===true) {
//	$ADAT['asWho'] = readVariable($_POST['asWho'],'strictstring','asIs',array('asAdmin','asIs'));
//	define('__ASWHO',$ADAT['asWho']);
//    } else define('__ASWHO','asIs');

    $ADAT['user']['feladoId'] = setUzenoFeladoId(false);
    $ADAT['user']['feladoTipus'] = getUzenoSzerep(false);
    /* ADMIN */

    initSzerep();
    define('__MEID',setUzenoFeladoId($nooverride=true));

    $ADAT['pattern'] = readVariable($_POST['pattern'],'string');
    $ADAT['cimzett'] = readVariable($_POST['cimzett'],'string');
    $ADAT['diakokkal'] = readVariable($_POST['diakokkal'],'bool'); // ez mindig true
    $ADAT['txt'] = readVariable($_POST['txt'],'string');
    $ADAT['tanev'] = readVariable($_POST['tanev'],'numeric',__TANEV);
    list($_cimzettTipus,$_cimzettId) = explode('|',$ADAT['cimzett']);
    $ADAT['cimzettTipus'] = readVariable($_cimzettTipus,'strictstring',null);    
    $ADAT['cimzettId'] = readVariable($_cimzettId,'id',null);    

    $ADAT['mId'] = readVariable($_GET['mId'],'id',null);
    if (isset($ADAT['mId'])) {
	$SET['filter'][] = 'uzeno.mId='.intval($ADAT['mId']);
	$SET['tanev'] = $ADAT['tanev'];
	$SET['count'] = false;
	$ADAT['uzenet'] = getUzenoUzenetek($SET);
    }

    if ($ADAT['pattern']!='') { /* Ha keres */
	if (uzenhet('diak')) $ADAT['r']['diak' ] = getDiakokByPattern($ADAT['pattern']);
	if (uzenhet('tanar')) $ADAT['r']['tanar'] = getTanarokByPattern($ADAT['pattern']);
	if (uzenhet('szulo')) $ADAT['r']['szulo'] = getSzulokByPattern($ADAT['pattern'],array('diakokkal'=>$ADAT['diakokkal']));
    } elseif ($ADAT['mId']!='' && count($ADAT['uzenet'])==1) {
	// egy üzenet adatait nézzük, akkor kérdezzük le a feladót és a címzettet
	$D = $ADAT['uzenet'][0];
	foreach (array('felado','cimzett') as $_t) {
	    switch ($D[$_t.'Tipus']) {
	    case 'tanar': $nev = getTanarNevById($D[$_t.'Id']); break;
	    case 'diak': $nev = getDiakNevById($D[$_t.'Id']); break;
	    case 'szulo': $nev = getSzuloNevById($D[$_t.'Id']); break;
	    case 'tankor':
	    case 'tankorSzulo': $nev = getTankorNevById($D[$_t.'Id']); break;
	    case 'munkakozosseg': $nev = getMunkakozossegNevById($D[$_t.'Id']); break;
	    case 'osztaly':
	    case 'osztalyTanar':
	    case 'osztalySzulo': $nev = getOsztalyNevById($D[$_t.'Id']); break;
	    }
	    if (uzenhet($_t.'Tipus')) $ADAT['r'][$D[$_t.'Tipus']][] = array($D[$_t.'Tipus'].'Id'=>$D[$_t.'Id'],$D[$_t.'Tipus'].'Nev'=>$nev);
	}
    } else {
	$ADAT['r'] = initUzenoTipusok(array('csakId'=>false,'tanev'=>$ADAT['tanev']));
	if (uzenhet('tanar') && in_array(__SZEREP,array('diak','szulo'))) /* Írjuk felül az üres tanart a tankör tanáraival */
	    $ADAT['r']['tanar'] = extendUzenoTipusok(array('csakId'=>false,'tanev'=>$ADAT['tanev']));
	if (uzenhet(__SZEREP)) $ADAT['r'][__SZEREP][] = array(__SZEREP.'Id'=>0, __SZEREP.'Nev'=>'');
    }

    if ($ADAT['cimzettTipus']!='' && is_numeric($ADAT['cimzettId']) && ($_POST['postazoHash']=='submit' || $_POST['gomb']=='OK')) {

       $P = array(
                'tanev'=>__TANEV,
                'cimzettId'=>readVariable($ADAT['cimzettId'],'id'),
                'cimzettTipus' => readVariable($ADAT['cimzettTipus'],'strictstring',null),
                'txt'=> $ADAT['txt'] );

        if (postUzenet($P) === true) {
	    /* Ha sikered */
	    unset($_POST);
	    header('Location: '.location('index.php?page=naplo&sub=uzeno&f=uzeno'));
	} else {
	    $_SESSION['alert'][] = 'page::uzenorogziteshiba';
	}

    }

?>
