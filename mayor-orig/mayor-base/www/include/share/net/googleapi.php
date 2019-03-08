<?php

//__GOOGLEAPI_ENABLED===true 

function mayorGoogleApiAuth() {

	if (__GOOGLEAPI_ENABLED !== true ) return false;

	if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	    $_SESSION['alert'][] = 'info::googleapi:szerver konfigurációs hiba, legalább 5.4-es php verzió szükséges!';
	    return false;
	} else {
	    /* google login start */
	    require_once ('include/share/googleapi/autoload.php');

	    $redirect_uri = _BASE_URL.'/index.php?action=googleapilogin';
	    $client = new Google_Client();
	    //$client->setAuthConfig($oauth_credentials);
	    $client->setClientId(__GOOGLEAPI_CLIENT_ID);
	    $client->setClientSecret(__GOOGLEAPI_CLIENT_SECRET);
	    $client->setRedirectUri($redirect_uri);
	    $client->setScopes('email');

	    try {
		$payload = $client->verifyIdToken($_GET['id_token']);
	    } catch(Exception $e) {
    		$_SESSION['alert'][] = 'info::googleapi SDK hiba: ' . $e->getMessage();
	    }
	    if (isset($payload['sub'])) { // subject
    		$_SESSION['googleapi_object'] = $payload;
    		// mayor auth start
		$accountInformation=array();
		$toPolicy = 'public';
		$data = getUserByGoogleSub($payload['sub']); // subject=google user id
		if ($data === false) {
		    $_SESSION['alert'][] = 'info:Nincs ilyen user (még) a MaYoR-ral összekötve, kérjük jelentkezz be jelszóval!';
		} elseif (is_array($data)) {
		    // Ha van, akkor ki az? Mert ő bemehet.
		    setGoogleToken($payload['sub'],$_GET['id_token']); // a verifyIdToken igazolja
		    return array('userAccount'=>$data['userAccount'],'toPolicy'=>$data['policy'],'googleUserEmail'=>$data['googleUserEmail'],'studyId'=>$data['studyId'],'googleUserCn'=>$data['googleUserCn'],'accessToken'=>$accessToken);
		}
		/* mayor auth stop */
	    } else {
		$_SESSION['alert'][] = 'info::googleapi:nem érvényes accessToken';
	    }
	    /* googleapi login stop */
	}
        return false;
}

function getUserByGoogleSub($googleSub) {
        if ($googleSub=='') return false;
        $q = "SELECT * FROM googleConnect WHERE googleSub='%s' ORDER BY policy LIMIT 1";
        $v = array($googleSub);
        $record = db_query($q,array('fv'=>'getUserByGoogleSub','modul'=>'login','result'=>'record','values'=>$v));
        return $record;
}

function setGoogleToken($googleSub, $id_token) {
        if ($googleSub=='') return false;
        if ($id_token=='') return false;
	$_SESSION['googleapi_id_token'] = $id_token;
}

?>
