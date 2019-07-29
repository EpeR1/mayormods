<?php

    require_once('include/share/print/pdf.php');    

    if (file_exists("lang/$lang/module-naplo/share/file.php")) {
	require_once("lang/$lang/module-naplo/share/file.php");
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/module-naplo/share/file.php')) {
	require_once('lang/'._DEFAULT_LANG.'/module-naplo/share/file.php');
    } else {
	echo $lang;
    }

    $Attrs = array(

	'diakid' => _ATTR_DIAKID,
	'oid' => _ATTR_OID,
	'diakigazolvanyszam' => _ATTR_IGAZOLVANYSZAM,
	'viseltnevelotag' => _ATTR_VNE,
	'viseltcsaladinev' => _ATTR_VCSN,
	'viseltutonev' => _ATTR_VUN,
	'szuleteskorinevelotag' => _ATTR_SZNE,
	'szuleteskoricsaladinev' => _ATTR_SZCSN,
	'szuleteskoriutonev' => _ATTR_SZUN,
	'szuletesihely' => _ATTR_SZH,
	'szuletesiido' => _ATTR_SZI,
	'anyanevelotag' => _ATTR_ANE,
	'anyacsaladinev' => _ATTR_ACSN,
	'anyautoneve' => _ATTR_AUN,
	'kezdotanev' => _ATTR_KEZDO_TANEV,
	'kezdoszemeszter' => _ATTR_KEZDO_SZEMESZTER,
	'vegzotanev' => _ATTR_VEGZO_TANEV,
	'vegzoszemeszter' => _ATTR_VEGZO_SZEMESZTER,
	'adoazonosito' => _ATTR_ADOAZONOSITO,
	'allampolgarsag' => _ATTR_ALLAMPOLGARSAG,
	'anyaid' => _ATTR_ANYAID,
	'apaid' => _ATTR_APAID,
	'gondviseloid' => _ATTR_GONDVISELOID,
	'neveloid' => _ATTR_NEVELOID,
	'diaknaplosorszam' => _ATTR_DIAKNAPLOSORSZAM,
	'elozoiskolaomkod' => _ATTR_ELOZOISKOLAOMKOD,
	'email' => _ATTR_EMAIL,
	'fogyatekossag' => _ATTR_FOGYATEKOSSAG,
	'gondozasiszam' => _ATTR_GONDOZASISZAM,
	'jogviszonykezdete' => _ATTR_JOGVISZONYKEZDETE,
	'jogviszonyvege' => _ATTR_JOGVISZONYVEGE,

	'lakhelyorszag' => _ATTR_LAKHELY_ORSZAG,
	'lakhelyirsz' => _ATTR_LAKHELY_IRSZ,
	'lakhelyhelyseg' => _ATTR_LAKHELY_HELYSEG,
	'lakhelykozteruletnev' => _ATTR_LAKHELY_KOZTERULETNEV,
	'lakhelykozteruletjelleg' => _ATTR_LAKHELY_KOZTERULETJELLEG,
	'lakhelyhazszam' => _ATTR_LAKHELY_HAZSZAM,
	'lakhelyemelet' => _ATTR_LAKHELY_EMELET,
	'lakhelyajto' => _ATTR_LAKHELY_AJTO,

	'tartorszag' => _ATTR_TART_ORSZAG,
	'tartirsz' => _ATTR_TART_IRSZ,
	'tarthelyseg' => _ATTR_TART_HELYSEG,
	'tartkozteruletnev' => _ATTR_TART_KOZTERULETNEV,
	'tartkozteruletjelleg' => _ATTR_TART_KOZTERULETJELLEG,
	'tarthazszam' => _ATTR_TART_HAZSZAM,
	'tartemelet' => _ATTR_TART_EMELET,
	'tartajto' => _ATTR_TART_AJTO,

	'tajszam' => _ATTR_TAJSZAM,
	'osztalyjel' => _ATTR_OSZTALYJEL,
	'penzugyistatusz' => _ATTR_PENZUGYISTATUSZ,
	'szemelyiigazolvanyszam' => _ATTR_SZEMELYIIGAZOLVANYSZAM,
	'szocialishelyzet' => _ATTR_SZOCIALISHELYZET,
	'statusz' => _ATTR_STATUSZ,
	'tartozkodasiokiratszam' => _ATTR_TARTOZKODASIOKIRATSZAM,
	'torvenyeskepviselo' => _ATTR_TORVENYESKEPVISELO,
	'telefon' => _ATTR_TELEFON,
	'mobil' => _ATTR_MOBIL,
	'nem' => _ATTR_NEM,
	'lakohelyijellemzo' => _ATTR_LAKOHELYIJELLEMZO,
	'megjegyzes' => _ATTR_MEGJEGYZES,
    );

    function readUpdateFile($fileName, $mezo_elvalaszto = '	') {


	$ADATOK = array();
	if ($fp = @fopen($fileName,'r')) {
		// Az első 50 sor beolvasása - minta a mező-hozzárendeléshez
		$i=0;
		while (($sor = fgets($fp,1024)) and ($i<50)) {
			$ADATOK[$i] = explode($mezo_elvalaszto,chop($sor));
			$i++;
		}
		fclose($fp);
	} else {
		$_SESSION['alert'][] = 'message:file_open_error:'.$fileName;
	}
	
	return $ADATOK;
    }
