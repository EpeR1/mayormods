<?php
/*
    Modules: share/session

    function getAccountInfo($userAccount, $toPolicy = '') {
    function getUserInfo($userAccount, $toPolicy = '') {

*/

######################################################
# getAccountInfo - felhasználói információk (backend)
######################################################

    function getAccountInfo($userAccount, $toPolicy = _POLICY, $SET=array('justThese'=>'*', 'withNewAccounts' => true)) {

        global $AUTH;

        require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/accountInfo.php');
        $func = $AUTH[$toPolicy]['backend'].'GetAccountInfo';
        return $func($userAccount, $toPolicy, $SET);


    }

##########################################################
# getUserInfo - felhasználói információk (keretrendszer)
##########################################################

    function getUserInfo($userAccount, $toPolicy = _POLICY) {

        global $AUTH;

        require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/accountInfo.php');
        $func = $AUTH[$toPolicy]['backend'].'GetUserInfo';
        return $func($userAccount, $toPolicy);

    }

?>