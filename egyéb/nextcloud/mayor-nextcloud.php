#!/usr/bin/php
<?php 

$cfg = array();
////////////////////////////////////////////// Figyelem! az alábbi konfig automatikusan külön fájból töltődik, ha létezik a "mayor-nextcloud.cfg.php" fájl!! /////////////////////////////////
$cfg['db_host'] = "localhost";
$cfg['db_port'] = "3306";
$cfg['db_user'] = "root";
$cfg['db_pass'] = "";
$cfg['db_m2n_db'] = "mayor_to_nextcloud";
$cfg['db_m2n_prefix'] = "m2n_";
$cfg['db_nxt_dbname'] = "Nextcloud";
$cfg['db_nxt_prefix'] = "oc_";
//$cfg['db_mayor_host'] = "";
//$cfg['db_mayor_port'] = "";
//$cfg['db_mayor_user'] = ""; 
//$cfg['db_mayor_pass'] = "";
  
$cfg['min_evfolyam'] = 1;
$cfg['isk_rovidnev'] = "rovid";
$cfg['csoport_prefix'] = "(tk) ";
$cfg['default_email'] = "indulo@iskola.hu";
//$cfg['default_passw'] = "EHYmGktzrdfS7wxJR6DFqxjJ";   //Megszűnt -> Helyette random jelszót generál
$cfg['always_set_diak_quota'] = false;
$cfg['default_quota'] = "10GB";
$cfg['diak_quota']    = "2GB";
$cfg['min_osztalyok'] =  array(); 	        //pl:  array('9.a','11.a');
$cfg['csoportnev_hossz'] = 40;
$cfg['felhasznalo_hossz'] = 45;
$cfg['megfigyelo_user'] = "naplo_robot";    //ha nem kell, akkor állítsd üres stringre.
$cfg['kihagy'] = array();                   //pl:  array('Trap.Pista', 'Ebeed.Elek', '22att')
$cfg['default_lang']  = "hu";
$cfg['manage_groups'] = true;
$cfg['manage_groupdirs'] = false;           // Foglalkozzon-e a script a tankörmappákkal
$cfg['groupdir_prefix'] = "tavsuli";
$cfg['groupdir_users'] = array("naplo_robot","123abcd");    //Ha mindenkire ->  =array(); //(legyen üres)
$cfg['mindenki_csop'] = "naplós_felhasználók";
$cfg['mindenki_tanar'] = "naplós_tanárok";
$cfg['mindenki_diak'] = "naplós_diákok";
$cfg['allapot_tartas'] =  "2018-06-14";	//A jelölt napnak megfelelő állapot betöltése minden futtatáskor, ha nem kell, akkor állítsd üresre!;
$cfg['infotxt_szöveg'] = "info.txt";
$cfg['verbose'] = 3 ;  

$occ_path = "/var/www/nextcloud/";
$occ_user = "www-data";
$nxt_version = 0;
$printhelp = false;
$printconfig = false;
$printpasswds = false;
$dryrun = false;
$debug = false;

