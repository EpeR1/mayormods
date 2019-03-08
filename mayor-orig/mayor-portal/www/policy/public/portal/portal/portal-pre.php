<?php

    require_once('include/modules/portal/share/hirek.php');
    require_once('include/modules/portal/share/nevnap.php');
    require_once('include/modules/portal/share/kerdoiv.php');

    $ev=date('Y');$honap=date('m');$nap=date('d');
    if ($ev % 4 ==0) {if ($ev % 100 !==0) {$szokoev=true;} else {if ($ev % 400==0) {$szokoev=true;}}} else {$szokoev=false;}
    if (!$szokoev && $honap==2 && $nap>=24) {$nap=$nap+1;}

    $ADAT['nevnap']['ma'] = getNevnap($honap,$nap);
    $ADAT['nevnap']['holnap'] = getNevnap($honap,$nap+1);

//    $ADAT['nevnap']['ma'] = getNevnap(date('m'),date('d'));
//    $ADAT['nevnap']['holnap'] = getNevnap(date('m'),date('d')+1);

    $FILTER=array('tolDt'=>date('Y-m-d H:i:s'), 'igDt'=>date('Y-m-d H:i:s'),'flag'=>array(1),'class'=>array(1));
    if (defined('__PORTAL_RESTRICT_CID')) 
	$FILTER['cid'] = explode(',',__PORTAL_RESTRICT_CID);

    $ADAT['hirek'] = getHirek($FILTER);
    $ADAT['kerdoiv'] = getKerdoiv(_POLICY);

    $kerdoivId = readVariable($_POST['kerdoivId'],'numeric',$ADAT['kerdoiv']['kerdes']['sorszam']);
    $vId = readVariable($_POST['vId'],'numeric');
    $szavazotte = szavazotte($kerdoivId);
    // Kérdőív - <<<<
    if ($action == 'szavaz' && $szavazotte==false) {
        szavaz($vId,1,$kerdoivId);
        $szavazotte = true;
	$_SESSION['kerdoivSzavazott'] = true;
    }
    $ADAT['kerdoiv'] = getKerdoiv();
    $ADAT['kerdoiv']['szavazott'] = $szavazotte;
    // Kérdőív - >>>>

?>
