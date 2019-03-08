<?php

    global $ADAT,$vmPost,$AUTH;

	$LINKEK['inside'] = array(
	    array('href'=>href('index.php?page=naplo'),
		    'szoveg' => 'Napló'),
	    array('href'=>href('index.php?page=portal&sub=hirek&f=egyhir'),
		    'szoveg' => 'Hír beküldés'),
	);	

    echo '<table class="portalMain">';
    echo '<tr><td class="bal">';

	// putDoboz('Napló',genLinkek($LINKEK['inside']),array('header-color'=>'#778877'));
	putDoboz('Születésnaposok :)',ajaxUpdaterForm('szulinap','index.php?page=naplo&sub=&f=szulinap',array(),'post',true),
	    array('header-color'=>'rgb(100,100,150)'));
//        putDoboz('Hiányzók',ajaxUpdaterForm('hianyzok','index.php?page=naplo&sub=hianyzas&f=info',array(),'post',true),
//            array('header-color'=>'rgb(100,150,150)'));
	if (in_array('diák',$AUTH['my']['categories']))
    	    putDoboz('Bejegyzések',ajaxUpdaterForm('bejegyzesek','index.php?page=naplo&sub=bejegyzesek&f=info',array(),'post',true),
        	array('header-color'=>'rgb(150,100,150)'));

    echo '</td>';
    echo '<td class="kozep">';

	echo ajaxUpdaterForm('intezmenyNev','index.php?page=naplo&sub=tools&f=intezmenyNev',array(),'post',true);
        echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/naplo.css\')</script>';
        echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/hirnok/hirnok.css\')</script>';
        echo ajaxUpdaterForm('hangya2','index.php?page=naplo&sub=hibabejelento&f=admin&view=2',array(),'post',true);
        echo ajaxUpdaterForm('hirnok','index.php?page=naplo&sub=hirnok&f=hirnok',array(),'post',true);
	//echo ajaxUpdaterForm('naploHaladasi','index.php?page=naplo',array(),'post',true);
        //echo ajaxUpdaterForm('hangya2','index.php?page=naplo&sub=hibabejelento&f=admin&view=2',array(),'post',true);

//	echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/uzeno/uzeno.css\')</script>';
//	echo ajaxUpdaterForm('uzenoKozep','index.php?page=naplo&sub=uzeno&f=uzeno',array(),'post',true);

	putHirek($ADAT);

    echo '</td>';
    echo '<td class="jobb">';

	if (is_array($ADAT['kerdoiv']['kerdes']) && count($ADAT['kerdoiv']['kerdes']) > 0)
            putDoboz('Kérdőív', genKerdoiv($ADAT['kerdoiv']), array('header-color'=>'rgb(140,160,120)'));

	echo '<script type="text/javascript">includeCSS(\'/skin/ajax/module-naplo/css/orarend/orarend.css\')</script>';
	$_refStamp = mktime(date('H')+8,0,0,date('m'), date('d'), date('Y'));
        $dt = date('Y-m-d', $_refStamp);
	putDoboz('Órarend '.$dt,ajaxUpdaterForm('orarend','index.php?page=naplo&sub=orarend&f=orarend',array('dt' => $dt),'post',true)
	,array('header-link'=>href('index.php?page=naplo&sub=orarend&f=orarend'),'header-color'=>'#f06'));

	// putDoboz('Hangya',ajaxUpdaterForm('hangya','index.php?page=naplo&sub=hibabejelento&f=admin',array(),'post',true),
	//    array('header-color'=>'rgb(150,100,150)'));

    echo '</td>';
    echo '</tr>';
    echo '</table>';

?>
