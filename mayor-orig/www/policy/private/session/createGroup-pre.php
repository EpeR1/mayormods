<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    if (_POLICY == 'private' && memberOf(_USERACCOUNT, $AUTH[_POLICY]['adminGroup'])) {
    } else {
	$_SESSION['alert'][] = 'page:insufficient_access';
    }
    $toPolicy = readVariable($_POST['toPolicy'], 'enum', _POLICY, $POLICIES);

    if ($action == 'createGroup') {

        $file = $_FILES['file']['tmp_name'];
        if ($file != '' and $file != 'none' and file_exists($file)) {
/*            $uidfp=fopen($file, 'r');
            while ($sor=fgets($uidfp, 4096)) {
                list($groupCn, $groupDesc, $category)=explode("	",chop($sor));
    !!!!!!!!            createGroup($groupCn, $groupDesc, $category, $toPolicy);
            }
            fclose($uidfp);
*/
        } else {

	    $groupCn = readVariable($_POST['groupCn'],'html');
	    $groupDesc = readVariable($_POST['groupDesc'],'html');
	    $category = readVariable($_POST['category'],'enum',null,$AUTH[_POLICY]['categories']);
	    $container = readVariable($_POST['container'],'enum','',$AUTH[$toPolicy][$AUTH[$toPolicy]['backend'].'Containers']);
            $policyGroupAttrs = array();
            if (is_array($AUTH[$toPolicy]['groupAttrs'])) foreach ($AUTH[$toPolicy]['groupAttrs'] as $attr) {
                if (isset($_POST[$attr]) and $_POST[$attr] != '') $policyGroupAttrs[$attr] = readVariable($_POST[$attr], 'string'); // ???
            }

            if ($groupCn == '' || $groupDesc == '' || $category == '') {
		// Csak policy váltás
                //$_SESSION['alert'][] = 'message:empty_field'.":$groupCn:$groupDesc:$category";
            } else {
                if (createGroup($groupCn, $groupDesc, $toPolicy, array('container'=> $container, 'policyAttrs' => $policyGroupAttrs))) {
		    header('Location: '.location("index.php?page=session&f=groupInfo&groupCn=$groupCn&toPolicy=$toPolicy"));
		}
            }
        }


    }

?>
