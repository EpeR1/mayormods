#!/usr/bin/php
<?php 
$db = array();
$m2n = array();

$db['host'] = "localhost";
$db['port'] = "3306";
$db['user'] = "root";
$db['pass'] = "";
$db['m2n_db'] = "mayor_to_nextcloud";
$db['m2n_prefix'] = "m2n_";
$db['nxt_dbname'] = "Nextcloud";
$db['nxt_prefix'] = "oc_";
//$db['mayor_host'] = "";
//$db['mayor_port'] = "";
//$db['mayor_user'] = "";
//$db['mayor_pass'] = "";

$m2n['min_evfolyam'] = 1;
$m2n['isk_rovidnev'] = "rovid";
$m2n['csoport_prefix'] = "(tk) ";
$m2n['default_email'] = "indulo@iskola.hu";
$m2n['default_passw'] = "EHYmGktzrdfS7wxJR6DFqxjJ";
$m2n['default_quota'] = "10GB";
$m2n['min_osztalyok'] =  array(); 	//pl:  array('9.a','11.a');
$m2n['csoportnev_hossz'] = 40;
$m2n['felhasznalo_hossz'] = 45;
$m2n['default_lang']  = "hu";
$m2n['mindenki_csop'] = "naplós_felhasználók";
$m2n['zaras_tartas'] =  "2018-06-14";	// Ha nem kell, akkor állítsd át "1970-01-01"-re.
$m2n['verbose'] = 3 ;  

$occ_path = "/var/www/nextcloud/";
$occ_user = "www-data";


?>
