<?php

   if (_RIGHTS_OK !== true) die();

    global $ADAT,$aHetNapjai;

//if ($skin=='ajax') {

    $szulinapos= false;

//    if (__NAPLOADMIN===true || __TANAR===true) {
/*
	echo '<ul>';
	for ($i=0; $i<count($ADAT['ma']['diak']); $i++) {
	echo '<li>';
	echo $ADAT['ma']['diak'][$i]['viseltNevElotag'].' ';
	echo $ADAT['ma']['diak'][$i]['viseltCsaladinev'].' ';
	echo $ADAT['ma']['diak'][$i]['viseltUtonev'];
	echo '</li>';
	}
	echo '</ul>';
*/
    if ($skin!='ajax') echo '<div style="margin-left:40px">';

    echo '<style type="text/css">
	a.btn_szulinap {display:block;text-align:center; margin-top:4px; margin-bottom:2px; }
	a.btn_szulinap button { font-size:x-small;}
	div.szulinaposok_dt {text-align: left; padding-left:4px; color:#888;padding-top:4px; padding-bottom:2px;}
	ul.szulinap li span.eletkor {display:none;}
	ul.szulinap:hover li span.eletkor { display: inline-block; background-color: #ddd; border-radius:10%; padding: 0px 4px; margin-left: 2px; }
</style>';

    for ($n=0; $n<=6; $n++) {
	echo '<div class="szulinaposok_dt">'.(date('Y.m.d.',strtotime($ADAT['heti'][$n]['dt']))).' '.$aHetNapjai[(6+date('w',strtotime($ADAT['heti'][$n]['dt'])))%7].'</div>';
	echo '<ul class="szulinap" style="list-style-type: none;">';
	for ($i=0; $i<count($ADAT['heti'][$n]['diakok']['diak']); $i++) {
	    $_D = $ADAT['heti'][$n]['diakok']['diak'][$i];
	    echo '<li class="diakAdat diakNev" data-diakid="'.$_D['diakId'].'">';
	    echo $ADAT['heti'][$n]['diakok']['diak'][$i]['viseltNevElotag'].' ';
	    echo $ADAT['heti'][$n]['diakok']['diak'][$i]['viseltCsaladinev'].' ';
	    echo $ADAT['heti'][$n]['diakok']['diak'][$i]['viseltUtonev'];
	    if ($ADAT['heti'][$n]['diakok']['diak'][$i]['diakEletkor']<=20) echo '<span class="eletkor">'.$ADAT['heti'][$n]['diakok']['diak'][$i]['diakEletkor'].'</span>';
	    if ($ADAT['heti'][$n]['diakok']['diak'][$i]['osztaly'][0]['osztalyJel']!='') {
		echo ' ('.$ADAT['heti'][$n]['diakok']['diak'][$i]['osztaly'][0]['osztalyJel'].')';
	    }
	    echo '</li>';
	}
	echo '</ul>';

    }

    if (is_array($ADAT['ma']['diakOsztaly'])) foreach($ADAT['ma']['diakOsztaly'] as $_diakId => $_osztalyId) {
	$O[$_osztalyId[0]]++;
	if ( defined('__USERDIAKID') && __USERDIAKID == $_diakId ) $szulinapos = true;

    }

    if ($szulinapos) echo _HAPPYBIRTHDAY;

    echo '<a href="'.href('index.php?page=naplo&f=szulinap').'" class="btn_szulinap">';
	echo '<button><span class="icon-th-list" style="font-size:8px; color:#888"></span> Születésnaposok</button>';
    echo '</a>';

    if ($skin!='ajax') echo '</div>';

/*
    if (is_array($O)) {
	echo '<ul>';
	foreach($O as $osztalyId => $db) {
	    echo '<li>';
	    echo $ADAT['osztaly'][$osztalyId]['osztalyJel'].': ' ;
	    echo $db.' ';
	    echo '</li>';
	}
	echo '</ul>';
    }
*/
//}

?>
