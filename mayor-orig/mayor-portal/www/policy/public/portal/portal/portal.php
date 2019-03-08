<?php

    /* including global phtml libs */

    require_once('skin/classic/module-portal/html/share/doboz.phtml');                                                                                                                                         
    require_once('skin/classic/module-portal/html/share/hirek.phtml');                                                                                                                                         
    require_once('skin/classic/module-portal/html/share/kerdoiv.phtml');
        
    global $skin,$SKINSSHOW;

/*
    if (in_array($skin,$SKINSSHOW)) {
	if (defined('_POLICY') && defined('__PORTAL_CODE') && file_exists('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.'._SKIN.'.php') ) {
	    require('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.'._SKIN.'.php');
	} else {
	    if (file_exists('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'._DEFAULT_SKIN.'.php')) 
		require('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'._DEFAULT_SKIN.'.php');
	}
    }
*/
                                                                                                                                                                                                   
    /* $skinnek lennie kell */                                                                                                                                                                             
    if (defined('_POLICY') && defined('__PORTAL_CODE')) {
	if (file_exists('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.'.$skin.'.php') )                                                      
	    require('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.'.$skin.'.php');                                                                                                                
	elseif (file_exists('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.classic.php') )                                                      
	    require('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.'.'classic'.'.php');
	elseif (file_exists('policy/'._POLICY.'/portal/portal/portal_demo.classic.php') )                                                      
	    require('policy/'._POLICY.'/portal/portal/portal_demo.classic'.'.php');
	else
	    echo 'portal ERROR.:(';
    } elseif (file_exists('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'.$skin.'.php')) {                                                                                                             
        require('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'.$skin.'.php');                                                                                                                         
    } elseif (file_exists('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'._DEFAULT_SKIN.'.php')) {                                                                                                     
        require('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'._DEFAULT_SKIN.'.php');                                                                                                                 
    }                                                                                                                                                                                                      
?>
