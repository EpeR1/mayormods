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
$db['nxt_dbname'] = "nextcloud";
$db['nxt_prefix'] = "nc_";
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
$m2n['default_lang']  = "hu";
$m2n['mindenki_csop'] = "naplós_felhasználók";

$occ_path = "/var/www/nextcloud/";
$occ_user = "www-data";

// Le kell cserélni az ékezetes betűket, mert a Vezetéknév.Keresztnév nem POSIX kompatibilis.
$search = array( 'á', 'ä', 'é', 'í', 'ó', 'ö', 'ő', 'ú', 'ü', 'ű', 'Á', 'Ä', 'É', 'Í', 'Ó', 'Ö', 'Ő', 'Ú', 'Ü', 'Ű');	// egyelőre csak a magyar betűket ismeri
$replace = array( 'aa', 'ae', 'ee', 'ii', 'oo', 'oe', 'ooe', 'uu', 'ue', 'uue', 'Aa', 'Aae', 'Ee', 'Ii', 'Oo', 'Oe', 'Ooe', 'Uu', 'Ue', 'Uue');



if (function_exists('mysqli_connect') and PHP_MAJOR_VERSION >= 7) { //MySQLi (Improved) és php7  kell!!!

    function db_connect(array $db){
        echo "Adatbázis kapcsolódás. (m2n_db=".$db['m2n_db'].")\n";
        $l = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['m2n_db'],$db['port']);
        if(!$l){
            echo "Adatbázis kapcsolat újrapróbálása... (m2n_db=) hiba:".mysqli_connect_errno()."\n";
            $db_old = $db['m2n_db'];
            $db['m2n_db'] = "";
            $l = mysqli_connect($db['host'], $db['user'], $db['pass'], $db['m2n_db'],$db['port']);
            if(!$l){
                echo "Sikertelen kapcsolódás. (m2n_db=".$db['m2n_db'].") info:".mysqli_get_host_info($l)."\n";
                return null;
            } else{
                echo "Sikeres kapcsolódás. (m2n_db=".$db['m2n_db'].") info:".mysqli_get_host_info($l)."\n";
                echo "Adatbázis létrehozása: ".$db_old." ...\n";
                mysqli_set_charset($l, "utf8");
                mysqli_query($l, "SET NAMES utf8 COLLATE utf8_general_ci;" );
                script_install($l);
                return $l;
            }
        } else {
            echo "Sikeres kapcsolódás. (m2n_db=".$db['m2n_db'].") info:".mysqli_get_host_info($l)."\n"; 
            mysqli_set_charset($l, "utf8");
            mysqli_query($l, "SET NAMES utf8 COLLATE utf8_general_ci;" );
            if(mysqli_query($l, "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register;" )) == FALSE ){
                script_install($l);
            }
            return $l;
        }
    }
