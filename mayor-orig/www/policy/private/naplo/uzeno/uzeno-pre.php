<?php

if (__UZENO_INSTALLED === true) {

    global $TOOL,$action;

    include_once('include/modules/naplo/share/tankor.php');
    include_once('include/modules/naplo/share/diak.php');
    include_once('include/modules/naplo/share/szulo.php');
    include_once('include/modules/naplo/share/tanar.php');
    include_once('include/modules/naplo/share/osztaly.php');
    include_once('include/modules/naplo/share/munkakozosseg.php');

//    define('__ASWHO','asIs');
    initSzerep(); // defines __SZEREP, lásd még __UZENOSZEREP
    define('__MEID',setUzenoFeladoId($nooverride=true));
    $csoportTipus = array('munkakozosseg','tankor','tankorSzulo','osztaly','osztalySzulo','osztalyTanar');

    if (__SZEREP=='tanar') {    // ha tanárok vagyunk és szereztünk diakId-t, akkor:
	$ADAT['diakId'] = readVariable($_GET['diakId'],'id',null);
	if ($ADAT['diakId']>0) $defaultSzalId = __MEID.'_'.__SZEREP.'_'.$ADAT['diakId'].'_diak';
	if ($ADAT['diakId']>0) $defaultCimzett = 'diak|'.$ADAT['diakId'];
    }
    // vezérléshez
    $ADAT['mId'] = readVariable($_GET['mId'],'id');
    $ADAT['szalId'] = readVariable($_GET['szalId'],'string',$defaultSzalId);
    $ADAT['cimzettId'] = readVariable($_GET['cimzettId'],'id');
    $ADAT['cimzettTipus'] = readVariable($_GET['cimzettTipus'],'strictstring');
    $ADAT['replyTipus'] = $ADAT['cimzettTipus'] ;
    $ADAT['feladoId']  = readVariable($_GET['feladoId'],'id');
    $ADAT['feladoTipus'] = readVariable($_GET['feladoTipus'],'strictstring');

    $ADAT['mutato'] = readVariable($_GET['mutato'], 'numeric');
    $ADAT['tanev'] = readVariable($_POST['tanev'],'numeric',readVariable($_GET['tanev'],'numeric',__TANEV));

    $ADAT['toSkin'] = readVariable($_GET['toSkin'],'enum',null,$SKINS);

    $ADAT['pattern'] = readVariable($_POST['pattern'],'string');
    /* Ha van cimzett mező, akkor az új UI-ről jön a kérés, felülírhatunk mindent. */
    $ADAT['cimzett'] = readVariable($_POST['cimzett'],'string',$defaultCimzett);
    if($ADAT['cimzett']!='') {
	//$_SESSION['alert'][] = '::postazonak kellene kezelni!!!'; // he?
	list($ADAT['cimzettTipus'],$ADAT['cimzettId']) = explode('|',$ADAT['cimzett']);
    } else {
	$ADAT['cimzettTipus'] = readVariable($_POST['cimzettTipus'],'strictstring');
	$ADAT['cimzettId'] = intval($_POST[$ADAT['cimzettTipus'].'Id']);
    }
    
//    $ADAT['r']['diak'] = getDiakokByPattern($ADAT['pattern']);

    /* Képkezelés */
    $ADAT['kepMutat'] = __SHOW_FACES_TF;

    $ADAT['tagsagok'] = initUzenoTipusok(array('csakId'=>false,'tanev'=>$ADAT['tanev']));
    $ADAT['tagsagok']['diak'] = getDiakok();
    $ADAT['tagsagok']['tanar'] = getTanarok();
//    $ADAT['tagsagok'][__SZEREP][] = setUzenoFeladoId();
    /* create id */

    /*..*/
    $AVAIL_TIPUSOK = (array_keys($ADAT['tagsagok']));
    /* Postázó 
	    mayorNaploUzen cookie-t minden oldalletöltésnél újrageneráljuk, így a posttal, ha nem egyezik, biza hiába minden.
    */
    $kuuk = readVariable($_COOKIE['mayorNaploUzen'],'strictstring');
    $txt = (readVariable($_POST['txt'],'string'));

    if ($action == 'postUzenet' && $kuuk == $_POST['kuuk'] && in_array($_POST['cimzettTipus'],$AVAIL_TIPUSOK) &&  $txt!='' 
	&& $_POST[$_POST['cimzettTipus'].'Id'] != ''
    ) {
	/* hacky spellchecker - to get rid of annoying mispelled... */
	if (mb_stristr($txt,'tanárúr',false,'UTF-8')!==false) $_SESSION['alert'][]  = 'message:check_spelling:Tanár úr!';
	if (mb_stristr($txt,'tanár nő',false,'UTF-8')!==false) $_SESSION['alert'][] = 'message:check_spelling:Tanárnő!';
	if (mb_stristr($txt,'muszály',false,'UTF-8')!==false) $_SESSION['alert'][]  = 'message:check_spelling:Muszáj!';
	/* --- */
	$P = array(
		'tanev'=>$ADAT['tanev'],
		'cimzettId'=> $ADAT['cimzettId'], 
		'cimzettTipus' => $ADAT['cimzettTipus'], 
		'txt'=> $txt );
	if (count($_SESSION['alert'])==0 && postUzenet($P)===true) {
	    $_SESSION['alert'][] = 'info:msg_success:'; // a félreértés kedvéért kitöröljük a szűrőket
	    unset($ADAT['feladoTipus']); 	    unset($ADAT['feladoId']);
	    unset($ADAT['cimzettTipus']); 	    unset($ADAT['cimzettId']);
	} else {
	    $_SESSION['alert'][] = 'info::figyelmezteto uzenetkor nem rogzitjuk az uzeneteket!';
	    $ADAT['txt']=$txt;
	}
	unset($P);
	unset($ADAT['mId']);
    } elseif (__UZENOADMIN===true && $_GET['action'] =='delUzenet') {
	delUzenet($ADAT['mId'],$ADAT['tanev']);
	unset($ADAT['mId']);
    } elseif ($_GET['action'] == 'flagUzenet') { // közvetlenül is olvashatjuk
	$FLAG['flag'] = readVariable($_GET['flag'],'numeric unsigned',1,array(0,1));
	$FLAG['mId']  = $ADAT['mId'];
	$FLAG['tanev']= $ADAT['tanev'];
	// a kapcsolótáblába rögzíthető status
	flagUzenet($FLAG);
	unset($ADAT['mId']);
    }

    $ADAT['kuuk'] = rand();
    setcookie('mayorNaploUzen', $ADAT['kuuk']);

    $_CONVERT = array('tankorSzulo'=>'tankor', 'osztalySzulo'=>'osztaly', 'osztalyTanar'=>'osztaly');
    if ($ADAT['cimzettId']!='' && in_array($ADAT['cimzettTipus'],$AVAIL_TIPUSOK) ) {	
	$b = false;
	for ($i=0; $i<count( $ADAT['tagsagok'][$ADAT['cimzettTipus']] ) ; $i++) {
	    $_tipus = (!in_array($ADAT['cimzettTipus'], array('tankorSzulo','osztalySzulo','osztalyTanar'))) ? $ADAT['cimzettTipus'].'Id' : str_replace('Szulo','',$ADAT['cimzettTipus']).'Id' ;
	    if (intval($ADAT['tagsagok'][$ADAT['cimzettTipus']][$i][$_tipus])==$ADAT['cimzettId']) {
		$b = true;
		break;
	    }
	}
	if ($b===true) {
	    $SET['filter'][] = 'cimzettId='.intval($ADAT['cimzettId']);
	    $SET['filter'][] = 'cimzettTipus="'.$ADAT['cimzettTipus'].'"';
	}
    }

    if ($ADAT['feladoId']!='' && in_array($ADAT['feladoTipus'],$AVAIL_TIPUSOK)) {
	$b = false;
	for ($i=0; $i<count( $ADAT['tagsagok'][$ADAT['feladoTipus']] ) ; $i++) {
	    if (intval($ADAT['tagsagok'][$ADAT['feladoTipus']][$i][$ADAT['feladoTipus'].'Id'])==$ADAT['feladoId']) {
		$b = true;
		break;
	    }
	}
	if ($b===true) {
	    $SET['filter'][] = 'feladoId='.intval($ADAT['feladoId']);
	    $SET['filter'][] = 'feladoTipus="'.$ADAT['feladoTipus'].'"';
	} 
    }

    if ($ADAT['szalId']!='') {
	list($_feladoId,$_feladoTipus,$_cimzettId,$_cimzettTipus) = explode('_',$ADAT['szalId']);
	if ($_cimzettId==0) { // mindenki
	    $SET['filter'][] = "(cimzettId=$_cimzettId AND cimzettTipus='$_cimzettTipus')";	    
	} elseif (in_array($_cimzettTipus,$csoportTipus)) {
	    $SET['filter'][] = "(cimzettId=$_cimzettId AND cimzettTipus='$_cimzettTipus')";
	} else {
	    $SET['filter'][] = "((feladoId=$_feladoId AND feladoTipus='$_feladoTipus' AND cimzettId=$_cimzettId AND cimzettTipus='$_cimzettTipus')
		OR (cimzettId=$_feladoId AND cimzettTipus='$_feladoTipus' AND feladoId=$_cimzettId AND feladoTipus='$_cimzettTipus'))";
	}
	$SET['limits'] = array('limit'=>100,'pointer'=>0);
	$SET['order'] = 'DESC';
    } elseif ($ADAT['mId']!='') {
	$SET['filter'][] = 'uzeno.mId='.intval($ADAT['mId']);
    } elseif ($skin=='ajax') {
	$SET['limits'] = array('limit'=>__UZENO_AJAXLIMIT,'pointer'=>0);
	$SET['filterFlag'][] = '`uzenoFlagek`.`flag`!=1 OR flag IS NULL';
    } else {
	if (!is_numeric($ADAT['mutato']) || $ADAT['mutato']==0) $SET['limits'] = array('limit'=>__UZENO_DEFAULTLIMIT, 'pointer'=>0);
	else $SET['limits'] = array('limit'=>__UZENO_DEFAULTLIMIT, 'mutato'=>$ADAT['mutato'], 'pointer'=>($ADAT['mutato']-1)*__UZENO_DEFAULTLIMIT);
    }
    $SET['tanev'] = $ADAT['tanev'];
    $ADAT['limits'] = $SET['limits'];
    $ADAT['uzenetek'] = getUzenoUzenetek($SET);
    /* id */
    //    for($i=0; $i<count($ADAT['uzenetek']); $i++) $ADAT['uzenetIdk'][] = $ADAT['uzenetek'][$i]['mId'];
    //--
    for($i=0; $i<count($ADAT['uzenetek']); $i++) {
	$_m = $ADAT['uzenetek'][$i];
	if ($_m['feladoTipus'] == 'szulo') $_SZULOIDS[] = $_m['feladoId']; 
    } 
    $ADAT['tagsagok']['szulo'] = getSzulok(array('csakId'=>false,'result'=>'standard','szuloIds'=>$_SZULOIDS));
    for($i=0; $i<count($ADAT['tagsagok']['szulo']); $i++) {
	$SZULOIDS[] = $ADAT['tagsagok']['szulo'][$i]['szuloId']; 
    }
    if (is_array($SZULOIDS) && count($SZULOIDS) > 0 && count($SZULOIDS)<50) $ADAT['szuloDiakjai'] = getSzulokDiakjai($SZULOIDS);
    //
    $SET['count'] = true;
    $ADAT['limits']['max'] = getUzenoUzenetek($SET);

    $TOOL['tanevSelect'] = array('tipus'=>'cella', 'paramName'=>'tanev', 'tanev'=>$ADAT['tanev'], 'post' => array('tanev'));
    $TOOL['vissza'] = array('tipus'=>'vissza','paramName'=>'','icon'=>'inbox','post'=>array('page'=>'naplo','sub'=>'uzeno','f'=>'uzeno'));
    if ($ADAT['szalId']!='' || $ADAT['mId']!='') {
	$TOOL['vissza']['icon'] = 'arrow-left';
    }

    getToolParameters();

} else {
    $_SESSION['alert'][] = '::uzeno_is_not_installed';
}
?>
