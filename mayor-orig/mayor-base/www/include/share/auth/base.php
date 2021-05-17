<?php
/*
    module: base/login

    A bejelentkezések kezeléséhez szükséges konstansok, beállítások.
*/

    define('_AUTH_SUCCESS',0);
    define('_AUTH_EXPIRED',1);
    define('_AUTH_FAILURE',2);
    define('_AUTH_FAILURE_1',21); // nincs ilyen azonosító
    define('_AUTH_FAILURE_2',22); // több ilyen azonosító is van
    define('_AUTH_FAILURE_3',23); // rossz jelszó
    define('_AUTH_FAILURE_4',24); // le van tiltva
    define('_AUTH_FAILURE_5',25); // a bejelentkezés korlátozva van

######################################################################
# Új session indítása és regisztrálása (vagy vissza a login-hoz)
######################################################################

    function newSession($accountInformation, $policy) {

        global $lang, $skin,  $sessionID, $SKINS, $AUTH;

        $lr = db_connect('login', array('fv' => 'newSession'));

        if (!$lr) {
            $_SESSION['alert'][] =  'message:sql_failure:session';
            return false;
        }

        $userAccount = db_escape_string($accountInformation['account'], $lr);
        // nem tárolunk jelszót! // 
        // $userPassword = 'nem tárolunk jelszót!'; // mégis tároljuk - session/search (backend: ads)
	$userPassword = db_escape_string($accountInformation['password'], $lr);
        $userCn = db_escape_string($accountInformation['cn'], $lr);
        $studyId = db_escape_string($accountInformation['studyId'], $lr);

        (bool)$toRegister = true; // be kell jegyezni (lásd alább)
        if ($sessionID == '') {
            // A sessionID generálása
            $sessionID = sessionHash();
        } else {
            // A meglevő sessionID használata - csak akkor hagyjuk ha az adott policy-hez még nincs ilyen,
            // de másik policy-hez van
            $query = "SELECT policy FROM session WHERE sessionID='%s'";
	    $ret = db_query($query, array('fv' => 'newSession', 'modul' => 'login', 'result' => 'idonly', 'values' => array($sessionID)), $lr);
            if (is_array($ret) && count($ret) > 0) {
		reset($ret);
		while ((list($key, $_policy) = each($ret)) && $toRegister) { // --TODO
                    if ($_policy == $policy) $toRegister = false;
                    // mégsem kell bejegyezni, már van; és ez az. $sessionID=$sessionID
                    // else be kell jegyezni, de ezt a $sessionID-t, nem generálunk
                }
            } else {
                $sessionID = sessionHash();
            }
        }
        // fetch predefined lang+skin
        {
            $query = "SELECT skin,lang,lastlogin FROM settings WHERE userAccount='%s' AND policy='%s'";
	    $ret = db_query($query, array('fv' => 'newSession', 'modul' => 'login', 'result' => 'record', 'values' => array($userAccount, $policy)), $lr);
            if (is_array($ret) && count($ret) > 0) {
		extract($ret);
		$_SESSION['lastLogin'] = $ret['lastlogin'];
		if (!in_array($skin,$SKINS))
		    $skin = (in_array($AUTH[$policy]['skin'],$SKINS)) ? $AUTH[$policy]['skin'] : _DEFAULT_SKIN;
                $q = "UPDATE settings SET lastlogin=now() WHERE userAccount='%s' AND policy='%s'";
		$v = array($userAccount, $policy);
            } else {
		/* Policy szerinti default skin*/
		$_SESSION['lastLogin'] = '2002-04-19';
		$skin = (in_array($AUTH[$policy]['skin'],$SKINS)) ? $AUTH[$policy]['skin'] : _DEFAULT_SKIN;
                $lang = _DEFAULT_LANG;
                $q = "INSERT INTO settings (userAccount,policy,skin,lang,lastlogin) VALUES ('%s', '%s', '%s', '%s', now())";
		$v = array($userAccount, $policy, '', $lang);
            }
            db_query($q, array('fv' => 'newSession', 'modul' => 'login', 'values' => $v), $lr);
        }
        // Ellenőrizzük, hogy hány karaktér fér bele az adatbázisba(!); ha eltérő, mint a generált, csonkoljuk.
        $_q = "SHOW COLUMNS FROM session WHERE Field='sessionID'";
        $_a = db_query($_q, array('fv' => 'newSession', 'modul' => 'login', 'result' => 'record'), $lr);
        $_mezohossz = intval(substr($_a['Type'],strpos($_a['Type'],'(')+1,2));
        if ($_mezohossz==0) $_SESSION['alert'][] = 'message:session_alter_needed:'.$_mezohossz;
        if (strlen($sessionID)!=$_mezohossz) {
    	    $_SESSION['alert'][] = 'message:session_alter_needed:'.$_mezohossz.':'.strlen($sessionID);
    	    $sessionID = substr($sessionID,0,$_mezohossz);
        }
        // Felvétel az adatbázisba
        if ($toRegister===false) {
	    $query = "DELETE FROM session WHERE sessionId='%s' and policy='%s'";
	    db_query($query, array('fv' => 'newSession', 'modul' => 'login', 'values' => array($sessionID, $policy)), $lr);
	} 
	$now = date('Y-m-d H:i:s');
	$_SC = sessionCookieEncode($sessionID, $now); // TODO
	$_studyId = ($studyId=='') ? 'NULL' : $studyId;
	if ($studyId=='') {
    	    $query="INSERT INTO session
		(sessionID, userPassword, userAccount, userCn, studyId, dt, policy, skin, lang, activity, sessionCookie)
		VALUES ('%s', aes_encrypt('%s', '%s'), '%s', '%s', NULL, '%s', '%s', '%s', %u, NOW(), '%s')"; // [SECURITY 002]
	    $v = array($sessionID, $userPassword, $_SC['pwHash'], $userAccount, $userCn, $now, $policy, $skin, $lang, $_SC['store']);
	} else {
    	    $query="INSERT INTO session
		(sessionID, userPassword, userAccount, userCn, studyId, dt, policy, skin, lang, activity, sessionCookie)
		VALUES ('%s', aes_encrypt('%s', '%s'), '%s', '%s', '%s', '%s', '%s', '%s', %u, NOW(), '%s')"; // [SECURITY 002]
	    $v = array($sessionID, $userPassword, $_SC['pwHash'],$userAccount, $userCn, $_studyId, $now, $policy, $skin, $lang, $_SC['store']);
	}
        db_query($query, array('fv' => 'newSession', 'modul' => 'login', 'values' => $v), $lr);
        db_close($lr);

	// Megjegyzés: a sessionID elhashelése nem jelent semmiféle védelmet, így tökéletesen megfelelő a gyenge hash is, de now alkalmazása hibás
	setcookie($_SC['name'],$_SC['value'],time()+60*60*_SESSION_MAX_TIME,'/','',_SECURECOOKIE);

        return $sessionID;
    }

?>
