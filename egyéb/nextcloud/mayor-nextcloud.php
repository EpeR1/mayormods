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
$m2n['verbose'] = 3 ;  

$occ_path = "/var/www/nextcloud/";
$occ_user = "www-data";

// Le kell cserélni az ékezetes betűket, mert a Vezetéknév.Keresztnév nem POSIX kompatibilis.
$search = array( 'á', 'ä', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', 'Á', 'Ä', 'É', 'Í', 'Ó', 'Ö', 'Ő', 'Ú', 'Ü', 'Ű');	// egyelőre csak a magyar betűket ismeri
$replace = array( 'aa', 'ae', 'ee', 'ii', 'oo', 'oe', 'ooe', 'uu', 'ue', 'uue', 'Aa', 'Aae', 'Ee', 'Ii', 'Oo', 'Oe', 'Ooe', 'Uu', 'Ue', 'Uue');

$log['verbose'] = $m2n['verbose'];
if(@$argv[1] == "--loglevel" and is_numeric($argv[2])){$log['verbose'] = $argv[2];}


if (function_exists('mysqli_connect') and PHP_MAJOR_VERSION >= 7) { //MySQLi (Improved) és php7  kell!

    function db_connect(array $db){
        global $log;
        if ($log['verbose'] > 0 ){  echo "***\tAdatbázis kapcsolódás. (m2n_db=".$db['m2n_db'].")\n"; }
        $l = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['m2n_db'],$db['port']);
        if(!$l){
            if ($log['verbose'] > 0 ){echo "*\tAdatbázis kapcsolat újrapróbálása... (m2n_db=) hiba:".mysqli_connect_errno()."\n";}
            $db_old = $db['m2n_db'];
            $db['m2n_db'] = "";
            $l = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['m2n_db'],$db['port']);
            if(!$l){
                echo "\n**** Sikertelen kapcsolódás! **** (m2n_db=".$db['m2n_db'].") info:".mysqli_get_host_info($l)."\n";
                return null;
            } else{
                if ($log['verbose'] > 4 ){ echo "*\tSikeres kapcsolódás. (m2n_db=".$db['m2n_db'].") info:".mysqli_get_host_info($l)."\n";}
                if ($log['verbose'] > 0 ){ echo "***\tAdatbázis létrehozása: ".$db_old." ...\n";}
                mysqli_set_charset($l, "utf8");
                mysqli_query($l, "SET NAMES utf8 COLLATE utf8_general_ci;" );
                script_install($l);
                return $l;
            }
        } else {
            if ($log['verbose'] > 4 ){ echo "*\tSikeres kapcsolódás. (m2n_db=".$db['m2n_db'].") info:".mysqli_get_host_info($l)."\n"; }
            mysqli_set_charset($l, "utf8");
            mysqli_query($l, "SET NAMES utf8 COLLATE utf8_general_ci;" );
            if(mysqli_query($l, "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register;" ) == FALSE ){
                script_install($l);
            }
            return $l;
        }
    }
