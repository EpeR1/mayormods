<?php

    global $ADAT,$vmPost,$AUTH;

	$LINKEK['inside'] = array(
	    array('href'=>href('index.php?page=naplo&sub=osztalyozo&f=diak'),'szoveg' => 'Osztályozó napló'),
	    array('href'=>href('index.php?page=naplo&sub=hianyzas&f=diak'),'szoveg' => 'Hiányzások'),
	    array('href'=>href('index.php?page=naplo&sub=uzeno&f=uzeno'),'szoveg' => 'Üzenő'),
	    array('href'=>href('index.php?page=naplo&sub=hirnok&f=hirnok'),'szoveg' => 'Hírnök'),
	);	

    echo '<table class="portalMain">';
    echo '<tr><td class="bal">';

	putDoboz('Napló',genLinkek($LINKEK['inside']),array('header-color'=>'cornflowerblue'));
        putDoboz('Bejegyzések',ajaxUpdaterForm('bejegyzesek','index.php?page=naplo&sub=bejegyzesek&f=info',array(),'post',true),
            array('header-color'=>'rgb(150,100,150)'));

    echo '</td>';
    echo '<td class="kozep">';

        echo ajaxUpdaterForm('intezmenyNev','index.php?page=naplo&sub=tools&f=intezmenyNev',array(),'post',true);
        echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/naplo.css\')</script>';
        echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/hirnok/hirnok.css\')</script>';
        echo ajaxUpdaterForm('hirnok','index.php?page=naplo&sub=hirnok&f=hirnok',array(),'post',true);
	putHirek($ADAT);

	echo '<script type="text/javascript">includeCSS(\'/skin/classic/module-naplo/css/uzeno/uzeno.css\')</script>';
	echo ajaxUpdaterForm('uzenoKozep','index.php?page=naplo&sub=uzeno&f=uzeno',array(),'post',true);

    echo '</td>';
    echo '<td class="jobb">';

	echo '<script type="text/javascript">includeCSS(\'/skin/ajax/module-naplo/css/orarend/orarend.css\')</script>';
	$_refStamp = mktime(date('H')+8,0,0,date('m'), date('d'), date('Y'));
        $dt = date('Y-m-d', $_refStamp);
	putDoboz('Órarend '.str_replace('-','.',$dt).'.',ajaxUpdaterForm('orarend','index.php?page=naplo&sub=orarend&f=orarend',array('dt' => $dt),'post',true)
	,array('header-color'=>'#f06'));

//	putDoboz('Hangya',ajaxUpdaterForm('hangya','index.php?page=naplo&sub=hibabejelento&f=admin',array(),'post',true),
//	    array('header-color'=>'rgb(150,100,150)'));

    echo '</td>';
    echo '</tr>';
    echo '</table>';

?>
