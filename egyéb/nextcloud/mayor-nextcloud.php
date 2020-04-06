#!/usr/bin/php
<?php 
$db = array();
$m2n = array();
$m2l = array();
////////////////////////////////////////////// Figyelem! az alábbi konfig automatikusan külön fájból töltődik, ha létezik a "mayor-nextcloud.cfg.php" fájl!! /////////////////////////////////
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
$m2n['always_set_diak_quota'] = false;
$m2n['default_quota'] = "10GB";
$m2n['diak_quota']    = "2GB";
$m2n['min_osztalyok'] =  array(); 	        //pl:  array('9.a','11.a');
$m2n['csoportnev_hossz'] = 40;
$m2n['felhasznalo_hossz'] = 45;
$m2n['megfigyelo_user'] = "naplo_robot";    //ha nem kell, akkor állítsd üres stringre.
$m2n['kihagy'] = array();                   //pl:  array('Trap.Pista', 'Ebeed.Elek', '22att')
$m2n['default_lang']  = "hu";
$m2n['manage_groupdirs'] = false;           // Foglalkozzon-e a script a tankörmappákkal
$m2n['groupdir_prefix'] = "tavsuli";
$m2n['groupdir_users'] = array("naplo_robot","123abcd");    //Ha mindenkire ->  =array(); //(legyen üres)
$m2n['mindenki_csop'] = "naplós_felhasználók";
$m2n['mindenki_tanar'] = "naplós_tanárok";
$m2n['mindenki_diak'] = "naplós_diákok";
$m2n['zaras_tartas'] =  "2018-06-14";	//A jelölt napon befejezett, de nem lezárt tanév adatainak megtartása. (pl. szeptemberig) Ha már nem kell, akkor állítsd "1970-01-01"-ra !;
$m2n['verbose'] = 3 ;  

$occ_path = "/var/www/nextcloud/";
$occ_user = "www-data";

