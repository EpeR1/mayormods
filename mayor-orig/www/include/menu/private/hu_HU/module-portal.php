<?php

    if (defined('__HIREKADMIN') && __HIREKADMIN === true) {
//	$MENU['portal'] = array(array('txt' => 'Portál', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'));
	$MENU['portal'] = array(array('txt' => 'Kezdőlap', 'url' => 'index.php'));
	$MENU['modules']['portal']['hirek'] = array(array('txt'=>'Hírek szerkesztése','url'=>'index.php?page=portal&sub=hirek&f=hirekAdmin'));
	$MENU['modules']['portal']['sub']['hirek'] = array(
	    'hirekAdmin'=>array(array('txt' => 'Hírek')),
	    'egyhir'=>array(array('txt' => 'Új hír')),
//	    array('txt' => 'Új kérdés', 'url' => 'index.php?page=portal&sub=kerdoiv&f=kerdoivAdmin')
	);
	$MENU['modules']['portal']['kerdoiv'] = array(
	    array('txt' => 'Új kérdés', 'url' => 'index.php?page=portal&sub=kerdoiv&f=kerdoivAdmin')
	);

	//if ($page != 'portal') 
	//    $NAV[1][] = array('page'=>'portal', 'sub' => 'hirek');
    } else { 
	/* Mégse rakjuk ki a többieknek */
	// $MENU['portal'] = array(array('txt' => 'Hír beküldése', 'url' => 'index.php?page=portal&sub=hirek&f=egyhir'));
    }

//    if (memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
//	$NAV[1][] = array('page'=>'session');
//    } else {
//	$NAV[1][] = array('page'=>'session','f'=>'changeMyPassword');
//    }

    //if ($page != 'naplo') $NAV[1][] = array('page' => 'naplo');



if ($page=='portal') {
    if (is_array($MENU['modules']['portal']['sub'][$sub])) foreach ($MENU['modules']['portal']['sub'][$sub] as $_f => $M) {
        $NAV[2][] = array('page' => 'portal', 'sub' => $sub, 'f' => $_f);
    } elseif (is_array($MENU['modules']['portal']))  foreach ($MENU['modules']['portal'] as $_sub => $M)    {
        if ($_sub != 'sub') $NAV[2][] = array('page' => 'portal', 'sub' => $_sub);
    }
}



?>
