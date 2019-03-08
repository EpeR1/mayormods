<?php

    // Ez egy redirector...


if (file_exists($_SERVER["DOCUMENT_ROOT"].'/wiki/doku.php')) {

    header('Location:'.str_replace('dok.php','doku.php',$_SERVER['REQUEST_URI']));

} else {
    header('Refresh: 5,http://wiki.mayor.hu/'.str_replace('/wiki/dok.php','doku.php',$_SERVER['REQUEST_URI']));

    echo '<?xml version="1.0" encoding="utf-8"?>
    <!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="hu">
    <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title>Átirányítás</title>
    </head>
    <body>';

    echo '<h1>Átirányítás</h1>';
    echo '<p>Nincs helyi Help modul telepítve, ezért másodperceken belül átirányítjuk a http://wiki.mayor.hu központi help oldalra...</p>';

    echo '</body></head>';
}

?>