// bezár: mysqli_close($link);

    function script_install($link){
        global $m2n,$db;
        $q = "CREATE DATABASE IF NOT EXISTS ".$db['m2n_db']." DEFAULT COLLATE 'utf8_general_ci'; ";
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            echo "Az ".$db['m2n_db']." adatbázis sikeresen létrehozva.\n";
        }
        $q = "CREATE TABLE IF NOT EXISTS ".$db['m2n_db'].".".$db['m2n_prefix']."register (
                account VARCHAR(64) NOT NULL COLLATE 'utf8_bin', 
                status ENUM('active','disabled','forbidden','deleted') NULL DEFAULT 'active' COLLATE 'utf8_bin',
                PRIMARY KEY (account)) 
                COLLATE='utf8_general_ci' 
                ENGINE=InnoDB;";
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
            echo "Az ".$db['m2n_db'].".".$db['m2n_prefix']."A nextcloud-register tábla sikeresen létrehozva.\n";
        }
    }
    
    function nxt_register_userlist($link){	//akiket a script hozott létre
        global $m2n,$db;
        $q = "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE STATUS != 'forbidden'; ";    
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
//            echo $q."\n";
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret['account'][] = $row['account'];
                $ret['status'][] = $row['status'];
            }
            mysqli_free_result($r);
            return $ret;
        } else {
            echo "adatbázis hiba";
        }
    }
    
    function nxt_register_forbiddenlist($link){      //akiket a rendszergazda kitiltott
        global $m2n,$db;
        $q = "SELECT * FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE STATUS = 'forbidden'; ";
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
//            echo $q."\n";
            $ret = array();
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret[] = $row['account'];
            }
            mysqli_free_result($r);
            return $ret;
        } else {
            echo "adatbázis hiba";
        }
    }
    
    function nxt_register_useradd($link, $account){	// feljegyzi az általa létrehozott felhasználókat
        global $m2n,$db;
        $q = "INSERT INTO ".$db['m2n_db'].".".$db['m2n_prefix']."register (account) VALUES ('".$account."')";
        if(( mysqli_query($link, $q)) !== FALSE ){
//            echo $q."\n";
        } 
    }

    function nxt_register_userena($link, $account){	// az engedélyezetteket
        global $m2n,$db;
        $q = "UPDATE ".$db['m2n_db'].".".$db['m2n_prefix']."register SET status='active' WHERE account='".$account."'";
        if(( mysqli_query($link, $q)) !== FALSE ){
//            echo $q."\n";
        }
    }

    function nxt_register_userdel($link, $account){	// a törölteket
        global $m2n,$db;
        $q = "DELETE FROM ".$db['m2n_db'].".".$db['m2n_prefix']."register WHERE account='".$account."' ";
        if(( mysqli_query($link, $q)) !== FALSE ){
//          echo $q."\n";
        }
    }
    
    function nxt_register_userdis($link, $account){	// a letiltottakat
        global $m2n,$db;
        $q = "UPDATE ".$db['m2n_db'].".".$db['m2n_prefix']."register SET status='disabled' WHERE account='".$account."'";
        if(( mysqli_query($link, $q)) !== FALSE ){
//            echo $q."\n";
        }
    }

    
    

    function user_add($userAccount, $fullName){		// létrehoz egy felhasználót a Nextcloud-ban
        global $occ_path,$occ_user,$m2n;
//  	export OC_PASS=ErősJelszó123; su -s /bin/sh www-data -c 'php web/occ user:add --password-from-env  --display-name="Teszt Tamás" --group="csop" t.tamas'
        if(strlen($userAccount) > 64 or strlen($fullName) > 64){
            echo "Hiba: A felahsználónév, vagy a teljes név hosszabb, mint 64 karakter!!";
        } else {
//            shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:enable $userAccount '");
            shell_exec("export OC_PASS=".$m2n['default_passw']."; su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:add --password-from-env --display-name=\"$fullName\" --group=\"".$m2n['mindenki_csop']."\" $userAccount '"); 
        }
    }

    function user_del($userAccount){	// kitöröl vagy letilt egy felhasználót a Nextcloud-ban
	global $occ_path,$occ_user;
        $last_login = json_decode(shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:info $userAccount --output=json '"),true)['last_seen'] ;
        if($last_login == "1970-01-01T00:00:00+00:00" ){							// Ha még soha nem lépett be
            shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:delete $userAccount '");	// akkor törölhető
        } else {
             shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:disable $userAccount '");	// különben csak letiltja
        }
        
    }
    
    function user_dis($userAccount){	// letiltja a felhasználót a Nextcloud-ban
        global $occ_path,$occ_user;
            shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:disable $userAccount '");	
    }

    function user_ena($userAccount){	// engedélyezi
        global $occ_path,$occ_user;
            shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:enable $userAccount '");   
    }


    function nxt_group_list() {		// Csoportok listázása a Nextcloud-ból
            global $occ_path,$occ_user;
            return (array)json_decode(shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:list --output=json '"));
    }
    
    function nxt_user_list() {		// Felhasználók listázása a Nextcloud-ból
            global $occ_path,$occ_user;
            $num = shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:report | grep \"total\" | sed -e \"s/[^0-9]//g\" | tr -d \"[:blank:]\n\" '"); 
            $num = $num + 100; 	// Biztos-ami-biztos, a nextcloud rejtett hibái miatt...
            return (array)json_decode(shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:list --limit $num --output=json '"));
    }
    
    function nxt_user_lastlogin($userAccount){ 	// legutóbbi belépés lekérdezése
        global $occ_path,$occ_user;
        return  json_decode(shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:info $userAccount  --output=json '"),true)['last_seen'] ;
    }

    
    function user_set($userAccount, array $params){	//beállítja az e-mailt, quota-t, nyelvet a paott értékekere
        global $occ_path,$occ_user;        
        if(isset($params['quota']))
            shell_exec( "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:setting $userAccount files quota \"".$params['quota']."\" '" );
        if(isset($params['email']))  
            shell_exec( "su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:setting $userAccount settings email \"".$params['email']."\" '" );
        if(isset($params['lang']))
            shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" user:setting $userAccount core lang \"".$params['lang']."\" '");
    }
    
    function group_add($groupName){ 	//Új csoport létrehozása a Nextcloud-ban 
        global $link,$db;
        if(strlen($groupName) > 64){	//mivel (egyelőre) nics erre 'occ' parancs, ezért közvetlenül kell...
            echo "Hiba: a csoportnév nagyobb, mint 64 karakter!!\n";
        } else {
            $q = "INSERT IGNORE INTO ".$db['nxt_dbname'].".".$db['nxt_prefix']."groups (gid) VALUES ('".$groupName."'); ";
            if(mysqli_query($link, $q) !== TRUE ) echo "group add query error";
            echo "\n".$q."\n";
        }
    }
    
    function group_del($groupName){	// Csoport törlése a Nextcloud-ból
        global $occ_user,$occ_path,$db,$link;
        $grp = nxt_group_list();
        if(isset($grp[$groupName])){	// Mivel erre még nincs hivatalos "occ" parancs, ezért közvetlenül kell...
            foreach($grp[$groupName] as $key => $user){
                shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:removeuser \"$groupName\" $user '");
                echo "$user removed from group:'$groupName' \n";
            }
            $q = "DELETE FROM ".$db['nxt_dbname'].".".$db['nxt_prefix']."groups WHERE gid='".$groupName."'; " ;
            if(mysqli_query($link, $q) !== TRUE ) echo "group del query error";
            echo "\n".$q."\n";
        }
    }

    function group_user_add($groupName, $userAccount){	// Hozzáad egy felhasználót egy csoporthoz a Nextcloud-ban
        global $occ_user, $occ_path;
        shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:adduser \"$groupName\" $userAccount '");
    }

    function group_user_del($groupName, $userAccount){	// Kitöröl egy felhasználót egy Nextcoud csoportból
        global $occ_user, $occ_path;
        shell_exec("su -s /bin/sh $occ_user -c 'php \"".$occ_path."/occ\" group:removeuser \"$groupName\" $userAccount '");
    }


    function get_mayor_tankor($link){		// A tankörök neveinek lekérdezése a mayorból
        global $m2n;
        $ret = array();
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
                WHERE evfolyamJel >= ".$m2n['min_evfolyam']." 
                ORDER BY osztalyId)
                ORDER BY tankorId ); 
            ";
            if(( $r = mysqli_query($link, $q)) !== FALSE ){
                echo $q."\n";
                while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                    $ret[] = $row;
                }
                mysqli_free_result($r);
                return $ret;
            } else {
                echo "adatbázis hiba";
            }
        }
    }

    
    function get_mayor_tanar($link){		// A tanárok lekérdezése a mayorból
        global $m2n;
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
  
        if(( $r = mysqli_query($link, $q)) !== FALSE ){
//            echo $q."\n";
//          mysqli_fetch_array($r, MYSQLI_ASSOC);
            while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) {
                $ret[] = $row;
            }
            mysqli_free_result($r);
            return $ret;
        } else {
            echo "adatbázis hiba";
        }
    }
    
    
    function get_mayor_diak($link){	// diákok lekérdezése
        global $m2n;
        $ret = array();
        $q = "SELECT tanev FROM intezmeny_".$m2n['isk_rovidnev'].".szemeszter WHERE statusz = 'aktív' GROUP BY tanev; ";
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
                WHERE evfolyamJel >= ".$m2n['min_evfolyam']."
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
            
            if(( $r = mysqli_query($link, $q)) !== FALSE ){
                echo $q."\n";
//                 mysqli_fetch_array($r, MYSQLI_ASSOC);
                  while ($row = mysqli_fetch_array($r, MYSQLI_ASSOC)) { 
                      $ret[] = $row;
                  }
                  mysqli_free_result($r);
                  return $ret;
            } else {
                echo "adatbázis hiba";
            }
        } else {
            echo "adatbázis hiba"; 
        }  
    
    }
    
    
