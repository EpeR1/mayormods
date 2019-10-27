<?php
/*
    Module: useradmin
*/

    if (file_exists('lang/'._LANG.'/backend/ads/attrs.php')) {
        require('lang/'._LANG.'/backend/ads/attrs.php');
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/backend/ads/attrs.php')) {
        require('lang/'._DEFAULT_LANG.'/backend/ads/attrs.php');
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

    define('_DEFAULT_ADS_RIGHTS','wr-');

######################################################
# Az LDAP account attribútumok
######################################################

    global $adsAccountAttrs;
    $adsAccountAttrs = array(
        'cn',
        'sn',
	'serialnumber',
        'givenname',
	'displayname',
	'name',
	'padpwdcount',
	'badpasswordtime',
	'lastlogon',
	'pwdlastset', // ~ shadowLastChane
	'accountexpires', // != shadowExpired - henme mi? 1601.01.01-től (60*60*24*1000*1000*10)*napok száma
	'samaccountname',
	'userprincipalname',
	'useraccountcontrol',
	'objectcategory',
        'uid',
	'mssfu30name',
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
	'otherpager'
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

    global $adsGroupAttrs;
    $adsGroupAttrs = array(
	'cn',
	'description',
	'member',
	'name',
	'samaccountname',
	'objectcategory',
	'gidnumber', // ennek kellene lennie - mitől lesz?
/*	'memberuid' */
    );

    global $accountAttrToADS; // Kis és nagybetű számít!!!
    $accountAttrToADS = array(
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

    global $groupAttrToADS;
    $groupAttrToADS = array(
	'groupCn' => 'cn',
	'groupDesc' => 'description',
	'member' => 'member',
    );

    global $adsAccountAttrDef;
    $adsAccountAttrDef = array(
        'dn' => array('desc' => _ADSDN, 'type' => 'text', 'rights' => 'rrr'),
        'cn'  => array('desc' => _ADSCN, 'type' => 'text', 'rights' => 'rrr'),
        'sn' => array('desc' => _ADSSN, 'type' => 'text', 'rights' => 'wrr'),
        'givenname' => array('desc' => _ADSGIVENNAME, 'type' => 'text'),
	'serialnumber' => array('desc' => _ADSSERIALNUMBER, 'type' => 'int', 'rights' => 'wrr'),
        'displayname'  => array('desc' => _ADSCN, 'type' => 'text', 'rights' => 'wrr'),
        'name'  => array('desc' => _ADSNAME, 'type' => 'text', 'rights' => 'r--'),
	'padpwdcount' => array('desc' => _ADSBADPWDCOUNT, 'type' => 'int', 'rights' => 'wrr'),
	'badpasswordtime' => array('desc' => _ADSBADPASSWORDTIME, 'type' => 'int', 'rights' => 'r--'),
	'lastlogon' => array('desc' => _ADSLASTLOGON, 'type' => 'int', 'rights' => 'r--'),
	'pwdlastset' => array('desc' => _ADSPWDLASTSET, 'type' => 'int', 'rights' => 'r--'),
	'accountexpires' => array('desc' => _ADSACCOUNTEXPIRES, 'type' => 'int', 'rights' => 'wrr'),
	'samaccountname' => array('desc' => _ADSSAMACCOUNTNAME, 'type' => 'text', 'rights' => 'wrr'),
	'useraccountcontrol' => array('desc' => _USERACCOUNTCONTROL, 'type' => 'text', 'rights' => 'wrr'),
	'userprincipalname' => array('desc' => _ADSUSERPRINCIPALNAME, 'type' => 'text', 'rights' => 'wrr'),
	'objectcategory' => array('desc' => _ADSOBJECTCATEGORY, 'type' => 'text', 'rights' => 'r--'),
        'uid' => array('desc' => _ADSUID, 'type' => 'text', 'rights' => 'rrr'),
        'uidnumber' => array('desc' => _ADSUIDNUMBER, 'type' => 'int', 'rights' => 'w--'),
        'gidnumber' => array('desc' => _ADSGIDNUMBER, 'type' => 'int', 'rights' => 'w--'),
	'mssfu30name' => array('desc' => _ADSUID, 'type' => 'text', 'rights' => 'r--'),
        'unixhomedirectory' => array('desc' => _ADSUNIXHOMEDIRECTORY, 'type' => 'text', 'rights' => 'wrr'),
	'loginshell' => array('desc' => _ADSLOGINSHELL, 'type' => 'text', 'rights' => 'wrr'),
	'shadowlastchange' => array('desc' => _ADSSHADOWLASTCHANGE, 'type' => 'text', 'rights' => 'wrr'),
	'shadowexpire' => array('desc' => _ADSSHADOWEXPIRE, 'type' => 'text', 'rights' => 'wrr'),
	'shadowwarning' => array('desc' => _ADSSHADOWWARNING, 'type' => 'text', 'rights' => 'wrr'),
	'shadowmin' => array('desc' => _ADSSHADOWMIN, 'type' => 'text', 'rights' => 'wrr'),
	'shadowmax' => array('desc' => _ADSSHADOWMAX, 'type' => 'text', 'rights' => 'wrr'),
	'shadowinactive' => array('desc' => _ADSSHADOWINACTICE, 'type' => 'text', 'rights' => 'wrr'),
	'otherpager' => array('desc' => _ADSOTHERPAGER, 'type' => 'text', 'rights' => 'wrr'),
/*
        'gecos' => array('desc' => _ADSGECOS, 'type' => 'text', 'rights' => 'w--'),
        'mail' => array('desc' => _ADSMAIL, 'type' => 'text', 'rights' => 'wwr'),
        'telephonenumber' => array('desc' => _ADSTELEPHONENUMBER, 'type' => 'text', 'rights' => 'ww-'),
        'mobile' => array('desc' => _ADSMOBILE, 'type' => 'text', 'rights' => 'ww-'),
        'l' => array('desc' => _ADSL, 'type' => 'text'),
        'street' => array('desc' => _ADSSTREET, 'type' => 'text'),
        'postaladdress' => array('desc' => _ADSPOSTALADDRESS, 'type' => 'text'),
        'postalcode' => array('desc' => _ADSPOSTALCODE, 'type' => 'text'),
*/
    );

    global $adsGroupAttrDef;
    $adsGroupAttrDef = array(
        'cn'  => array('desc' => _ADSCN, 'type' => 'text','rights' => 'rrr'),
        'name'  => array('desc' => _ADSNAME, 'type' => 'text','rights' => 'rrr'),
	'samaccountname' => array('desc' => _ADSSAMACCOUNTNAME, 'type' => 'text','rights' => 'wrr'),
        'description' => array('desc' => _ADSDESCRIPTION, 'type' => 'text'),
        'gidnumber' => array('desc' => _ADSGIDNUMBER, 'type' => 'int','rights' => 'w--'),
        'member' => array('desc' => _ADSMEMBER, 'type' => 'select'),
	'objectcategory' => array('desc' => _ADSOBJECTCATEGORY, 'type' => 'text','rights' => 'rrr'),

        'memberuid' => array('desc' => _ADSMEMBERUID, 'type' => 'select'),
    );

?>
