<?php

if (_RIGHTS_OK !== true) die();

require_once('include/modules/naplo/share/file.php');

if (!__NAPLOADMIN) {
	$_SESSION['alert'][] = 'message:insufficient_access';
} else {

	$mezo_elvalaszto = '	';
	if (isset($_POST['dbtable'])) {
		$dbtable = $_POST['dbtable'];
		list($db,$table) = explode(':',$dbtable);
	}
	//IDEIGLENESEN if (isset($_POST['fileName'])) $fileName = fileNameNormal($_POST['fileName']);
	if (isset($_POST['fileName'])) $fileName = ($_POST['fileName']);
	$ADATOK = array();

	if (isset($table)) {
		if ($fileName != '') {
			define('_SKIP_ON_DUP',readVariable($_POST['skipOnDup'],'bool'));

			if (file_exists($fileName)) {
				$MEZO_LISTA = $_POST['MEZO_LISTA'];
				$KULCS_MEZOK = $_POST['KULCS_MEZOK'];
				if (!is_array($MEZO_LISTA)) {

					$ADATOK = readUpdateFile($fileName);
					if (count($ADATOK) > 0) $attrList = getTableFields($table, $db);
					else $_SESSION['alert'][] = 'message:wrong_data';

				} else {
					updateTable($table, $fileName, $MEZO_LISTA, $KULCS_MEZOK, $mezo_elvalaszto, readVariable($_POST['rovatfej'], 'bool'), $db);

				} // MEZO_LISTA tömb
			} else {
				$_SESSION['alert'][] = 'message:file_not_found:'.$fileName;
			} // A file létezik-e

		} // van file
	} // van $table ($db)

} // naploadmin

$TOOL['TableSelect'] = array('tipus'=>'cella','paramName' => 'dbtable', 'post'=>array());
getToolParameters();

?>
