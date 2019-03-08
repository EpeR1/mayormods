<?php
    global $OG;

    require_once('include/modules/portal/share/hirek.php');
    $hirId = readVariable($_GET['hirId'],id);
    if ($hirId>=1) {
	if (_POLICY=='private')
	    $FILTER=array('id'=>$hirId);
	else
	    $FILTER=array('id'=>$hirId,'flag'=>array(1),'class'=>array(1));

	if (defined('__PORTAL_RESTRICT_CID')) 
	    $FILTER['cid'] = explode(',',__PORTAL_RESTRICT_CID);

	$ADAT['hirek'] = getHirek($FILTER);
	/* opengraph attributumok */
	$OG = array('title'=>$ADAT['hirek']['szovegek'][0]['cim'],'getparameter'=>'hirId='.$hirId);
	if ($ADAT['hirek']['szovegek'][0]['pic']!='') {
	    $OG['image'] = $ADAT['hirek']['szovegek'][0]['pic'];
	}
    }


?>
