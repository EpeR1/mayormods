<?php
/*
    Module: naplo
*/

    if (_RIGHTS_OK !== true) die();

    global $ADAT, $ho, $_TANEV;
    global $initResult;

    if (__MUNKATERV_OK || $initResult===true) {
	if (__NAPLOADMIN===true) putAdminForm($ADAT);
	else putNapokSzama($ADAT);
	if (
	    isset($ho)
	    && $ho != ''
	    && $_TANEV['statusz'] == 'aktív'
	    && (__FOLYO_TANEV || __NAPLOADMIN)
	) $ADAT['action'] = 'munkatervModositas'; //putNapokForm($ADAT['Napok'], $ADAT['napTipusok'], 'munkatervModositas', $ADAT['Hetek']);
	else $ADAT['action'] = 'honapValasztas'; //putNapokForm($ADAT['Napok'], $ADAT['napTipusok'], 'honapValasztas');
	putNapokForm($ADAT);
    } elseif (__NAPLOADMIN===true) {
	putNapokInit($ADAT, array('hide' => false));
    }

?>
