<?php

    define('_TANARORARENDFILE',_DOWNLOADDIR.'/private/export/tanarOrarend');

    function getTanarOrarend($orarendiHet = 1, $felev=1, $dt = 'CURDATE()') {

	if ($dt=='') $dt = date('Y-m-d');

	$q = "SELECT * FROM orarendiOra LEFT JOIN orarendiOraTankor USING (tanarId,osztalyJel,targyJel) LEFT JOIN ".__INTEZMENYDBNEV.".tankorSzemeszter USING (tankorId)
		WHERE tanev=".__TANEV." AND szemeszter=%u 
		AND tolDt<='%s' AND igDt>='%s' AND het=%u ORDER BY nap,ora";
	$v = array($felev, $dt, $dt, $orarendiHet);
	$A = db_query($q, array('fv' => 'getTanarOrarend', 'modul' => 'naplo', 'result' => 'multiassoc', 'keyfield' => 'tanarId', 'values' => $v ));

	if (is_array($A)) foreach ($A as $tanarId => $tOrak) {
	    for ($i = 0; $i < count($tOrak); $i++) {
		$nap = $tOrak[$i]['nap'];
		$ora = $tOrak[$i]['ora'];
		$ret[$tanarId][$nap][$ora] = $tOrak[$i];
	    }
	}
	return $ret;

    }

    // Ezzel a fügvénnyel jó Excelben, de nem jó OpenOffice-ban
    function OrarendFileGeneralasXLS($Tanarok, $Orak) {

	$fp = fopen(_TANARORARENDFILE.'.xls', 'w');
	if (!$fp) {
	    $_SESSION['alert'][] = 'message:wrong_data:OrarendFileGeneralas:file:'._TANARORARENDFILE;
	    return false;
	}

	$napiMinOra = getMinOra();
	$napiMaxOra = getMaxOra();

	fputs($fp, '<?xml version="1.0" encoding="utf-8" ?>'."\r\n");
	fputs($fp, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">'."\r\n");
	fputs($fp, '<Worksheet ss:Name="Tanári órarend">'."\r\n");
	fputs($fp, '<Table>'."\r\n");

        for ($i = 0; $i < count($Tanarok); $i++) {

            $tanarId = $Tanarok[$i]['tanarId'];
            $tanarNev = $Tanarok[$i]['tanarNev'];

            $sor = "  <Row>\r\n    <Cell><Data ss:Type=\"String\">$tanarNev</Data></Cell>\r\n";
            for ($nap = 1; $nap < 6; $nap++) {
                for ($ora = $napiMinOra; $ora <= $napiMaxOra; $ora++) {
                    $Ora = $Orak[$tanarId][$nap][$ora];
                    $sor .= '    <Cell><Data ss:Type="String">'.$Ora['osztalyJel']." ".$Ora['targyJel'].'</Data></Cell>'."\r\n";
                }
            }
            $sor .= "    <Cell><Data ss:Type=\"String\">$tanarNev</Data></Cell>\r\n  </Row>\r\n";

	    fputs($fp, $sor);
        }

	fputs($fp, '</Table>'."\r\n");
	fputs($fp, '</Worksheet>'."\r\n");
	fputs($fp, '</Workbook>'."\r\n");
	
	fclose($fp);
	return true;

    }


    // Ha ezt rakom be, akkor OpenOffice-szal jó, Excellel csak akkor, ha előbb lementem
    function OrarendFileGeneralasCSV($Tanarok, $Orak) {

	$fp = fopen(_TANARORARENDFILE.'.csv', 'w');
	if (!$fp) {
	    $_SESSION['alert'][] = 'message:wrong_data:OrarendFileGeneralas:file:'._TANARORARENDFILE;
	    return false;
	}
	$napiMaxOra = getMaxOra();
	$napiMinOra = getMinOra();

	$sor = "Tanár";
        for ($nap = 1; $nap < 6; $nap++) {
            for ($ora = $napiMinOra; $ora <= $napiMaxOra; $ora++) {
		$sor .= ";$nap./$ora.";
	    }
	}
	$sor .= ";Tanár\n";
	fputs($fp, $sor);

        for ($i = 0; $i < count($Tanarok); $i++) {

            $tanarId = $Tanarok[$i]['tanarId'];
            $tanarNev = $Tanarok[$i]['tanarNev'];

            $sor = "$tanarNev;";
            for ($nap = 1; $nap < 6; $nap++) {
                for ($ora = $napiMinOra; $ora <= $napiMaxOra; $ora++) {
                    $Ora = $Orak[$tanarId][$nap][$ora];
                    $sor .= $Ora['tankorNev']." ".$Ora['tankorId'].';';
//                    $sor .= $Ora['osztalyJel']." ".$Ora['targyJel'].';';
                }
            }
            $sor .= "$tanarNev\n";

	    fputs($fp, $sor);
        }

	fclose($fp);
	return true;

    }

    // Ha ezt rakom be, akkor OpenOffice-szal jó, Excellel csak akkor, ha előbb lementem
    function OrarendFileGeneralasTXT($Tanarok, $Orak) {

	$fp = fopen(_TANARORARENDFILE.'.txt', 'w');
	if (!$fp) {
	    $_SESSION['alert'][] = 'message:wrong_data:OrarendFileGeneralas:file:'._TANARORARENDFILE;
	    return false;
	}
	$napiMaxOra = getMaxOra();
	$napiMinOra = getMinOra();

	$sor = "Tanár";
        for ($nap = 1; $nap < 6; $nap++) {
            for ($ora = $napiMinOra; $ora <= $napiMaxOra; $ora++) {
		$sor .= "	$nap./$ora.";
	    }
	}
	$sor .= "	Tanár\n";
	fputs($fp, $sor);

        for ($i = 0; $i < count($Tanarok); $i++) {

            $tanarId = $Tanarok[$i]['tanarId'];
            $tanarNev = $Tanarok[$i]['tanarNev'];

            $sor = "$tanarNev	";
            for ($nap = 1; $nap < 6; $nap++) {
                for ($ora = $napiMinOra; $ora <= $napiMaxOra; $ora++) {
                    $Ora = $Orak[$tanarId][$nap][$ora];
                    $sor .= $Ora['tankorNev']." ".$Ora['tankorId'].'	';
//                    $sor .= $Ora['osztalyJel']." ".$Ora['targyJel'].'	';
                }
            }
            $sor .= "$tanarNev\n";

	    fputs($fp, $sor);
        }

	fclose($fp);
	return true;

    }


    function OrarendFileGeneralasHTML($Tanarok, $Orak) {

	$fp = fopen(_TANARORARENDFILE.'.html', 'w');
	if (!$fp) {
	    $_SESSION['alert'][] = 'message:wrong_data:OrarendFileGeneralas:file:'._TANARORARENDFILE;
	    return false;
	}
	$napiMaxOra = getMaxOra();
	$napiMinOra = getMinOra();

	fputs($fp, '
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="hu">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Tanár órarend</title>
    <style type="text/css">
	table { border-spacing: 0px; border: 1px solid black; }
	th, td { border: 1px solid black; }
    </style>
</head>
<body>
');

        fputs($fp,'<table>'."\n");

	$sor = "<tr><th>Tanár</th>";
        for ($nap = 1; $nap < 6; $nap++) {
            for ($ora = $napiMinOra; $ora <= $napiMaxOra; $ora++) {
		$sor .= "<th>$nap./$ora.</th>";
	    }
	}
	$sor .= "<th>Tanár</th></tr>\n";
	fputs($fp, $sor);

        for ($i = 0; $i < count($Tanarok); $i++) {

            fputs($fp, '<tr>'."\n");

            $tanarId = $Tanarok[$i]['tanarId'];
            $tanarNev = $Tanarok[$i]['tanarNev'];
            fputs($fp, '<th>'.$tanarNev.'</th>'."\n");
            for ($nap = 1; $nap < 6; $nap++) {
                for ($ora = $napiMinOra; $ora <= $napiMaxOra; $ora++) {
                    $Ora = $Orak[$tanarId][$nap][$ora];
                    $sor = '<td>'.$Ora['tankorNev']." ".$Ora['tankorId'].' - '.$Ora['teremId'].'</td>'."\n";
                    fputs($fp, $sor);
                }
            }
            fputs($fp, '<th>'.$tanarNev.'</th>'."\n");
            fputs($fp, '</tr>'."\n");
        }
        fputs($fp, '</table></body></html>'."\n");

	fclose($fp);
	return true;

    }


?>
