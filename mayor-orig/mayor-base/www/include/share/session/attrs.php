<?php
/*
    Module: useradmin

    Minden auth-típus esetén lekérdezhető kell legyen az alábbi néhány attribútum.
    Az egyes backend-ek esetén ezek kiegészülhetnek további attribútumokkal.

    Az attribútumokhoz tartozik egy adott nyelvű elnevezés (desc) és egy típus (type),
    ami alapján megjelenítjük, beolvassuk, módosítjuk, stb...

    Esetleg ez a tömb használható arra is, hogy az egyes attribútumok hozzáférési jogosultságait
    később megadjuk... (lásd LDAP backend)

*/

//    global $attrDescription, $attrView, $unicodeAttrs, $timestampAttrs, $memberTypes;

    $accountAttrs = array(
//    -- kötelező --
        'userAccount',
        'userCn',
        'userPassword',
//    -- lekérdezhető, opcionális --
	'uidNumber',
        'mail',
	'telephoneNumber',
        'studyId',
        'shadowLastChange',
        'shadowMin',
        'shadowMax',
        'shadowWarning',
        'shadowInactive',
        'shadowExpire'
    );

    $groupAttrs = array(
	'groupCn',
	'groupDesc',
	'member',
    );

    $attrDef = array(
        'userAccount' => array('desc' => _ATTR_USERACCOUNT, 'type' => 'text'),
        'userCn' => array('desc' => _ATTR_USERCN, 'type' => 'text'),
        'userPassword' => array('desc' => _ATTR_USERPASSWORD, 'type' => 'text'),
	'uidNumber' => array('desc' => _ATTR_UIDNUMBER, 'type' => 'text'),
        'mail' => array('desc' => _ATTR_MAIL, 'type' => 'text'),
        'telephoneNumber' => array('desc' => _ATTR_TELEPHONENUMBER, 'type' => 'text'),
        'studyId' => array('desc' => _ATTR_STUDYID, 'type' => 'text'),
        'shadowLastChange' => array('desc' => _ATTR_SHADOWLASTCHANGE, 'type' => 'text'),
        'shadowMin' => array('desc' => _ATTR_SHADOWMIN, 'type' => 'text'),
        'shadowMax' => array('desc' => _ATTR_SHADOWMAX, 'type' => 'text'),
        'shadowWarning' => array('desc' => _ATTR_SHADOWWARNING, 'type' => 'text'),
        'shadowInactive' => array('desc' => _ATTR_SHADOWINACTIVE, 'type' => 'text'),
        'shadowExpire' => array('desc' => _ATTR_SHADOWEXPIRE, 'type' => 'text'),
	'groupCn' => array('desc' => _ATTR_GROUPCN, 'type' => 'text'),
	'groupDesc' => array('desc' => _ATTR_GROUPDESC, 'type' => 'text'),
	'member' => array('desc' => _ATTR_MEMBER, 'type' => 'select'),
    );


    
?>
