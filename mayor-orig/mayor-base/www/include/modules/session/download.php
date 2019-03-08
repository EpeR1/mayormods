<?php

    $allowedExtensions = array(

      // archives
      'zip' => 'application/zip',
      'tgz' => 'application/tar',

      // documents
      'pdf' => 'application/pdf',
      'doc' => 'application/msword',
      'xls' => 'application/vnd.ms-excel',
      'ppt' => 'application/vnd.ms-powerpoint',
      'csv' => 'application/vnd.ms-excel',
      'ods' => 'application/vnd.oasis.opendocument.spreadsheet',

      // executables
      'exe' => 'application/octet-stream',

      // text
      'xml' => 'text/xml',

      // images
      'gif' => 'image/gif',
      'png' => 'image/png',
      'jpg' => 'image/jpeg',
      'jpeg' => 'image/jpeg',

      // audio
      'mp3' => 'audio/mpeg',
      'wav' => 'audio/x-wav',

      // video
      'mpeg' => 'video/mpeg',
      'mpg' => 'video/mpeg',
      'mpe' => 'video/mpeg',
      'mov' => 'video/quicktime',
      'avi' => 'video/x-msvideo'
    );

    $allowedMimeTypes = array(
	'application/zip',
	'application/tar',
	'application/pdf',
	'application/msword',
	'application/vnd.ms-excel',
	'application/vnd.ms-powerpoint',
	'application/octet-stream',
	'text/xml',
	'image/gif',
	'image/png',
	'image/jpeg',
	'audio/mpeg',
	'audio/x-wav',
	'video/mpeg',
	'video/quicktime',
	'video/x-msvideo',
	'application/vnd.oasis.opendocument.spreadsheet'
    );

    function passFile($ADAT) {
	if (in_array($ADAT['ext'],array('jpg','jpeg','png'))) passFile_image($ADAT);
	else passFile_other($ADAT);
	die();
    }

    function passFile_image($ADAT) {
	ob_clean();
	ignore_user_abort(true);
	set_time_limit(0);
	// Getting headers sent by the client.
	$headers = apache_request_headers();
	$fn = $ADAT['path'] ;
	// Checking if the client is validating his cache and if it is current.
	if (isset($headers['If-Modified-Since']) && (strtotime($headers['If-Modified-Since']) == filemtime($fn))) {
    	    // Client's cache IS current, so we just respond '304 Not Modified'.
    	    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 304);
            header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+10 day')).' GMT', true);
            header("Cache-Control: max-age=604800, public", true);
            header("Pragma: public", true);
	} else {
	    header("Pragma: public", true);
	    header("Cache-Control: max-age=604800, public", true);
    	    header('Last-Modified: '.gmdate('D, d M Y H:i:s', filemtime($fn)).' GMT', true, 200);
    	    header('Expires: '.gmdate('D, d M Y H:i:s', strtotime('+10 day')).' GMT', true);
	    header("Content-Length: " . $ADAT['size'], true);
	    header('Content-type: '.$ADAT['mime'], true);
    	    echo file_get_contents($fn);
	}
    }

    function passFile_other($ADAT) {
	ob_clean();
	header('Content-type: '.$ADAT['mime']);
	header("Pragma: public");
	header("Expires: 0");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: public");
	header("Content-Description: File Transfer");
	header("Content-Type: ".$ADAT['mime']);
        if (!isset($ADAT['pure'])) // akkor force download as
	    header('Content-Disposition: attachment; filename="'.$ADAT['file'].'"');
	header("Content-Transfer-Encoding: binary");
	header("Content-Length: " . $ADAT['size']);
	echo file_get_contents($ADAT['path']);
    }

?>
