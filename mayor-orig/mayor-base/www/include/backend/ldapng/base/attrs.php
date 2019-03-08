<?php
/*
    Module: useradmin
*/

    if (file_exists('lang/'._LANG.'/backend/ldapng/attrs.php')) {
        require('lang/'._LANG.'/backend/ldapng/attrs.php');
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/backend/ldapng/attrs.php')) {
        require('lang/'._DEFAULT_LANG.'/backend/ldapng/attrs.php');
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

    global $ldapngAccountAttrs;
    $ldapngAccountAttrs = array(
        'cn',
	'serialnumber',
        'uid',
        'uidnumber',
        'gidnumber',
	'unixhomedirectory',
	'loginshell',

	'shadowlastchange',
	'shadowexpire',
	'shadowwarning',
	'shadowmin',
	'shadowmax',
	'shadowinactive',

/*
        'gecos',
        'mail',
        'telephonenumber',
        'mobile',
        'l',
        'street',
        'postaladdress',
        'postalcode',
        'homedirectory',
*/
    );

    global $ldapngGroupAttrs;
    $ldapngGroupAttrs = array(
	'cn',
	'description',
	'member',
	'name',
	'samaccountname',
	'objectcategory',
	'gidnumber', // ennek kellene lennie - mitől lesz?
/*	'memberuid' */
    );

    global $accountAttrToLDAP; // Kis és nagybetű számít!!!
    $accountAttrToLDAP = array(
	'userAccount' => 'uid',
	'userCn' => 'displayName',
	'mail' => 'mail',
	'studyId' => 'employeeNumber', // Ez konfig-ban külön van állítva, az itteni érték irreleváns
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
	'member' => 'member',
    );

    global $ldapngAccountAttrDef;
    $ldapngAccountAttrDef = array(
        'dn' => array('desc' => _LDAPDN, 'type' => 'text', 'rights' => 'rrr'),
        'cn'  => array('desc' => _LDAPCN, 'type' => 'text', 'rights' => 'rrr'),
        'sn' => array('desc' => _LDAPSN, 'type' => 'text', 'rights' => 'wrr'),
        'givenname' => array('desc' => _LDAPGIVENNAME, 'type' => 'text'),
	'employeenumber' => array('desc' => _LDAPEMPLOYEENUMBER, 'type' => 'int', 'rights' => 'wrr'),
        'displayname'  => array('desc' => _LDAPCN, 'type' => 'text', 'rights' => 'wrr'),
        'name'  => array('desc' => _LDAPNAME, 'type' => 'text', 'rights' => 'r--'),
        'uid' => array('desc' => _LDAPUID, 'type' => 'text', 'rights' => 'rrr'),
        'uidnumber' => array('desc' => _LDAPUIDNUMBER, 'type' => 'int', 'rights' => 'w--'),
        'gidnumber' => array('desc' => _LDAPGIDNUMBER, 'type' => 'int', 'rights' => 'w--'),
	'mssfu30name' => array('desc' => _LDAPUID, 'type' => 'text', 'rights' => 'r--'),
        'unixhomedirectory' => array('desc' => _LDAPUNIXHOMEDIRECTORY, 'type' => 'text', 'rights' => 'wrr'),
	'loginshell' => array('desc' => _LDAPLOGINSHELL, 'type' => 'text', 'rights' => 'wrr'),
	'shadowlastchange' => array('desc' => _LDAPSHADOWLASTCHANGE, 'type' => 'text', 'rights' => 'wrr'),
	'shadowexpire' => array('desc' => _LDAPSHADOWEXPIRE, 'type' => 'text', 'rights' => 'wrr'),
	'shadowwarning' => array('desc' => _LDAPSHADOWWARNING, 'type' => 'text', 'rights' => 'wrr'),
	'shadowmin' => array('desc' => _LDAPSHADOWMIN, 'type' => 'text', 'rights' => 'wrr'),
	'shadowmax' => array('desc' => _LDAPSHADOWMAX, 'type' => 'text', 'rights' => 'wrr'),
	'shadowinactive' => array('desc' => _LDAPSHADOWINACTICE, 'type' => 'text', 'rights' => 'wrr'),
/*
        'gecos' => array('desc' => _LDAPGECOS, 'type' => 'text', 'rights' => 'w--'),
        'mail' => array('desc' => _LDAPMAIL, 'type' => 'text', 'rights' => 'wwr'),
        'telephonenumber' => array('desc' => _LDAPTELEPHONENUMBER, 'type' => 'text', 'rights' => 'ww-'),
        'mobile' => array('desc' => _LDAPMOBILE, 'type' => 'text', 'rights' => 'ww-'),
        'l' => array('desc' => _LDAPL, 'type' => 'text'),
        'street' => array('desc' => _LDAPSTREET, 'type' => 'text'),
        'postaladdress' => array('desc' => _LDAPPOSTALADDRESS, 'type' => 'text'),
        'postalcode' => array('desc' => _LDAPPOSTALCODE, 'type' => 'text'),
*/
    );

    global $ldapngGroupAttrDef;
    $ldapngGroupAttrDef = array(
        'cn'  => array('desc' => _LDAPCN, 'type' => 'text','rights' => 'rrr'),
        'name'  => array('desc' => _LDAPNAME, 'type' => 'text','rights' => 'rrr'),
	'samaccountname' => array('desc' => _LDAPSAMACCOUNTNAME, 'type' => 'text','rights' => 'wrr'),
        'description' => array('desc' => _LDAPDESCRIPTION, 'type' => 'text'),
        'gidnumber' => array('desc' => _LDAPGIDNUMBER, 'type' => 'int','rights' => 'w--'),
        'member' => array('desc' => _LDAPMEMBER, 'type' => 'select'),
	'objectcategory' => array('desc' => _LDAPOBJECTCATEGORY, 'type' => 'text','rights' => 'rrr'),

        'memberuid' => array('desc' => _LDAPMEMBERUID, 'type' => 'select'),
    );

?>