//-------------------------------------------------------------------------------------------------------------------------------    
if(($link = db_connect($db)) == FALSE){			//csatlakozás
    echo "MySQL (general) kapcsolat hiba\n";
    die();
}
$link2 = $link;

group_add($m2n['mindenki_csop']);			// A "mindenki" csoport hozzáadása 


if(isset($db['mayor_user']) and isset($db['mayor_pass']) and isset($db['mayor_host']) or isset($db['mayor_port'])) 
{
    $db['user'] = $db['mayor_user'];			//ha a mayor egy másik szerveren lenne
    $db['pass'] = $db['mayor_pass'];
    $db['host'] = $db['mayor_host'];
    $db['port'] = $db['mayor_port'];
    if(($link2 = db_connect($db)) == FALSE){
        echo "MySQL (mayor) kapcsolat hiba\n";
        die();
    } else {
        echo "Mayor DB connect";
    }
}

// Létrehozza az új Mayor tanköröket
$tankorok = get_mayor_tankor($link2);
$nxt_csop = nxt_group_list();
foreach($tankorok as $key => $val){			//Végignézi a tankörök szerint
    foreach($nxt_csop as $key2 => $val2){		// 
        if($key2 == $val['tankorNev']){			//Már van ilyen (tankör)nevű csoport
            echo "group:".$val['tankorNev']." found\n";
            break;
        }
    }
    unset($nxt_csop[$val['tankorNev']]);		//Megvizsgálva, többször már nem kell dönteni róla. 
    if($key2 != $val['tankorNev']){ 			//Ha nincs ilyen (tankör)nevű csoport
        group_add($val['tankorNev']);   		//Akkor létrehozza
        echo "group:[".$val['tankorNev']."] created\n";
     }
}
// A megszűnt tanköröket-csoportokat kitörli 
foreach($nxt_csop as $key => $val){           
    if(substr($key, 0, strlen($m2n['csoport_prefix'])) === $m2n['csoport_prefix'] ){	//Csak a "prefix"-el kezdődő nevűekre.
        group_del($key);								//elvégzi a törlést
        echo "group:[".$key."] Yes, removed\n";
    } else {
        echo "group: $key Non removed\n";
    }	// Figyelem! A csoport prefix-szel: "(tk) " kezdődő csoportokat magáénak tekinti, automatikusan töröli!
}	// 	Akkor is, ha az külön, kézzel lett létrehozva.



