<?php

    if (_RIGHTS_OK !== true) die();

    require_once('include/modules/naplo/share/tanar.php');
    require_once('include/modules/naplo/share/orarend.php');
//    require_once('include/share/print/pdf.php');

    $tolDt = readVariable($_POST['tolDt'], 'date', date('Y-m-d'));
    $orarendiHet = readVariable($_POST['orarendiHet'], 'numeric unsigned', 1);
    $felev = readVariable($_POST['felev'], 'numeric unsigned', 1);

    $napiMinOra = getMinOra();
    $napiMaxOra = getMaxOra();
    $Orak = getTanarOrarend($orarendiHet, $felev,$tolDt);
    $Tanarok = getTanarok();

    if (isset($_POST['csv'])) {
	if (OrarendFileGeneralasCSV($Tanarok, $Orak))
	    header('Location: '.location('index.php?page=session&f=download&download=true&dir=export&file=tanarOrarend.csv'));
    } elseif (isset($_POST['xls'])) {
	if (OrarendFileGeneralasXLS($Tanarok, $Orak))
	    header('Location: '.location('index.php?page=session&f=download&download=true&dir=export&file=tanarOrarend.xls'));
    } elseif (isset($_POST['txt'])) {
	if (OrarendFileGeneralasTXT($Tanarok, $Orak))
	    header('Location: '.location('index.php?page=session&f=download&download=true&dir=export&file=tanarOrarend.txt'));
    } elseif (isset($_POST['html'])) {
	if (OrarendFileGeneralasHTML($Tanarok, $Orak))
	    header('Location: '.location('index.php?page=session&f=download&download=true&dir=export&file=tanarOrarend.html'));
    }


    $TOOL['datumSelect'] = array(
            'tipus'=>'cella', 'post'=>array('orarendiHet','felev'),
            'paramName' => 'tolDt', 'hanyNaponta' => 1,
            'override'=>true, // használathoz még át kell írni pár függvényt!!!
            'tolDt' => date('Y-m-d', strtotime('last Monday', strtotime($_TANEV['kezdesDt']))),
            'igDt' => $_TANEV['zarasDt'],
    );
    $TOOL['orarendiHetSelect'] = array(                                                                                                                                 
	'tanev' => $tanev, 'tolDt' => $tolDt, 'igDt' => $igDt, 'tipus' => 'cella',                                                                                      
//	'megjelenitendoHetek' => array(1,2,3,4,5,6,7,8,9,10),                                                                                                           
	'post' => array('fileName','tanev','tolDt','igDt','conv','felev'), 'paramName' => 'orarendiHet'
    );
    $TOOL['felevSelect'] = array(
	'tanev'=>$tanev,'tipus' => 'cella', 'post' => array('fileName','tanev','tolDt','igDt','conv','orarendiHet'), 'paramName' => 'felev'
    );
    getToolParameters(); 
?>
