<?php

    global $skin;

    /* $skinnek lennie kell */                                                                                                                                                                             

    if (defined('_POLICY') && defined('__PORTAL_CODE') && file_exists('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.'.$skin.'.php') ) {                                                       
        require('policy/'._POLICY.'/portal/portal/portal_'.__PORTAL_CODE.'.'.$skin.'.php');                                                                                                                
    } elseif (file_exists('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'.$skin.'.php')) {                                                                                                             
        require('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'.$skin.'.php');                                                                                                                         
    } elseif (file_exists('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'._DEFAULT_SKIN.'.php')) {                                                                                                             
        require('policy/'._POLICY.'/portal/portal/portal_'.demo.'.'._DEFAULT_SKIN.'.php');                                                                                                                         
    }                                                    

?>
