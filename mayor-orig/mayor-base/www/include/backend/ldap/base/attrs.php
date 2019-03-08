<?php
/*
    Module: useradmin
*/

    if (file_exists('lang/'._LANG.'/backend/ldap/attrs.php')) {
        require('lang/'._LANG.'/backend/ldap/attrs.php');
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/backend/ldap/attrs.php')) {
        require('lang/'._DEFAULT_LANG.'/backend/ldap/attrs.php');
    }

######################################################
# Alapértelmezett jogosultságok
#
#  w - Írható/olvasható
#  r - olvasható
#  - - egyik sem
#
#  Három karakter: admin, self, other jogai
######################################################

    define('_DEFAULT_LDAP_RIGHTS','wr-');

######################################################
# Az LDAP account attribútumok
######################################################

    global $ldapAccountAttrs;
    $ldapAccountAttrs = array(
        'uid',
        'uidnumber',
        'gidnumber',
        'gecos',
        'cn',
	'studyid',
        'sn',
        'givenname',
        'mail',
        'telephonenumber',
        'mobile',
        'l',
        'street',
        'postaladdress',
        'postalcode',
        'homedirectory',
	'shadowlastchange',
	'shadowexpire',
	'shadowwarning',
	'shadowmin',
	'shadowmax',
	'shadowinactive',
    );

    global $ldapGroupAttrs;
    $ldapGroupAttrs = array(
	'gidnumber',
	'cn',
	'description',
	'member',
	'memberuid'
    );

    global $accountAttrToLDAP;
    $accountAttrToLDAP = array(
	'userAccount' => 'uid',
	'userCn' => 'cn',
	'mail' => 'mail',
	'studyId' => 'studyId',
	'shadowLastChange' => 'shadowLastChange',
	'shadowWarning' => 'shadowWarning',
	'shadowMin' => 'shadowMin',
	'shadowMax' => 'shadowMax',
	'shadowExpire' => 'shadowExpire',
	'shadowInactive' => 'shadowInactive',
    );

    global $groupAttrToLDAP;
    $groupAttrToLDAP = array(
	'groupCn' => 'cn',
	'groupDesc' => 'description',
	'member' => 'member'
    );

    global $ldapAccountAttrDef;
    $ldapAccountAttrDef = array(
        'dn' => array('desc' => _LDAPDN, 'type' => 'text', 'rights' => 'rrr'),
        'uid' => array('desc' => _LDAPUID, 'type' => 'text', 'rights' => 'rrr'),
        'uidnumber' => array('desc' => _LDAPUIDNUMBER, 'type' => 'int', 'rights' => 'w--'),
        'gidnumber' => array('desc' => _LDAPGIDNUMBER, 'type' => 'int', 'rights' => 'w--'),
        'gecos' => array('desc' => _LDAPGECOS, 'type' => 'text', 'rights' => 'w--'),
        'cn'  => array('desc' => _LDAPCN, 'type' => 'text', 'rights' => 'wrr'),
	'studyid' => array('desc' => _LDAPSTUDYID, 'type' => 'int', 'rights' => 'wrr'),
        'sn' => array('desc' => _LDAPSN, 'type' => 'text'),
        'givenname' => array('desc' => _LDAPGIVENNAME, 'type' => 'text'),
        'mail' => array('desc' => _LDAPMAIL, 'type' => 'text', 'rights' => 'wwr'),
        'telephonenumber' => array('desc' => _LDAPTELEPHONENUMBER, 'type' => 'text', 'rights' => 'ww-'),
        'mobile' => array('desc' => _LDAPMOBILE, 'type' => 'text', 'rights' => 'ww-'),
        'l' => array('desc' => _LDAPL, 'type' => 'text'),
        'street' => array('desc' => _LDAPSTREET, 'type' => 'text'),
        'postaladdress' => array('desc' => _LDAPPOSTALADDRESS, 'type' => 'text'),
        'postalcode' => array('desc' => _LDAPPOSTALCODE, 'type' => 'text'),
        'homedirectory' => array('desc' => _LDAPHOMEDIRECTORY, 'type' => 'text'),
	'shadowlastchange' => array('desc' => _LDAPSHADOWLASTCHANGE, 'type' => 'text'),
	'shadowexpire' => array('desc' => _LDAPSHADOWEXPIRE, 'type' => 'text'),
	'shadowwarning' => array('desc' => _LDAPSHADOWWARNING, 'type' => 'text'),
	'shadowmin' => array('desc' => _LDAPSHADOWMIN, 'type' => 'text'),
	'shadowmax' => array('desc' => _LDAPSHADOWMAX, 'type' => 'text'),
	'shadowinactive' => array('desc' => _LDAPSHADOWINACTICE, 'type' => 'text'),
    );

    global $ldapGroupAttrDef;
    $ldapGroupAttrDef = array(
        'cn'  => array('desc' => _LDAPCN, 'type' => 'text','rights' => 'wrr'),
        'description' => array('desc' => _LDAPDESCRIPTION, 'type' => 'text'),
        'gidnumber' => array('desc' => _LDAPGIDNUMBER, 'type' => 'int','rights' => 'w--'),
        'memberuid' => array('desc' => _LDAPMEMBERUID, 'type' => 'select'),
        'member' => array('desc' => _LDAPMEMBER, 'type' => 'select'),
    );

?>