// bezár: mysqli_close($link);

    function script_install($link){
        global $db,$log;
        $q = "CREATE DATABASE IF NOT EXISTS ".$db['m2n_db']." DEFAULT COLLATE 'utf8_general_ci'; ";
        if ($log['verbose'] > 0 ){ echo "M2N -> \t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 0 ){ echo "*\tAz ".$db['m2n_db']." adatbázis sikeresen létrehozva.\n"; }
        }
        $q = "CREATE TABLE IF NOT EXISTS ".$db['m2n_db'].".".$db['m2n_prefix']."register (
                account VARCHAR(64) NOT NULL COLLATE 'utf8_bin', 
                status ENUM('active','disabled','forbidden','deleted') NULL DEFAULT 'active' COLLATE 'utf8_bin',
                PRIMARY KEY (account)) 
                COLLATE='utf8_general_ci' 
                ENGINE=InnoDB;";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 0 ){ echo "*\tAz ".$db['m2n_db'].".".$db['m2n_prefix']."register (nextcloud-register) tábla sikeresen létrehozva.\n";}
        }
    }
    
    function nxt_register_userlist($link){	//akiket a script hozott létre
        global $db,$log;
        $q = "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE STATUS != 'forbidden'; ";    
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret['account'][] = $row['account'];
                $ret['status'][] = $row['status'];
            }
            mysqli_free_result($r);
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-feljegyzések lekérdezése.\n"; }
            return $ret;
        } else {
            echo "\nM2N -> \t**** Adatbázislekérdezési hiba! ****\n";
        }
    }
    
    function nxt_register_forbiddenlist($link){      //akiket a rendszergazda kitiltott
        global $log,$db;
        $q = "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE STATUS = 'forbidden'; ";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            $ret = array();
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret[] = $row['account'];
            }
            mysqli_free_result($r);
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-letiltások feljegyzésének lekérdezése.\n"; }
            return $ret;
        } else {
            echo "\nM2N ->\t**** Adatbázislekérdezési hiba! ****\n";
        }
    }
    
    function nxt_register_useradd($link, $account){	// feljegyzi az általa létrehozott felhasználókat
        global $log,$db;
        $q = "INSERT INTO ".$db['m2n_db'].".".$db['m2n_prefix']."register (account) VALUES ('".$account."')";
        if ($log['verbose'] > 5 ){ echo "M2N -> \t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-hozzáadás feljegyzése.\n"; }
        } 
    }

    function nxt_register_userena($link, $account){	// az engedélyezetteket
        global $db,$log;
        $q = "UPDATE ".$db['m2n_db'].".".$db['m2n_prefix']."register SET status='active' WHERE account='".$account."'";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-engedélyezés feljegyzése.\n" ;}
        }
    }

    function nxt_register_userdel($link, $account){	// a törölteket
        global $db,$log;
        $q = "DELETE FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE account='".$account."' ";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 5 ){ echo "*\tFelhasználó-törlés feljegyzése.\n"; }
        }
    }
    
    function nxt_register_userdis($link, $account){	// a letiltottakat
        global $m2n,$db,$log;
        $q = "UPDATE ".$db['m2n_db'].".".$db['m2n_prefix']."register SET status='disabled' WHERE account='".$account."'";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 5 ){ echo "*\tFelhasználó-letiltás feljegyzése.\n"; }
        }
    }

    
    

    function user_add($userAccount, $fullName){		// létrehoz egy felhasználót a Nextcloud-ban
        global $occ_path,$occ_user,$m2n,$log;
//  	export OC_PASS=ErősJelszó123; su -s /bin/sh www-data -c 'php web/occ user:add --password-from-env  --display-name="Teszt Tamás" --group="csop" t.tamas'
        if(strlen($userAccount) > 64 or strlen($fullName) > 64){
            echo "\n******** Hiba: A felahsználónév, vagy a \"teljes név\" hosszabb, mint 64 karakter! ********\n";
        } else {
            $e = "export OC_PASS=".$m2n['default_passw']."; su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:add 	\
                 --password-from-env --display-name=\"$fullName\" --group=\"".$m2n['mindenki_csop']."\" $userAccount'" ;
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);
        }
    }

    function user_del($userAccount){	// kitöröl vagy letilt egy felhasználót a Nextcloud-ban
	global $occ_path,$occ_user,$log;
        $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:info $userAccount --output=json'";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        $last_login = json_decode(shell_exec($e),true)['last_seen'] ;
        if($last_login == "1970-01-01T00:00:00+00:00" ){	
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:delete $userAccount'";		// Ha még soha nem lépett be
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);											// akkor törölhető
        } else {
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:disable $userAccount'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);											// különben csak letiltja
        }
        
    }
    
    function user_dis($userAccount){	// letiltja a felhasználót a Nextcloud-ban
        global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:disable $userAccount'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);
    }

    function user_ena($userAccount){	// engedélyezi
        global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:enable $userAccount'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);   
    }


    function nxt_group_list() {		// Csoportok listázása a Nextcloud-ból
            global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:list --output=json'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            return (array)json_decode(shell_exec($e),true);
    }
    
    function nxt_user_list() {		// Felhasználók listázása a Nextcloud-ból
            global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:report | grep \"total\" | sed -e \"s/[^0-9]//g\" | tr -d \"[:blank:]\n\" '";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            $num = shell_exec($e); 
            $num = $num + 100; 	// Biztos-ami-biztos, a nextcloud rejtett hibái miatt...
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:list --limit $num --output=json'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            return (array)json_decode(shell_exec($e),true);
    }
    
    function nxt_user_lastlogin($userAccount){ 	// legutóbbi belépés lekérdezése
        global $occ_path,$occ_user,$log;
        $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:info $userAccount  --output=json'";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        return  json_decode(shell_exec($e),true)['last_seen'] ;
    }

    
    function user_set($userAccount, array $params){	//beállítja az e-mailt, quota-t, nyelvet a kapott értékekre
        global $occ_path,$occ_user,$log;        
        if(isset($params['quota']))
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:setting $userAccount files quota \"".$params['quota']."\"'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec( $e );
        if(isset($params['email']))  
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:setting $userAccount settings email \"".$params['email']."\"'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec( $e );
        if(isset($params['lang']))
            $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:setting $userAccount core lang \"".$params['lang']."\"'";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);
    }
    
    function group_add($groupName){ 	//Új csoport létrehozása a Nextcloud-ban 
        global $link,$db,$log;
        if(strlen($groupName) > 64){	//mivel (egyelőre) nics erre 'occ' parancs, ezért közvetlenül kell...
            echo "\n****** Hiba: a csoportnév nagyobb, mint 64 karakter!! ******\n";
        } else {
            $q = "INSERT IGNORE INTO ".$db['nxt_dbname'].".".$db['nxt_prefix']."groups (gid) VALUES ('".$groupName."'); ";
            if ($log['verbose'] > 5 ){ echo "NXT ->\t".$q."\n"; }
            if(mysqli_query($link, $q) !== TRUE ) echo "\nNXT -> \t****** Csoport létrehozási hiba. (adatbázis) ******\n";
        }
    }
    
    function group_del($groupName){	// Csoport törlése a Nextcloud-ból
        global $occ_user,$occ_path,$db,$link,$log,$m2n;
        $grp = nxt_group_list();
        if(isset($grp[$groupName])){	// Mivel erre még nincs hivatalos "occ" parancs, ezért közvetlenül kell...
            foreach($grp[$groupName] as $key => $user){
                $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:removeuser \"$groupName\" $user'";
                if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
                shell_exec($e);
                if ($log['verbose'] > 1 ){ echo "*--\t\tTörölve".po(" ($user) a: $groupName",$m2n['csoportnev_hossz']+5,1)."\t csoportból.\n"; }
            }
            $q = "DELETE FROM ".$db['nxt_dbname'].".".$db['nxt_prefix']."groups WHERE gid='".$groupName."'; " ;
            if ($log['verbose'] > 5 ){ echo "NXT ->\t".$q."\n"; }
            if(mysqli_query($link, $q) !== TRUE ) echo "\n NXT -> \t****** csoport törlési hiba. (adatbázis) ******\n";
        }
    }

    function group_user_add($groupName, $userAccount){	// Hozzáad egy felhasználót egy csoporthoz a Nextcloud-ban
        global $occ_user, $occ_path,$log;
        $e = "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:adduser \"$groupName\" $userAccount'";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        shell_exec($e);
    }

    function group_user_del($groupName, $userAccount){	// Kitöröl egy felhasználót egy Nextcoud csoportból
        global $occ_user, $occ_path,$log;
        $e =  "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:removeuser \"$groupName\" $userAccount'";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        shell_exec($e);
    }

    function po($inp,$ll,$dir){
            while(grapheme_strlen($inp) < $ll){
                if($dir == 0){
                    $inp = " ".$inp." ";
                } else if($dir == 1){
                    $inp = $inp." ";
                } else if ($dir == -1){
                    $inp = " ".$inp;
                }
            }
        return $inp;
    }

    function get_mayor_tankor($link){				// A tankörök neveinek lekérdezése a mayorból
        global $m2n,$log;
        $ret = array();
        $req_oszt = "'#'";
        foreach($m2n['min_osztalyok'] as $key => $val){		//A megadott konkrét osztályokra
            $req_oszt .= ",'$val'";
        }
//Létező összes tankör:
/*        $q = "SELECT tankorId, TRIM(BOTH ' '
            FROM CONCAT('".$m2n['csoport_prefix']."',tankorNev)) AS tankorNev
            FROM intezmeny_".$m2n['isk_rovidnev'].".tankorSzemeszter
            WHERE tanev = (
            SELECT tanev
            FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
            WHERE statusz = 'aktív'
            GROUP BY tanev) AND szemeszter = (
            SELECT szemeszter
            FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
            WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND CURRENT_DATE() <= zarasDt);        ";
*/             
//csak a megadott évfeolyamokhoz kötődő tankörök:
        $q = "SELECT tanev FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter WHERE statusz = 'aktív' GROUP BY tanev; ";
        if ($log['verbose'] > 5 ){ echo "MAY ->\t".$q."\n"; }
        if( ($r = mysqli_query($link, $q)) !== FALSE ){
            $ev = mysqli_fetch_array($r, MYSQLI_ASSOC);
            $q = "SELECT tankorId, TRIM(BOTH ' '
                FROM CONCAT('".$m2n['csoport_prefix']."',tankorNev)) AS tankorNev
                FROM intezmeny_".$m2n['isk_rovidnev'].".tankorSzemeszter
                WHERE tanev = (
                SELECT tanev
                FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
                WHERE statusz = 'aktív'
                GROUP BY tanev) AND szemeszter = (
                SELECT szemeszter
                FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
                WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND CURRENT_DATE() <= zarasDt) AND tankorId IN(
                SELECT tankorId
                FROM intezmeny_".$m2n['isk_rovidnev'].".tankorOsztaly
                WHERE osztalyId IN (
                SELECT osztalyId
                FROM naplo_".$m2n['isk_rovidnev']."_".$ev['tanev'].".osztalyNaplo
                WHERE evfolyamJel >= ".$m2n['min_evfolyam']."  OR osztalyJel IN(".$req_oszt.") 
                ORDER BY osztalyId)
                ORDER BY tankorId ); 
            ";
            if ($log['verbose'] > 5 ){ echo "MAY ->\t".$q."\n"; }
            if(( $r = mysqli_query($link, $q)) !== FALSE ){
                while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                    $ret[] = $row;
                }
                mysqli_free_result($r);
                return $ret;
            } else {
                echo "\nMAY ->\t ******** Mayor_napló (tankör)lekérdezési hiba. (adatbázis) ********\n";
            }
        }
    }

    
    function get_mayor_tanar($link){		// A tanárok lekérdezése a mayorból
        global $m2n,$log;
        $ret = array();
        $q = "SELECT userAccount, email, tanar.tanarId, tankorTanar.tankorId, TRIM(BOTH ' ' 
            FROM CONCAT_WS(' ',viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS fullName, TRIM(BOTH ' '
            FROM CONCAT('".$m2n['csoport_prefix']."',tankorNev)) AS tankorNev
            FROM intezmeny_".$m2n['isk_rovidnev'].".tanar, mayor_private.accounts, intezmeny_".$m2n['isk_rovidnev'].".tankorTanar, intezmeny_".$m2n['isk_rovidnev'].".tankorSzemeszter
            WHERE accounts.studyId = tanar.oId AND statusz != 'jogviszonya lezárva' AND tanar.beDt <= CURRENT_DATE() AND (CURRENT_DATE() <= tanar.kiDt 
            OR tanar.kiDt IS NULL) AND tanar.tanarId = tankorTanar.tanarId AND tankorTanar.beDt <= CURRENT_DATE() AND CURRENT_DATE() <= tankorTanar.kiDt 
            AND tankorTanar.tankorId = tankorSzemeszter.tankorId AND tankorSzemeszter.tanev = (
            SELECT tanev
            FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
            WHERE statusz = 'aktív'
            GROUP BY tanev) AND szemeszter = (
            SELECT szemeszter
            FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
            WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND CURRENT_DATE() <= zarasDt)
            ORDER BY userAccount ;
        ";
        if ($log['verbose'] > 5 ){ echo "MAY ->\t".$q."\n"; }  
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret[] = $row;
            }
            mysqli_free_result($r);
            return $ret;
        } else {
            echo "\nMAY ->\t ******** Mayor_napló (tanár)lekérdezési hiba. (adatbázis) ********\n";
        }
    }
    
    
    function get_mayor_diak($link){	// diákok lekérdezése
        global $m2n,$log;
        $ret = array();
        $req_oszt = "'#'";
        foreach($m2n['min_osztalyok'] as $key => $val){         //A megadott konkrét osztályokra
            $req_oszt .= ",'$val'";
        }
        $q = "SELECT tanev FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter WHERE statusz = 'aktív' GROUP BY tanev; ";
        if ($log['verbose'] > 5 ){ echo "MAY ->\t".$q."\n"; }
        if( ($r = mysqli_query($link, $q)) !== FALSE ){
            $ev = mysqli_fetch_array($r, MYSQLI_ASSOC);
            $q = "SELECT userAccount, email, diak.diakId, tankorDiak.tankorId, TRIM(BOTH ' '
                FROM CONCAT_WS(' ',viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS fullName, TRIM(BOTH ' '
                FROM CONCAT('".$m2n['csoport_prefix']."',tankorNev)) AS tankorNev
                FROM intezmeny_".$m2n['isk_rovidnev'].".diak, mayor_private.accounts,intezmeny_".$m2n['isk_rovidnev'].".tankorDiak, intezmeny_".$m2n['isk_rovidnev'].".tankorSzemeszter
                WHERE diak.diakId IN (
                SELECT diakId
                FROM intezmeny_".$m2n['isk_rovidnev'].".osztalyDiak
                WHERE osztalyId IN (
                SELECT osztalyId
                FROM naplo_".$m2n['isk_rovidnev']."_".$ev['tanev'].".osztalyNaplo
                WHERE evfolyamJel >= ".$m2n['min_evfolyam']." OR osztalyJel IN(".$req_oszt.") 
                ORDER BY osztalyId)
                ORDER BY diakId) AND diak.statusz != 'jogviszonya lezárva' AND diak.statusz != 'felvételt nyert' AND diak.oId = accounts.studyId 
                AND tankorDiak.diakId = diak.diakId AND tankorDiak.beDt <= CURRENT_DATE() AND (tankorDiak.kiDt >= CURRENT_DATE() OR tankorDiak.kiDt IS NULL) 
                AND tankorSzemeszter.tankorId = tankorDiak.tankorId AND tankorSzemeszter.tanev = (
                SELECT tanev
                FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
                WHERE statusz = 'aktív'
                GROUP BY tanev) AND tankorSzemeszter.szemeszter = (
                SELECT szemeszter
                FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
                WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND CURRENT_DATE() <= zarasDt)
                ORDER BY userAccount ;
            ";
            if ($log['verbose'] > 5 ){ echo "MAY ->\t".$q."\n"; }
            if(( $r = mysqli_query($link, $q)) !== FALSE ){
//                 mysqli_fetch_array($r, MYSQLI_ASSOC);
                  while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) { 
                      $ret[] = $row;
                  }
                  mysqli_free_result($r);
                  return $ret;
            } else {
                echo "\nMAY ->\t ******** Mayor_napló (diák)lekérdezési hiba. (adatbázis) ********";
            }
        } else {
            echo "\nMAY ->\t ******** Mayor_napló (diák)lekérdezési hiba. (adatbázis) ********"; 
        }  
    
    }
    
    
