<?php

    function updateSessionParentDiakId($parentDiakId) {

	    $q = "UPDATE session SET parentDiakId=%u WHERE policy='parent' and sessionID='"._SESSIONID."'";
	    db_query($q, array('fv' => 'updateSessionOid', 'modul' => 'naplo_base', 'values' => array($parentDiakId)));

    }

?>
