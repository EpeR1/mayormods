<?php
/*
    Előállít egy megadott képből egy 200px széles, adott színre átszínezett képet - vagy ha a kép nem érhető el, akkor az alapértelmezett képet adja
*/

//error_reporting(E_ALL);                                                                                                                                                        
//ini_set('display_errors', 1);                                                                                                                                                  
    require('/etc/mayor/main-config.php');
    require(_CONFIGDIR.'/skin-teszt/config.php');
    if(!function_exists('imagefilter')) require(_BASEDIR.'/include/share/gd/imagefilter.php');

    if (!defined('_CACHEDIR')) define('_CACHEDIR','/tmp');
    $defaultImgUrl = _BASEDIR.'/skin/teszt/base/img/mayor_small_ssl.png';
    $imgUrl = ($_GET['imgUrl'] != '')?$_GET['imgUrl']:$defaultImgUrl;
    $red = 242;
    $green = 54;
    $blue = 104;
    $maxWidth = 200;
    $maxHeight = 80;

$h=0.5;
$red = 242*$h;
$green = 54*$h;
$blue = 104*$h;

    $fileName = basename($imgUrl);
    $cacheFile = _CACHEDIR.'/'.$fileName;
    header('Content-Type: image/png');
    if (!_SCHOOL_LOGO_COLORIZE && !_SCHOOL_LOGO_RESIZE) {
	// A képet nem kell átalakítani - közvetlenül adjuk át
	$fp = fopen($imgUrl, 'rb');
	fpassthru($fp);
	exit;
    } elseif ((!file_exists($cacheFile)) || (_SCHOOL_LOGO_FORCE_GENERATE === true)) { // Ha nincs elkeselve még a kép, vagy kényszerítjük a generálást
	// beolvassuk
	$im = imagecreatefrompng($imgUrl);
	// Ha nem sikerül, akkor az alapértelmezettet vesszük
	if (!$im && $imgUrl != $defaultImgUrl) {
	    $imgUrl = $defaultImgUrl;
	    $fileName = basename($imgUrl);
	    $cacheFile = _CACHEDIR.'/'.$fileName;
	    // Ha már el van cache-elve, akkor kiadjuk
	    if (file_exists($cacheFile)) {
		$fp = fopen($cacheFile, 'rb');
		fpassthru($fp);
		exit;
	    }
	    // ha nincs a cache-ben, akkor beolvassuk
	    $im = imagecreatefrompng($defaultImgUrl);
	}

	// Ha sikerült
	if ($im) {
		// Átszínezzük
		if (_SCHOOL_LOGO_COLORIZE !== false) {
		    // Szüreke árnyalatossá tesszük
		    imagefilter($im, IMG_FILTER_GRAYSCALE);
		    // Majd az adott színárnyalatra átszínezzük
		    imagefilter($im, IMG_FILTER_COLORIZE, $red, $green, $blue);
		}
		// Átméretezzük
		list($width, $height) = getimagesize($imgUrl);
		if (_SCHOOL_LOGO_RESIZE !== false) {
		    // Új méretek meghatározása
		    $percent = min($maxWidth / $width, $maxHeight / $height);
		    $newHeight = intval($height * $percent);
		    $newWidth = intval($width * $percent);		
		} else {
		    $newHeight = $height;
		    $newWidth = $width;
		}
		// Ha nem kell átméretezni, akkor is átméretezzük - különben csúnya pixeles lesz :(
		$imNew = imagecreatetruecolor($newWidth, $newHeight);
 
		imagealphablending($imNew, false); // setting alpha blending on
		imagecopyresampled($imNew, $im, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
		imagesavealpha($imNew, true); // save alphablending setting (important)
		// A képet a cache-be mentjük
		imagepng($imNew, $cacheFile);
		imagedestroy($imNew);
		imagedestroy($im);

	}

    }
    $fp = fopen($cacheFile, 'rb');
    fpassthru($fp);
    exit;


?>
