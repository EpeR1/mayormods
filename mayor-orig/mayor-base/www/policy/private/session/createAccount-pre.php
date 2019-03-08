<?php
/*
    Module:	base/session
    createAccount => byAdmin esetén csak az érintet policy-n belül az adminGroup tagjainak lehet létrehozni új account-ot
    createAccount => byRegistration esetén bárki regisztrálhat bármely policy-ből
*/

    if (_RIGHTS_OK !== true) die();

    if (memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
	$DEFAULTS['userAccount'] = readVariable($_GET['userAccount'],'userAccount');
	$DEFAULTS['userCn'] = readVariable($_GET['userCn'],'emptystringnull');
	$DEFAULTS['userPassword'] = readVariable($_GET['userPassword'],'emptystringnull');
	$DEFAULTS['mail'] = readVariable($_GET['email'],'emptystringnull');
	$DEFAULTS['telephoneNumber'] = readVariable($_GET['tel'],'emptystringnull');
    }

    $toPolicy = readVariable($_POST['toPolicy'], 'enum', readVariable($_GET['toPolicy'], 'enum',_POLICY, $POLICIES), $POLICIES);

    @$toPSF = $_REQUEST['toPSF'];

    if ($toPolicy != _POLICY) require_once(_CONFIGDIR."/$toPolicy-conf.php");
    if (
	(
    	    $AUTH[$toPolicy]['createAccount'] == 'byAdmin'
	    and memberOf(_USERACCOUNT, $AUTH['private']['adminGroup'])
	)
	or (
	    $AUTH[$toPolicy]['createAccount'] == 'byRegistration'
	    && _USERACCOUNT ==''
	)
    ) {
	define('_ENABLE',true);
    } else {
	define('_ENABLE',false);
	$_SESSION['alert'][] = 'page:insufficient_access:#1';
    }

    if (_ENABLE && $action == 'createAccount' && isset($_POST['new'])) {

        $file = $_FILES['file']['tmp_name'];
        if ($file != '' && $file != 'none' && file_exists($file)) {

            $uidfp=fopen($file, 'r');
            while ($sor=fgets($uidfp, 4096)) {
                list($userCn, $userAccount, $userPassword, $category, $studyId, $container)=explode("	",chop($sor));
		// A biztonság kedvéért ez a html form validációval egyező legyen 
		$userCn = readVariable($userCn,'html');
		$userAccount = readvariable($userAccount,'html');
		$studyId = readVariable($studyId,'number');
		$category = readVariable($category, 'enum','',$AUTH[$toPolicy]['categories']);
		$container = readVariable($container,'enum','',$AUTH[$toPolicy][$AUTH[$toPolicy]['backend'].'Containers']);
		$policyAccountAttrs = array();
		if (is_array($AUTH[$toPolicy]['accountAttrs'])) foreach ($AUTH[$toPolicy]['accountAttrs'] as $attr) {
		    if (isset($$attr) and $$attr != '') $policyAccountAttrs[$attr] = readVariable($$attr, 'string');
		}
		if (createAccount($userCn, $userAccount, $userPassword, $toPolicy, array('container'=> $container, 'category' => $category, 'policyAttrs' => $policyAccountAttrs)) ===false) {
		    $_SESSION['alert'][] = "info:user_create_failure: cn.$userCn|account.$userAccount|policy.$toPolicy|category.$category|container.$container";
		}
            }
            fclose($uidfp);

	} else {

	    // kötelező paraméterek
	    $userCn = readVariable($_POST['userCn'],'html');
	    $userAccount = readvariable($_POST['userAccount'],'html');
	    $studyId = readVariable($_POST['studyId'],'number');
	    $userPassword = $_POST['userPassword'];
	    $verification = $_POST['verification'];

	    // opcionális  paraméterek
	    $category = readVariable($_POST['category'], 'enum','',$AUTH[$toPolicy]['categories']);
	    $container = readVariable($_POST['container'],'enum','',$AUTH[$toPolicy][$AUTH[$toPolicy]['backend'].'Containers']);

	    $policyAccountAttrs = array();
	    if (is_array($AUTH[$toPolicy]['accountAttrs'])) foreach ($AUTH[$toPolicy]['accountAttrs'] as $attr) {
		if (isset($_POST[$attr]) and $_POST[$attr] != '') $policyAccountAttrs[$attr] = readVariable($_POST[$attr], 'string'); // ???
	    }

            if ($userCn == '' or $userAccount == '' or $userPassword == '' or $verification == '') {
		// Csak policy váltás történt
                // $_SESSION['alert'][] = 'message:empty_field';
            } elseif ($userPassword != $verification) {
                $_SESSION['alert'][] = 'message:pw_not_match';
            } else {
                if (createAccount($userCn, $userAccount, $userPassword, $toPolicy, 
            		    array('container'=> $container, 'category' => $category, 'policyAttrs' => $policyAccountAttrs))) {
		    if (
			_POLICY == 'private'
			&& memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])
		    ) header('Location: '.location("index.php?page=session&f=accountInfo&userAccount=$userAccount&toPolicy=$toPolicy"));
		    elseif (_POLICY == 'public') {
			$toPSF = ($toPSF=='') ? 'auth::login' : $toPSF;
			header(
			    'Location: '.location("index.php?page=auth&f=login&userAccount=$userAccount&policy=public&toPolicy=$toPolicy&toPSF=$toPSF", array('skin','lang','sessionID'))
			);
		    } else {
			$toPSF = ($toPSF=='') ? 'session::accountInfo' : $toPSF;
			header(
			    'Location: '.location("index.php?page=auth&f=login&userAccount=$userAccount&policy=public&toPolicy=$toPolicy&toPSF=$toPSF", array('skin','lang','sessionID'))
			);
		    }
		}
            }
        }

    }

?>