$cfgfile = realpath(pathinfo($argv[0])['dirname'])."/"."mayor-nextcloud.cfg.php";  // A fenti konfig behívható config fájlból is, így a nextcloud-betöltő (ez a php) szerkesztés nélkül frissíthető.
if( file_exists($cfgfile)===TRUE ){     include($cfgfile);  }
// Le kell cserélni az ékezetes betűket, mert a Vezetéknév.Keresztnév nem POSIX kompatibilis.
$search = array( 'á', 'ä', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', 'Á', 'Ä', 'É', 'Í', 'Ó', 'Ö', 'Ő', 'Ú', 'Ü', 'Ű');	// egyelőre csak a magyar betűket ismeri
$replace = array( 'aa', 'ae', 'ee', 'ii', 'oo', 'oe', 'ooe', 'uu', 'ue', 'uue', 'Aa', 'Aae', 'Ee', 'Ii', 'Oo', 'Oe', 'Ooe', 'Uu', 'Ue', 'Uue');

for($i = 1; $i<$argc; $i++){
    if($argv[$i] == "--loglevel" and is_numeric($argv[$i+1])){$m2l['log_verbose'] = intval($argv[$i+1]); $i++;}
    if($argv[$i] == "--set-diak-quota" ){ $m2l['always_set_diak_quota'] = true;  }
    if($argv[$i] == "--create-groupdir"){ $m2l['groupdir_users'] = array($argv[$i+1]); $i++;}
}


 
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
            if ($log['verbose'] > 0 ){ echo "*\tAz ".$db['m2n_db'].".".$db['m2n_prefix']."register (script-katalógus) tábla sikeresen létrehozva.\n";}
        }
    }

    function rmnp($str){    //Remove non-printable
        return preg_replace('/[\x00-\x1F\x7F-\xA0\xAD]/u', '', $str);
    }

    function escp($str){                //Escape strings
        $str = str_replace(array("\\","`", "\'", "\"" ),array("\\\\", "\`", "\\\'", "\\\""), $str);
        return escapeshellarg($str);
    }

    function rnescp($str){                //Escape strings
        $str = rmnp($str);
        $str = escapeshellarg($str);
        $str = str_replace(array("\\", "`", "'", "\"", "\ ", ), array("", "", "", "", "_", ), $str);
        return $str;
    }

    function nxt_get_version(){
        global $occ_path,$occ_user,$m2n,$log;
        // sudo -u honlap-felho php /home/honlap-felho/web/occ status --output=json
        $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." status --output=json \"" ;
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        return explode(".", json_decode(shell_exec($e),true)['version'])[0]; 
        echo "\n\n\n".explode(".", json_decode(shell_exec($e),true)['version'])[0]."\n\n\n";
    }
    
    function catalog_userlist($link){	//akiket a script hozott létre
        global $db,$log,$m2n;
        $ret['account'] = array();
        $ret['status'] = array();
        $q = "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE STATUS != 'forbidden'; ";    
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                if(!in_array($row['account'], $m2n['kihagy'])){
                    $ret['account'][] = $row['account'];
                    $ret['status'][] = $row['status'];
                }
            }
            mysqli_free_result($r);
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó m2n nyilvántartás lekérdezése.\n"; }
            return $ret;
        } else {
            echo "\nM2N -> \t**** Adatbázislekérdezési hiba! ****\n";
        }
    }
    
    function catalog_forbiddenlist($link){      //akiket a rendszergazda kitiltott
        global $log,$db,$m2n;
        $q = "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE STATUS = 'forbidden'; ";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            $ret = array();
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret[] = $row['account'];
            }
            mysqli_free_result($r);
            $ret = array_merge($ret, $m2n['kihagy']);
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-letiltások m2n nyilvántartás lekérdezése.\n"; }
            return $ret;
        } else {
            echo "\nM2N ->\t**** Adatbázislekérdezési hiba! ****\n";
        }
    }
    
    function catalog_useradd($link, $account){	// feljegyzi az általa létrehozott felhasználókat
        global $log,$db;
        $q = "INSERT INTO ".$db['m2n_db'].".".$db['m2n_prefix']."register (account) VALUES ('".mysqli_real_escape_string($link, $account)."')";
        if ($log['verbose'] > 5 ){ echo "M2N -> \t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-hozzáadás, m2n nyilvántartásba vétele.\n"; }
        } 
    }

    function catalog_userena($link, $account){	// az engedélyezetteket
        global $db,$log;
        $q = "UPDATE ".$db['m2n_db'].".".$db['m2n_prefix']."register SET status='active' WHERE account='".mysqli_real_escape_string($link, $account)."'";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-engedélyezés, m2n nyilvántartásba vétele.\n" ;}
        }
    }

    function catalog_userdel($link, $account){	// a törölteket
        global $db,$log;
        $q = "DELETE FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE account='".mysqli_real_escape_string($link, $account)."' ";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 5 ){ echo "*\tFelhasználó-törlés, m2n nyilvántartásba vétele.\n"; }
        }
    }
    
    function catalog_userdis($link, $account){	// a letiltottakat
        global $m2n,$db,$log;
        $q = "UPDATE ".$db['m2n_db'].".".$db['m2n_prefix']."register SET status='disabled' WHERE account='".mysqli_real_escape_string($link, $account)."'";
        if ($log['verbose'] > 5 ){ echo "M2N ->\t".$q."\n"; }
        if(( mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 5 ){ echo "*\tFelhasználó-letiltás, m2n nyilvántartásba vétele.\n"; }
        }
    }
    
    function user_add($userAccount, $fullName){		// létrehoz egy felhasználót a Nextcloud-ban
        global $occ_path,$occ_user,$m2n,$log;
//  	export OC_PASS=ErősJelszó123; su -s /bin/sh www-data -c 'php web/occ user:add --password-from-env  --display-name="Teszt Tamás" --group="csop" t.tamas'
        if(strlen($userAccount) > 64 or strlen($fullName) > 64){
            echo "\n******** Hiba: A felahsználónév, vagy a \"teljes név\" hosszabb, mint 64 karakter! NEM hozható létre!! ********\n";
        } else {
            $e = "export OC_PASS=".escp($m2n['default_passw'])."; su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:add  --password-from-env --display-name=".escp($fullName)." --group=".escp($m2n['mindenki_csop'])." ".escp($userAccount)." \"" ;
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);
        }
    }

    function user_del($userAccount){	// kitöröl vagy letilt egy felhasználót a Nextcloud-ban
	global $occ_path,$occ_user,$log;
        $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:info ".escp($userAccount)." --output=json \"";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        $last_login = json_decode(shell_exec($e),true)['last_seen'] ;
        if($last_login == "1970-01-01T00:00:00+00:00" ){	
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:delete ".escp($userAccount)." \"";		// Ha még soha nem lépett be
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);											// akkor törölhető
        } else {
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:disable ".escp($userAccount)." \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);											// különben csak letiltja
        }
        
    }
    
    function user_info($userAccount){	// User állpot a Nextcloudban
        global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:info ".escp($userAccount)." --output=json \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            return (array)json_decode(shell_exec($e),true);
    }


    function user_dis($userAccount){	// letiltja a felhasználót a Nextcloud-ban
        global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:disable ".escp($userAccount)." \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);
    }

    function user_ena($userAccount){	// engedélyezi
        global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:enable ".escp($userAccount)." \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);   
    }


    function nxt_group_list() {		// Csoportok listázása a Nextcloud-ból
            global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." group:list --limit=1000000 --output=json \"";  //* Jó nagy limittel dolgozzunk
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            return (array)json_decode(shell_exec($e),true);
    }
    
    function nxt_user_list() {		// Felhasználók listázása a Nextcloud-ból
            global $occ_path,$occ_user,$log;
        //    $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:report | grep 'total' | sed -e 's/[^0-9]//g' | tr -d '[:blank:]\n' \"";
        //    if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        //    $num = shell_exec($e); 
            $num = 1000000;      //inkább kézi limit!
        //    $num = $num + 100; 	// Biztos-ami-biztos, a nextcloud rejtett hibái miatt...
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:list --limit ".escp($num)." --output=json \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            return (array)json_decode(shell_exec($e),true);
    }
    
    function nxt_user_lastlogin($userAccount){ 	// legutóbbi belépés lekérdezése
        global $occ_path,$occ_user,$log;
        $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:info ".escp($userAccount)."  --output=json \"";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        return  json_decode(shell_exec($e),true)['last_seen'] ;
    }

    
    function user_set($userAccount, array $params){	//beállítja az e-mailt, quota-t, nyelvet a kapott értékekre
        global $occ_path,$occ_user,$log;        
        if(isset($params['quota'])){
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:setting ".escp($userAccount)." files quota ".escp($params['quota'])." \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec( $e );
        }
        if(isset($params['email'])){
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:setting ".escp($userAccount)." settings email ".escp($params['email'])." \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec( $e );
        }
        if(isset($params['lang'])){
            $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." user:setting ".escp($userAccount)." core lang ".escp($params['lang'])." \"";
            if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
            shell_exec($e);
        }
    } 
    


    function group_add($groupName){ 	//Új csoport létrehozása a Nextcloud-ban 
        global $occ_user,$occ_path,$link,$db,$log;
        $groupName = rmnp($groupName);
        if(strlen($groupName) > 64){	//mivel (egyelőre) nics erre 'occ' parancs, ezért közvetlenül kell...
            echo "\n****** Hiba: a csoportnév nagyobb, mint 64 karakter!! ******\n";
        } else {
            if(nxt_get_version() < 14) {
                $q = "INSERT IGNORE INTO ".mysqli_real_escape_string($link, $db['nxt_dbname']).".".mysqli_real_escape_string($link, $db['nxt_prefix'])."groups (gid) VALUES ('".mysqli_real_escape_string($link,$groupName)."'); ";
                if ($log['verbose'] > 5 ){ echo "NXT ->\t".$q."\n"; }
                if(mysqli_query($link, $q) !== TRUE ) echo "\nNXT -> \t****** Csoport létrehozási hiba. (adatbázis) ******\n";
            } else {
                $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." group:add ".escp($groupName)." \"";
                if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
                shell_exec($e);
            }
        }
    } 
    
    function group_del($groupName){	// Csoport törlése a Nextcloud-ból
        global $occ_user,$occ_path,$db,$link,$log,$m2n;
        $grp = nxt_group_list();
        $groupName = rmnp($groupName);
        if(isset($grp[$groupName])){
	        if(nxt_get_version() < 14){	// Mivel erre csak a Nextcloud 14-től van "occ" parancs, ezért néha közvetlenül kell...

                foreach($grp[$groupName] as $key => $user){
                    $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." group:removeuser ".escp($groupName)." ".escp($user)." \"";
                    if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
                    shell_exec($e);
                    if ($log['verbose'] > 1 ){ echo "*--\t\tTörölve".po(" ($user) a: $groupName",$m2n['csoportnev_hossz']+5,1)."\t csoportból.\n"; }
                }
                $q = "DELETE FROM ".mysqli_real_escape_string($link, $db['nxt_dbname']).".".mysqli_real_escape_string($link,$db['nxt_prefix'])."groups WHERE gid='".mysqli_real_escape_string($link, $groupName)."'; " ;
                if ($log['verbose'] > 5 ){ echo "NXT ->\t".$q."\n"; }
                if(mysqli_query($link, $q) !== TRUE ) echo "\n NXT -> \t****** csoport törlési hiba. (adatbázis) ******\n";
                
            } else {
                $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." group:delete ".escp($groupName)." \"";
                if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
                shell_exec($e);  
            }
        }
    }

    function group_user_add($groupName, $userAccount){	// Hozzáad egy felhasználót egy csoporthoz a Nextcloud-ban
        global $occ_user, $occ_path,$log;
        $e = "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." group:adduser ".escp($groupName)." ".escp($userAccount)." \"";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        shell_exec($e);
    }

    function group_user_del($groupName, $userAccount){	// Kitöröl egy felhasználót egy Nextcoud csoportból
        global $occ_user, $occ_path,$log;
        $e =  "su -s /bin/sh $occ_user -c \"php ".escp($occ_path."/occ")." group:removeuser ".escp($groupName)." ".escp($userAccount)." \"";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        shell_exec($e);
    }


    function create_dir($user, $path){	    // Készít egy mappát a: data/$user/files/$path alá
        global $occ_user, $occ_path,$log;
        $ret = null;
        if(!file_exists($occ_path."/data/".$user."/files/".$path)){                      // Ha Még nem létezik
            $ret = @mkdir($occ_path."/data/".$user."/files/".$path, 0755, true);            // Akkor létrehozza
            chown($occ_path."/data/".$user."/files/".$path, $occ_user);
            chgrp($occ_path."/data/".$user."/files/".$path, $occ_user);
            if($ret === true && $log['verbose'] > 5) { echo "php ->\tDIR: \"".$occ_path."/data/".$user."/files/".$path."\" \t created.\n"; }
            if($ret === false && $log['verbose'] > -1) { echo "php ->\tDIR: \"".$occ_path."/data/".$user."/files/".$path."\" \t makedir failed!!\n"; } //mondjuk ilyen egyáltalán mikor lehet?
        }
        return $ret;
    }


    function write_tofile($user, $path, $msg ){	            // Fájlba írja a $msg tartalmát
        global $occ_user, $occ_path,$log;
        $ret = 0;
        if( ($h = @fopen($occ_path."/data/".$user."/files/".$path, 'w+')) !== false ){
            $ret = fwrite($h, $msg );
            fclose($h);
            chown($occ_path."/data/".$user."/files/".$path, $occ_user);
            chgrp($occ_path."/data/".$user."/files/".$path, $occ_user);
            if($log['verbose'] > 5) { echo "php ->\tFILE: \"".$occ_path."/data/".$user."/files/".$path."\" \t created.\n"; }
        } else {
            if($log['verbose'] > 5) { echo "php ->\tFILE ERROR: \"".$occ_path."/data/".$user."/files/".$path."\" \t dir not found.\n"; }
        }
        return $ret;
    }

    function files_scan($user, $path ){                     // Nextcloud files:scan --path=xxx
        global $occ_user, $occ_path,$log;
        $e =  "su -s /bin/sh $occ_user -c \"php '".$occ_path."/occ' files:scan --path=".escp($user."/files/".$path)."   \"";  // -v 
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        shell_exec($e);
    }

    function user_notify($user, $msg, $title ){             // Nextcloud értesítés
        global $occ_user, $occ_path, $log;
        $e =  "su -s /bin/sh $occ_user -c \"php '".$occ_path."/occ' notification:generate -l ".escp($msg)." -- ".escp($user)." ".escp($title)." \"";
        if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
        shell_exec($e);
    }

    function scan_dir($user, $path ){                       // PHP mappa listázása
        global $occ_user, $occ_path,$log;
        $ret = array();
        if(is_dir($occ_path."/data/".$user."/files/".$path)){
            $ret = scandir($occ_path."/data/".$user."/files/".$path);
            if($ret[0] == "." && $ret[1] == ".."){
                unset($ret[0]);
                unset($ret[1]);
            }
        }
        return $ret;
    }

    function clean_dir($user, $path, $tankorei){    //Tankörmappák kitisztítása (path: mappagyökér)
        global $occ_user, $occ_path, $log, $m2n;
        $listdir = scan_dir($user, $path);
        $ret[0] = array();
        $ret[1] = array();
        $ret[2] = array();
        foreach($listdir as $key => $val) {
            if((!in_array($val, $tankorei) && !in_array(basename($val,"_beadás"), $tankorei) || !is_dir($occ_path."/data/".$user."/files/".$path."/".$val)) && $val != "INFO.txt"){              //Nincs a tanköreiben, akkor  törölni kell (de csak ha üres)    

                if(is_dir($occ_path."/data/".$user."/files/".$path."/".$val) && empty(scan_dir($user, $path."/".$val))){        // Ha mappa, és üres -> törlés
                    rmdir($occ_path."/data/".$user."/files/".$path."/".$val);
                    $ret[0][] = $val;
                    if($log['verbose'] > 5) { echo "php ->\tDIR: \"".$occ_path."/data/".$user."/files/".$path."/".$val."\" deleted.\n"; }    

                } else {    //Nem mappa, vagy nem üres
                    if(file_exists( $occ_path."/data/".$user."/files/".$path."/".pathinfo(basename($occ_path."/data/".$user."/files/".$path."/".$val ,".please-remove"))['basename'])){       //Ha az eredeti könyvtár  vagy fájl él
                        rename($occ_path."/data/".$user."/files/".$path."/".$val, $occ_path."/data/".rnescp($user."/files/".$path."/".basename($val, '.please-remove').".".time().".please-remove"));
                        $ret[1][] = basename($val, '.please-remove').".".time().".please-remove";
                        user_notify($user,"Az ön >>".$path."/<< könyvtárában tiltott helyen lévő fájl, vagy olyan (tankör)mappa található, amely tankörnek ön továbbá már nem tagja.   Kérem helyezze el kívül a >>".$path."/<< mappán, vagy törölje belőle!  Eltávolításra megjeleölve! A fájl átnevezve, új neve -->   ".rnescp(basename($val, '.please-remove').".".time().".please-remove"), "Fájl/Mappa rossz helyen! --> ".rnescp($path."/".basename($val, '.please-remove').".".time().".please-remove" ));
                        if($log['verbose'] > 5) { echo "php ->\tF/D: \"".$occ_path."/data/".$user."/files/".$path."/".$val."\" \t renamed -> ".rnescp(basename($val, '.please-remove').".".time().".please-remove")."\n"; }
                    } else {    
                        // A Hanyagul otthagyottakért csak figyelmeztessen:
                        user_notify($user,"Az ön >>".$path."/<< könyvtárában tiltott helyen lévő fájl, vagy olyan (tankör)mappa található, amely tankörnek ön továbbá már nem tagja.   Kérem helyezze el kívül a >>".$path."/<< mappán, vagy törölje belőle!  Eltávolításra megjelölve! --> ".$val, "Fájl/Mappa rossz helyen! --> ".$path."/".$val );
                    }
                }
            } else {
                $ret[2][] = $val;
            }
        }
        return $ret;
    }
 

    function groupdir_create_root($user, $oktId, $path){                //Tankörmappák gyökerét előállítja $path=tankörgyökér
        global $m2n, $occ_path, $occ_user,$log;
        $ret = array(false, false);
        if((empty($m2n['groupdir_users']) || in_array($user, $m2n['groupdir_users'])) && $oktId > 0 && $m2n['manage_groupdirs'] === true){   //Ha null -> mindenki, Ha "user" -> scak neki, && tanár && groupdir bekapcsolava
            
            if(is_file($occ_path."/data/".$user."/files/".$path) || is_link($occ_path."/data/".$user."/files/".$path)){     //Ha már vam ott valami ilyen fájl 
                rename($occ_path."/data/".$user."/files/".$path, $occ_path."/data/".rnescp($user."/files/".$path.".".time().".please-remove"));    //Átnevezi, hogy azért mégse vasszen oda
                echo "php ->\tFILE: \"".$occ_path."/data/".$user."/files/".$path."\" \t \t moved away!!!\n"; 
                user_notify($user,"Fájl:  >>".$path.".please-remove<<  Illegális helyen volt. Server által eltávolítva.", "Fájl: >>".$path."<< eltávolítva!");
                files_scan($user, "" ); //Ekkor az egész $user/files mappát szkenneli
            } 
            $ret[0] = create_dir($user, $path);                                             // Tankörmappa gyökér létrehozása
            $ret[1] = write_tofile($user, $path."/"."INFO.txt", $m2n['infotxt_szöveg']);    // INFO.txt (Újra)Írása.
            if($ret[0] === true){                                                           // Ha frissen létrehozott mappa, akkor az egész userre kell jogot adni
                $e =  "/bin/chown -R ".escp($occ_user.":".$occ_user)." ".escp($occ_path."/data/".$user."/")." ";
                if($log['verbose'] > 5) { echo "bash ->\t".$e."\n"; }
                shell_exec($e);
                files_scan($user, $path);
            }
        }     
        return $ret; 
    }

    function groupdir_create_groupdir($user, $oktId, $path){        // $path = tankörmappa
        global $m2n;
        $ret = false;
        if( basename($path,"_beadás") != $m2n['mindenki_tanar'] and basename($path,"_beadás") != $m2n['mindenki_diak'] and basename($path,"_beadás") != $m2n['mindenki_csop']){   //Ezekre a csoportokra minek? 
            if((empty($m2n['groupdir_users']) || in_array($user, $m2n['groupdir_users'])) && $oktId > 0 && $m2n['manage_groupdirs'] === true){   
                $ret = create_dir($user, $path);                                                // Tankörmappa létrehozása
                if($ret === true){
                    files_scan($user, $path);
                }
            }     
            return $ret;
        }
    }

    function groupdir_finish($user, $oktId, $path, $tankorei ){     //$path=tankörgyökér
        global $m2n;
        $ret = array(array(),array(),array(),false,false);      //return sekelton
        if((empty($m2n['groupdir_users']) || in_array($user, $m2n['groupdir_users'])) && $oktId > 0 && $m2n['manage_groupdirs'] === true){   
            if(isset($tankorei)) {
                $ret = clean_dir($user, $path, $tankorei);
                $ret[3] = false; //mert felülírja a skeleton-t
            }
            if(!empty($ret[0]) or !empty($ret[1]) ){
                files_scan($user, $path);                                                       // Nextcloud értesítése
                $ret[3] = true;
            } 
            files_scan($user, $path."/INFO.txt");
        }     
        return $ret;
    }




    function add_tk_to_users($list, $user, $tankorname){      //Naplón kívüli csoportokat adhatunk a felhasználókhoz
        $curr = "";
        foreach($list as $key => $val){                       // Csak rendezett tömbökön!
            if($curr != $val['userAccount'] && ($user === null or ($user !== null && $val['userAccount'] == $user ))){ //Vagy mindenki vagy adott user + rendezett lista
                
                if(!isset($val['tanarId'])){        //workaround
                    $val['tanarId'] = 0;
                }
                if(!isset($val['diakId'])){         //workaround
                    $val['diakId'] = 0;
                }
                $list = array_merge($list, array(   
                    array( 'userAccount' => $val['userAccount'], 
                        'email' => $val['email'],  
                        'tanarId' => $val['tanarId'],
                        'diakId' => $val['diakId'],
                        'tankorId' => 0,
                        'fullName' => $val['fullName'],
                        'tankorNev' => $tankorname,
                    )));

                if($user !== null && $val['userAccount'] == $user ){    // Null -> mindenkihez, "user" -> csak neki
                    break;
                }
                $curr = $val['userAccount'];
            }
        }
        return $list;    
    }

    function set_param_to_user($list, $user, $paramname, $param){       // Paramétert állít be a felhasználónak.
        foreach($list as $key => $val){                                 // Csak rendezett tömbökön! (vagy mégsem?)
            if($user === null or ($user !== null && $val['userAccount'] == $user )){ //Vagy mindenki vagy adott user 

                $list[$key][$paramname] = $param;    // A paraméter
            }
        }
        return $list;    
    } 

    function mayor_userlistcmp($a, $b){
        return strcmp($a['userAccount'], $b['userAccount']);
    }

    function po($inp,$ll,$dir){                         // Szép kimenetet gyárt
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
            WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND (CURRENT_DATE() <= zarasDt OR zarasDt = '".$m2n['zaras_tartas']."' ));	 ";
