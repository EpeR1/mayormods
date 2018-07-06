<?php
/*
    Modules: base/session
*/

    function createGroup($groupCn, $groupDesc, $toPolicy = _POLICY, $SET = array('category' => null, 'container' => null, 'policyAttrs' => array())) {

        global $AUTH;

        require_once('include/backend/'.$AUTH[$toPolicy]['backend'].'/session/createGroup.php');
        $func = $AUTH[$toPolicy]['backend'].'CreateGroup';
        return $func($groupCn, $groupDesc, $toPolicy, $SET);

    }

?>