//-------------------------------------------------------------------------------------------------------------------------------    

    if($log['verbose'] > 0) { echo "\n\n######## Mayor-Nextcloud Script ########\n\n\n"; }

    if(($link = db_connect($db)) == FALSE){			//csatlakozás
        echo "\n******** MySQL (general) kapcsolat hiba. ********\n";
        echo "\n******** Script leáll... ********\n";
        die();
        }
    $link2 = $link;

    group_add($m2n['mindenki_csop']);				// A "mindenki" csoport hozzáadása 


    if(isset($db['mayor_user']) and isset($db['mayor_pass']) and isset($db['mayor_host']) or isset($db['mayor_port'])) 
    {
        $db['user'] = $db['mayor_user'];			//ha a mayor egy másik szerveren lenne
        $db['pass'] = $db['mayor_pass'];
        $db['host'] = $db['mayor_host'];
        $db['port'] = $db['mayor_port'];
        if(($link2 = db_connect($db)) == FALSE){
            echo "\n******** MySQL (mayor) kapcsolat hiba. ********\n";
            echo "\n******** Script leáll... ********\n";
            die();
        } else {
            if ($log['verbose'] > 0 ){ echo "***\tMayor DB connect.\n"; }
        }
    }


//------------------------------------------------------------------------------------------------------------------------------

