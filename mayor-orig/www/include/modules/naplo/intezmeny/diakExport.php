<?php

    define('__DIAK_EXPORT_FILE',_DOWNLOADDIR.'/private/export/diakExport');

    $exportFormatum = array(
	'alapértelmezett' => array('oId','viseltNevElotag','viseltCsaladinev','viseltUtonev','diakNaploSorszam'),
	'egyszerű' => array('diakigazolvanySzam','viseltNevElotag','viseltCsaladinev','viseltUtonev','diakNaploSorszam'),
	'osztályfőnöki' => array('diakigazolvanySzam','viseltNevElotag','viseltCsaladinev','viseltUtonev','oId','diakNaploSorszam'),
	'taninformTanuló' => array('oId','diakigazolvanySzam','viseltNevElotag','viseltCsaladinev','viseltUtonev','szuleteskoriNevElotag',
	    'szuleteskoriCsaladinev','szuleteskoriUtonev','szuletesiHely','szuletesiIdo','anyaSzuleteskoriNevElotag','anyaSzuleteskoriCsaladinev',
	    'anyaSzuleteskoriUtonev','allampolgarsag','lakhelyOrszag','lakhelyHelyseg',
	    'lakhelyIrsz','lakhelyKozteruletNev','lakhelyKozteruletJelleg','lakhelyHazszam','lakhelyEmelet','lakhelyAjto','tartOrszag','tartHelyseg',
	    'tartIrsz','tartKozteruletNev','tartKozteruletJelleg','tartHazszam','tartEmelet','tartAjto','jogviszonyKezdete','diakNaploSorszam'
	),
    );

    function diakExport($ADAT) {

	$tanevDbNev = tanevDbNev(__INTEZMENY, $ADAT['tanev']);
	$W = array();
	$q = "SELECT diak.*, osztalyId, osztalyJel, diakNaploSorszam(osztalyDiak.diakId,".$ADAT['tanev'].",osztalyDiak.osztalyId) AS diakNaploSorszam FROM diak LEFT JOIN osztalyDiak USING (diakId)
		LEFT JOIN `%s`.osztalyNaplo USING (osztalyId)";
	$v = array($tanevDbNev);
	if (isset($ADAT['osztalyId'])) { $W[] = "osztalyId=%u"; $v[] = $ADAT['osztalyId']; }
	if (isset($ADAT['dt'])) { $W[] = "beDt<='%s' AND ('%s'<=kiDt OR kiDt IS NULL)"; array_push($v, $ADAT['dt'], $ADAT['dt']); }

	$q .= " WHERE ".implode(' AND ', $W);

	$ret = db_query($q, array('fv' => 'diakExport', 'modul' => 'naplo_intezmeny', 'result' => 'indexed', 'values' => $v));

	if (!$ret) return false;

	$osztalyAdat = getOsztalyok($ADAT['tanev'], array('result' => 'assoc'));

	$Szulok = getSzulok();
	for ($i = 0; $i < count($ret); $i++) {
	    $ret[$i]['telephelyId'] = $osztalyAdat[ $ret[$i]['osztalyId'] ]['telephelyId'];
	    foreach (array('anya','apa','gondviselo') as $tipus) {
		$szuloId = $ret[$i][ $tipus.'Id' ];
		if (is_array($Szulok[$szuloId])) foreach ($Szulok[$szuloId] as $attr => $value) {
		    $ret[$i][ $tipus . ucfirst($attr) ] = $value;
		} elseif ($i == 0 && is_array($Szulok[1])) foreach ($Szulok[1] as $attr => $value) {
		    $ret[$i][ $tipus . ucfirst($attr) ] = '';
		}
	    }
	}

	return $ret;

    }

    function createFile($ADAT) {
	if ($ADAT['formatum'] == 'xls' || $ADAT['formatum'] == 'xml') return generateXLSExport($ADAT['export'], $ADAT['mezok']);
	elseif ($ADAT['formatum'] == 'pdf') return generatePDFExport($ADAT['export'], $ADAT['mezok']);
	else return generateCSVExport($ADAT['export'], $ADAT['mezok']);
    }

    function generateCSVExport($ret, $Mezok = array()) {

	$fp = fopen(__DIAK_EXPORT_FILE . '.csv', 'w');
	if (!$fp) return false;

	fputs($fp, implode('	',$Mezok)."\n");
	for ($i = 0; $i < count($ret); $i++) {
	    $A = array();
	    foreach ($Mezok as $attr => $attrNev) $A[] = $ret[$i][$attr];
	    $sor = implode('	', $A)."\n";
	    fputs($fp, $sor);
	}

	fclose($fp);
	return true;

    }

    function generateXLSExport($ret, $Mezok = array()) {

	$fp = fopen(__DIAK_EXPORT_FILE . '.xml', 'w');
	if (!$fp) return false;

        fputs($fp, '<?xml version="1.0"?>'."\r\n");
        fputs($fp, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"'."\r\n"
			.' xmlns:o="urn:schemas-microsoft-com:office:office"'."\r\n"
			.' xmlns:x="urn:schemas-microsoft-com:office:excel"'."\r\n"
			.' xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"'."\r\n"
			.' xmlns:html="http://www.w3.org/TR/REC-html40">'."\r\n");

	fputs($fp, ' <Styles>'."\r\n"
		.'  <Style ss:ID="s21">'."\r\n".'   <NumberFormat ss:Format="Short Date" />'."\r\n".'  </Style>'."\r\n"
		.'  <Style ss:ID="s22">'."\r\n".'   <NumberFormat ss:Format="yyyy\.m\.d\.\ h:mm" />'."\r\n".'  </Style>'."\r\n"
		."\r\n".' </Styles>'."\r\n");

        fputs($fp, '<Worksheet ss:Name="Diák adatok">'."\r\n");
        fputs($fp, '<Table>'."\r\n");

	// fejléc
	fputs($fp, '<Row>'."\r\n");
	    foreach ($Mezok as $index => $attr) {
		fputs($fp, "    <Cell><Data ss:Type=\"String\">".$attr."</Data></Cell>\r\n");
	    }
	fputs($fp, '</Row>'."\r\n");


	for ($i = 0; $i < count($ret); $i++) {
	    fputs($fp, '<Row>'."\r\n");
	    foreach ($Mezok as $attr => $attrNev) {
		$value = $ret[$i][$attr];
		$time = strtotime($value);
		if (is_numeric($value))
		    fputs($fp, "    <Cell><Data ss:Type=\"Number\">".$value."</Data></Cell>\r\n");
		elseif (is_numeric($time) && $value == date('Y-m-d H:i:s', $time))
		    fputs($fp, "    <Cell ss:StyleID=\"s22\"><Data ss:Type=\"DateTime\">".str_replace(' ','T',$value).'.000'."</Data></Cell>\r\n");
		elseif (is_numeric($time) && $value == date('Y-m-d', $time))
		    fputs($fp, "    <Cell ss:StyleID=\"s21\"><Data ss:Type=\"DateTime\">".$value.'T08:40:00.000'."</Data></Cell>\r\n");
		else
		    fputs($fp, "    <Cell><Data ss:Type=\"String\">".$value."</Data></Cell>\r\n");
	    }
	    fputs($fp, '</Row>'."\r\n");
	}

        fputs($fp, '</Table>'."\r\n");
        fputs($fp, '</Worksheet>'."\r\n");
        fputs($fp, '</Workbook>'."\r\n");

	fclose($fp);
	return true;

    }

    function generatePDFExport($ret, $mezok = array()) {


	$_SESSION['alert'][] = 'message:not implemented';

    }

?>
