<?php
/* 
    MaYoR http://www.mayor.hu./ 
    License: LICENSE.txt
*/
    @error_reporting(E_ERROR | E_WARNING | E_PARSE);
    require('include/base/config.php');    
    require('include/base/mysql.php');
    require('include/base/var.php');
    require('include/base/base.php');
    require('include/base/cache.php');
    require('include/base/str.php');
    require('include/base/log.php');
    require('include/alert/base.php');
    if (version_compare(PHP_VERSION, '5.3.0', '>')) {
	if (file_exists('vendor/autoload.php')) { 
	    require_once('vendor/autoload.php');
	    require_once('include/base/error.php'); 
	}
    }
    require('include/base/rights.php');
    require('include/base/login.php');
    require('include/menu/base.php') ;
    /* --- */
    require('skin/default/base/html/base.phtml');
    if (file_exists("skin/$skin/base/html/alert.phtml")) {
        require("skin/$skin/base/html/alert.phtml");
    } else {
        require('skin/'._DEFAULT_SKIN.'/base/html/alert.phtml');
    }
    if (file_exists("skin/$skin/base/html/base.phtml")) {
        require("skin/$skin/base/html/base.phtml");
    } else {
        require('skin/'._DEFAULT_SKIN.'/base/html/base.phtml');
    }
    html_base($sessionID,$policy,$page,$sub,$f,$lang,$skin,$MENU);

?>
