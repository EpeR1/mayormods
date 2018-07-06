<?php
/*
    Module:	base/password
*/

$ALERT_MSG['force_pw_update']     = 'A jelszó megváltoztatása kötelező!';
$ALERT_MSG['pw_change_failed']    = 'A jelszóváltoztatás nem sikerült!';
$ALERT_MSG['pw_change_disabled']    = 'A jelszóváltoztatás tiltott!';
$ALERT_MSG['pw_change_success']    = 'A jelszóváltoztatás sikerült!';
$ALERT_MSG['pw_not_match']     = 'A jelszó és a megerősítés nem egyezik!';
$ALERT_MSG['pw_not_changed']     = 'A jelszó nem változott. Az új jelszónak különböznie kell a régi jelszótól!';

// Jelszóváltoztatás előtt mindenképp authentikáció van - kellenek a hibaüzenetek
if (file_exists('include/alert/hu_HU/module-auth.php')) {
    require('include/alert/hu_HU/module-auth.php');
}

?>
