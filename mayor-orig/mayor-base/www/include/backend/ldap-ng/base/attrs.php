<?php
/*
    Module: useradmin
*/

    if (file_exists('lang/'._LANG.'/backend/ldap-ng/attrs.php')) {
        require('lang/'._LANG.'/backend/ldap-ng/attrs.php');
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/backend/ldap-ng/attrs.php')) {
        require('lang/'._DEFAULT_LANG.'/backend/ldap-ng/attrs.php');
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

    global $ldapGroupAttrs;
    $ldapGroupAttrs = array(
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
	'userAccount' => 'sAMAccountName',
	'userCn' => 'displayName',
	'mail' => 'mail',
	'studyId' => 'serialNumber', // Ez konfig-ban külön van állítva, az itteni érték irreleváns
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

    global $ldapAccountAttrDef;
    $ldapAccountAttrDef = array(
        'dn' => array('desc' => _LDAPDN, 'type' => 'text', 'rights' => 'rrr'),
        'cn'  => array('desc' => _LDAPCN, 'type' => 'text', 'rights' => 'rrr'),
        'sn' => array('desc' => _LDAPSN, 'type' => 'text', 'rights' => 'wrr'),
        'givenname' => array('desc' => _LDAPGIVENNAME, 'type' => 'text'),
	'serialnumber' => array('desc' => _LDAPSERIALNUMBER, 'type' => 'int', 'rights' => 'wrr'),
        'displayname'  => array('desc' => _LDAPCN, 'type' => 'text', 'rights' => 'wrr'),
        'name'  => array('desc' => _LDAPNAME, 'type' => 'text', 'rights' => 'r--'),
	'padpwdcount' => array('desc' => _LDAPBADPWDCOUNT, 'type' => 'int', 'rights' => 'wrr'),
	'badpasswordtime' => array('desc' => _LDAPBADPASSWORDTIME, 'type' => 'int', 'rights' => 'r--'),
	'lastlogon' => array('desc' => _LDAPLASTLOGON, 'type' => 'int', 'rights' => 'r--'),
	'pwdlastset' => array('desc' => _LDAPPWDLASTSET, 'type' => 'int', 'rights' => 'r--'),
	'accountexpires' => array('desc' => _LDAPACCOUNTEXPIRES, 'type' => 'int', 'rights' => 'wrr'),
	'samaccountname' => array('desc' => _LDAPSAMACCOUNTNAME, 'type' => 'text', 'rights' => 'wrr'),
	'useraccountcontrol' => array('desc' => _USERACCOUNTCONTROL, 'type' => 'text', 'rights' => 'wrr'),
	'userprincipalname' => array('desc' => _LDAPUSERPRINCIPALNAME, 'type' => 'text', 'rights' => 'wrr'),
	'objectcategory' => array('desc' => _LDAPOBJECTCATEGORY, 'type' => 'text', 'rights' => 'r--'),
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

    global $ldapGroupAttrDef;
    $ldapGroupAttrDef = array(
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
