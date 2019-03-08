<?php

    if (_RIGHTS_OK !== true) die();

    global $db_ok, $admin_ok, $group_ok;

if (defined('__NAPLO_INSTALLED') && __NAPLO_INSTALLED === false) {
    if (!$admin_ok) {
	echo 'Nem vagy a <code>naploadmin</code> csoport tagja!</p>';
    }
    if (!$group_ok) {
	echo '<p>Még nem hoztál létre <q>naploadmin</q> csoportot!</p>';
	echo '<p>Hozd létre a <q>Felhasználói adatok/Új csoport</q> menüpont alatt!</p>';
    } elseif (!$db_ok) {
	echo '<p>Létre kell hoznunk a naplo modul alap adatbázisát!</p>';
	echo '<p>Ehhez szükség lesz a MySQL root jelszó megadására.</p>';
	echo '<form method="post" action="">'; // --TODO
	    echo '<input type="hidden" name="action" value="createDatabase" />';
	    echo 'User: ';
	    echo '<input type="text" name="rootUser" value="root" /><br/>';
	    echo 'Jelszó: ';
	    echo '<input type="password" name="rootPassword" value="" /><br/>';
	    echo '<input type="submit" value="OK" />';
	echo '</form>'; // --TODO
    } else {
	echo '<p>Úgy tűnik a modul telepítése kész. Az <code>base/config.php</code>-ben módosítsuk a <code>__NAPLO_INSTALLED</code> értékét true-ra és kész.</p>';
    }
} else {
    echo '<p>Már telepítve!</p>';
}
?>