$cfgfile = realpath(pathinfo($argv[0])['dirname'])."/"."mayor-nextcloud.cfg.php";  // A fenti konfig behívható config fájlból is, így a nextcloud-betöltő (ez a php) szerkesztés nélkül frissíthető.
// Le kell cserélni az ékezetes betűket, mert a Vezetéknév.Keresztnév nem POSIX kompatibilis.
$search = array( 'á', 'ä', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', 'Á', 'Ä', 'É', 'Í', 'Ó', 'Ö', 'Ő', 'Ú', 'Ü', 'Ű');	// egyelőre csak a magyar betűket ismeri
$replace = array( 'aa', 'ae', 'ee', 'ii', 'oo', 'oe', 'ooe', 'uu', 'ue', 'uue', 'Aa', 'Aae', 'Ee', 'Ii', 'Oo', 'Oe', 'Ooe', 'Uu', 'Ue', 'Uue');
$pwchars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789-_?";

for($i = 1; $i<$argc; $i++){  //Ha van külön config megadva, akkor először azt töltjük be.
    if($argv[$i] == "--config-file" ){$cfgfile = strval($argv[$i+1]); $i++;}
}
if(file_exists($cfgfile) === TRUE){ $cfg_o = $cfg; include($cfgfile); $cfg_n = $cfg; $cfg = array_merge($cfg, $cfg_o, $cfg_n); }   //Config betöltés
if(!empty($m2n)){  $cfg_o = $cfg; $cfg = array_merge($cfg, $m2n, $cfg_o); }    //Ha valahol még a régi config lenne 


for($i = 1; $i<$argc; $i++){    // Kézzel felülbírált config opciók
    if($argv[$i] == "--help" ){$printhelp = true;}
    if($argv[$i] == "--debug" ){$debug = true;}
    if($argv[$i] == "--loglevel" and is_numeric($argv[$i+1])){$cfg['verbose'] = intval($argv[$i+1]); $i++;}
    if($argv[$i] == "--set-diak-quota" ){ $cfg['always_set_diak_quota'] = true;  }
    if($argv[$i] == "--create-groupdir"){ $cfg['groupdir_users'] = array($argv[$i+1]); $i++;}
    if($argv[$i] == "--manage-groupdirs" and is_string($argv[$i+1])){$cfg['manage_groupdirs'] = boolval($argv[$i+1]); $i++;}
    if($argv[$i] == "--manage-groups" and is_string($argv[$i+1])){$cfg['manage_groups'] = boolval($argv[$i+1]); $i++;}
    if($argv[$i] == "--allapot-tartas" and is_string($argv[$i+1])){$cfg['allapot_tartas'] = strval($argv[$i+1]); $i++;}
    if($argv[$i] == "--print-passwords" ){ $printpasswds = true;  }
    if($argv[$i] == "--print-config" ){ $printconfig = true;  }
    if($argv[$i] == "--dry-run" ){ $dryrun = true;  }
}

function print_help(){
    echo "".phpv()." mayor-nextcloud.php [kapcsolók] \n";
    echo "Kapcsolók: (felülbírálja a configot!)\n";
    echo "  --help                      :  Help kiírása. \n";
    echo "  --debug                     :  Ugyanaz mint a \"--loglevel 100\" \n";
    echo "  --config-file               :  Konfig fájl elérési útvonala.\n";
    echo "  --loglevel x                :  A bőbeszédűséget/logolást tudjuk ezzel szabályozni, ekkor ez az érték érvényesül, nem a configban megadott. \n";
    echo "  --set-diak-quota            :  Az összes diák qvótáját átállítja az \"\$cfg['diak_quota'] = x\" -nél megadott értékre, \n"; 
    echo "                                    csak kézzel futtatva működik, az automatikus, napi futtatásban nicns benne.\n";
    echo "  --create-groupdir <username>:  A távoktatást segítő könyvtárstruktúrát csak az <username> felhasználónak  hozza létre, \n";
    echo "                                    illetve kapcsoló nélküli híváskor, automatikusan, a napi futásban, esténként az összes tanárnak egyszerre.\n";
    echo "  --manage-groups <1/0>       :  Ha 1: A felhasználókat csoportokba rendezi a MaYor tankörök szerint, ha 0, nem foglalkozik vele.\n";
    echo "  --manage-groupdirs <1/0>    :  Ha 1: tankörmappákat hoz létre a tankör-csoportokhoz, ha 0, nem foglalkozik vele. (kell hozzá a --manage-groups is!)\n";
    echo "  --print-config              :  A betöltött konfig kiírása a konzolra.\n";
    echo "  --print-passwords           :  A létrehozott felhasználóknál a jelszavakat is megjeleníti a konzolon.\n";
    echo "  --dry-run                   :  Csak megmutatja, de nem végzi el a változtatásokat.\n";
    echo "\n\n";
}


function phpv() {   //Az aktuális php verzió lekérdezése
    $v = explode('.', phpversion());
    return "php".$v[0].".".$v[1];
}

function rndstr($l, $chs) { //Ál-Véletlen stringet generál
    return substr(str_shuffle($chs), 0, $l);
}

function gen_password($l = 12, $inp = array()){
    global $pwchars;
    return rndstr($l, $pwchars);
}
 
function gen_username($inp){            //Felhasználónevet generál
    global $search, $replace;
    $ret = str_replace($search, $replace, $inp['userAccount']);  // (pl: Á->Aa, á->aa, ...)
    return $ret;
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


if (function_exists('mysqli_connect') and version_compare(phpversion(), '5.0', '>=')) { //MySQLi (Improved) és php7  kell!

    function db_connect($db = ""){ 
        global $log,$cfg;
        if ($log['verbose'] > 0 ){  echo "***\tAdatbázis kapcsolódás. (db='".$db."')\n"; }
        $l = mysqli_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $db, $cfg['db_port']);
        if(!$l){
            if ($log['verbose'] > 0 ){ echo "*\tAdatbázis kapcsolat újrapróbálása... (db=''),  info:".mysqli_connect_errno()."\n\n"; }
            $db = "";
            $l = mysqli_connect($cfg['db_host'], $cfg['db_user'], $cfg['db_pass'], $db, $cfg['db_port']);
            if(!$l){
                echo "\n**** Sikertelen kapcsolódás! **** (db='".$db."') info:".mysqli_get_host_info($l)."\n\n";
                return null;    //Hiba esetén visszatér
            }
        }
        if ($log['verbose'] > 0 ){ echo "*\tSikeres kapcsolódás. (db='".$db."') info:".mysqli_get_host_info($l)."\n\n"; }
        mysqli_set_charset($l, "utf8");
        mysqli_query($l, "SET NAMES utf8 COLLATE utf8_general_ci;" );
        return $l;      //Egyébként a sikeressel tér vissza
    } 
    // bezár: mysqli_close($link); 

    function script_install($l){
        global $cfg,$log;
        if(mysqli_query($l, "SELECT * FROM ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register LIMIT 10;" ) == FALSE ){ //nincs nyilvántartás
            $q = "CREATE DATABASE IF NOT EXISTS ".$cfg['db_m2n_db']." DEFAULT COLLATE 'utf8_general_ci'; ";
            if ($log['verbose'] > 7 ){ echo "M2N -> \t".$q."\n\n"; }
            if(( $r = mysqli_query($l, $q)) !== FALSE ){
                if ($log['verbose'] > 0 ){ echo "*\tAz ".$cfg['db_m2n_db']." adatbázis sikeresen létrehozva.\n"; }
            }
            $q = "CREATE TABLE IF NOT EXISTS ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register (
                    account VARCHAR(64) NOT NULL COLLATE 'utf8_bin', 
                    status ENUM('active','disabled','forbidden','deleted') NULL DEFAULT 'active' COLLATE 'utf8_bin',
                    PRIMARY KEY (account)) 
                    COLLATE='utf8_general_ci' 
                    ENGINE=InnoDB;";
            if ($log['verbose'] > 7 ){ echo "M2N ->\t".$q."\n"; }
            if(( $r = mysqli_query($l, $q)) !== FALSE ){
                if ($log['verbose'] > 0 ){ echo "*\tAz ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register (script-katalógus) tábla sikeresen létrehozva.\n\n";}
            }
        }
    }


    function nxt_get_version(){
        global $occ_path,$occ_user,$cfg,$log;
        $ret = array();
        // sudo -u honlap-felho ".phpv()." /home/honlap-felho/web/occ status --output=json
        $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." status --output=json \"" ;
        if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        $ret[0] = shell_exec($e);
        $ret[1] = intval(explode(".", json_decode($ret[0],true)['version'])[0]); 
        if ($log['verbose'] > 10 ){ print_r($ret); echo "\n\n"; }
        return $ret;
    }
    
    function catalog_userlist($link){	//akiket a script hozott létre
        global $cfg,$log,$cfg;
        $ret['account'] = array();
        $ret['status'] = array();
        $q = "SELECT * FROM ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register WHERE STATUS != 'forbidden'; ";    
        if ($log['verbose'] > 7 ){ echo "M2N ->\t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                if(!in_array($row['account'], $cfg['kihagy'])){
                    $ret['account'][] = $row['account'];
                    $ret['status'][] = $row['status'];
                }
            }
            mysqli_free_result($r);
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó m2n nyilvántartás lekérdezése.\n"; }
            return $ret;
            if ($log['verbose'] > 10 ){ print_r($ret); }
        } else {
            echo "\nM2N -> \t**** Adatbázislekérdezési hiba! ****\n";
        }
    }
    
    function catalog_forbiddenlist($link){      //akiket a rendszergazda kitiltott
        global $log,$cfg,$cfg;
        $q = "SELECT * FROM ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register WHERE STATUS = 'forbidden'; ";
        if ($log['verbose'] > 7 ){ echo "M2N ->\t".$q."\n"; }
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            $ret = array();
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret[] = $row['account'];
            }
            mysqli_free_result($r);
            $ret = array_merge($ret, $cfg['kihagy']);
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-letiltások m2n nyilvántartás lekérdezése.\n"; }
            return $ret;
        } else {
            echo "\nM2N ->\t**** Adatbázislekérdezési hiba! ****\n";
        }
    }
    
    function catalog_useradd($link, $account){	// feljegyzi az általa létrehozott felhasználókat
        global $log,$cfg,$dryrun;
        $q = "INSERT INTO ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register (account) VALUES ('".mysqli_real_escape_string($link, $account)."')";
        if ($log['verbose'] > 7 ){ echo "M2N -> \t".$q."\n"; }
        if( $dryrun or (mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-hozzáadás, m2n nyilvántartásba vétele.\n"; }
        } 
    }

    function catalog_userena($link, $account){	// az engedélyezetteket
        global $cfg,$log,$dryrun;
        $q = "UPDATE ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register SET status='active' WHERE account='".mysqli_real_escape_string($link, $account)."'";
        if ($log['verbose'] > 7 ){ echo "M2N ->\t".$q."\n"; }
        if( $dryrun or (mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 4 ){ echo "*\tFelhasználó-engedélyezés, m2n nyilvántartásba vétele.\n" ;}
        }
    }

    function catalog_userdel($link, $account){	// a törölteket
        global $cfg,$log,$dryrun;
        $q = "DELETE FROM ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register WHERE account='".mysqli_real_escape_string($link, $account)."' ";
        if ($log['verbose'] > 7 ){ echo "M2N ->\t".$q."\n"; }
        if( $dryrun or (mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 5 ){ echo "*\tFelhasználó-törlés, m2n nyilvántartásba vétele.\n"; }
        }
    }
    
    function catalog_userdis($link, $account){	// a letiltottakat
        global $cfg,$cfg,$log,$dryrun;
        $q = "UPDATE ".$cfg['db_m2n_db'].".".$cfg['db_m2n_prefix']."register SET status='disabled' WHERE account='".mysqli_real_escape_string($link, $account)."'";
        if ($log['verbose'] > 7 ){ echo "M2N ->\t".$q."\n"; }
        if( $dryrun or (mysqli_query($link, $q)) !== FALSE ){
            if ($log['verbose'] > 5 ){ echo "*\tFelhasználó-letiltás, m2n nyilvántartásba vétele.\n"; }
        }
    }
    
    function user_add($userAccount, $fullName){		// létrehoz egy felhasználót a Nextcloud-ban
        global $occ_path,$occ_user,$cfg,$log,$dryrun;
        $ret = array();
//  	export OC_PASS=ErősJelszó123; su -s /bin/sh www-data -c '".phpv()." web/occ user:add --password-from-env  --display-name="Teszt Tamás" --group="csop" t.tamas'
        if(strlen($userAccount) > 64 or strlen($fullName) > 64){
            echo "\n******** Hiba: A felahsználónév, vagy a \"teljes név\" hosszabb, mint 64 karakter! NEM hozható létre!! ********\n";
        } else {
            $pw = gen_password(16);
            $ret[1] = $pw;
            $e = "export OC_PASS=".escp($pw)."; su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:add  --password-from-env --display-name=".escp($fullName)." --group=".escp($cfg['mindenki_csop'])." ".escp($userAccount)." \"" ;
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun){ $ret[0] = shell_exec($e); } else { $ret[0] = true; }
            if ($log['verbose'] > 11 ){ print_r($ret); }
        }
        return $ret;
    }

    function user_del($userAccount){	// kitöröl vagy letilt egy felhasználót a Nextcloud-ban
	global $occ_path,$occ_user,$log,$dryrun;
        $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:info ".escp($userAccount)." --output=json \"";
        if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        $last_login = json_decode(shell_exec($e),true)['last_seen'] ;
        if($last_login == "1970-01-01T00:00:00+00:00" ){	
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:delete ".escp($userAccount)." \"";		// Ha még soha nem lépett be
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }											// akkor törölhető
            if ($log['verbose'] > 11 ){ print_r($ret); }
        } else {
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:disable ".escp($userAccount)." \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }											// különben csak letiltja
            if ($log['verbose'] > 11 ){ print_r($ret); }
        }
        
    }
    
    function user_info($userAccount){	// User állpot a Nextcloudban
        global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:info ".escp($userAccount)." --output=json \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            $ret = (array)json_decode(shell_exec($e),true);
            if ($log['verbose'] > 10 ){ print_r($ret); }
            return $ret;
    }


    function user_dis($userAccount){	// letiltja a felhasználót a Nextcloud-ban
        global $occ_path,$occ_user,$log,$dryrun;
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:disable ".escp($userAccount)." \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
            if ($log['verbose'] > 11 ){ print_r($ret); }
    }

    function user_ena($userAccount){	// engedélyezi
        global $occ_path,$occ_user,$log,$dryrun;
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:enable ".escp($userAccount)." \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
            if ($log['verbose'] > 11 ){ print_r($ret); }
    }


    function nxt_group_list() {		// Csoportok listázása a Nextcloud-ból
            global $occ_path,$occ_user,$log;
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." group:list --limit=1000000 --output=json \"";  //* Jó nagy limittel dolgozzunk
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            $ret = (array)json_decode(shell_exec($e),true);
            if ($log['verbose'] > 10 ){ print_r($ret); }
            return $ret;
    }
    
    function nxt_user_list() {		// Felhasználók listázása a Nextcloud-ból
            global $occ_path,$occ_user,$log;
        //    $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:report | grep 'total' | sed -e 's/[^0-9]//g' | tr -d '[:blank:]\n' \"";
        //    if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        //    $num = shell_exec($e); 
            $num = 1000000;      //inkább kézi limit!
        //    $num = $num + 100; 	// Biztos-ami-biztos, a nextcloud rejtett hibái miatt...
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:list --limit ".escp($num)." --output=json \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            $ret =  (array)json_decode(shell_exec($e),true);
            if ($log['verbose'] > 10 ){ print_r($ret); }
            return $ret;
    }
    
    function nxt_user_lastlogin($userAccount){ 	// legutóbbi belépés lekérdezése
        global $occ_path,$occ_user,$log;
        $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:info ".escp($userAccount)."  --output=json \"";
        if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        $ret = json_decode(shell_exec($e),true)['last_seen'];
        if ($log['verbose'] > 10 ){ print_r($ret); }
        return $ret;
    }

    
    function user_set($userAccount, array $params){	//beállítja az e-mailt, quota-t, nyelvet a kapott értékekre
        global $occ_path,$occ_user,$log,$dryrun;        
        if(isset($params['quota'])){
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:setting ".escp($userAccount)." files quota ".escp($params['quota'])." \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun){ shell_exec( $e ); }
        }
        if(isset($params['email'])){
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:setting ".escp($userAccount)." settings email ".escp($params['email'])." \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun){ shell_exec( $e ); }
        }
        if(isset($params['lang'])){
            $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." user:setting ".escp($userAccount)." core lang ".escp($params['lang'])." \"";
            if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
            if(!$dryrun) { shell_exec($e); }
        }
    } 
    


    function group_add($groupName){ 	//Új csoport létrehozása a Nextcloud-ban 
        global $occ_user,$occ_path,$link,$cfg,$log,$nxt_version,$dryrun;
        $groupName = rmnp($groupName);
        if(strlen($groupName) > 64){	//mivel (egyelőre) nics erre 'occ' parancs, ezért közvetlenül kell...
            echo "\n****** Hiba: a csoportnév nagyobb, mint 64 karakter!! ******\n";
        } else {
            if($nxt_version < 14) {
                $q = "INSERT IGNORE INTO ".mysqli_real_escape_string($link, $cfg['db_nxt_dbname']).".".mysqli_real_escape_string($link, $cfg['db_nxt_prefix'])."groups (gid) VALUES ('".mysqli_real_escape_string($link,$groupName)."'); ";
                if ($log['verbose'] > 7 ){ echo "NXT ->\t".$q."\n"; }
                if(!$dryrun){
                    if( mysqli_query($link, $q) !== TRUE ) { echo "\nNXT -> \t****** Csoport létrehozási hiba. (adatbázis) ******\n"; }
                }
            } else {
                $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." group:add ".escp($groupName)." \"";
                if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
                if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
                if ($log['verbose'] > 11 ){ print_r($ret); }
            }
        }
    } 
    
    function group_del($groupName, $grp){	// Csoport törlése a Nextcloud-ból
        global $occ_user,$occ_path,$cfg,$link,$log,$cfg,$nxt_version,$dryrun;
        $groupName = rmnp($groupName);
        if(isset($grp[$groupName])){
	        if($nxt_version < 14){	// Mivel erre csak a Nextcloud 14-től van "occ" parancs, ezért néha közvetlenül kell...

                foreach($grp[$groupName] as $key => $user){
                    $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." group:removeuser ".escp($groupName)." ".escp($user)." \"";
                    if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
                    if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
                    if ($log['verbose'] > 11 ){ print_r($ret); }
                    if ($log['verbose'] > 1 ){ echo "*--\t\tTörölve".po(" ($user) a: $groupName",$cfg['csoportnev_hossz']+5,1)."\t csoportból.\n"; }
                }
                $q = "DELETE FROM ".mysqli_real_escape_string($link, $cfg['db_nxt_dbname']).".".mysqli_real_escape_string($link,$cfg['db_nxt_prefix'])."groups WHERE gid='".mysqli_real_escape_string($link, $groupName)."'; " ;
                if ($log['verbose'] > 7 ){ echo "NXT ->\t".$q."\n"; }
                if(mysqli_query($link, $q) !== TRUE ) echo "\n NXT -> \t****** csoport törlési hiba. (adatbázis) ******\n";
                
            } else {
                $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." group:delete ".escp($groupName)." \"";
                if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
                $ret = shell_exec($e);  
                if ($log['verbose'] > 11 ){ print_r($ret); }
            }
        }
    }

    function group_user_add($groupName, $userAccount){	// Hozzáad egy felhasználót egy csoporthoz a Nextcloud-ban
        global $occ_user, $occ_path,$log,$dryrun;
        $e = "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." group:adduser ".escp($groupName)." ".escp($userAccount)." \"";
        if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
        if ($log['verbose'] > 11 ){ print_r($ret); }
    }

    function group_user_del($groupName, $userAccount){	// Kitöröl egy felhasználót egy Nextcoud csoportból
        global $occ_user, $occ_path,$log,$dryrun;
        $e =  "su -s /bin/sh $occ_user -c \"".phpv()." ".escp($occ_path."/occ")." group:removeuser ".escp($groupName)." ".escp($userAccount)." \"";
        if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
        if ($log['verbose'] > 11 ){ print_r($ret); }
    }


    function create_dir($user, $path){	    // Készít egy mappát a: data/$user/files/$path alá
        global $occ_user, $occ_path,$log,$dryrun;
        $ret = null;
        if(!file_exists($occ_path."/data/".$user."/files/".$path)){                      // Ha Még nem létezik
            if(!$dryrun){
                $ret = @mkdir($occ_path."/data/".$user."/files/".$path, 0755, true);            // Akkor létrehozza
                chown($occ_path."/data/".$user."/files/".$path, $occ_user);
                chgrp($occ_path."/data/".$user."/files/".$path, $occ_user);
            } else { $ret = true; }
            if($ret === true && $log['verbose'] > 7) { echo "php ->\tDIR: \"".$occ_path."/data/".$user."/files/".$path."\" \t created.\n"; }
            if($ret === false && $log['verbose'] > -1) { echo "php ->\tDIR: \"".$occ_path."/data/".$user."/files/".$path."\" \t makedir failed!!\n"; } //mondjuk ilyen egyáltalán mikor lehet?
        }
        if ($log['verbose'] > 11 ){ print_r($ret); }
        return $ret;
    }


    function write_tofile($user, $path, $msg ){	            // Fájlba írja a $msg tartalmát
        global $occ_user, $occ_path,$log,$dryrun;
        $ret = 0;
        if( ($h = @fopen($occ_path."/data/".$user."/files/".$path, 'w+')) !== false ){
            if(!$dryrun){
                $ret = fwrite($h, $msg );
            } else { $ret = strlen($msg); }
            fclose($h);
            if(!$dryrun){
                chown($occ_path."/data/".$user."/files/".$path, $occ_user);
                chgrp($occ_path."/data/".$user."/files/".$path, $occ_user);
            }
            if($log['verbose'] > 7) { echo "php ->\tFILE: \"".$occ_path."/data/".$user."/files/".$path."\" \t created.\n"; }
        } else {
            if($log['verbose'] > 5) { echo "php ->\tFILE ERROR: \"".$occ_path."/data/".$user."/files/".$path."\" \t dir not found.\n"; }
        }
        if ($log['verbose'] > 11 ){ print_r($ret); }
        return $ret;
    }

    function files_scan($user, $path ){                     // Nextcloud files:scan --path=xxx
        global $occ_user, $occ_path,$log,$dryrun;
        $e =  "su -s /bin/sh $occ_user -c \"".phpv()." '".$occ_path."/occ' files:scan --path=".escp($user."/files/".$path)."   \"";  // -v 
        if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
        if ($log['verbose'] > 11 ){ print_r($ret); }
    }

    function user_notify($user, $msg, $title ){             // Nextcloud értesítés
        global $occ_user, $occ_path, $log,$dryrun;
        $e =  "su -s /bin/sh $occ_user -c \"".phpv()." '".$occ_path."/occ' notification:generate -l ".escp($msg)." -- ".escp($user)." ".escp($title)." \"";
        if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
        if(!$dryrun){ $ret = shell_exec($e); } else { $ret = true; }
        if ($log['verbose'] > 11 ){ print_r($ret); }
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
        if ($log['verbose'] > 11 ){ print_r($ret); }
        return $ret;
    }

    function clean_dir($user, $path, $tankorei){    //Tankörmappák kitisztítása (path: mappagyökér)
        global $occ_user, $occ_path, $log, $cfg, $dryrun;
        $listdir = scan_dir($user, $path);
        $ret[0] = array();
        $ret[1] = array();
        $ret[2] = array();
        foreach($listdir as $key => $val) {
            if((!in_array($val, $tankorei) && !in_array(basename($val,"_beadás"), $tankorei) || !is_dir($occ_path."/data/".$user."/files/".$path."/".$val)) && $val != "INFO.txt"){              //Nincs a tanköreiben, akkor  törölni kell (de csak ha üres)    

                if(is_dir($occ_path."/data/".$user."/files/".$path."/".$val) && empty(scan_dir($user, $path."/".$val))){        // Ha mappa, és üres -> törlés
                    if(!$dryrun){ rmdir($occ_path."/data/".$user."/files/".$path."/".$val); }
                    $ret[0][] = $val;
                    if($log['verbose'] > 7) { echo "php ->\tDIR: \"".$occ_path."/data/".$user."/files/".$path."/".$val."\" deleted.\n"; }    

                } else {    //Nem mappa, vagy nem üres
                    if(file_exists( $occ_path."/data/".$user."/files/".$path."/".pathinfo(basename($occ_path."/data/".$user."/files/".$path."/".$val ,".please-remove"))['basename'])){       //Ha az eredeti könyvtár  vagy fájl él
                        if(!$dryrun){ rename($occ_path."/data/".$user."/files/".$path."/".$val, $occ_path."/data/".rnescp($user."/files/".$path."/".basename($val, '.please-remove').".".time().".please-remove")); }
                        $ret[1][] = basename($val, '.please-remove').".".time().".please-remove";
                        user_notify($user,"Az ön >>".$path."/<< könyvtárában tiltott helyen lévő fájl, vagy olyan (tankör)mappa található, amely tankörnek ön továbbá már nem tagja.   Kérem helyezze el kívül a >>".$path."/<< mappán, vagy törölje belőle!  Eltávolításra megjeleölve! A fájl átnevezve, új neve -->   ".rnescp(basename($val, '.please-remove').".".time().".please-remove"), "Fájl/Mappa rossz helyen! --> ".rnescp($path."/".basename($val, '.please-remove').".".time().".please-remove" ));
                        if($log['verbose'] > 7) { echo "php ->\tF/D: \"".$occ_path."/data/".$user."/files/".$path."/".$val."\" \t renamed -> ".rnescp(basename($val, '.please-remove').".".time().".please-remove")."\n"; }
                    } else {    
                        // A Hanyagul otthagyottakért csak figyelmeztessen:
                        user_notify($user,"Az ön >>".$path."/<< könyvtárában tiltott helyen lévő fájl, vagy olyan (tankör)mappa található, amely tankörnek ön továbbá már nem tagja.   Kérem helyezze el kívül a >>".$path."/<< mappán, vagy törölje belőle!  Eltávolításra megjelölve! --> ".$val, "Fájl/Mappa rossz helyen! --> ".$path."/".$val );
                    }
                }
            } else {
                $ret[2][] = $val;
            }
        }
        if ($log['verbose'] > 12 ){ print_r($ret); }
        return $ret;
    }
 

    function groupdir_create_root($user, $oktId, $path){                //Tankörmappák gyökerét előállítja $path=tankörgyökér
        global $cfg, $occ_path, $occ_user,$log,$dryrun;
        $ret = array(false, false);
        if((empty($cfg['groupdir_users']) || in_array($user, $cfg['groupdir_users'])) && $oktId > 0 && $cfg['manage_groupdirs'] === true){   //Ha null -> mindenki, Ha "user" -> scak neki, && tanár && groupdir bekapcsolava
            
            if(is_file($occ_path."/data/".$user."/files/".$path) || is_link($occ_path."/data/".$user."/files/".$path)){     //Ha már vam ott valami ilyen fájl 
                if(!$dryrun){ rename($occ_path."/data/".$user."/files/".$path, $occ_path."/data/".rnescp($user."/files/".$path.".".time().".please-remove")); }   //Átnevezi, hogy azért mégse vasszen oda
                echo "php ->\tFILE: \"".$occ_path."/data/".$user."/files/".$path."\" \t \t moved away!!!\n"; 
                user_notify($user,"Fájl:  >>".$path.".please-remove<<  Illegális helyen volt. Server által eltávolítva.", "Fájl: >>".$path."<< eltávolítva!");
                files_scan($user, "" ); //Ekkor az egész $user/files mappát szkenneli
            } 
            $ret[0] = create_dir($user, rmnp($path));                                             // Tankörmappa gyökér létrehozása
            $ret[1] = write_tofile($user, $path."/"."INFO.txt", $cfg['infotxt_szöveg']);    // INFO.txt (Újra)Írása.
            if($ret[0] === true){                                                           // Ha frissen létrehozott mappa, akkor az egész userre kell jogot adni
                $e =  "/bin/chown -R ".escp($occ_user.":".$occ_user)." ".escp($occ_path."/data/".$user."/")." ";
                if($log['verbose'] > 7) { echo "bash ->\t".$e."\n"; }
                if(!$dryrun){ shell_exec($e); } 
                files_scan($user, $path);
            }
        }     
        if ($log['verbose'] > 12 ){ print_r($ret); }
        return $ret; 
    }

    function groupdir_create_groupdir($user, $oktId, $path){        // $path = tankörmappa
        global $cfg,$log;
        $ret = false;
        if( basename($path,"_beadás") != $cfg['mindenki_tanar'] and basename($path,"_beadás") != $cfg['mindenki_diak'] and basename($path,"_beadás") != $cfg['mindenki_csop']){   //Ezekre a csoportokra minek? 
            if((empty($cfg['groupdir_users']) || in_array($user, $cfg['groupdir_users'])) && $oktId > 0 && $cfg['manage_groupdirs'] === true){   
                $ret = create_dir($user, rmnp($path));                                                // Tankörmappa létrehozása
                if($ret === true){
                    files_scan($user, $path);
                }
            }    
            if ($log['verbose'] > 12 ){ print_r($ret); } 
            return $ret;
        }
    }

    function groupdir_finish($user, $oktId, $path, $tankorei ){     //$path=tankörgyökér
        global $cfg,$log;
        $ret = array(array(),array(),array(),false,false);      //return sekelton
        if((empty($cfg['groupdir_users']) || in_array($user, $cfg['groupdir_users'])) && $oktId > 0 && $cfg['manage_groupdirs'] === true){   
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
        if ($log['verbose'] > 12 ){ print_r($ret); }
        return $ret;
    }




    function add_tk_to_users($list, $user, $tankorname){      //Naplón kívüli csoportokat adhatunk a felhasználókhoz
        global $log;
        $curr = "";
        foreach($list as $key => $val){                       // Csak rendezett tömbökön!
            if($curr != $val['userAccount'] && ($user === null or ($user !== null && $val['userAccount'] == $user ))){ //Vagy mindenki vagy adott user + rendezett lista
                
                if(!isset($val['tanarId'])){        //workaround
                    $val['tanarId'] = 0;
                }
                if(!isset($val['diakId'])){         //workaround
                    $val['diakId'] = 0;
                }
                $list = array_merge($list, array( $val));
                $list[$key]['tankorNev'] = $tankorname;
                $list[$key]['tankorId'] = 0;
                    
                if($user !== null && $val['userAccount'] == $user ){    // Null -> mindenkihez, "user" -> csak neki
                    break;
                }
                $curr = $val['userAccount'];
            }
        }
        if ($log['verbose'] > 15 ){ print_r($list); }
        return $list;    
    }

    function set_param_to_user($list, $user, $paramname, $param){       // Paramétert állít be a felhasználónak.
        global $log;
        foreach($list as $key => $val){                                 // Csak rendezett tömbökön! (vagy mégsem?)
            if($user === null or ($user !== null && $val['userAccount'] == $user )){ //Vagy mindenki vagy adott user 

                $list[$key][$paramname] = $param;    // A paraméter
            }
        }
        if ($log['verbose'] > 15 ){ print_r($list); }
        return $list;    
    } 

    function mayor_userlistcmp($a, $b){
        // return strcmp($a['oId'], $b['oId']);     //Ez lenne a jó, de az átfedések miatt nem működik
        return strcmp($a['userAccount'], $b['userAccount']);    //Pillanatnyilag csak az az egyedi
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

    function get_mayor_szemeszter($link, $date) {
        global $cfg,$log;
        $ret = array();
        $q = "SELECT * FROM intezmeny_".$cfg['isk_rovidnev'].".szemeszter 
                WHERE (statusz = 'aktív' OR statusz = 'lezárt') AND kezdesDt <= '".$date."'  
                AND '".$date."' <= (SELECT MAX(zarasDt)  FROM intezmeny_".$cfg['isk_rovidnev'].".szemeszter WHERE statusz = 'aktív' OR statusz = 'lezárt' )
		        AND '".$date."' >= (SELECT MIN(kezdesDt) FROM intezmeny_".$cfg['isk_rovidnev'].".szemeszter WHERE statusz = 'aktív' OR statusz = 'lezárt' )
                ORDER BY zarasDt DESC 
                LIMIT 1; 
        ";
        if ($log['verbose'] > 7 ){ echo "MAY ->\t".$q."\n"; }
        if( ($r = mysqli_query($link, $q)) !== FALSE ){
            $ret = mysqli_fetch_array($r, MYSQLI_ASSOC); 
            if(!empty($ret)){  $ret["reqDt"] = $date; }             //szükség lehet a megkérdezett dt-re is
            mysqli_free_result($r);
        } else {
            echo "\nMAY ->\t ******** Mayor_napló (szemeszter)lekérdezési hiba. (adatbázis) ********\n";
        }
        if ($log['verbose'] > 10 ){ print_r($ret); }
        return $ret;
    }




    function get_mayor_tankor($link, $date){				// A tankörök neveinek lekérdezése a mayorból
        global $cfg,$log;
        $ret = array();
        $req_oszt = "'#'";
        foreach($cfg['min_osztalyok'] as $key => $val){		//A megadott konkrét osztályokra
            $req_oszt .= ",'$val'";
        }
        $szm = get_mayor_szemeszter($link, $date);
        if(!empty($szm)){
            //Létező összes tankör:
            /* $q = "SELECT tankorId, TRIM(BOTH ' ' FROM CONCAT('".$cfg['csoport_prefix']."',tankorNev)) AS tankorNev
                    FROM intezmeny_".$cfg['isk_rovidnev'].".tankorSzemeszter
                    WHERE tanev = '".$szm['tanev']."' AND szemeszter = '".$szm['szemeszter']."';	 
                ";
            */             
            //csak a megadott évfeolyamokhoz kötődő tankörök:
                $q = "SELECT tankorId, TRIM(BOTH ' ' FROM CONCAT('".$cfg['csoport_prefix']."',tankorNev)) AS tankorNev
                    FROM intezmeny_".$cfg['isk_rovidnev'].".tankorSzemeszter
                    WHERE tanev = '".$szm['tanev']."' AND szemeszter = '".$szm['szemeszter']."' AND tankorId IN(
                        SELECT tankorId
                        FROM intezmeny_".$cfg['isk_rovidnev'].".tankorOsztaly
                        WHERE osztalyId IN (
                            SELECT osztalyId
                            FROM naplo_".$cfg['isk_rovidnev']."_".$szm['tanev'].".osztalyNaplo
                            WHERE evfolyamJel >= ".$cfg['min_evfolyam']."  OR osztalyJel IN(".$req_oszt.") 
                            ORDER BY osztalyId )
                        ORDER BY tankorId ) 
                    ORDER BY tankorNev; 
                ";
            if ($log['verbose'] > 7 ){ echo "MAY ->\t".$q."\n"; }
            if(( $r = mysqli_query($link, $q)) !== FALSE ){
                while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                    $ret[] = $row;
                }
                mysqli_free_result($r);
            } else {
                echo "\nMAY ->\t ******** Mayor_napló (tankör)lekérdezési hiba. (adatbázis) ********\n";
            }
        } else {
            echo "\nMAY ->\t ******** Mayor_napló (tankör)lekérdezési hiba. (Nincs ilyen Szemeszter: ".$date."!) ********\n";
        }
        if ($log['verbose'] > 10 ){ print_r($ret); }
        return $ret;
    }

    
    function get_mayor_tanar($link, $date){		// A tanárok lekérdezése a mayorból
        global $cfg,$log;
        $ret = array();
        $szm = get_mayor_szemeszter($link, $date);
        if(!empty($szm)){
            $q = "SELECT accounts.userAccount, accounts.mail, tanar.email, tanar.tanarId, tankorTanar.tankorId, tanar.oId, '' AS employeeId, 
                        tanar.beDt, tanar.kiDt, '' AS kezdoTanev, '' AS vegzoTanev, '' AS jel, '' AS beEv, '' AS osztalyJel,
                        tanar.viseltNevElotag, tanar.viseltCsaladinev, tanar.viseltUtonev,
                        tanar.szuletesiHely,tanar.szuletesiIdo,  '' as lakhelyOrszag, '' as lakhelyHelyseg, '' as lakhelyIrsz,
                        '' AS lakHely, '' AS telefon, '' AS mobil, tanar.statusz, 'Tanár' AS beoszt, 
                        TRIM(BOTH ' ' FROM CONCAT_WS(' ',viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS fullName, 
                        TRIM(BOTH ' ' FROM CONCAT('".$cfg['csoport_prefix']."',tankorNev)) AS tankorNev
                    FROM intezmeny_".$cfg['isk_rovidnev'].".tanar, mayor_private.accounts, intezmeny_".$cfg['isk_rovidnev'].".tankorTanar, intezmeny_".$cfg['isk_rovidnev'].".tankorSzemeszter
                    WHERE accounts.studyId = tanar.oId AND statusz != 'jogviszonya lezárva' 
                    AND ( (tanar.beDt <= '".$szm['reqDt']."' AND ( tanar.kiDt >= '".$szm['reqDt']."' OR tanar.kiDt IS NULL)) 
						/*OR (tanar.beDt <= CURRENT_DATE()  AND (CURRENT_DATE() <= tanar.kiDt OR tanar.kiDt IS NULL))*/ )
                    AND tanar.tanarId = tankorTanar.tanarId 
                    AND  ((tankorTanar.beDt <= '".$szm['reqDt']."'  AND tankorTanar.kiDt >= '".$szm['reqDt']."'  ) 
						/*OR (tankorTanar.beDt <= CURRENT_DATE() AND CURRENT_DATE() <= tankorTanar.kiDt )*/ )
                    AND tankorTanar.tankorId = tankorSzemeszter.tankorId 
                        AND tankorSzemeszter.tanev = '".$szm['tanev']."' AND tankorSzemeszter.szemeszter = '".$szm['szemeszter']."'
                    /* ORDER BY oId; */
                    ORDER BY userAccount ;
                ";
            if ($log['verbose'] > 7 ){ echo "MAY ->\t".$q."\n"; }  
            if(( $r = mysqli_query($link, $q)) !== FALSE ){
                while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                    $ret[] = $row;
                }
                mysqli_free_result($r);
            } else {
                echo "\nMAY ->\t ******** Mayor_napló (tanár)lekérdezési hiba. (adatbázis) ********\n";
            }
        } else {
            echo "\nMAY ->\t ******** Mayor_napló (tanár)lekérdezési hiba. (Nincs ilyen Szemeszter: ".$date."!) ********\n";
        }
        if ($log['verbose'] > 10 ){ print_r($ret); }
        return $ret;
    }
    
    
    function get_mayor_diak($link, $date){	// diákok lekérdezése
        global $cfg,$log;
        $ret = array();
        $req_oszt = "'#'";
        foreach($cfg['min_osztalyok'] as $key => $val){         //A megadott konkrét osztályokra
            $req_oszt .= ",'$val'";
        }
        $szm = get_mayor_szemeszter($link, $date);
        if(!empty($szm)){
            $q = "SELECT accounts.userAccount, accounts.mail, diak.email, diak.diakId, tankorDiak.tankorId, diak.oId, diak.diakigazolvanySzam AS employeeId, 
                        diak.jogviszonyKezdete AS beDt, diak.jogviszonyVege AS kiDt, osztaly.kezdoTanev, osztaly.vegzoTanev, osztaly.jel, diak.kezdoTanev AS beEv,
                        osztalyNaplo.osztalyJel, diak.viseltNevElotag, diak.viseltCsaladinev, diak.viseltUtonev, 
                        diak.szuletesiHely, diak.szuletesiIdo, diak.lakhelyOrszag, diak.lakhelyHelyseg, diak.lakhelyIrsz, 
                        TRIM(BOTH ' ' FROM CONCAT_WS(' ',diak.lakhelyKozteruletNev, diak.lakhelyKozteruletJelleg, diak.lakhelyHazszam, diak.lakhelyEmelet, diak.lakhelyAjto)) AS lakHely,
                        diak.telefon, diak.mobil, diak.statusz,
                        TRIM(BOTH ' ' FROM CONCAT_WS(' ',viseltNevElotag, viseltCsaladinev, viseltUtonev)) AS fullName, 
                        TRIM(BOTH ' ' FROM CONCAT('".$cfg['csoport_prefix']."',tankorNev)) AS tankorNev
                    FROM intezmeny_".$cfg['isk_rovidnev'].".diak, mayor_private.accounts, intezmeny_".$cfg['isk_rovidnev'].".tankorDiak, intezmeny_".$cfg['isk_rovidnev'].".tankorSzemeszter, 
                         intezmeny_".$cfg['isk_rovidnev'].".osztalyDiak, intezmeny_".$cfg['isk_rovidnev'].".osztaly, naplo_".$cfg['isk_rovidnev']."_".$szm['tanev'].".osztalyNaplo
                    WHERE diak.diakId IN (
                        SELECT diakId
                        FROM intezmeny_".$cfg['isk_rovidnev'].".osztalyDiak
                        WHERE osztalyId IN (
                            SELECT osztalyId
                            FROM naplo_".$cfg['isk_rovidnev']."_".$szm['tanev'].".osztalyNaplo
                            WHERE evfolyamJel >= ".$cfg['min_evfolyam']." OR osztalyJel IN(".$req_oszt.") 
                            ORDER BY osztalyId)
                        ORDER BY diakId) 
                    AND diak.statusz != 'jogviszonya lezárva' AND diak.statusz != 'felvételt nyert' AND diak.oId = accounts.studyId 
                    AND tankorDiak.diakId = diak.diakId 
                    AND ((tankorDiak.beDt <= '".$szm['reqDt']."' AND (tankorDiak.kiDt IS NULL OR tankorDiak.kiDt >= '".$szm['reqDt']."' ))
						/*OR (tankorDiak.beDt <= CURRENT_DATE() AND (tankorDiak.kiDt IS NULL OR tankorDiak.kiDt >= CURRENT_DATE() ))*/ )
                    AND tankorSzemeszter.tankorId = tankorDiak.tankorId 
                        AND tankorSzemeszter.tanev = '".$szm['tanev']."' AND tankorSzemeszter.szemeszter = '".$szm['szemeszter']."'
                    AND osztalyDiak.diakId = diak.diakId
                        AND (osztalyDiak.beDt <= '".$szm['reqDt']."' AND (osztalyDiak.kiDt >= '".$szm['reqDt']."' OR osztalyDiak.kiDt IS NULL))   
                    AND osztaly.osztalyId = osztalyDiak.osztalyId
                    AND osztalyDiak.osztalyId = osztalyNaplo.osztalyId
                    /* ORDER BY oId ; */
                    ORDER BY userAccount ;
                ";
            if ($log['verbose'] > 7 ){ echo "MAY ->\t".$q."\n"; }
            if(( $r = mysqli_query($link, $q)) !== FALSE ){
                while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) { 
                    $ret[] = $row;
                }
                mysqli_free_result($r);
            } else {
                echo "\nMAY ->\t ******** Mayor_napló (diák)lekérdezési hiba. (adatbázis) ********";
            }
        } else {
            echo "\nMAY ->\t ******** Mayor_napló (diák)lekérdezési hiba. (Nincs ilyen Szemeszter: ".$date."!) ********";
        }
        if ($log['verbose'] > 10 ){ print_r($ret); }
        return $ret;
    }
    


//--------------------------------------------------------------------------------------------------------------------------------------------//
// RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN  --  RUN //
//--------------------------------------------------------------------------------------------------------------------------------------------//
  

    if(true){ echo "\n\n###################################################\n" ;}
    if(true){ echo "########       Mayor-Nextcloud Script      ########\n"; }
    if(true){ echo "########    Start: ".date("Y-m-d H:i:s")."     ########\n"; }
    $t_start = microtime(true); 
    
    if(!isset($cfg['allapot_tartas']) or empty($cfg['allapot_tartas']) or $cfg['allapot_tartas'] == "1970-01-01"){      //A jelölt nap állapotának betöltése
        $cfg['allapot_tartas'] = date("Y-m-d");
    }
    if(true) { echo "######## A (".$cfg['allapot_tartas'].")-i állapot szerint. ########\n"; }
    if(true){ echo "###################################################\n" ;}

    if(true) { echo "\n***	M2N Config betöltése: ($cfgfile fájlból.) ***\n\n"; }
    if($printconfig === true or $debug === true){
        echo "\n Betöltött Konfig:\n";
        $m2l = $cfg;
        $m2l['infotxt_szöveg'] = "<-- TEXT -->";    //Túl hosszú lenne kiprintelni
        var_export($m2l);
        echo "\n";
    }
    $log['verbose'] = $cfg['verbose'];
    if($debug === true) {$log['verbose'] = 1000;    $printpasswds = true;}
    if( $cfg['always_set_diak_quota'] === true && $log['verbose'] < 4 ){    $log['verbose'] = 4; }


    

//-------------------------------------------------------------------------------------------------------------------------------    
    $ret = nxt_get_version();
    $nxt_version = $ret[1];
    if($ret[1] < 13){         //Nextcloud 13-tól támogatott
        echo "\n\n******** Legalább Nextcloud 13-mas verzió szükséges! ********\n\n";
        echo $ret[0]."\n\n";
        die();
    }
    if($printhelp === true){
        print_help();
        die();
    }
    if ($cfg['manage_groupdirs'] === true && $cfg['manage_groups'] === false){
        print_help();
        die();
    }


    if(($link = db_connect($cfg['db_m2n_db'])) == FALSE){			//csatlakozás
        echo "\n******** MySQL (general) kapcsolat hiba. ********\n";
        echo "\n******** Script leáll... ********\n";
        die();
    }
    script_install($link);      // $link -> script, $link2 -> mayor
    
       
    //ha a mayor egy másik szerveren lenne 
    if(!empty($cfg['db_mayor_user'])){ $cfg['db_user'] = $cfg['db_mayor_user']; }			
    if(!empty($cfg['db_mayor_pass'])){ $cfg['db_pass'] = $cfg['db_mayor_pass']; } 
    if(!empty($cfg['db_mayor_host'])){ $cfg['db_host'] = $cfg['db_mayor_host']; } 
    if(!empty($cfg['db_mayor_port'])){ $cfg['db_port'] = $cfg['db_mayor_port']; }
              
    if(($link2 = db_connect()) == FALSE){
        echo "\n******** MySQL (mayor) kapcsolat hiba. ********\n";
        echo "\n******** Script leáll... ********\n";
        die();
    } else {
        if ($log['verbose'] > 0 ){ echo "***\tMayor DB connected.\n"; }
    }

    // group_add($cfg['mindenki_csop']);				// A "mindenki" csoport hozzáadása (később adjuk hozzá)
    // group_add($cfg['mindenki_tanar']);				// A "mindenki"/tanár csoport hozzáadása
    // group_add($cfg['mindenki_diak']);				// A "mindenki"/diák csoport hozzáadása

//------------------------------------------------------------------------------------------------------------------------------

// Létrehozza az új coportokat a Mayor tankörök szerint
    if ($log['verbose'] > 0 ){ echo "\n***\tCsoportok egyeztetése.\n";}
    $tankorok = get_mayor_tankor($link2, $cfg['allapot_tartas']);
    $tankorok = array_merge($tankorok, array( array("tankorId" => 0, "tankorNev" => $cfg['mindenki_csop'] )));
    $tankorok = array_merge($tankorok, array( array("tankorId" => 0, "tankorNev" => $cfg['mindenki_tanar'] )));
    $tankorok = array_merge($tankorok, array( array("tankorId" => 0, "tankorNev" => $cfg['mindenki_diak'] )));
    $nxt_csop = nxt_group_list();
    $nxt_csop2 = nxt_group_list();                                                              //Gyorsítási célzattal, mert lassú
    $elozo_tcsop = "";
    $mod_nxt_group = 0;
    if($cfg['manage_groups'] === true){
        foreach($tankorok as $key => $val){                                                     //Végignézi a tankörök szerint
            foreach($nxt_csop as $key2 => $val2){                                                
                if($key2 == $val['tankorNev']){                                                 //Már van ilyen (tankör)nevű csoport
                    if ($log['verbose'] > 3 ){ echo "  -\t Csoport:".po("\t".$val['tankorNev'],$cfg['csoportnev_hossz'],1)."-\tok.\n";}
                    $elozo_tcsop = $val['tankorNev'];
                    break;
                }
            }
            unset($nxt_csop[$val['tankorNev']]);                                                //Megvizsgálva, többször már nem kell dönteni róla. 
            if( $val['tankorNev'] == $elozo_tcsop and $key2 != $val['tankorNev'] ){             //Duplikált tankör(név) a Mayorban
                    if($log['verbose'] > 2 ){ echo "* -\t Dupla tankör:".po("\t".$val['tankorNev'], $cfg['csoportnev_hossz'],1)."-\tmayor.\n";}
            }
            else if($key2 != $val['tankorNev']){                                                //Ha nincs ilyen (tankör)nevű csoport
                group_add($val['tankorNev']);                                                   //Akkor létrehozza
                $mod_nxt_group++;
                if ($log['verbose'] > 2 ){ echo "* -\t Új csoport:".po("\t".$val['tankorNev'],$cfg['csoportnev_hossz'],1)."-\thozzáadva.\n";}
            }
        }
    
        // A megszűnt tanköröket-csoportokat kitörli 
        foreach($nxt_csop as $key => $val){           
            if(substr($key, 0, strlen($cfg['csoport_prefix'])) === $cfg['csoport_prefix'] ){	//Csak a "prefix"-el kezdődő nevűekre.
                group_del($key, $nxt_csop2);									                            //elvégzi a törlést
                $mod_nxt_group++;
                if ($log['verbose'] > 1 ){ echo "** -\t Megszűnő csop:".po("\t$key",$cfg['csoportnev_hossz'],1)."-\t eltávolítva.\n";}
            } else {
                if ($log['verbose'] > 3 ){ echo " ---\t Egyéb csoport:".po("\t$key",$cfg['csoportnev_hossz'],1)."-\t békén hagyva.\n";}
            }	// Figyelem! A csoport prefix-szel: "(tk) " kezdődő csoportokat magáénak tekinti, automatikusan töröli!
        }	// 	Akkor is, ha az külön, kézzel lett létrehozva.
    }



//-------------------------------------------------------------------------------------------------------------------------------
// Felhasználónevek egyeztetése
    if ($log['verbose'] > 0 ){ echo "\n***\tFelhasználók egyeztetése.\n";}
    
    $mayor_tanar = get_mayor_tanar($link2, $cfg['allapot_tartas']);     //Rendezve jön 
    $mayor_tanar = add_tk_to_users( $mayor_tanar, null, $cfg['mindenki_tanar']);    //csak rendezett tömbökön!
    $mayor_tanar = set_param_to_user($mayor_tanar, null, 'quota', $cfg['default_quota']);
    $mayor_tanar = set_param_to_user($mayor_tanar, null, 'diakId', -1 ); 
    usort($mayor_tanar, "mayor_userlistcmp");

    $mayor_diak = get_mayor_diak($link2, $cfg['allapot_tartas']);       //mysql rendezi
    $mayor_diak = add_tk_to_users( $mayor_diak, null, $cfg['mindenki_diak']);		//csak rendezett tömbökön!
    $mayor_diak = set_param_to_user($mayor_diak, null, 'quota', $cfg['diak_quota']);
    $mayor_diak = set_param_to_user($mayor_diak, null, 'tanarId', -1 );
    usort($mayor_diak, "mayor_userlistcmp");

    $mayor_user = array();
    $mayor_user = array_merge($mayor_tanar, $mayor_diak);                               //Tanár, és diák lista együtt

    if(!empty($cfg['megfigyelo_user'])){                                                //A megfigyelő user felvétele a lista végére
        $mayor_user = array_merge($mayor_user, array(
            array( 'userAccount' => $cfg['megfigyelo_user'],                            //A megfigyelő user legyen egyben  virtuális tanár is
                'tanarId' => 1,  'oId' => 70000000000, 'diakId' => 0, 'tankorId' => 0, 'fullName' => "Napló Admin Megfigyelő",
                'email' => $cfg['default_email'],
                'tankorNev' => $cfg['mindenki_tanar'],
            )));
        foreach(get_mayor_tankor($link2, $cfg['allapot_tartas']) as $key => $val){      //És beléptetve az összes létező csoportba
            $mayor_user = array_merge($mayor_user, array(
                array( 'userAccount' => $cfg['megfigyelo_user'], 
                    'email' => $cfg['default_email'],  
                    'tanarId' => 1,
                    'diakId' => 0,
                    'oId' => 70000000000,
                    'tankorId' => $val['tankorId'],
                    'fullName' => "Napló Admin Megfigyelő",
                    'tankorNev' => $val['tankorNev'],
                    )
                )
            );
            //if($val['tankorNev'] == "(tk) 10.b kémia" ){ break; }
        }
    }
    usort($mayor_user, "mayor_userlistcmp");                                        //rendezés
    $mayor_user = add_tk_to_users( $mayor_user, null, $cfg['mindenki_csop']);       //csak rendezett tömbökön //mindenki csoport
    usort($mayor_user, "mayor_userlistcmp");                                        //Végén ismét rendezzük az egészet 
    $mayor_user = array_merge($mayor_user, array(array('userAccount' => null, 'fullName' => null, 'tankorNev' => null, 'diakId' => 0, 'tanarId' => 0,)) ); //strázsa a lista végére

    $nxt_user = nxt_user_list();
    $nxt_group = nxt_group_list();
    $m2n_catalog = catalog_userlist($link);
    $m2n_forbidden = catalog_forbiddenlist($link);
    
    if ($log['verbose'] > 3 ){ echo "\n";}

    foreach($mayor_user as $key => $val){                                           //Lecseréli az ékezetes betűket a felhasználónévből 
        $mayor_user[$key]['userAccount'] = gen_username($val);                      // lehet saját függvény is
        if(in_array($mayor_user[$key]['userAccount'], $m2n_forbidden) ){            //És, ha a nyilvántartásban "forbidden"-ként szerepel, 
            unset($mayor_user[$key]);                                               // akkor nem foglalkozik vele tovább.
        }
    }

    $curr = "";
    $curr_o = array();
    $tankorei = array();
    $mod_nxt_user_all = 0;
    $mod_nxt_user = 0;
    foreach($mayor_user as $key => $val){                                                                           //Végignézi a mayorból kinyert lista alapján.
    
        if($curr != $val['userAccount']){                                                                           //CSAK Rendezett tömbökön !! 
            foreach($nxt_user as $key2 => $val2){
                if($curr == $key2){                                                                                 //Már létezik a felhasználó a Nextcloud-ban
                    $log['curr'] = "-\n-\tFelhasználó:".po("\t$curr_n ($curr)",$cfg['felhasznalo_hossz'],1)."--\tlétezik.\n";
                    if ($log['verbose'] > 3 ){ echo " -".$log['curr']; $log['curr'] = "";}
                    if ( in_array($curr, $m2n_catalog['account'])){                                                  //Benne van-e a nyilvántartásban?
                        if($m2n_catalog['status'][array_keys($m2n_catalog['account'], $curr)[0]] == 'disabled' ){   // Ha le lett tiltva
                        //if(user_info($curr)['enabled']!=true){                                                    // Ez valós, de irtó lassú
                            catalog_userena($link, $curr);                                                          //Ha netán le lenne tiltva, akkor engedélyezi,
                            user_ena($curr);                                                                        // ha a script tiltotta le.
                            $mod_nxt_user++;
                            $log['curr'] = "-\n-\tFelhasználó:".po("\t$curr_n ($curr)",$cfg['felhasznalo_hossz'],1)."--\tengedélyezve.\n";
                            if ($log['verbose'] > 2 ){ echo " -".$log['curr']; $log['curr'] = ""; }                  //Ez is változtatás
                        }
                    } else  {                                                                                       //Nincs a katalógusban, nincs tiltva,  felvesszük        
                        catalog_useradd($link, $curr);                                      
                        if ($log['verbose'] > 1 ){ echo "-\t\tA felhasználó:".po("\t$curr",$cfg['felhasznalo_hossz'],1)."-\tnyilvántartásba véve.\n";} 
                    }
                    //---------------------------------------  QUOTA -----------------------------------//
                    if($cfg['always_set_diak_quota'] === true && $curr_tanarId < 0 && $curr_diakId > 0 ){           //Állítsunk-e erőből (diák) qvótát?
                        $params['quota'] = $cfg['diak_quota'];                                                      // Alapértelmezett diák kvóta
                        user_set($curr,$params);
                        $mod_nxt_user++;
                        if ($log['verbose'] > 3 ){ echo "* -\t\tBeállítva:\t"."Qvóta: ".$params['quota']."\t\n"; }
                    }

                    if($cfg['manage_groups'] === true){                                                             //Csak, ha a acsoportokhoz is nyúlunk
                        //------------------------- Tankörmappa  györkér + info.txt ------------------------//     
                        $ret = groupdir_create_root($curr, $curr_tanarId, $cfg['groupdir_prefix']);
                        if ($ret[0] === true && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/".$cfg['groupdir_prefix'],$cfg['csoportnev_hossz'],1)."\ttankörmappa gyökér.\n";}
                        if ($ret[1] > 0 && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/". $cfg['groupdir_prefix']."/INFO.txt",$cfg['csoportnev_hossz'],1)."\tfájl.\n";}
       
                        //------------------------------------------ Tankörök egyeztetése -------------------------------------------//
                        foreach($nxt_group as $key3 => $val3){                                                      //A tankörök egyeztetése
                            if(in_array($key3, $tankorei) /*or $key3 == $cfg['mindenki_csop']*/){                   //szerepel-e a felhasználó tankörei között a csoport, vagy a "mindenki" csoport?
                                if( in_array($curr, $val3)){                                                        //Igen, és már benne is van +++
                                    if ($log['verbose'] > 3 ){ echo "  -\t\tBenne van a:".po("\t$key3",$cfg['csoportnev_hossz'],1)."\tcsoportban.\n";} 
                                } else {                                                                            //Nincs, most kell beletenni
                                    if ($log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tHozzáadva a:".po("\t$key3",$cfg['csoportnev_hossz'],1)."\tcsoporthoz.\n"; }
                                    group_user_add($key3, $curr);                                                   //A "mindenki csoportot is ellenőrzi
                                    $mod_nxt_user++;
                                }

                                //------------------------------- Tankörmappa -----------------------------//       //( "_" --> mindenkinek, "username" --> csak neki ) && tanár
                                $ret = groupdir_create_groupdir($curr, $curr_tanarId, $cfg['groupdir_prefix']."/".$key3); 
                                if ($ret === true && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\tÚj mappa Létrehozva:".po("\t/".$key3."/",$cfg['csoportnev_hossz'],1)."\t./".$curr."/files/".$cfg['groupdir_prefix']."/   mappába\n";}
                                $ret = groupdir_create_groupdir($curr, $curr_tanarId, $cfg['groupdir_prefix']."/".$key3."_beadás");
                                if ($ret === true && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\tÚj mappa Létrehozva:".po("\t/".$key3."_beadás/",$cfg['csoportnev_hossz'],1)."\t./".$curr."/files/".$cfg['groupdir_prefix']."/   mappába\n";}
                        
                            //------------------------------------- Tankör (Csoportból) törlés -------------------------//
                            } else {                                                                                //Nem szerepel a tankörei között
                                if(in_array($curr, $val3) and  (substr($key3, 0, strlen($cfg['csoport_prefix'])) === $cfg['csoport_prefix']) ){ // korábban benne volt egy tankörben, de már nincs, vagy a hozzátartozó tankörben már nem tanít  => kiveszi
                                    if ($log['verbose'] > 1 ){if($log['curr'] !== ""){echo "*".$log['curr'];$log['curr'] = "";} echo  "* -\t\tTörölve a:".po("\t$key3",$cfg['csoportnev_hossz'],1)."\tcsoportból.\n"; }
                                    group_user_del($key3, $curr);                                                   //egy korábbi tankör lehetett...
                                    $mod_nxt_user++;
                                }
                            }
                        }

                        //------------------------------------- Tankörmappa törlés + NXT-rescan ----------------------------------//     //( "_" --> mindenkinek, "username" --> csak neki ) && tanár
                        $ret = groupdir_finish($curr, $curr_tanarId, $cfg['groupdir_prefix'], $tankorei);
                        if (count($ret[0]) > 0 && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} foreach($ret[0] as $retkey => $retval){ echo "* -\t Üres (Tankör)mappa:".po("\t/".$retval."/", $cfg['csoportnev_hossz'],1)."\t./".$curr."/files/".$cfg['groupdir_prefix']."/   mappából törölve.\n";}}
                        if (count($ret[1]) > 0 && $log['verbose'] > 2 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} foreach($ret[1] as $retkey => $retval){ echo "* -\tFájl/Mappa Átnevezve:".po("\t/".$retval."/", $cfg['csoportnev_hossz'],1)."\t./".$curr."/files/".$cfg['groupdir_prefix']."/   mappában.\n";}}
                        if (count($ret[2]) > 0 && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} foreach($ret[2] as $retkey => $retval){ echo "* -\t\tTankörmappa:".po("\t/".$retval."/", $cfg['csoportnev_hossz'],1)."\t./".$curr."/files/".$cfg['groupdir_prefix']."/   mappában békén hagyva.\n";}}
                        if ($ret[3] === true && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";}   echo "* -\t\tNXT-rescan :".po("\t./".$curr."/files/".$cfg['groupdir_prefix']."/", $cfg['csoportnev_hossz'],1)."\t mappán.\n";}
                    }
                    $mod_nxt_user_all++;
                    break;
                }       
            }  
            unset($nxt_user[$curr]);                                                        //Felhasználó Megvizsgálva, többször már nem kell dönteni róla.
            if($curr != $key2 and $curr != null){                                           //Nincs még ilyen felhasználó
                
                $ret = user_add($curr, $curr_n);                                                   //Akkor hozzá kell adni
                catalog_useradd($link, $curr);
                if ($printpasswds === true ){ $pw = strval($ret[1]); } else { $pw = "<password>"; }
                if ($log['verbose'] > 2 ){ echo "-\n**-\tFelhasználó:".po("\t".po($curr_n, 25, 1)." ($curr/$pw)",$cfg['felhasznalo_hossz'],1)." --\tlétrehozva.\n"; }
                $mod_nxt_user++;

                if($cfg['manage_groups'] === true){
                    $ret = groupdir_create_root($curr, $curr_tanarId, $cfg['groupdir_prefix']);
                    if ($ret[0] === true && $log['verbose'] > 2 ){ echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/".$cfg['groupdir_prefix']."/",$cfg['csoportnev_hossz'],1)."\ttankörmappa gyökér.\n";}
                    if ($ret[1] > 0 && $log['verbose'] > 2 ){ echo "* -\t\tLétrehozva :".po("\t./".$curr."/files/".$cfg['groupdir_prefix']."/INFO.txt",$cfg['csoportnev_hossz'],1)."\tfájl.\n";}

                    foreach($tankorei as $key3 => $val3){                                       //Hozzáadja a (tankör)csoportokhoz is egyből,
                        if(array_key_exists($val3, $nxt_group)) {                               // de, csak akkor, ha az a csoport a Nextcloud-ban is létezik.
                            group_user_add($val3, $curr);
                            if ($log['verbose'] > 2 ){ echo "* -\t\tHozzáadva a:".po("\t $val3",$cfg['csoportnev_hossz'],1)."\tcsoporthoz.\n"; }
                            $ret = groupdir_create_groupdir($curr, $curr_tanarId, $cfg['groupdir_prefix']."/".$val3);
                            if ($ret === true && $log['verbose'] > 2 ){echo "* -\tÚj mappa Létrehozva:".po("\t/".$val3."/",$cfg['csoportnev_hossz'],1)."\t./".$curr."/files/".$cfg['groupdir_prefix']."/   mappa\n";}
                            $ret = groupdir_create_groupdir($curr, $curr_tanarId, $cfg['groupdir_prefix']."/".$val3."_beadás");
                            if ($ret === true && $log['verbose'] > 2 ){echo "* -\tÚj mappa Létrehozva:".po("\t/".$val3."_beadás/",$cfg['csoportnev_hossz'],1)."\t./".$curr."/files/".$cfg['groupdir_prefix']."/   mappa\n";}
                        }
                    } 
                    $ret = groupdir_finish($curr, $curr_tanarId, $cfg['groupdir_prefix'], null);                                    
                    if ($ret[3] === true && $log['verbose'] > 3 ){if($log['curr'] !== ""){echo "**".$log['curr'];$log['curr'] = "";} echo "* -\t\tNXT-rescan :".po("\t./".$curr."/files/".$cfg['groupdir_prefix']."/", $cfg['csoportnev_hossz'],1)."\t mappán.\n";}
                }

                if($curr_diakId > 0) {      //Ennyi is  elég                                // Diákról van szó    /// if($curr_tanarId < 0 && $curr_diakId > 0)
                    $params['quota'] = $cfg['diak_quota'];                                  // Alapértelmezett kvóta
                } else {
                    $params['quota'] = $cfg['default_quota'];                               // Alapértelmezett kvóta
                }
                $params['lang'] = $cfg['default_lang'];                                     // Nyelv
                /*
                if($curr_email == ""){
                    $params['email'] = $cfg['default_email'];                               // e-mail beállítása
                } else {
                    $params['email'] = $curr_email;                                             // ha van a mysql-ben e-mail, akkor azt használja
                }
                */
                $params['email'] = $curr_email;
                user_set($curr,$params);                                                    //Alapértelmezett paraméterek érvényesítése
                if ($log['verbose'] > 3 ){ echo "* -\t\tBeállítva:\t"."Qvóta: ".$params['quota']."\tNyelv: ".$params['lang']."\tE-mail: ".$params['email']."\n";}
            }

            unset($tankorei);
            $tankorei = array();                            // új ciklus kezdődik
            $curr = $val['userAccount'];                    //
            $curr_n = $val['fullName'];                     //
            $curr_tanarId = $val['tanarId'];
            $curr_diakId = $val['diakId'];
            if(!empty($val['email'])) { 
                $curr_email = $val['email']; 
            } else if(!empty($val['mail'])){
                $curr_email = $val['mail'];
            } else {
                $curr_email = $cfg['default_email'];                               // e-mail beállítása
            }
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
                    if ($log['verbose'] > 1 ){ echo "-\n**-\tFelhasználó:".po("\t$val ($key)",$cfg['felhasznalo_hossz'],1)."--\ttörölve.\n"; } 
                } else {
                    user_dis($key);                                             //Különben csak letiltja (fájlok ne vesszenek el)
                    catalog_userdis($link, $key);                               //Feljegyzi a nyilvántartásba
                    if ($log['verbose'] > 1 ){ echo "-\n**-\tFelhasználó:".po("\t$val ($key)",$cfg['felhasznalo_hossz'],1)."--\tletiltva.\n"; } 
                }
                $mod_nxt_user++;
                $mod_nxt_user_all++;
            }
            // döntési logika:
            // ha benne van a $mayor_user-ben,
            // -    akkor vagy új user, vagy már meglévő, 
            // -    ezért őt kihúzza az $nxt_user listáról, --> megtartja
            // ezután ha valaki még rajta van az $nxt_user listán, az
            // -    vagy más, mayor_naplón kívüli user (rendszergazda vette föl) --> nem törli
            // -    vagy megszűnő, korábbi mayor_napló-s user --> törli, vagy letiltja
            // ha rajta van a $catalog listán is, és nincs rajta $mayor_user listán 
            // -	akkor őt a script hozta létre régen --> megszűnő, törli, vagy letiltja
            // (hiszen, ha aktív lenne, rajta lenne a $mayor_user listán, és kihúzta volna a $nxt_user-ből)
    }

// Végül a nyilvántartás kipucolása
    if ($log['verbose'] > 0 ){ echo "\n***\tNyilvántartás ellenőrzése.\n";}
    $nxt_user = nxt_user_list();
    $m2n_catalog = catalog_userlist($link);
    $m2n_forbidden = catalog_forbiddenlist($link);

    foreach($m2n_catalog['account'] as $key => $val){    
        if(@$nxt_user[$val] === null  ){         //Erre a nextcloud "occ" parancs hibakezelése miatt van szükség
            if ($log['verbose'] > 4 ){ echo "-\n**-\tFelhasználónév:".po("\t($val)",$cfg['felhasznalo_hossz'],1)."--\tkivéve a nyilvántartásból.\n";}
            catalog_userdel($link, $val);
        }
    }
    foreach($m2n_forbidden as $key => $val){    //Szinkronizálja a $cfg['kihagy'] listát a nyilvántartással.    
        if(!in_array($val, $cfg['kihagy'])){
            if ($log['verbose'] > 4 ){ echo "-\n**-\tFelhasználó:".po("\t($val)",$cfg['felhasznalo_hossz'],1)."--\tújra kezelve.\n";}
            catalog_userena($link,$val);
            user_ena($val);
        }
    }

//-------------------------------------------------------------------------------------------------------------------------------

//test
//script_install($link);
    if($log['verbose'] > 0 ){ echo "\n\nStatisztikák:\n";}
    if($log['verbose'] > 0 ){ echo "Összes művelet: ".($mod_nxt_user + $mod_nxt_group)."db.\n";} 
    if($log['verbose'] > 0 ){ echo "Feldolgozva: ".$mod_nxt_user_all."db felhasználó.\n";} 
    if($log['verbose'] > 0 ){ echo "Változtatás: ".$mod_nxt_user."db a Nextcloud felhasználói beállításokban.\n";}
    if($log['verbose'] > 0 ){ echo "Változtatás: ".$mod_nxt_group."db a Nextcloud csoport beállításokban.\n";}
    if($dryrun){ echo " (( !! DRY RUN !! ))\n"; }
 
 
    @mysqli_close($link2);
    @mysqli_close($link);
    $t_run = round((microtime(true) - $t_start)/60, 3);
    if ($log['verbose'] > 0 ){ echo "\n(Runtime: ".$t_run." min.)\nkész.\n";} //endline
 
} else {
    echo "\n\n******** Legalább PHP5 és mysqli szükséges! ********\n\n";
}
 


?>

