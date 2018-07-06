<?php
{
	if (_RIGHTS_OK !== true) die();

	if (!__NAPLOADMIN) {
		$_SESSION['alert'][] = 'message:insufficient_access';
	} else {
		require_once('include/modules/naplo/share/file.php');
		
		$mezo_elvalaszto = '	';
		if (isset($_POST['fileName'])) $fileName = fileNameNormal($_POST['fileName']);
		$ADATOK = array();
		
		if ($fileName != '') {

			if (file_exists($fileName)) {

				$MEZO_LISTA = $_POST['MEZO_LISTA'];
				$KULCS_MEZOK = $_POST['KULCS_MEZOK'];
				if (!is_array($MEZO_LISTA)) {

					$ADATOK = readUpdateFile($fileName);
					if (count($ADATOK) > 0) $attrList = getTableFields('tankor', 'naplo_intezmeny',array('targyNev'));
					else $_SESSION['alert'][] = 'message:wrong_data';

				} else {

					updateTankor($fileName, $MEZO_LISTA, $KULCS_MEZOK, $mezo_elvalaszto, $_POST['rovatfej']);

				} // MEZO_LISTA tömb
			} else {
				$_SESSION['alert'][] = 'message:file_not_found:'.$fileName;
			} // A file létezik-e

		} // van file
	}

}
?>
