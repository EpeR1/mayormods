<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    global $attr, $pattern, $searchResult, $searchAttrList, $toPolicy, $ADAT;

    putSearchAccountForm($attr, $pattern, $searchAttrList, $toPolicy);

    if (is_array($searchResult)) {
        putSearchResultBox($searchResult, $toPolicy);
    }

?>