*/             
//csak a megadott évfeolyamokhoz kötődő tankörök:
        $qq = "SELECT tanev FROM intezmeny_".mysqli_real_escape_string($link, $m2n['isk_rovidnev']).".szemeszter WHERE statusz = 'aktív' GROUP BY tanev; ";
        if ($log['verbose'] > 5 ){ echo "MAY ->\t".$qq."\n"; }
        if( ($r = mysqli_query($link, $qq)) !== FALSE ){
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
                WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND (CURRENT_DATE() <= zarasDt OR zarasDt = '".$m2n['zaras_tartas']."')) AND tankorId IN(
                SELECT tankorId
                FROM intezmeny_".$m2n['isk_rovidnev'].".tankorOsztaly
                WHERE osztalyId IN (
                SELECT osztalyId
                FROM naplo_".$m2n['isk_rovidnev']."_".$ev['tanev'].".osztalyNaplo
                WHERE evfolyamJel >= ".$m2n['min_evfolyam']."  OR osztalyJel IN(".$req_oszt.") 
                ORDER BY osztalyId)
                ORDER BY tankorId ) ORDER BY tankorNev; 
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
            OR tanar.kiDt IS NULL) AND tanar.tanarId = tankorTanar.tanarId AND tankorTanar.beDt <= CURRENT_DATE() AND (CURRENT_DATE() <= tankorTanar.kiDt OR tankorTanar.kiDt = '".$m2n['zaras_tartas']."' ) 
            AND tankorTanar.tankorId = tankorSzemeszter.tankorId AND tankorSzemeszter.tanev = (
            SELECT tanev
            FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
            WHERE statusz = 'aktív'
            GROUP BY tanev) AND szemeszter = (
            SELECT szemeszter
            FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
            WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND (CURRENT_DATE() <= zarasDt OR zarasDT = '".$m2n['zaras_tartas']."' ))
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
                AND tankorDiak.diakId = diak.diakId AND tankorDiak.beDt <= CURRENT_DATE() AND (tankorDiak.kiDt >= CURRENT_DATE() OR tankorDiak.kiDt IS NULL OR tankorDiak.kiDt = '".$m2n['zaras_tartas']."' ) 
                AND tankorSzemeszter.tankorId = tankorDiak.tankorId AND tankorSzemeszter.tanev = (
                SELECT tanev
                FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
                WHERE statusz = 'aktív'
                GROUP BY tanev) AND tankorSzemeszter.szemeszter = (
                SELECT szemeszter
                FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter
                WHERE statusz = 'aktív' AND kezdesDt <= CURRENT_DATE() AND (CURRENT_DATE() <= zarasDt OR zarasDt = '".$m2n['zaras_tartas']."' ))
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
    if(nxt_get_version() < 13){         //Nextcloud 13-tól támogatott
        echo "\n\n******** Legalább Nextcloud 13-mas verzió szükséges! ********\n\n";
        die();
    }
