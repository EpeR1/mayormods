<?php

//__FBCONNECT_ENABLED===true 

function mayorFacebookAuth() {

	if (__FBCONNECT_ENABLED !== true ) return false;

	if (version_compare(PHP_VERSION, '5.4.0', '<')) {
	    $_SESSION['alert'][] = 'info::facebook:szerver konfigurációs hiba, legalább 5.4-es php verzió szükséges!';
	    return false;
	} else {
	    /* facebook login start */
	    require_once ('include/share/facebook/autoload.php');
	    $fb = new Facebook\Facebook(array(
    		'app_id' => __FB_APP_ID,
    		'app_secret' => __FB_APP_SECRET,
    		'default_graph_version' => 'v2.5',
	    ));
	    $helper = $fb->getJavaScriptHelper();
	    try {
    		$accessToken = $helper->getAccessToken();
	    } catch(Facebook\Exceptions\FacebookResponseException $e) {
    		// When Graph returns an error
    		$_SESSION['alert'][] = 'info::facebook gráf hiba: ' . $e->getMessage();
	    } catch(Facebook\Exceptions\FacebookSDKException $e) {
    		// When validation fails or other local issues
    		$_SESSION['alert'][] = 'info::facebook SDK hiba: ' . $e->getMessage();
	    }
	    if (isset($accessToken)) {
    		$_SESSION['facebook_access_token'] = (string) $accessToken;
    		$_SESSION['facebook_access_token_object'] = $accessToken;
		try {
    		    $response = $fb->get('/me?fields=id,name,email',$accessToken);
		    $userNode = $response->getGraphUser();
		    $fbUserId = $userNode->getField('id');
		    //$fbUserCn = $userNode->getField('name');
		    //$fbUserEmail = $userNode->getField('email');
		} catch (Facebook\Exceptions\FacebookSDKException $e) {
		    $_SESSION['alert'][] = 'info::facebook gráf hiba: ' . $e->getMessage();
		    return false;
		}
    		/* mayor auth start */
		$accountInformation=array();
		$toPolicy = 'public';
		$data = getUserByFbUserId($fbUserId);
		if ($data === false) {
		    $_SESSION['alert'][] = 'info:Nincs ilyen user (még) a MaYoR-ral összekötve, kérjük jelentkezz be jelszóval!';
		} elseif (is_array($data)) {
		    // Ha van, akkor ki az? Mert ő bemehet.
		    return array('userAccount'=>$data['userAccount'],'toPolicy'=>$data['policy'],'fbUserEmail'=>$data['fbUserEmail'],'studyId'=>$data['studyId'],'fbUserCn'=>$data['fbUserCn'],'accessToken'=>$accessToken);
		}
		/* mayor auth stop */
	    } else {
		$_SESSION['alert'][] = 'info::facebook:nem érvényes accessToken';
	    }
	    /* facebook login stop */
	}
        return false;
}

function getUserByFbUserId($fbUserId) {
        if ($fbUserId=='') return false;
        $q = "SELECT * FROM facebookConnect WHERE fbUserId='%s' ORDER BY policy LIMIT 1";
        $v = array('fbUserId'=>$fbUserId);
        $record = db_query($q,array('fv'=>'getUserByFbUserId','modul'=>'login','result'=>'record','values'=>$v));
        return $record;
}

?>
