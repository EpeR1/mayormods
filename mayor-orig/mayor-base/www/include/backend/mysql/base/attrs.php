<?php

    if (file_exists('lang/'._LANG.'/backend/mysql/attrs.php')) {
        require('lang/'._LANG.'/backend/mysql/attrs.php');
    } elseif (file_exists('lang/'._DEFAULT_LANG.'/backend/mysql/attrs.php')) {
        require('lang/'._DEFAULT_LANG.'/backend/mysql/attrs.php');
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

    define('_DEFAULT_MYSQL_RIGHTS','wr-');

    global $mysqlAccountAttrDef;
    $mysqlAccountAttrDef = array(
        'uid' => array('desc' => _MYSQLUID, 		'type' => 'text',  'rights' => 'rrr'),
        'policy' => array('desc' => _MYSQLPOLICY, 	'type' => 'text',  'rights' => 'r--'),
        'useraccount' => array('desc' => _MYSQLUIDNUMBER, 'type' => 'text','rights' => 'r--'),
        'userCn'  => array('desc' => _MYSQLCN, 		'type' => 'text',  'rights' => 'wrr'),
        'studyId' => array('desc' => _MYSQLSTUDYID, 	'type' => 'int',   'rights' => 'wrr'),
        'mail' => array('desc' => _MYSQLMAIL, 		'type' => 'text',  'rights' => 'wwr'),
        'telephoneNumber' => array('desc' => _MYSQLTELEPHONENUMBER, 'type' => 'text', 'rights' => 'ww-'),
//	'userPassword' => array('desc' => _MYSQLUSERPASSWORD, 'type' => 'text', 'rights' => 'r--'),
        'shadowLastChange' => array('desc' => _MYSQLSHADOWLASTCHANGE, 'type' => 'text', 'rights' => 'wrr'),
        'shadowExpire' => array('desc' => _MYSQLSHADOWEXPIRE, 	'type' => 'text', 'rights' => 'wrr'),
        'shadowWarning' => array('desc' => _MYSQLSHADOWWARNING, 'type' => 'text', 'rights' => 'wrr'),
        'shadowMin' => array('desc' => _MYSQLSHADOWMIN, 	'type' => 'text', 'rights' => 'wrr'),
        'shadowMax' => array('desc' => _MYSQLSHADOWMAX, 	'type' => 'text', 'rights' => 'wrr'),
        'shadowInactive' => array('desc' => _MYSQLSHADOWINACTICE, 'type' => 'text', 'rights' => 'wrr'),
    );

    global $mysqlGroupAttrDef;
    $mysqlGroupAttrDef = array(
        'gid'  => array('desc' => _MYSQLGID, 'type' => 'text', 'rights' => 'rrr'),
        'groupDesc' => array('desc' => _MYSQLGROUPDESC, 'type' => 'text', 'rights' => 'wrr'),
        'policy' => array('desc' => _MYSQLPOLICY, 'type' => 'int', 'rights' => 'r--'),
        'member' => array('desc' => _MYSQLMEMBER, 'type' => 'select', 'rights' => 'w--'),
    );


?>