//-------------------------------------------------------------------------------------------------------------------------------    

    if(true) { echo "\n\n######## Mayor-Nextcloud Script ########\n";   echo "######## (".date("Y-m-d H:i:s").")\n\n"; $t_start = microtime(true); }
    

    if( file_exists($cfgfile)===TRUE ){
        include($cfgfile);
        if($log['verbose'] > 0) { echo "***	M2N Config betöltése: ($cfgfile fájlból.) ***\n\n"; }
    } else {
        if($log['verbose'] > 0) { echo "***	M2N Config betöltése: (".pathinfo($cfgfile)['dirname']."/mayor-nextcloud.php fejlécéből.) ***\n\n"; }
    }
    $log['verbose'] = $m2n['verbose'];
    if(isset($m2l['always_set_diak_quota'])){ $m2n['always_set_diak_quota'] = $m2l['always_set_diak_quota']; }
    if(isset($m2l['groupdir_users'])){ $m2n['groupdir_users'] = $m2l['groupdir_users']; }
    if(isset($m2l['log_verbose'])){ $log['verbose'] = $m2l['log_verbose']; }
    if( $m2n['always_set_diak_quota'] === true && $log['verbose'] < 4 ){    $log['verbose'] = 4; }
    
    if(($link = db_connect($db)) == FALSE){			//csatlakozás
        echo "\n******** MySQL (general) kapcsolat hiba. ********\n";
        echo "\n******** Script leáll... ********\n";
        die();
        }
    $link2 = $link;

    // group_add($m2n['mindenki_csop']);				// A "mindenki" csoport hozzáadása 
    // group_add($m2n['mindenki_tanar']);				// A "mindenki"/tanár csoport hozzáadása
    // group_add($m2n['mindenki_diak']);				// A "mindenki"/diák csoport hozzáadása

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