// Létrehozza az új Mayor tanköröket
    if ($log['verbose'] > 0 ){ echo "\n***\tCsoportok egyeztetése.\n";}
    $tankorok = get_mayor_tankor($link2);
    $nxt_csop = nxt_group_list();
    foreach($tankorok as $key => $val){								//Végignézi a tankörök szerint
        foreach($nxt_csop as $key2 => $val2){							// 
            if($key2 == $val['tankorNev']){							//Már van ilyen (tankör)nevű csoport
                if ($log['verbose'] > 3 ){ echo "  -\t Csoport:".po("\t".$val['tankorNev'],$m2n['csoportnev_hossz'],1)."-\tok.\n";}
                break;
            }
        }
        unset($nxt_csop[$val['tankorNev']]);							//Megvizsgálva, többször már nem kell dönteni róla. 
        if($key2 != $val['tankorNev']){ 							//Ha nincs ilyen (tankör)nevű csoport
            group_add($val['tankorNev']);   							//Akkor létrehozza
            if ($log['verbose'] > 2 ){ echo "* -\t Új csoport:".po("\t".$val['tankorNev'],$m2n['csoportnev_hossz'],1)."-\thozzáadva.\n";}
         }
    }
// A megszűnt tanköröket-csoportokat kitörli 
    foreach($nxt_csop as $key => $val){           
        if(substr($key, 0, strlen($m2n['csoport_prefix'])) === $m2n['csoport_prefix'] ){	//Csak a "prefix"-el kezdődő nevűekre.
            group_del($key);									//elvégzi a törlést
            if ($log['verbose'] > 1 ){ echo "** -\t Korábbi csoport:".po("\t$key",$m2n['csoportnev_hossz'],1)."\t eltávolítva.\n";}
        } else {
            if ($log['verbose'] > 5 ){ echo " ---\t Külső csoport:".po("\t$key",$m2n['csoportnev_hossz'],1)."\t békén hagyva.\n";}
        }	// Figyelem! A csoport prefix-szel: "(tk) " kezdődő csoportokat magáénak tekinti, automatikusan töröli!
    }	// 	Akkor is, ha az külön, kézzel lett létrehozva.



