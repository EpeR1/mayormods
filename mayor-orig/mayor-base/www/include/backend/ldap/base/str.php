<?php
/*
    Module: useradmin

    function date2timestamp($date)
    function timestamp2date($stamp)
    !! -- function ldap_cn_cmp($a,$b)  -- !! Kell ez?
    !! -- function tanar_cn_cmp($a,$b) -- !! Használjuk ezt?

    // - fuggoseg - //    require_once('include/share/ldap/attrs.php');

*/

// -------------------------------------
// Date2Timestamp
// -------------------------------------

    function date2timestamp($date) {
	$date = str_replace('-','',$date);
	$date = str_replace('.','',$date).'010101Z';
	if (strlen($date) == 15) return $date;
	else return '';
    }

// -------------------------------------
// Timestamp2Date
// -------------------------------------

    function timestamp2date($stamp) {
        $date = substr($stamp,0,4).'-'.substr($stamp,4,2).'-'.substr($stamp,6,2);
        if (strlen($date) == 10) return $date;
        else return '';
    }

/*
// ---------------------------------------------------------------------------
//  LDAP eredmény elemeinek összehasonlítása cn-alapján (Már latin2-es kódolású!!!)
// ---------------------------------------------------------------------------

    function ldap_cn_cmp($a,$b) {
	return str_cmp($a['cn'][0],$b['cn'][0]);
    }

// ---------------------------------------------------------------------------
//  $TANAROK tömb rendezéséhez (include/naplo/helyettesít.php) (Már latin2-es kódolású!!!)
// ---------------------------------------------------------------------------

    function tanar_cn_cmp($a,$b) {
	return str_cmp($a['cn'],$b['cn']);
    }
*/

?>