//-------------------------------------------------------------------------------------------------------------------------------
// Felhasználónevek egyeztetése
$mayor_user = array_merge( get_mayor_tanar($link2), get_mayor_diak($link2) );		//tanár, diák
$mayor_user = array_merge($mayor_user, array(array('userAccount' => null, 'fullName' => null, 'tankorNev' => null,)) ); //strázsa a lista végére
$nxt_user = nxt_user_list();
$nxt_group = nxt_group_list();
$nxt_registered = nxt_register_userlist($link);

foreach($mayor_user as $key => $val){
                                                                //Lecseréli az ékezetes betűket a felhasználónévből
    $mayor_user[$key]['userAccount'] = str_replace($search, $replace, $val['userAccount']);  // (pl: Á->Aa, á->aa, ...)
    if(in_array($val['userAccount'], nxt_register_forbiddenlist($link)) ){      //Ha a nyilvántartásban "forbidden"-ként szerepel, 
        unset($mayor_user[$key]);                                               // akkor nem foglalkozik vele tovább.
    }
}

$curr = "";
$tankorei = array();
foreach($mayor_user as $key => $val){				//Végignézi a mayorból kinyert lista alapján.

    if($curr != $val['userAccount']){   		 	//A következő felhasználó..
        foreach($nxt_user as $key2 => $val2){
            if($curr == $key2){ 				//Már létezik a felhasználó a Nextcloud-ban
                echo "user found: $curr:$curr_n\n";
                if($nxt_registered['status'][array_keys($nxt_registered['account'], $curr)[0]] == 'disabled' ){
                    nxt_register_userena($link, $curr);		//Ha netán le lenne tiltva, akkor engedélyezi,
                    user_ena($curr);				//ha a script tiltotta le.
                }				

                foreach($nxt_group as $key3 => $val3){		//A tankörök egyeztetése
                    if(in_array($key3, $tankorei)){		//szerepel-e a felhasználó tankörei között a csoport?
                        if( in_array($curr, $val3)){		//Igen, és már benne is van +++

                             echo  "$curr már benne van a \"$key3\" csoportban\n";
                        } else {				//Nincs, most kell beletenni
                            echo  "$curr még nincs bent a \"$key3\" csoportban\n";
                            group_user_add($key3, $curr);
                        }
                    } else {					//Nem szerepel a tankörei között
                        if(in_array($curr, $val3) and  (substr($key3, 0, strlen($m2n['csoport_prefix'])) === $m2n['csoport_prefix']) ){
                            // korábban benne volt egy tankörben, de már nincs, vagy a hozzátartozó tankörben már nem tanít  => kivenni
                            echo  "$curr benne volt a \"$key3\" csoportban, kiszedve\n";
                            group_user_del($key3, $curr);	//egy korábbi tankör lehetett...
                        }
                    }
                } 
                break;
            }       
        }
//        echo "\n\n curr:$curr, key2:$key2, val2:$val2, key3:$key3, val3:$val3,\n\n";
        unset($nxt_user[$curr]);  				//Megvizsgálva, többször már nem kell dönteni róla.
        if($curr != $key2 and $curr != null){			//Nincs még ilyen felhasználó
            echo "user added: $curr:$curr_n \n";
            user_add($curr, $curr_n);				//Akkor hozzá kell adni
            nxt_register_useradd($link, $curr);
                
            foreach($tankorei as $key3 => $val3){		//Hozzáadja a (tankör)csoportokhoz is.
                group_user_add($val3,$curr);
                echo "felhasználó hozzáadva: $val3 csoporthoz\n";
            }                
            $params['quota'] = $m2n['default_quota'];		// Alapértelmezett kvóta
            $params['lang'] = $m2n['default_lang'];		// Nyelv
            if($curr_e == ""){
                $params['email'] = $m2n['default_email']; 	// e-mail beállítása
            } else {
                $params['email'] = $curr_e;			// ha van a mysql-ben e-mail, akkor azt használja
            }
            user_set($curr,$params);				//Alapértelmezett paraméterek érvényesítése
            echo "paraméterek beállítva:".$params['quota'].$params['lang'].$params['email']."\n";
        }

        echo "--$curr--$curr_n--\n";
        $tankorei = array(); 					// új ciklus kezdődik
        $curr = $val['userAccount'];				//
        $curr_n = $val['fullName'];				//
        $curr_e = $val['email'];				//
    }
    $tankorei[] = $val['tankorNev'];				// Egyébként a csoportok (tankörök) összegyűjtése
}


