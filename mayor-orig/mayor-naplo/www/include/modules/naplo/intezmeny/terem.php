<?php

    function teremAdatModositas($ADAT,$uj=false) {
	
	if ($uj===true) {
	    $q = "INSERT INTO `terem` (`teremId`,`leiras`,`tipus`,`ferohely`,`telephelyId`) VALUES ((SELECT max(teremId)+1 FROM terem AS s),'%s','%s',%u,NULL)";
	    $v = array($ADAT['leiras'], $ADAT['tipus'], intval($ADAT['ferohely']));
	} else { 
	    $q = "UPDATE `terem` SET `leiras`='%s',`tipus`='%s'";
	    $v = array($ADAT['leiras'], $ADAT['tipus']);
	    if (isset($ADAT['ferohely'])) { $q .= ",`ferohely`=%u"; $v[] = $ADAT['ferohely']; }
	    else { $q .= ",`ferohely`=NULL"; }
	    if (isset($ADAT['telephelyId'])) { $q .= ",`telephelyId`=%u"; $v[] = $ADAT['telephelyId']; }
	    else { $q .= ",`telephelyId`=NULL"; }

	    $q .= " WHERE teremId=%u";
	    $v[] = $ADAT['teremId'];
	}

	return db_query($q, array('fv' => 'teremAdatModositas', 'modul' => 'naplo_intezmeny', 'values' => $v));


    }

?>
