<?php
if (defined('_LOCKFILE') && @file_exists(_LOCKFILE)) {
    echo '<?xml version="1.0" encoding="utf-8"?>'."\n";
    echo '<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">'."\n";
    echo '<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="hu">'."\n";
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8" /><title>MaYoR</title></head><body>'."\n";
    echo '<div>'."\n";
    echo '<h1>MaYoR software update</h1>'."\n";
    echo '<p>Hopp! Az automatikus frissítés épp fut, vagy a szolgáltatást a rendszerüzemeltető letiltotta.</p>'."\n";
    echo '<p>Oops! Automatic update process is running or the system administrator has locked down this service.</p>'."\n";
    echo '<p style="font-size:smaller;">'.date('Y-m-d H:i:s').'</p>';
    echo '</div>'."\n";

echo '<div>';
$i=0; $i++;
//for ($i=0; $i<count(); $i++) {
//
//}
echo '</div>';
                      
    echo '</body></html>';
} else {
    header('index.php');
}
?>
