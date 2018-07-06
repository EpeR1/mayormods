<?php
/*
    Module: naplo
*/

    if (file_exists("lang/$lang/date/names.php")) {
        require("lang/$lang/date/names.php");
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/date/names.php')) {
        require('lang/'._DEFAULT_LANG.'/date/names.php');
    }

    $aHetNapjai = array(
	_MONDAY,_TUESDAY,_WEDNESDAY,_THURSDAY,_FRIDAY,_SATURDAY,_SUNDAY
    );
    define('AHETNAPJAI',json_encode($aHetNapjai));

    $Honapok = array(
	_JANUARY,_FEBRUARY,_MARCH,_APRIL,_MAY,_JUNE,
	_JULY,_AUGUSTUS,_SEPTEMBER,_OCTOBER,_NOVEMBER,_DECEMBER
    );
    define('AZEVHONAPJAI',json_encode($Honapok));

    function dateToString($dt) {

	global $Honapok;

	list($ev,$ho,$nap) = explode('-', $dt);
	return $ev.'. '.kisbetus($Honapok[$ho-1]).' '.intval($nap).'.';

    }

?>
