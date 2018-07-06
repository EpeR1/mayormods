<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/tankor.php');
    require_once('include/modules/naplo/share/kereso.php');


    $ADAT['tankorId'] = readVariable($_POST['tankorId'],'id',null);
    $ADAT['tankorAdat'] = getTankorAdat($ADAT['tankorId']); // tanév!!!
    $ADAT['tankorTipusok'] = getTankorTipusok();
    $ADAT['html']['blokk0'] = htmlTankorAdat($ADAT);
    $ADAT['html']['blokkok'] = 1;
    $_JSON[] = $ADAT;


    function htmlTankorAdat($ADAT) {
	$tankorAdat = $ADAT['tankorAdat'][$ADAT['tankorId']][0]; // index?
	$r.= '<style type="text/css">
	    table._t tr th { font-weight:normal; text-align: right; }
	</style>';
	$r.='<table class="_t">';
	foreach($tankorAdat as $k => $v) {
	    $r .= '<tr><th>'.$k.':</th><td>'.$v.'</td></tr>';
	}
	$r.='</table>'."\n";

/*	$r.= 'Tankörtípus:';
	$r.= '<select>';
	    for ($i=0; $i<10; $i++) {
		$r.= '<option value="'.$i.'">'.serialize($TA[$i]).'</option>';
	    }
	$r.= '</select>';
	$r.=serialize($TA);
*/
	return $r;
    }

?>