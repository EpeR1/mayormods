<?php

    if (__GOOGLEAPI_ENABLED !== true ) return false;

    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
        return false;
    } else {
        require_once ('include/share/googleapi/autoload.php');

	if ($action=='googleapiRevoke') {
	    googleapiRevoke();	
	    unset($_SESSION['googleapi_id_token']);
	    unset($_SESSION['googleapi_object']);
	} elseif ($action=='googleapiGrant') {
	    // itt nem áll rendelkezésre adat! googleapiGrant($ADAT);
	    // a get id_token résznél kötjük össze a usert és irányítjuk tovább
	}
	//$ADAT['googleapiStatus'] = googleapiCheckAssoc($ADAT);
	if ($_SESSION['googleapi_id_token']!='') { 
	    // van azonosított user
	    // a sessionben rendelkezésre is áll az objektum (googleapi_object), 
	    // de itt most lekérdezzük a google szervertől újra!
            $redirect_uri = _BASE_URL.'/index.php';
            $client = new Google_Client();
            $client->setClientId(__GOOGLEAPI_CLIENT_ID);
            $client->setClientSecret(__GOOGLEAPI_CLIENT_SECRET);
	    $client->setScopes('email');
	    // $client->setAccessToken($_SESSION['googleapi_id_token']);
	    try {
                $ADAT['payload'] = $payload = $client->verifyIdToken($_SESSION['googleapi_id_token']);
		$ADAT['googleapiStatusz'] = 1;
            } catch(Exception $e) {
                $_SESSION['alert'][] = 'info::googleapi SDK hiba: ' . $e->getMessage();
		$ADAT['googleapiStatusz'] = 2;
            }
	} elseif ($_GET['id_token']!='') {
            $redirect_uri = _BASE_URL.'/index.php';
            $client = new Google_Client();
            //$client->setAuthConfig($oauth_credentials);
            $client->setClientId(__GOOGLEAPI_CLIENT_ID);
            $client->setClientSecret(__GOOGLEAPI_CLIENT_SECRET);
//            $client->setRedirectUri($redirect_uri);
	    $client->setScopes('email');
	    try {
                $payload = $client->verifyIdToken($_GET['id_token']);
            } catch(Exception $e) {
                $_SESSION['alert'][] = 'info::googleapi SDK hiba: ' . $e->getMessage();
            }
            if (isset($payload['sub'])) { // subject
                $_SESSION['google_access_token'] = (string) $payload;
                $_SESSION['google_access_token_object'] = $payload;
                // mayor auth start
                $accountInformation=array();
                $toPolicy = 'public';
		  $ADAT['googleSub'] = $payload['sub'];
//                $ADAT['fbUserId'] = $userNode->getField('id');
                $ADAT['googleUserCn'] = $payload['name'];
                $ADAT['googleUserEmail'] = $payload['email'];
		googleapiGrant($ADAT);
                /* mayor auth stop */
            } else {
                $_SESSION['alert'][] = 'info:nem érvényes accessToken';
            }
	}

    }
    function googleapiCheckAssoc($ADAT) {
	if ($ADAT['googleSub']=='') return 0;

	$q = "SELECT count(*) AS db FROM googleConnect where userAccount='%s' AND policy='%s' AND googleSub='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'fbUserId'=>$ADAT['googleSub']);
	$db = db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','result'=>'value','values'=>$v));
	if ($db==1) return 1; // 'OK';

	$q = "SELECT count(*) AS db FROM googleConnect where userAccount='%s' AND policy='%s' AND googleSub!='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'fbUserId'=>$ADAT['googleSub']);
	$db = db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','result'=>'value','values'=>$v));
	if ($db==1) return 2; // 'masik googleSub van megadva';

	$q = "SELECT count(*) AS db FROM googleConnect where userAccount!='%s' AND policy='%s' AND googleSub='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'googleSub'=>$ADAT['googleSub']);
	$db = db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','result'=>'value','values'=>$v));
	if ($db==1) return 3; // 'masik userAccount van hozzárendelve ehhez a googleSub azonosítóhoz';

	return false;
    }

    function googleapiRevoke() {
	$q = "DELETE FROM googleConnect where userAccount='%s' AND policy='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY);
	return db_query($q,array('fv'=>'googleapiRevoke','modul'=>'login','values'=>$v));
    }
    function googleapiGrant($ADAT) {
	if ($ADAT['googleSub']=='') return 0;
	$q = "INSERT IGNORE INTO googleConnect (userAccount,policy,googleSub,googleUserCn,googleUserEmail,studyId) VALUES ('%s','%s','%s','%s','%s','%s')";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'googleSub'=>$ADAT['googleSub'],_USERACCOUNT.' ('.$ADAT['googleUserCn'].')',$ADAT['googleUserEmail'],_STUDYID);
	return db_query($q,array('debug'=>false,'fv'=>'googleapiGrant','modul'=>'login','result'=>'insert','values'=>$v));
    }

?>