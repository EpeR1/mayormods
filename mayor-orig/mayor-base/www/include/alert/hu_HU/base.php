<?php
/*
    Module: base
*/

//
// Az $_SESSION['alert'] két részből áll: típus:hiba
// típus: alert
//	  message
//	  page
//	  info
//

$ALERT_MSG = array(

    'default'              => '',
    'change_success'       => 'Az adatmódosítás sikeresen megtörtént!',
    'page_missing'         => 'A keresett oldal nem található a szerveren.',
    'sql_failure'        => 'SQL adatbázis hiba!',
    'sql_warning'	   => 'SQL figyelmeztetés!',
    'sql_connect_failure'=> 'SQL adatbázis csatlakozási hiba!',
    'sql_query_failure'  => 'SQL lekérdezési hiba!',
    'sql_select_db_failure'  => 'A %0% modul %1% SQL adatbázisának kiválasztása nem sikerült!',
    'auth_failure'         => 'Azonosítatlan felhasználó! Az adott hozzáférési szinten nem hitelesítetted magad!',
    'bad_pw'               => 'Hibás jelszó!',
    'account_expired'	   => 'A jelszó érvényessége %0% napja lejárt!',
    'account_warning'	   => 'A jelszó %0% nap múlva lejár!',
    'warn_account_disable' => 'A felhasználói fiók %0% nap múlva letiltásra kerül!',
    'empty_field'          => 'Üres adatbeviteli mező! Egy kötelező paraméter nincs megadva!', // login
    'session_alter_needed' => 'Nem egyező hash-hossz az adatbázisban! (%0% &rarr; %1%)',
    'insufficient_access'  => 'Jogosulatlan hozzáférés!',
    'wrong_data'		   => 'Hibás/rossz adat!',
    'wrong_page'		   => 'Hibás oldalhivatkozás!',
    'deadline_expired'	   => 'A módosítási határidő lejárt!',
    'pw_change_success'	   => 'A jelszóváltoztatás sikeresen megtörtént',
    'file_not_found'	   => 'A file nem található!',
    'success'              => 'A művelet sikeresen befejeződött!',
    'not_changed'          => 'Nem történt adatváltozás.',
    'unknown_type'	   => 'Ismeretlen tipus',
    'config_error'		=> 'Konfigurációs hiba',
    'not_valid_form'	=> 'Érvénytelen űrlapadatok! A feldolgozás megszakadt.',
    'raw' => 'Részletek:',
);

// + auth

$ALERT_MSG['no_account']          = 'Rossz azonosítót adtál meg!';
$ALERT_MSG['account_disabled']    = 'A felhasználói fiók letilrásra került, ezzel az azonosítóval nem lehet belépni! További információkért fordulj a rendszergazdához!';
$ALERT_MSG['force_pw_update']     = 'A jelszó megváltoztatása kötelező!';
$ALERT_MSG['cookie']              = 'Lejárt a munkamenet vagy nem engedélyezett a sütik használata.';

?>
