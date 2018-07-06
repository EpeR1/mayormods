<?php

    if (__FBCONNECT_ENABLED !== true ) return false;

    if (version_compare(PHP_VERSION, '5.4.0', '<')) {
            return false;
    } else {
            /* facebook login start */
            require_once ('include/share/facebook/autoload.php');
            $fb = new Facebook\Facebook(array(
                'app_id' => __FB_APP_ID,
                'app_secret' => __FB_APP_SECRET,
		'cookie'     => true,
		'status'     => true,
                'default_graph_version' => 'v2.5',
            ));
	    $oAuth2Client = $fb->getOAuth2Client();

            $helper = $fb->getJavaScriptHelper();
            try {
                $accessToken = $helper->getAccessToken();
		// convert
		if ($accessToken !='' && !$accessToken->isLongLived()) {
		    try {
			$accessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);
		    } catch (Facebook\Exceptions\FacebookSDKException $e) {
			$_SESSION['alert'][] = "info::Error getting long-lived access token: " . $helper->getMessage() . "";
		    }
		}
            } catch(Facebook\Exceptions\FacebookResponseException $e) {
                // When Graph returns an error
                $_SESSION['alert'][] = 'info::Graph returned an error: ' . $e->getMessage();
            } catch(Facebook\Exceptions\FacebookSDKException $e) {
                // When validation fails or other local issues
                $_SESSION['alert'][] = 'info::Facebook SDK returned an error: ' . $e->getMessage();
            }
            if (isset($accessToken)) {
                $_SESSION['facebook_access_token'] = (string) $accessToken;
                $_SESSION['facebook_access_token_object'] = $accessToken;
                $response = $fb->get('/me?fields=id,name,email',$accessToken);
                $userNode = $response->getGraphUser();
                $ADAT['fbUserId'] = $userNode->getField('id');
                $ADAT['fbUserCn'] = $userNode->getField('name');
                $ADAT['fbUserEmail'] = $userNode->getField('email');
	    }
    }
    if ($action=='revokeFbAuth') {
	fbConnectRevoke($ADAT['fbUserId']);	
    } elseif ($action=='grantFbAuth') {
	fbConnectGrant($ADAT);	
    }

    $ADAT['fbUserIdStatusz'] = checkFbConnectAssoc($ADAT);

    function checkFbConnectAssoc($ADAT) {
	if ($ADAT['fbUserId']=='') return 0;

	$q = "SELECT count(*) AS db FROM facebookConnect where userAccount='%s' AND policy='%s' AND fbUserId='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'fbUserId'=>$ADAT['fbUserId']);
	$db = db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','result'=>'value','values'=>$v));
	if ($db==1) return 1; // 'OK';

	$q = "SELECT count(*) AS db FROM facebookConnect where userAccount='%s' AND policy='%s' AND fbUserId!='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'fbUserId'=>$ADAT['fbUserId']);
	$db = db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','result'=>'value','values'=>$v));
	if ($db==1) return 2; // 'masik fbUserId van megadva';

	$q = "SELECT count(*) AS db FROM facebookConnect where userAccount!='%s' AND policy='%s' AND fbUserId='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'fbUserId'=>$ADAT['fbUserId']);
	$db = db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','result'=>'value','values'=>$v));
	if ($db==1) return 3; // 'masik userAccount van hozzárendelve ehhez a fb azonosítóhoz';

	return false;
    }

    function fbConnectRevoke($fbUserId) {
	if ($fbUserId=='') return 0;

	$q = "DELETE FROM facebookConnect where userAccount='%s' AND policy='%s' AND fbUserId='%s'";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'fbUserId'=>$fbUserId);
	return db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','values'=>$v));
    }
    function fbConnectGrant($ADAT) {
	if ($ADAT['fbUserId']=='') return 0;

	$q = "INSERT IGNORE INTO facebookConnect (userAccount,policy,fbUserId,fbUserCn,fbUserEmail,studyId) VALUES ('%s','%s','%s','%s','%s','%s')";
	$v = array('userAccount'=>_USERACCOUNT,'policy'=>_POLICY,'fbUserId'=>$ADAT['fbUserId'],'fbUserCn'=>_USERCN.' ('.$ADAT['fbUserCn'].')','fbUserEmail'=>$ADAT['fbUserEmail'],'studyId'=>_STUDYID);
	return db_query($q,array('fv'=>'facebookConnectCheck','modul'=>'login','result'=>'insert','values'=>$v));
    }

?>