//-------------------------------------------------------------------------------------------------------------------------------
// Felhasználónevek egyeztetése
    if ($log['verbose'] > 0 ){ echo "\n***\tFelhasználók egyeztetése.\n";}
    $mayor_user = array_merge( get_mayor_tanar($link2), get_mayor_diak($link2) );		//tanár, diák
    $mayor_user = array_merge($mayor_user, array(array('userAccount' => null, 'fullName' => null, 'tankorNev' => null,)) ); //strázsa a lista végére
    $nxt_user = nxt_user_list();
    $nxt_group = nxt_group_list();
    $nxt_registered = nxt_register_userlist($link);
    $m2n_forbidden = nxt_register_forbiddenlist($link);
    if ($log['verbose'] > 3 ){ echo "\n";}

    foreach($mayor_user as $key => $val){
                                                                                //Lecseréli az ékezetes betűket a felhasználónévből
        $mayor_user[$key]['userAccount'] = str_replace($search, $replace, $val['userAccount']);  // (pl: Á->Aa, á->aa, ...)
        if(in_array($val['userAccount'], $m2n_forbidden) ){			//Ha a nyilvántartásban "forbidden"-ként szerepel, 
            unset($mayor_user[$key]);                                   	// akkor nem foglalkozik vele tovább.
        }
    }

    $curr = "";
    $tankorei = array();
    foreach($mayor_user as $key => $val){					//Végignézi a mayorból kinyert lista alapján.
    
        if($curr != $val['userAccount']){   		 			//A következő felhasználó..
            foreach($nxt_user as $key2 => $val2){
                if($curr == $key2){ 						//Már létezik a felhasználó a Nextcloud-ban
                    $log['curr'] = "-\tFelhasználó:".po("\t$curr_n ($curr)",$m2n['felhasznalo_hossz'],1)."--\tok.\n";
                    if ($log['verbose'] > 3 ){ echo " -".$log['curr']; $log['curr'] = "";}
                    if( in_array($curr, $nxt_registered['account'])){
                        if($nxt_registered['status'][array_keys($nxt_registered['account'], $curr)[0]] == 'disabled' ){
                            nxt_register_userena($link, $curr);			//Ha netán le lenne tiltva, akkor engedélyezi,
                            user_ena($curr);					//ha a script tiltotta le.
                        }
                    } else { if ($log['verbose'] > 1 ){ echo "? -\t\tA felhasználó:".po("\t$curr",$m2n['felhasznalo_hossz'],1)."\tnincs benne a nyilvántartásban.\n";} }
                    
                    foreach($nxt_group as $key3 => $val3){			//A tankörök egyeztetése
                        if(in_array($key3, $tankorei) or $key3 == $m2n['mindenki_csop']){ //szerepel-e a felhasználó tankörei között a csoport, vagy a "mindenki" csoport?
                            if( in_array($curr, $val3)){			//Igen, és már benne is van +++
                            
                                 if ($log['verbose'] > 3 ){ echo "  -\t\tBenne van a:".po("\t$key3",$m2n['csoportnev_hossz'],1)."\tcsoportban.\n";} 
                            } else {						//Nincs, most kell beletenni
                                if ($log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tHozzáadva a:".po("\t$key3",$m2n['csoportnev_hossz'],1)."\tcsoporthoz.\n";}
                                group_user_add($key3, $curr);
                            }
                        } else {						//Nem szerepel a tankörei között
                            if(in_array($curr, $val3) and  (substr($key3, 0, strlen($m2n['csoport_prefix'])) === $m2n['csoport_prefix']) ){
                                // korábban benne volt egy tankörben, de már nincs, vagy a hozzátartozó tankörben már nem tanít  => kiveszi
                                if ($log['verbose'] > 1 ){if($log['curr'] !== ""){echo "*".$log['curr'];$log['curr'] = "";} echo  "* -\t\tTörölve a:".po("\t$key3",$m2n['csoportnev_hossz'],1)."\tcsoportból.\n";}
                                group_user_del($key3, $curr);			//egy korábbi tankör lehetett...
                            }
                        }
                    } 
                    break;
                }       
            }
            unset($nxt_user[$curr]);  						//Megvizsgálva, többször már nem kell dönteni róla.
            if($curr != $key2 and $curr != null){				//Nincs még ilyen felhasználó
                if ($log['verbose'] > 2 ){ echo "**-\tFelhasználó:".po("\t$curr_n ($curr)",$m2n['felhasznalo_hossz'],1)."--\tlétrehozva.\n";}
                user_add($curr, $curr_n);					//Akkor hozzá kell adni
                nxt_register_useradd($link, $curr);
                    
                foreach($tankorei as $key3 => $val3){				//Hozzáadja a (tankör)csoportokhoz is egyből.
                    group_user_add($val3,$curr);
                    if ($log['verbose'] > 2 ){ echo "* -\t\tHozzáadva a:".po("\t $val3",$m2n['csoportnev_hossz'],1)."\tcsoporthoz.\n"; }
                }                
                $params['quota'] = $m2n['default_quota'];			// Alapértelmezett kvóta
                $params['lang'] = $m2n['default_lang'];				// Nyelv
                if($curr_e == ""){
                    $params['email'] = $m2n['default_email']; 			// e-mail beállítása
                } else {
                    $params['email'] = $curr_e;					// ha van a mysql-ben e-mail, akkor azt használja
                }
                user_set($curr,$params);					//Alapértelmezett paraméterek érvényesítése
                if ($log['verbose'] > 2 ){ echo "* -\t\tBeállítva:\t"."Qvóta: ".$params['quota']."\tNyelv: ".$params['lang']."\tE-mail: ".$params['email']."\n";}
            }
            
            $tankorei = array(); 						// új ciklus kezdődik
            $curr = $val['userAccount'];					//
            $curr_n = $val['fullName'];						//
            $curr_e = @$val['email'];						//
        }
        $tankorei[] = $val['tankorNev'];					// Egyébként a csoportok (tankörök) összegyűjtése
    }


// A megszűnő felhasználónevek egyeztetése
    if ($log['verbose'] > 0 ){ echo "\n***\tTörlendő/Letiltandó felhasználók egyeztetése.\n";}
    $nxt_registered = nxt_register_userlist($link);
    foreach($nxt_user as $key => $val){						//Benne van a nyilvántartásban,
            if(in_array($key, $nxt_registered['account'])){ 			//vagyis a script adta hozzá korábban
                if( nxt_user_lastlogin($key) == "1970-01-01T00:00:00+00:00" ){	//Még soha nem lépett be = 1970.01.01 ??
                    user_del($key);						//Akkor törli 
                    nxt_register_userdel($link, $key);				//A listáról is
                    if ($log['verbose'] > 1 ){ echo "**-\tFelhasználó:".po("\t$val ($key)",$m2n['felhasznalo_hossz'],1)."--\ttörölve.\n";} 
                } else {
                    user_dis($key);            					//Különben csak letiltja (fájlok ne vesszenek el)
                    nxt_register_userdis($link, $key);				//Feljegyzi a nyilvántartásba
                    if ($log['verbose'] > 1 ){ echo "**-\tFelhasználó:".po("\t$val ($key)",$m2n['felhasznalo_hossz'],1)."--\tletiltva.\n";} 
                }
            }
            // döntési logika:
            // ha benne van a $mayor_user-ben,
            // -    akkor vagy új user, vagy már meglévő, 
            // -    ezért őt kihúzza az $nxt_user listáról, --> megtartja
            // ezután ha valaki még rajta van az $nxt_user listán, az
            // -    vagy más, mayor_naplón kívüli user (rendszergazda vette föl) --> nem nyúl hozzá
            // -    vagy megszűnő, korábbi mayor_napló-s user --> törli (vagy letiltja)
            // ha rajta van a $nxt_registered listán is, és nincs rajta $mayor_user listán 
            // -	akkor őt a script hozta létre régen --> megszűnő, törli (vagy letiltja)
            // (hiszen, ha aktív lenne, rajta lenne a $mayor_user listán, és kihúzta volna a $nxt_user-ből)
    }

// Végül a nyilvántartás kipucolása
    if ($log['verbose'] > 0 ){ echo "\n***\tNyilvántartás ellenőrzése.\n";}
    $nxt_user = nxt_user_list();
    $nxt_registered = nxt_register_userlist($link);
    foreach($nxt_registered['account'] as $key => $val){    //Erre a nextcloud "occ" parancs hibakezelése miatt van szükség
    
        if(@$nxt_user[$val] === null ){
            if ($log['verbose'] > 4 ){ echo "**-\tFelhasználónév:".po("\t($val)",$m2n['felhasznalo_hossz'],1)."--\tkivéve a nyilvántartásból.";}
            nxt_register_userdel($link, $val);
        }
    }

//-------------------------------------------------------------------------------------------------------------------------------

//test
//script_install($link);


    @mysqli_close($link2);
    @mysqli_close($link);
    if ($log['verbose'] > 0 ){echo "kész.\n";} //endline

} else {
    echo "\n\n******** Legalább PHP7 és mysqli szükséges! ********\n\n";
}



?>