// Létrehozza az új coportokat a Mayor tankörök szerint
    if ($log['verbose'] > 0 ){ echo "\n***\tCsoportok egyeztetése.\n";}
    $tankorok = get_mayor_tankor($link2);
    $tankorok = array_merge($tankorok, array( array("tankorId" => 0, "tankorNev" => $m2n['mindenki_csop'] )));
    $tankorok = array_merge($tankorok, array( array("tankorId" => 0, "tankorNev" => $m2n['mindenki_tanar'] )));
    $tankorok = array_merge($tankorok, array( array("tankorId" => 0, "tankorNev" => $m2n['mindenki_diak'] )));
    $nxt_csop = nxt_group_list();
    $elozo_tcsop = "";
    foreach($tankorok as $key => $val){                                                 //Végignézi a tankörök szerint
        foreach($nxt_csop as $key2 => $val2){                                           // 
            if($key2 == $val['tankorNev']){                                             //Már van ilyen (tankör)nevű csoport
                if ($log['verbose'] > 3 ){ echo "  -\t Csoport:".po("\t".$val['tankorNev'],$m2n['csoportnev_hossz'],1)."-\tok.\n";}
                $elozo_tcsop = $val['tankorNev'];
                break;
            }
        }
        unset($nxt_csop[$val['tankorNev']]);                                            //Megvizsgálva, többször már nem kell dönteni róla. 
        if( $val['tankorNev'] == $elozo_tcsop and $key2 != $val['tankorNev'] ){         //Duplikált tankör(név) a Mayorban
                if($log['verbose'] > 2 ){ echo "* -\t Dupla tankör:".po("\t".$val['tankorNev'], $m2n['csoportnev_hossz'],1)."-\tmayor.\n";}
        }
        else if($key2 != $val['tankorNev']){                                            //Ha nincs ilyen (tankör)nevű csoport
            group_add($val['tankorNev']);                                               //Akkor létrehozza
            if ($log['verbose'] > 2 ){ echo "* -\t Új csoport:".po("\t".$val['tankorNev'],$m2n['csoportnev_hossz'],1)."-\thozzáadva.\n";}
         }
    }