/*
 * Lekérdezi egy adatbázis (naplo_intezmeny, naplo (tanév)) egy adott táblájának mezőit.
 * Ha szükséges ezek listáját kiegészíti az extraAttrs tömbben felsorolt mezőkkel.
 * A mezőnevekhez nyelvi konstansokat rendelhetünk ($Attrs tömb és lang/.../share/file.php)
 */
    function getTableFields($table, $db = 'naplo_intezmeny', $extraAttrs = array(), $SET = array('withType' => false)) {

	global $Attrs;
	
	$return = $type = $name = array();
	$q = "SHOW FIELDS FROM `%s`";
	$r = db_query($q, array('fv' => 'getTableFields','modul' => $db, 'result' => 'indexed', 'values' => array($table)));
	for ($i = 0; $i < count($r); $i++) {
	    if ($SET['withType']) {
		if (substr($r[$i]['Type'],0,7) == 'varchar') $type[ $r[$i]['Field'] ] = 'string';
		elseif (substr($r[$i]['Type'],0,4) == 'enum') $type[ $r[$i]['Field'] ] = 'enum';
		elseif (substr($r[$i]['Type'],0,4) == 'date') $type[ $r[$i]['Field'] ] = 'date';
		elseif (strpos($r[$i]['Type'],'int(') !== false) $type[ $r[$i]['Field'] ] = 'int';
		else $type[ $r[$i]['Field'] ] = 'string';
	    }
	    if ($Attrs[kisbetus($r[$i]['Field'])] != '') $name[$r[$i]['Field']] = $Attrs[kisbetus($r[$i]['Field'])];
	    else $name[$r[$i]['Field']] = $r[$i]['Field'];
	}
	for ($i = 0; $i < count($extraAttrs); $i++) {
		if (!isset($name[$extraAttrs[$i]])) {
			if ($Attrs[kisbetus($extraAttrs[$i])] != '') $name[$extraAttrs[$i]] = $Attrs[kisbetus($extraAttrs[$i])];
			else $name[$extraAttrs[$i]] = $extraAttrs[$i];
		}
	}
	ksort($name);

	if ($SET['withType']) return array('names' => $name, 'types' => $type);
	return $name;

    }


    function getEnumField($modul, $table, $field) {

	$table = '`'.str_replace('.','`.`',$table).'`';
        $q = "SHOW FIELDS FROM %s LIKE '%s'";
	$v = array($table, $field);
        $field = db_query($q, array('debug'=>true,'fv' => 'getEnumField', 'modul' => $modul, 'result' => 'record', 'values' => $v));
        $enum = substr($field['Type'], 6, -2);
        $values = explode("','", $enum);

        return $values;
    }

    function getSetField($modul, $table, $field) {

	$table = '`'.str_replace('.','`.`',$table).'`';
        $q = "SHOW FIELDS FROM %s LIKE '%s'";
	$v = array($table, $field);
        $field = db_query($q, array('fv' => 'getSetField', 'modul' => $modul, 'result' => 'record', 'values' => $v));
        $set = substr($field['Type'], 5, -2);
        $values = explode("','", $set);

        return $values;
    }


    function updateTable($table, $file, $MEZO_LISTA, $KULCS_MEZOK, $mezo_elvalaszto = '	', $rovatfej = false, $db = 'naplo_intezmeny') {


	if (!file_exists($file)) {
		$_SESSION['alert'][] = 'message:file_not_found:updateTable:'.$file;
		return false;
	}

	if (!is_array($MEZO_LISTA)) {
		$_SESSION['alert'][] = 'message:wrong_data:updateTable:MEZO_LISTA';
		return false;
	}

	if (!is_array($KULCS_MEZOK)) {
		$_SESSION['alert'][] = 'message:wrong_data:updateTable:KULCS_MEZOK';
		return false;
	}

	// A frissítendő attribútumok listája
	$attrList = array_values(array_filter($MEZO_LISTA));

	$fp = fopen($file,'r');
	if (!$fp) {
		$_SESSION['alert'][] = 'message:file_open_error:updateTable:'.$file;
		return false;
	}

	$lr = db_connect($db, array('fv' => 'updateTable'));
	if (!$lr) {
		$_SESSION['alert'][] = 'message:db_connect_failure:updateTable';
		fclose($fp);
		return false;
	}
	db_start_trans($lr);

	// Az első sor kihagyása
	if ($rovatfej) $sor = fgets($fp,1024);
	while ($sor = fgets($fp,1024)) {

		$insertValues = $insertPatterns = array();
		$adatSor = explode($mezo_elvalaszto,chop($sor));
		$update = false;

		// keresési feltétel összerakása
		$where = $v = $vw = array();
		for ($i = 0; $i < count($KULCS_MEZOK); $i++) {
			if ($adatSor[$KULCS_MEZOK[$i]] != '') {
				$where[] = "`%s`='%s'";
				array_push($vw, $MEZO_LISTA[$KULCS_MEZOK[$i]], $adatSor[$KULCS_MEZOK[$i]]);
			}
		}
		$num = 0;
		if (count($where) != 0) {
			$q = 'SELECT COUNT(*) FROM `%s` WHERE '.implode(' AND ', $where);
			array_unshift($vw, $table);
			$num = db_query($q, array('fv' => 'updateTable', 'values' => $vw, 'result' => 'value', 'modul' => $db), $lr);
		}
		if ($num == 1 && _SKIP_ON_DUP === true) { $_SESSION['alert'][] = 'info:_SKIP_ON_DUP:'.serialize($sor); continue; }
		if ($num == 1) { // update
		    $v = $vw;
		    array_shift($v); //$table kivétele
		    $UPDATE = array();
		    for ($i = 0; $i < count($MEZO_LISTA); $i++) {
			if (
			    $MEZO_LISTA[$i] != ''
			    and $adatSor[$i] != ''
			    and !in_array($i,$KULCS_MEZOK)
			) {
			    if ($adatSor[$i] == '\N') {
				array_unshift($UPDATE, "`%s`=NULL");
				array_unshift($v, $MEZO_LISTA[$i]);
			    } else {
				array_unshift($UPDATE, "`%s`='%s'");
				array_unshift($v, $MEZO_LISTA[$i], $adatSor[$i]);
			    }
			}
		    }
		    if (count($UPDATE) > 0) {
			array_unshift($v, $table);
			$q = 'UPDATE `%s` SET '.implode(',',$UPDATE).' WHERE '.implode(' AND ', $where);
			$r = db_query($q, array('fv' => 'updateTable/update', 'values' => $v, 'modul' => $db, 'rollback' => true), $lr);
			if (!$r) {
			    db_close($lr);
			    return false;
			}
		    }
		} elseif ($num == 0) { // insert
		    for ($i = 0; $i < count($MEZO_LISTA); $i++) {
			if ($MEZO_LISTA[$i] != '') {
			    if ($adatSor[$i] == '\N') {
				$insertValues[] = 'NULL';
				$insertPatterns[] = '%s';
			    } else {
				$insertValues[] = $adatSor[$i];
				$insertPatterns[] = "'%s'";
			    }
			}
		    }
		    $q = 'INSERT INTO `%s` ('.implode(',', array_fill(0, count($attrList), '%s')).') 
				    VALUES ('.implode(',', $insertPatterns).')';
		    $v = mayor_array_join(array($table), $attrList, $insertValues);
		    $r = db_query($q, array('fv' => 'updateTable/insert', 'modul' => $db, 'values' => $v, 'rollback' => true), $lr);
		    if (!$r) {
			db_close($lr);
			return false;
		    }
		} else {
		    $_SESSION['alert'][] = 'message:wrong_data:updateTable:több illeszkedő rekord is van, túl laza a kulcs feltétel ('
				.call_user_func_array('sprintf', array_merge(array('%s tábla, '.implode(' AND ',$where)), $vw)).')';
		    db_rollback($lr);
		    db_close($lr);
		    return false;
		}
	} // while
	db_commit($lr);
	db_close($lr);

	fclose($fp);

    }

    function generatePDF($outputFile, $outputDir, $str, $booklet=false) {


	// A szöveg file-ba írása
	if (!$fp = fopen($outputDir.'/'.$outputFile.'-u8.tex', 'w')) {
	    $_SESSION['alert'][] = 'message:file_open_failure:generatePDF:'.$outputDir.'/'.$outputFile.'-u8.tex';
	    return false;
        }
	if (!fwrite($fp, $str)) {
	    $_SESSION['alert'][] = 'message:file_write_failure:generatePDF:'.$outputDir.'/'.$outputFile.'-u8.tex';
	    return false;	
        }
	fclose($fp);
	if (__NYOMTATAS_XETEX===true) {
    	    $ret = exec('cd '.$outputDir.'; cat <<EOF > '.$outputFile.'.tex
%\font\kicsi=ecrm0500
%\font\nagy=ecbx1200
%\font\vastag=ecsx0800
%\font\nagyss=ecsx1200
%\font\normal=ecss0800
%\font\dolt=ecsi0800

\font\kicsi="Linux Libertine O" at 5pt
\font\nagy="Linux Libertine O/B" at 12pt
\font\nagyss="Arial/B" at 12pt
\font\normal="Linux Biolinum O" at 8pt
\font\dolt="Linux Biolinum O/I" at 8pt
\normal

EOF
');
    	    $ret = exec('cd '.$outputDir.'; cat '.$outputFile.'-u8.tex >> '.$outputFile.'.tex');
    	    $ret = exec('cd '.$outputDir.'; xetex -fmt '._MAYOR_DIR.'/print/module-naplo/xetex/mayor-xetex '.$outputFile.'.tex');
#ex -fmt '._MAYOR_DIR.'/print/module-naplo/tex/mayor '.$outputFile.'.tex');
	} else {
	    // utf8 --> cork (t1)
    	    $ret = exec('cd '.$outputDir.'; cat '.$outputFile.'-u8.tex | recode u8..T1 > '.$outputFile.'.tex');
	    // DVI, PS, PFD generálás (a rotate miatt nem megy a pdftex közvetlenül :o(
    	    $ret = exec('cd '.$outputDir.'; tex -fmt '._MAYOR_DIR.'/print/module-naplo/tex/mayor '.$outputFile.'.tex');
	    if ($ret === false) { $_SESSION['alert'][] = 'message:futási_hiba:generatePDF:tex'; return false; }
# LOG        	$ret = exec('cd '.$outputDir.'; dvips '.$outputFile.'.dvi  2>&1 | tee -a /tmp/x.log   ');
    	    $ret = exec('HOME=/tmp && export HOME && cd '.$outputDir.'; dvips '.$outputFile.'.dvi ');
	    if ($ret === false) { $_SESSION['alert'][] = 'message:futási_hiba:generatePDF:ps'; return false; }
    	    $ret = exec('cd '.$outputDir.'; ps2pdf -sPAPERSIZE=a4 -dAutoRotatePages=/None '.$outputFile.'.ps');
	    if (strpos($ret, 'error') !== false) { $_SESSION['alert'][] = 'message:futási_hiba:generatePDF:pdf'; return false; }

	}
	if ($booklet) {
    	    $ret = exec('cd '.$outputDir.'; mv '.$outputFile.'.pdf '.$outputFile.'-A4.pdf; pdfbook --short-edge --outfile '.$outputFile.'.pdf '.$outputFile.'-A4.pdf');
	}
	return true;
    }

    function generateXLS($fileName, $Table, $title) {

	global $policy, $page, $sub, $f;

	if (dirname($fileName) == '.') $fileName = _DOWNLOADDIR."/$policy/$page/$sub/$f/$fileName";
	$fp = fopen($fileName, 'w');
	if (!$fp) {
	    $_SESSION['alert'][] = 'message:file_open_failure:'.$fileName;
	    return false;
	}

	fputs($fp, '<?xml version="1.0"?>'."\r\n");
        fputs($fp, '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">'."\r\n");
        fputs($fp, ' <Styles>'."\r\n"
                .'  <Style ss:ID="s21">'."\r\n".'   <NumberFormat ss:Format="Short Date" />'."\r\n".'  </Style>'."\r\n"
                .'  <Style ss:ID="s22">'."\r\n".'   <NumberFormat ss:Format="yyyy\.m\.d\.\ h:mm" />'."\r\n".'  </Style>'."\r\n"
                ."\r\n".' </Styles>'."\r\n");

        fputs($fp, '<Worksheet ss:Name="'.$title.'">'."\r\n");
        fputs($fp, '<Table>'."\r\n");

	for ($i = 0; $i < count($Table); $i++) {
	    fputs($fp, '  <Row>'."\r\n");
	    foreach ($Table[$i] as $index => $value) {
                if (is_numeric($value))
                    fputs($fp, "    <Cell><Data ss:Type=\"Number\">".$value."</Data></Cell>\r\n");
                elseif (strtotime($value))
                    if (strlen($value) > 10) {
                        fputs($fp, "    <Cell ss:StyleID=\"s22\"><Data ss:Type=\"DateTime\">".str_replace(' ','T',$value).'.000'."</Data></Cell>\r\n");
                    } else {
                        fputs($fp, "    <Cell ss:StyleID=\"s21\"><Data ss:Type=\"DateTime\">".$value.'T08:40:00.000'."</Data></Cell>\r\n");
                    }
                else
                    fputs($fp, "    <Cell><Data ss:Type=\"String\">".$value."</Data></Cell>\r\n");
            }
	    fputs($fp, '  </Row>'."\r\n");
	}

        fputs($fp, '</Table>'."\r\n");
        fputs($fp, '</Worksheet>'."\r\n");
        fputs($fp, '</Workbook>'."\r\n");

        fclose($fp);
        return true;

    }

    function generateCSV($fileName, $Table, $title, $mezoElvalaszto='	') {

	global $policy, $page, $sub, $f;

	if (dirname($fileName) == '.') $fileName = _DOWNLOADDIR."/$policy/$page/$sub/$f/$fileName";
	$fp = fopen($fileName, 'w');
	if (!$fp) {
	    $_SESSION['alert'][] = 'message:file_open_failure:'.$fileName;
	    return false;
	}

        if ($title !='') fputs($fp, $title."\n");
        for ($i = 0; $i < count($Table); $i++) fputs($fp, implode($mezoElvalaszto, $Table[$i])."\n");

        fclose($fp);
        return true;

    }

    function generateODS($fileName, $Table, $title) {

	global $policy, $page, $sub, $f;

	if (dirname($fileName) == '.') $fileName = _DOWNLOADDIR."/$policy/$page/$sub/$f/$fileName";
	define('TMPZIP','/tmp/'.substr(basename($fileName), 0, strpos(basename($fileName), '.')));
	define('ODS_MIMETIPE','application/vnd.oasis.opendocument.spreadsheet');
	define('ODS_MANIFEST','<?xml version="1.0" encoding="UTF-8"?>
<manifest:manifest xmlns:manifest="urn:oasis:names:tc:opendocument:xmlns:manifest:1.0">
 <manifest:file-entry manifest:media-type="application/vnd.oasis.opendocument.spreadsheet" manifest:version="1.2" manifest:full-path="/"/>
 <manifest:file-entry manifest:media-type="text/xml" manifest:full-path="content.xml"/>
</manifest:manifest>');
	define('ODS_START_XMLDOCUMENT','<?xml version="1.0" encoding="UTF-8"?>
<office:document-content
 xmlns:office="urn:oasis:names:tc:opendocument:xmlns:office:1.0"
 xmlns:style="urn:oasis:names:tc:opendocument:xmlns:style:1.0"
 xmlns:text="urn:oasis:names:tc:opendocument:xmlns:text:1.0"
 xmlns:table="urn:oasis:names:tc:opendocument:xmlns:table:1.0"
 xmlns:draw="urn:oasis:names:tc:opendocument:xmlns:drawing:1.0"
 xmlns:fo="urn:oasis:names:tc:opendocument:xmlns:xsl-fo-compatible:1.0"
 xmlns:xlink="http://www.w3.org/1999/xlink"
 xmlns:dc="http://purl.org/dc/elements/1.1/"
 xmlns:meta="urn:oasis:names:tc:opendocument:xmlns:meta:1.0"
 xmlns:number="urn:oasis:names:tc:opendocument:xmlns:datastyle:1.0"
 xmlns:presentation="urn:oasis:names:tc:opendocument:xmlns:presentation:1.0"
 xmlns:svg="urn:oasis:names:tc:opendocument:xmlns:svg-compatible:1.0"
 xmlns:chart="urn:oasis:names:tc:opendocument:xmlns:chart:1.0"
 xmlns:dr3d="urn:oasis:names:tc:opendocument:xmlns:dr3d:1.0"
 xmlns:math="http://www.w3.org/1998/Math/MathML"
 xmlns:form="urn:oasis:names:tc:opendocument:xmlns:form:1.0"
 xmlns:script="urn:oasis:names:tc:opendocument:xmlns:script:1.0"
 xmlns:ooo="http://openoffice.org/2004/office"
 xmlns:ooow="http://openoffice.org/2004/writer"
 xmlns:oooc="http://openoffice.org/2004/calc"
 xmlns:dom="http://www.w3.org/2001/xml-events"
 xmlns:xforms="http://www.w3.org/2002/xforms"
 xmlns:xsd="http://www.w3.org/2001/XMLSchema"
 xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"
 xmlns:rpt="http://openoffice.org/2005/report"
 xmlns:of="urn:oasis:names:tc:opendocument:xmlns:of:1.2"
 xmlns:xhtml="http://www.w3.org/1999/xhtml"
 xmlns:grddl="http://www.w3.org/2003/g/data-view#"
 xmlns:tableooo="http://openoffice.org/2009/table"
 xmlns:field="urn:openoffice:names:experimental:ooo-ms-interop:xmlns:field:1.0"
 xmlns:formx="urn:openoffice:names:experimental:ooxml-odf-interop:xmlns:form:1.0"
 office:version="1.2" grddl:transformation="http://docs.oasis-open.org/office/1.2/xslt/odf2rdf.xsl">');
	define('ODS_ASTYLES','<office:automatic-styles>
    <number:date-style style:name="N84">
      <number:year number:style="long"/>
      <number:text>-</number:text>
      <number:month number:style="long"/>
      <number:text>-</number:text>
      <number:day number:style="long"/>
    </number:date-style>
    <style:style style:name="ce1" style:family="table-cell" style:parent-style-name="Default" style:data-style-name="N84"/>
  </office:automatic-styles>');
	define('ODS_START_SPREADSHEET','<office:body><office:spreadsheet>');
	define('ODS_START_TABLE','<table:table table:name="Export">');
	define('ODS_START_ROW','<table:table-row>');
	define('ODS_END_ROW','</table:table-row>');
	define('ODS_END_TABLE','</table:table>');
	define('ODS_END_SPREADSHEET','</office:spreadsheet></office:body>');
	define('ODS_END_XMLDOCUMENT','</office:document-content>');

	$content = ODS_START_XMLDOCUMENT . ODS_ASTYLES . ODS_START_SPREADSHEET . ODS_START_TABLE;
	for ($i = 0; $i < count($Table); $i++) {
	    $content .= ODS_START_ROW;
	    foreach ($Table[$i] as $index => $value) {
                if (is_numeric($value))
		    $content .= '<table:table-cell office:value-type="float" office:value="'.$value.'"/>'."\n";
                elseif (strtotime($value))
                    if (strlen($value) > 10) {
			$content .= '<table:table-cell table:style-name="ce1" office:value-type="date" office:date-value="'.$value.'"/>'."\n";
                    } else {
			$content .= '<table:table-cell table:style-name="ce1" office:value-type="date" office:date-value="'.$value.'"/>'."\n";
                    }
                elseif ($value[0] == '=') $content .= '<table:table-cell table:formula="'.$value.'" office:value-type="float"/>'."\n";
		// formula példa: $value = 'of:=SUM([.A1:.B1])*2+[.A1]'
		else $content .= '<table:table-cell office:value-type="string"><text:p>'.$value.'</text:p></table:table-cell>'."\n";
            }
	    $content .= ODS_END_ROW;
	}
	$content .= ODS_END_TABLE . ODS_END_SPREADSHEET . ODS_END_XMLDOCUMENT;
	
	mkdir(TMPZIP);
	mkdir(TMPZIP."/META-INF");
	file_put_contents(TMPZIP."/META-INF/manifest.xml", ODS_MANIFEST);
	file_put_contents(TMPZIP."/content.xml", $content);
	file_put_contents(TMPZIP."/mimetype", ODS_MIMETIPE);
	system("cd ".TMPZIP."; zip -mr ".$fileName." mimetype META-INF/* content.xml >/dev/null");
	rmdir(TMPZIP."/META-INF");
	rmdir(TMPZIP);

	return true;
    }

/* --------------------------------------- */

    function _template2array($fp, $type, &$aTeX) {
        $vege = false;
        $aTeX[$type] = array();
        while (!$vege && ($sor = fgets($fp, 1024))) {
            $sor = chop($sor);
            if ($sor == "%}$type") {
                $vege = true;
            } elseif (substr($sor, 0, 2) != '%!') { // A feldolgozást végző függvény megadása
                if (substr($sor, 0, 2) == '%}') {
		    echo "HIBA #1 Megnyitás előtti blokk lezárás a {$type} blokkban: $sor<hr>";
                } else {
                    if (substr($sor, 0, 2) == '%{') {
                        $_type = substr($sor, 2);
                        _template2array($fp, $_type, $aTeX);
                        $aTeX[$type][] = '%{'.$_type.'}';
                    } else {
			// feltételes szövegrészek
			$condArray = explode('%?', $sor);
			for ($i = 1; $i < count($condArray); $i = $i + 2) {
			    $str = $condArray[$i];
			    $tmpArray = explode('|', $str);
			    $j = 0; while (is_array($aTeX['conditional']["$j".$tmpArray[0]])) $j++;
			    $newCondition = "$j".$tmpArray[0];
			    $aTeX['conditional'][$newCondition] = array(true => $tmpArray[1], false => $tmpArray[2], 'orig' => $tmpArray[0]);
			    $sor = str_replace($str.'%?', $newCondition, $sor);
			}
//                        $aTeX[$type][] = $sor;
			// lezáró eset
			$finalArray = explode('%>', $sor);
			for ($i = 1; $i < count($finalArray); $i = $i + 2) {
			    $str = $finalArray[$i];
			    $tmpArray = explode('<!>', $str);
			    $j = 0; while (is_array($aTeX['finalCase']["$j".$type])) $j++;
			    $newCondition = "$j".$type;
			    $aTeX['finalCase'][$newCondition] = array(true => $tmpArray[0], false => $tmpArray[1]);
			    $sor = str_replace($str.'%>', $newCondition, $sor);
			}
                        $aTeX[$type][] = $sor;
                    }
                }
            }
        }
    }

    function _array2text($type, $id, $mit, $mire, $aTeX, $ADAT, $flag = null) {
        $ret = '';
        if (is_null($id)) $A = $ADAT[$type];
        else $A = $ADAT[$type][$id];

        // A cserélendő attribútumok
        if (is_array($A)) foreach ($A as $attr => $value) {
            if (!is_array($value)) {
		if (true || !is_bool($value)) { // feltételes szövegrészek külön kezelendők ??? Miért is? Az általánosabb feltételes kiíráshoz kell!
            	    if (in_array('%$'.$attr, $mit)) { // A már szereplő mintát felülírjuk!
			$key = array_search('%$'.$attr, $mit);
			$mit[$key] = '%$'.$attr;
            		$mire[$key] = $value;
		    } else {
			$mit[] = '%$'.$attr;
            		$mire[] = $value;
		    }
		}
            }
        }

        // aTeX feldolgozása
        $TeX = $aTeX[$type];
        for ($i = 0; $i < count($TeX); $i++) {
            $sor = $TeX[$i];
            if (substr($sor, 0, 2) == '%{') {
                // Almodul feldolgozása
                $_type = substr($sor, 2, -1);
                if (is_array($A[$_type])) {
                    if (is_null($id)) {
                        foreach ($A[$_type] as $key => $_id) $ret .= _array2text($_type, $_id, $mit, $mire, $aTeX, $ADAT);
                    } else {
			$count = count($A[$_type]); $db = 0;
                        foreach ($A[$_type] as $_id => $_data) {
			    $db++; if ($count == $db) $_flag = 'final'; else $_flag = null;
                            if (!is_array($ADAT[$_type][$_id]) && !is_array($_data)) {
                                echo '<br>HIBA#2!!! '.$_type.':'.$_id.':'.$_data.'<hr />';
//                                return false;
                            } else {
				if (!is_array($ADAT[$_type][$_id])) $ADAT[$_type][$_id] = array();
				elseif (!is_array($_data)) $_data = array();
                        	$ADAT[$_type][$_id] = $ADAT[$_type][$_id] + $_data;
                        	$ret .= _array2text($_type, $_id, $mit, $mire, $aTeX, $ADAT, $_flag);
			    }
                        }
                    }
                } elseif (__DEBUG === true)  { echo '<br>HIBA#3: '.$sor.'<br />'.$_type.':'; var_dump($A[$_type]); echo '<hr />';}
            } else {
                    // Csere - lezáró eset
		    if (strpos($sor, '%>') !== false) foreach ($aTeX['finalCase'] as $attr => $values) {
			$sor = str_replace('%>'.$attr, $values[ $flag === 'final' ], $sor);
		    }
		    // Csete - feltételes kiírás
                    if (strpos($sor, '%?') !== false) foreach ($aTeX['conditional'] as $attr => $values) {
			// Nem csak az adott szintről veszi az értéket, hanem feljebbről is (a feljebbi a meghatározó - ez nem biztos, hogy jó...)
			if ($key = array_search('%$'.$values['orig'], $mit)) $_val = $mire[$key];
			else $_val = $A[$values['orig']];
			$sor = str_replace('%?'.$attr, $values[ $_val==true ], $sor);
			//$sor = str_replace('%?'.$attr, $values[ $A[$values['orig']]==true ], $sor);
		    }
		    // Kiírás
                    $ret .= str_replace($mit, $mire, $sor)."\n";
            }
        }

        return $ret;
    }

    function template2text($templateFile, $ADAT) {

	$mit = $mire = array();
	$aTeX = array('conditional' => array());

	$fp = fopen($templateFile, 'r');
	_template2array($fp, 'base', $aTeX);
	fclose($fp);
	return _array2text('base', null, $mit, $mire, $aTeX, $ADAT);

    }

    function template2file($templateFile, $ADAT) {

	global $policy, $page, $sub, $f;

	$mit = $mire = array();
	$aTeX = array('conditional' => array());

	$fp = fopen($templateFile, 'r');
	// A feldolgozást végző függvény neve
	$sor = fgets($fp, 1024);
	rewind($fp);
	if (substr($sor, 0, 2) == '%!') {
	    list($func,$ext,$opt) = explode(' ', substr(chop($sor), 2));
	    if (!function_exists($func)) unset($func);
	}
	if ($ext=='') $ext = 'txt';
	_template2array($fp, 'base', $aTeX);
	fclose($fp);
	$text = _array2text('base', null, $mit, $mire, $aTeX, $ADAT);
//die();
	if ($text === false) return false;
	$success = true;
	if (isset($func)) {
	    $success = $func($text, $ADAT['file'], $opt);
	} else {
	    $fp = fopen(_DOWNLOADDIR."/$policy/$page/$sub/$f/".$ADAT['file'].'.'.$ext, 'w');
	    fputs($fp, $text);
	    fclose($fp);
	}
	if ($success) return $ADAT['file'].".$ext";
	else return false;

    }



?>
