<?php
/*
    Module:	base/session
*/

    if (_RIGHTS_OK !== true) die();

    global $attr, $pattern, $searchAttrs, $searchResult, $toPolicy;

    putSearchGroupForm($attr, $pattern, $searchAttrs, $toPolicy);

    if (is_array($searchResult)) {
        putSearchResultBox($searchResult, $toPolicy);
    }

?>
