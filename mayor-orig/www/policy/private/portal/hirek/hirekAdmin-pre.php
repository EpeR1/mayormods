<?php
  if (_RIGHTS_OK !== true) die();

    if (!__HIREKADMIN) {
	$_SESSION['alert'][] = 'page:insufficient_access';
    }

    require_once('include/modules/portal/share/hirek.php');
    $HIREK = getHirek(array('all'=>true)); // minden nyelvű hír

?>