// A megszűnt tanköröket-csoportokat kitörli 
    foreach($nxt_csop as $key => $val){           
        if(substr($key, 0, strlen($m2n['csoport_prefix'])) === $m2n['csoport_prefix'] ){	//Csak a "prefix"-el kezdődő nevűekre.
            group_del($key);									//elvégzi a törlést
            if ($log['verbose'] > 1 ){ echo "** -\t Megszűnő csop:".po("\t$key",$m2n['csoportnev_hossz'],1)."-\t eltávolítva.\n";}
        } else {
            if ($log['verbose'] > 3 ){ echo " ---\t Egyéb csoport:".po("\t$key",$m2n['csoportnev_hossz'],1)."-\t békén hagyva.\n";}
        }	// Figyelem! A csoport prefix-szel: "(tk) " kezdődő csoportokat magáénak tekinti, automatikusan töröli!
    }	// 	Akkor is, ha az külön, kézzel lett létrehozva.



//-------------------------------------------------------------------------------------------------------------------------------
// Felhasználónevek egyeztetése
    if ($log['verbose'] > 0 ){ echo "\n***\tFelhasználók egyeztetése.\n";}
    
    $mayor_tanar = get_mayor_tanar($link2);     //Rendezve jön
    $mayor_tanar = add_tk_to_users( $mayor_tanar, null, $m2n['mindenki_tanar']);    //csak rendezett tömbökön!
    $mayor_tanar = set_param_to_user($mayor_tanar, null, 'quota', $m2n['default_quota']);
    $mayor_tanar = set_param_to_user($mayor_tanar, null, 'diakId', -1 ); 
    usort($mayor_tanar, "mayor_userlistcmp");

    $mayor_diak = get_mayor_diak($link2);       //mysql rendezi
    $mayor_diak = add_tk_to_users( $mayor_diak, null, $m2n['mindenki_diak']);		//csak rendezett tömbökön!
    $mayor_diak = set_param_to_user($mayor_diak, null, 'quota', $m2n['diak_quota']);
    $mayor_diak = set_param_to_user($mayor_diak, null, 'tanarId', -1 );
    usort($mayor_diak, "mayor_userlistcmp");

    $mayor_user = array();
    $mayor_user = array_merge($mayor_tanar, $mayor_diak);                               //Tanár, és diák lista együtt
    if(isset($m2n['megfigyelo_user']) && $m2n['megfigyelo_user'] != "" ){               //A megfigyelő felvétele a lista végére
        $mayor_user = array_merge($mayor_user, array(
            array( 'userAccount' => $m2n['megfigyelo_user'],                            //A virtuális "naplo_admin" legyen egyben tanár is
                'tanarId' => 1, 'diakId' => 0, 'tankorId' => 0, 'fullName' => "Napló Admin",
                'email' => $m2n['default_email'],
                'tankorNev' => $m2n['mindenki_tanar'],
            )));
        foreach(get_mayor_tankor($link2) as $key => $val){
            $mayor_user = array_merge($mayor_user, array(
                array( 'userAccount' => $m2n['megfigyelo_user'], 
                    'email' => $m2n['default_email'],  
                    'tanarId' => 1,
                    'diakId' => 0,
                    'tankorId' => $val['tankorId'],
                    'fullName' => "Napló Admin",
                    'tankorNev' => $val['tankorNev'],
                )));
                //if($val['tankorNev'] == "(tk) 10.b kémia" ){ break; }

        }
    }
    usort($mayor_user, "mayor_userlistcmp");        //rendezés
    $mayor_user = add_tk_to_users( $mayor_user, null, $m2n['mindenki_csop']);       //csak rendezett tömbökön //mindenki csoport
    usort($mayor_user, "mayor_userlistcmp");        //Végén ismét rendezzük az egészet 
    $mayor_user = array_merge($mayor_user, array(array('userAccount' => null, 'fullName' => null, 'tankorNev' => null, 'diakId' => 0, 'tanarId' => 0,)) ); //strázsa a lista végére

    $nxt_user = nxt_user_list();
    $nxt_group = nxt_group_list();
    $m2n_catalog = catalog_userlist($link);
    $m2n_forbidden = catalog_forbiddenlist($link);
    
    if ($log['verbose'] > 3 ){ echo "\n";}

    foreach($mayor_user as $key => $val){
                                                                                    //Lecseréli az ékezetes betűket a felhasználónévből
        $mayor_user[$key]['userAccount'] = str_replace($search, $replace, $val['userAccount']);  // (pl: Á->Aa, á->aa, ...)
        if(in_array($mayor_user[$key]['userAccount'], $m2n_forbidden) ){                         //És, ha a nyilvántartásban "forbidden"-ként szerepel, 
            unset($mayor_user[$key]);                                               // akkor nem foglalkozik vele tovább.
        }
    }

    $curr = "";
    $tankorei = array();
    foreach($mayor_user as $key => $val){                                                   //Végignézi a mayorból kinyert lista alapján.
    
        if($curr != $val['userAccount']){                                                   //CSAK Rendezett tömbökön !! 
            foreach($nxt_user as $key2 => $val2){
                if($curr == $key2){                                                         //Már létezik a felhasználó a Nextcloud-ban
                    $log['curr'] = "-\tFelhasználó:".po("\t$curr_n ($curr)",$m2n['felhasznalo_hossz'],1)."--\tok.\n";
                    if ($log['verbose'] > 3 ){ echo " -".$log['curr']; $log['curr'] = "";}
                    if( in_array($curr, $m2n_catalog['account'])){                          //Benne van-e a nyilvántartásban?
                        if($m2n_catalog['status'][array_keys($m2n_catalog['account'], $curr)[0]] == 'disabled' ){ // Ha le lett tiltva
                        //if(user_info($curr)['enabled']!=true){                              // Ez valós, de irtó lassú
                            catalog_userena($link, $curr);                                  //Ha netán le lenne tiltva, akkor engedélyezi,
                            user_ena($curr);                                                // ha a script tiltotta le.
                        }
                    } else  {                                                               //Nincs a katalógusban, nincs tiltva,  felvesszük        
                        catalog_useradd($link, $curr);                                      
                        if ($log['verbose'] > 1 ){ echo "-\t\tA felhasználó:".po("\t$curr",$m2n['felhasznalo_hossz'],1)."-\tnyilvántartásba véve.\n";} 
                    }
                    //---------------------------------------  QUOTA -----------------------------------//
                    if($m2n['always_set_diak_quota'] === true && $curr_tanarId < 0 && $curr_diakId > 0 ){              //Állítsunk-e erőből (diák) qvótát?
                        $params['quota'] = $m2n['diak_quota'];                                                  // Alapértelmezett diák kvóta
                        user_set($curr,$params);
                        if ($log['verbose'] > 3 ){ echo "* -\t\tBeállítva:\t"."Qvóta: ".$params['quota']."\t\n";}
                    }
                    //------------------------- Tankörmappa  györkér + info.txt ------------------------//     
                    $ret = groupdir_create_root($curr, $curr_tanarId, $m2n['groupdir_prefix']);
                    if ($ret[0] === true && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/".$m2n['groupdir_prefix'],$m2n['csoportnev_hossz'],1)."\ttankörmappa gyökér.\n";}
                    if ($ret[1] > 0 && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/". $m2n['groupdir_prefix']."/INFO.txt",$m2n['csoportnev_hossz'],1)."\tfájl.\n";}
       
                    //------------------------------------------ Tankörök egyeztetése -------------------------------------------//
                    foreach($nxt_group as $key3 => $val3){                                                      //A tankörök egyeztetése
                        if(in_array($key3, $tankorei) /*or $key3 == $m2n['mindenki_csop']*/){                   //szerepel-e a felhasználó tankörei között a csoport, vagy a "mindenki" csoport?
                            if( in_array($curr, $val3)){                                                        //Igen, és már benne is van +++
                                if ($log['verbose'] > 3 ){ echo "  -\t\tBenne van a:".po("\t$key3",$m2n['csoportnev_hossz'],1)."\tcsoportban.\n";} 
                            } else {                                                                            //Nincs, most kell beletenni
                                if ($log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tHozzáadva a:".po("\t$key3",$m2n['csoportnev_hossz'],1)."\tcsoporthoz.\n";}
                                group_user_add($key3, $curr);                                                   //A "mindenki csoportot is ellenőrzi
                            }

                            //------------------------------- Tankörmappa -----------------------------//       //( "_" --> mindenkinek, "username" --> csak neki ) && tanár
                            $ret = groupdir_create_groupdir($curr, $curr_tanarId, $m2n['groupdir_prefix']."/".$key3); 
                            if ($ret === true && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\tÚj mappa Létrehozva:".po("\t/".$key3."/",$m2n['csoportnev_hossz'],1)."\t./".$curr."/files/".$m2n['groupdir_prefix']."/   mappába\n";}
                            $ret = groupdir_create_groupdir($curr, $curr_tanarId, $m2n['groupdir_prefix']."/".$key3."_beadás");
                            if ($ret === true && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\tÚj mappa Létrehozva:".po("\t/".$key3."_beadás/",$m2n['csoportnev_hossz'],1)."\t./".$curr."/files/".$m2n['groupdir_prefix']."/   mappába\n";}
                    
                        //------------------------------------- Tankör (Csoportból) törlés -------------------------//
                        } else {                                                                                //Nem szerepel a tankörei között
                            if(in_array($curr, $val3) and  (substr($key3, 0, strlen($m2n['csoport_prefix'])) === $m2n['csoport_prefix']) ){ // korábban benne volt egy tankörben, de már nincs, vagy a hozzátartozó tankörben már nem tanít  => kiveszi
                                if ($log['verbose'] > 1 ){if($log['curr'] !== ""){echo "*".$log['curr'];$log['curr'] = "";} echo  "* -\t\tTörölve a:".po("\t$key3",$m2n['csoportnev_hossz'],1)."\tcsoportból.\n";}
                                group_user_del($key3, $curr);                                                   //egy korábbi tankör lehetett...
                            }
                        }
                    }
                    //------------------------------------- Tankörmappa törlés + NXT-rescan ----------------------------------//     //( "_" --> mindenkinek, "username" --> csak neki ) && tanár
                     
                    $ret = groupdir_finish($curr, $curr_tanarId, $m2n['groupdir_prefix'], $tankorei);
                    if (count($ret[0]) > 0 && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} foreach($ret[0] as $retkey => $retval){ echo "* -\t Üres (Tankör)mappa:".po("\t/".$retval."/", $m2n['csoportnev_hossz'],1)."\t./".$curr."/files/".$m2n['groupdir_prefix']."/   mappából törölve.\n";}}
                    if (count($ret[1]) > 0 && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} foreach($ret[1] as $retkey => $retval){ echo "* -\tFájl/Mappa Átnevezve:".po("\t/".$retval."/", $m2n['csoportnev_hossz'],1)."\t./".$curr."/files/".$m2n['groupdir_prefix']."/   mappában.\n";}}
                    if (count($ret[2]) > 0 && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} foreach($ret[2] as $retkey => $retval){ echo "* -\t\tTankörmappa:".po("\t/".$retval."/", $m2n['csoportnev_hossz'],1)."\t./".$curr."/files/".$m2n['groupdir_prefix']."/   mappában békén hagyva.\n";}}
                    if ($ret[3] === true && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";}   echo "* -\t\tNXT-rescan :".po("\t./".$curr."/files/".$m2n['groupdir_prefix']."/", $m2n['csoportnev_hossz'],1)."\t mappán.\n";}
                    
                    break;
                }       
            }  
            unset($nxt_user[$curr]);                                                        //Felhasználó Megvizsgálva, többször már nem kell dönteni róla.
            if($curr != $key2 and $curr != null){                                           //Nincs még ilyen felhasználó
                
                user_add($curr, $curr_n);                                                   //Akkor hozzá kell adni
                catalog_useradd($link, $curr);
                if ($log['verbose'] > 2 ){ echo "**-\tFelhasználó:".po("\t$curr_n ($curr)",$m2n['felhasznalo_hossz'],1)."--\tlétrehozva.\n";}
                
                $ret = groupdir_create_root($curr, $curr_tanarId, $m2n['groupdir_prefix']);
                if ($ret[0] === true && $log['verbose'] > 2 ){ echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/".$m2n['groupdir_prefix']."/",$m2n['csoportnev_hossz'],1)."\ttankörmappa gyökér.\n";}
                if ($ret[1] > 0 && $log['verbose'] > 2 ){ echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/".$m2n['groupdir_prefix']."/INFO.txt",$m2n['csoportnev_hossz'],1)."\tfájl.\n";}
       
                foreach($tankorei as $key3 => $val3){                                       //Hozzáadja a (tankör)csoportokhoz is egyből,
                    if(array_key_exists($val3, $nxt_group)) {                               // de, csak akkor, ha az a csoport a Nextcloud-ban is létezik.
                        group_user_add($val3, $curr);
                        if ($log['verbose'] > 2 ){ echo "* -\t\tHozzáadva a:".po("\t $val3",$m2n['csoportnev_hossz'],1)."\tcsoporthoz.\n"; }
                        $ret = groupdir_create_groupdir($curr, $curr_tanarId, $m2n['groupdir_prefix']."/".$val3);
                        if ($ret === true && $log['verbose'] > 2 ){echo "* -\tÚj mappa Létrehozva:".po("\t/".$val3."/",$m2n['csoportnev_hossz'],1)."\t./".$curr."/files/".$m2n['groupdir_prefix']."/   mappa\n";}
                        $ret = groupdir_create_groupdir($curr, $curr_tanarId, $m2n['groupdir_prefix']."/".$val3."_beadás");
                        if ($ret === true && $log['verbose'] > 2 ){echo "* -\tÚj mappa Létrehozva:".po("\t/".$val3."_beadás/",$m2n['csoportnev_hossz'],1)."\t./".$curr."/files/".$m2n['groupdir_prefix']."/   mappa\n";}
                    }
                } 
                $ret = groupdir_finish($curr, $curr_tanarId, $m2n['groupdir_prefix'], null);                                    
                if ($ret[3] === true && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tNXT-rescan :".po("\t./".$curr."/files/".$m2n['groupdir_prefix']."/", $m2n['csoportnev_hossz'],1)."\t mappán.\n";}


                if($curr_diakId > 0) {      //Ennyi is  elég                                // Diákról van szó    /// if($curr_tanarId < 0 && $curr_diakId > 0)
                    $params['quota'] = $m2n['diak_quota'];                                  // Alapértelmezett kvóta
                } else {
                    $params['quota'] = $m2n['default_quota'];                               // Alapértelmezett kvóta
                }
                $params['lang'] = $m2n['default_lang'];                                     // Nyelv
                if($curr_e == ""){
                    $params['email'] = $m2n['default_email'];                               // e-mail beállítása
                } else {
                    $params['email'] = $curr_e;                                             // ha van a mysql-ben e-mail, akkor azt használja
                }
                user_set($curr,$params);                                                    //Alapértelmezett paraméterek érvényesítése
                if ($log['verbose'] > 2 ){ echo "* -\t\tBeállítva:\t"."Qvóta: ".$params['quota']."\tNyelv: ".$params['lang']."\tE-mail: ".$params['email']."\n";}
            }

            unset($tankorei);
            $tankorei = array();                            // új ciklus kezdődik
            $curr = $val['userAccount'];                    //
            $curr_n = $val['fullName'];                     //
            $curr_tanarId = $val['tanarId'];
            $curr_diakId = $val['diakId'];
            $curr_e = @$val['email'];                       //
        }
        $tankorei[] = $val['tankorNev'];                    // Másodszor/Egyébként a csoportok (tankörök) gyűjtése
    }


// A (maradék) megszűnő felhasználónevek egyeztetése
    if ($log['verbose'] > 0 ){ echo "\n***\tTörlendő/Letiltandó felhasználók egyeztetése.\n";}
    $m2n_catalog = catalog_userlist($link);
    foreach($nxt_user as $key => $val){                                         //Benne van a nyilvántartásban,
            if(in_array($key, $m2n_catalog['account'])){                        //vagyis a script adta hozzá korábban
                if( nxt_user_lastlogin($key) == "1970-01-01T00:00:00+00:00" ){   //Még soha nem lépett be = 1970.01.01 ??
                    user_del($key);                                             //Akkor törli 
                    catalog_userdel($link, $key);                               //A listáról is
                    if ($log['verbose'] > 1 ){ echo "**-\tFelhasználó:".po("\t$val ($key)",$m2n['felhasznalo_hossz'],1)."--\ttörölve.\n";} 
                } else {
                    user_dis($key);                                             //Különben csak letiltja (fájlok ne vesszenek el)
                    catalog_userdis($link, $key);                               //Feljegyzi a nyilvántartásba
                    if ($log['verbose'] > 1 ){ echo "**-\tFelhasználó:".po("\t$val ($key)",$m2n['felhasznalo_hossz'],1)."--\tletiltva.\n";} 
                }
            }
            // döntési logika:
            // ha benne van a $mayor_user-ben,
            // -    akkor vagy új user, vagy már meglévő, 
            // -    ezért őt kihúzza az $nxt_user listáról, --> megtartja
            // ezután ha valaki még rajta van az $nxt_user listán, az
            // -    vagy más, mayor_naplón kívüli user (rendszergazda vette föl) --> nem törli, ha kellene
            // -    vagy megszűnő, korábbi mayor_napló-s user --> törli (vagy letiltja)
            // ha rajta van a $catalog listán is, és nincs rajta $mayor_user listán 
            // -	akkor őt a script hozta létre régen --> megszűnő, törli (vagy letiltja)
            // (hiszen, ha aktív lenne, rajta lenne a $mayor_user listán, és kihúzta volna a $nxt_user-ből)
    }

// Végül a nyilvántartás kipucolása
    if ($log['verbose'] > 0 ){ echo "\n***\tNyilvántartás ellenőrzése.\n";}
    $nxt_user = nxt_user_list();
    $m2n_catalog = catalog_userlist($link);
    $m2n_forbidden = catalog_forbiddenlist($link);

    foreach($m2n_catalog['account'] as $key => $val){    
        if(@$nxt_user[$val] === null  ){         //Erre a nextcloud "occ" parancs hibakezelése miatt van szükség
            if ($log['verbose'] > 4 ){ echo "**-\tFelhasználónév:".po("\t($val)",$m2n['felhasznalo_hossz'],1)."--\tkivéve a nyilvántartásból.\n";}
            catalog_userdel($link, $val);
        }
    }
    foreach($m2n_forbidden as $key => $val){    //Szinkronizálja a $m2n['kihagy'] listát a nyilvántartással.    
        if(!in_array($val, $m2n['kihagy'])){
            if ($log['verbose'] > 4 ){ echo "**-\tFelhasználó:".po("\t($val)",$m2n['felhasznalo_hossz'],1)."--\tújra engedélyezve.\n";}
            catalog_userena($link,$val);
            user_ena($val);
        }
    }

//-------------------------------------------------------------------------------------------------------------------------------

//test
//script_install($link);


    @mysqli_close($link2);
    @mysqli_close($link);
    $t_run = (microtime(true) - $t_start)/60;
    if ($log['verbose'] > 0 ){ echo "\n(Runtime: ".$t_run."min.)\nkész.\n";} //endline
 
} else {
    echo "\n\n******** Legalább PHP7 és mysqli szükséges! ********\n\n";
}



?>

