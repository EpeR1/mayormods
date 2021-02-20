<?php
  if (_RIGHTS_OK !== true) die();

    if (!__HIREKADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    }

    require_once('include/modules/portal/share/hirek.php');

    if ($action=='kategoriaSzerkeszt') {
	$_id = readVariable($_POST['id'],'id');
	if (is_numeric($_id)) {
	    $_leiras = readVariable($_POST['leiras'],'string');
	    $_precode = readVariable($_POST['precode'],'html');
	    $_postcode = readVariable($_POST['postcode'],'html');
	    $q = "INSERT INTO kategoriak (id,leiras,precode,postcode) VALUES (%u,'%s','%s','%s')";
	    $v = array($_id,$_leiras,$_precode,$_postcode);
	    db_query($q,array('modul'=>'portal','values'=>$v));
	}
	$KATEGORIAIDK = readVariable($_POST['kategoriaId'],'id');
	for ($i=0; $i<count($KATEGORIAIDK); $i++) {
	    $_id = $KATEGORIAIDK[$i];
	    $_leiras = readVariable($_POST['leiras_'.$_id],'string');
	    $_precode = readVariable($_POST['precode_'.$_id],'string');
	    $_postcode = readVariable($_POST['postcode_'.$_id],'string');
	    $q = "UPDATE kategoriak SET leiras='%s',precode='%s',postcode='%s' WHERE id=%u";
	    $v = array($_leiras,$_precode,$_postcode,$_id);
	    db_query($q,array('modul'=>'portal','values'=>$v));
	}
	$KATEGORIAIDK = readVariable($_POST['kategoriaTorlendo'],'id');
	for ($i=0; $i<count($KATEGORIAIDK); $i++) {
	    $_id = $KATEGORIAIDK[$i];
	    $q = "DELETE FROM kategoriak WHERE id=%u";
	    $v = array($_id);
	    db_query($q,array('modul'=>'portal','values'=>$v));
	}
    } 


    $ADAT = getHirek(array('all'=>true)); // minden nyelvű hír
    $ADAT['kategoriak'] = getKategoriak();
    $ADAT['kategoriaId2txt'] = reindex($ADAT['kategoriak'],array('id'));
dump($ADAT['kategoriaId2txt']);
?>
