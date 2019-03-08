<?php

    if (_RIGHTS_OK !== true) die();
    if (!((defined('_POLICY') && _POLICY=='public') 
	    || (defined('__TITKARSAG') && __TITKARSAG===true) 
	    || (defined('__TANAR') && __TANAR===true) 
	    || (defined('__NAPLOADMIN') && __NAPLOADMIN===true)
	    || (defined('__DIAK') && __DIAK===true) 
    )) {
	return false;
    }

    // $file  -  csak a file neve, útvonal nem lehet benne
    if (isset($_POST['file']) && $_POST['file'] != '') $file = basename($_POST['file']);
    elseif (isset($_GET['file']) && $_GET['file'] != '') $file = basename($_GET['file']);

    if (!isset($file)) $_SESSION['alert'][] = 'page:empty_fields:file';
    else {
	// $dir  -  betű, szám, -, _, és / lehet benne (elején csak betű vagy szám)
	$dir = readVariable($_POST['dir'], 'path', readVariable($_GET['dir'], 'path'));

	// Az útvonal beállítása
	$path = _DOWNLOADDIR.'/'._POLICY;
	if (isset($dir)) $path .= '/'.$dir;
	$path .= '/'.$file;

	// Jogosultságok ellenőzése dir alapján
	if (__DIAK===true) {
	    if (strstr($dir,'naplo/face/')===false) return false;
	}
	// Titkarsag, Tanar, Naploadmin letoltheti, amit szeretne

	// Létezik-e a file
	if (!file_exists($path)) $_SESSION['alert'][] = 'page:file_not_found:'.substr($path, strlen(_DOWNLOADDIR.'/'._POLICY.'/')).':'.$path;
	else {

	    $ADAT['path'] = $path;
	    $ADAT['dir'] = $dir;
	    $ADAT['file'] = $file;
	    $ADAT['ext'] = strtolower(substr(strrchr($file,"."),1));
	    $ADAT['size'] = filesize($path);
	    $ADAT['mime'] = readVariable($_POST['mimetype'], 'enum', readVariable($_GET['mimetype'], 'enum', null, $allowedMimeTypes), $allowedMimeTypes);

	    // MiME típus megállapítása
	    if (!isset($ADAT['mime'])) {
		if ($allowedExtensions[$ADAT['ext']] != '') $ADAT['mime'] = $allowedExtensions[$ADAT['ext']];
		else {
		    if (function_exists('mime_content_type')) {
			$ADAT['mime'] = mime_content_type($ADAT['path']);
		    } elseif (function_exists('finfo_file')) {
			$finfo = finfo_open(FILEINFO_MIME);
			$ADAT['mime'] = finfo_file($finfo, $ADAT['path']);
			finfo_close($finfo);
		    }
		    if ($ADAT['mime'] == '') $ADAT['mime'] = "application/force-download";
		}
	    }
	    if (in_array($ADAT['mime'],array('image/gif','image/png','image/jpeg','image/jpeg'))) $ADAT['pure']=true;
	    // letöltés
	    if (isset($_GET['download'])) passFile($ADAT);

	}
    }


?>