// A megszűnő felhasználónevek egyeztetése
$nxt_registered = nxt_register_userlist($link);
foreach($nxt_user as $key => $val){						//Benne van a nyilvántartásban,
        if(in_array($key, $nxt_registered['account'])){ 			//vagyis a script adta hozzá korábban
            if( nxt_user_lastlogin($key) == "1970-01-01T00:00:00+00:00" ){	//Még soha nem lépett be = 1970.01.01 ??
                user_del($key);							//Akkor törli
                nxt_register_userdel($link, $key);				//A listáról is
                echo "felhasználó eltávolítva: $key\n";
            } else {
                user_dis($key);            					//Különben csak letiltja (fájlok ne vesszenek el)
                nxt_register_userdis($link, $key);				//Feljegyzi a nyilvántartásba
                echo "felhasználó letiltva: $key\n";
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
$nxt_user = nxt_user_list();
$nxt_registered = nxt_register_userlist($link);
foreach($nxt_registered['account'] as $key => $val){    //Erre a nextcloud "occ" parancs hibakezelése miatt van szükség

    if(@$nxt_user[$val] === null ){
        echo "kivéve a nyilvántartásból: $val";
        nxt_register_userdel($link, $val);
    }
}

//-------------------------------------------------------------------------------------------------------------------------------

//test
//script_install($link);


@mysqli_close($link2);
@mysqli_close($link);
echo "\n"; //endline

} else {
    echo "Legalább PHP7 és mysqli szükséges!\n\n";
}





?>
