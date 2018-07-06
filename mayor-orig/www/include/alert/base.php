<?php
#
# MaYoR keretrendszer - figyelmeztető üzenetek
#

// Base
if (file_exists('include/alert/'.$lang.'/base.php')) {
    require_once('include/alert/'.$lang.'/base.php');
} elseif (file_exists('include/alert/'._DEFAULT_LANG.'/base.php')) {
    require_once('include/alert/'._DEFAULT_LANG.'/base.php');
}

// Policy - Backend
if (file_exists('include/alert/'.$lang.'/'.$AUTH[$policy]['backend'].'.php')) {
    require_once('include/alert/'.$lang.'/'.$AUTH[$policy]['backend'].'.php');
} elseif (file_exists('include/alert/'._DEFAULT_LANG.'/'.$AUTH[$policy]['backend'].'.php')) {
    require_once('include/alert/'._DEFAULT_LANG.'/'.$AUTH[$policy]['backend'].'.php');
}

// Module(s) - 2016
try {
    $_dirlist = scandir('include/alert/'.$lang.'/');
    for ($i=0; $i<count($_dirlist); $i++) {
	list($_filename,$_ext) = explode('.',$_dirlist[$i]);
	list($_prefix,$_module) = explode('-',$_filename);
	if (substr($_filename,0,7)==='module-' && $_ext == 'php' && in_array($_module,$VALID_MODULES)) {
	    if (file_exists('include/alert/'.$lang.'/module-'.$_module.'.php')) {
		require_once('include/alert/'.$lang.'/module-'.$_module.'.php');
	    } elseif (file_exists('include/alert/'._DEFAULT_LANG.'/module-'.$_module.'.php')) {
		require_once('include/alert/'._DEFAULT_LANG.'/module-'.$_module.'.php');
	    }
	}
    }
} catch (Exception $e) {
    $_SESSION['alert'][] = 'info:'.$e->getMessage();
}

?>
