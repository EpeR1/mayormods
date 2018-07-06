<?php
/*
    Modules: base/session

    function getGroupInfo($groupCn, $toPolicy = '') {
    function changeGroupInfo($userAccount, $toPolicy = '') {
*/

######################################################
# getGroupInfo - csoport információk (backend)
######################################################

    function getGroupInfo($groupCn, $toPolicy = _POLICY, $SET = array('withNewAccounts' => true)) {

        global $AUTH;

        require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/accountInfo.php');
        $func = $AUTH[$toPolicy]['backend'].'GetGroupInfo';
        return $func($groupCn, $toPolicy, $SET);


    }

###########################################################
# changeGroupInfo - csoport információk módosítása
###########################################################

    function changeGroupInfo($groupCn, $toPolicy = _POLICY) {

        global $AUTH;

        require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/accountInfo.php');
        $func = $AUTH[$toPolicy]['backend'].'ChangeGroupInfo';
        return $func($groupCn, $toPolicy);


    }
?>
