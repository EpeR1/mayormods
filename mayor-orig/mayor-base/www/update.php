<?php
if (defined('_LOCKFILE') && @file_exists(_LOCKFILE)) {
    header('HTTP/1.1 503 Service Temporarily Unavailable', true, 503);
    header('Retry-After: '.gmdate('D, d M Y H:i:s', strtotime('+60 second')).' GMT');

    echo '<!DOCTYPE html>'."\n";
    echo '<html>'."\n";
    echo '<head><meta http-equiv="Content-Type" content="text/html; charset=utf-8">'."\n";
    echo '<title>MaYoR karbantartás</title>';
	echo '<style type="text/css">
	    html {height:100%; min-height: 100%;}
	    body {font-family: Verdana; background-color: white;
/* Permalink - use to edit and share this gradient: http://colorzilla.com/gradient-editor/#a7cfdf+0,23538a+100;Blue+3d+%238 */
background: rgb(167,207,223); /* Old browsers */
background: -moz-linear-gradient(-45deg, rgba(167,207,223,1) 0%, rgba(35,83,138,1) 100%); /* FF3.6-15 */
background: -webkit-linear-gradient(-45deg, rgba(167,207,223,1) 0%,rgba(35,83,138,1) 100%); /* Chrome10-25,Safari5.1-6 */
background: linear-gradient(135deg, rgba(167,207,223,1) 0%,rgba(35,83,138,1) 100%); /* W3C, IE10+, FF16+, Chrome26+, Opera12+, Safari7+ */

} 
	    a {color: cornflowerblue }
	    h1, h2, footer {text-align: center; margin: 60px; background-color: rgba(255,255,255); color: #444; padding:4px; border-radius: 4px; }
	    h1 { border-bottom: solid 0px #f06; }
	    div.content {
		font-size:normal; color:white; padding: 5px 60px; 
		border-left: solid 4px white;
		border-right: solid 4px white;
	    }
	</style>'."\n";
	echo '<link rel="shortcut icon" href="/skin/classic/base/img/favicon.ico">'."\n";
    echo '<meta http-equiv="refresh" content="60">';
    echo '</head><body>'."\n";

    $id = urlencode(getHostname().'+'.$_SERVER['SERVER_NAME'].'+'.$_SERVER['SERVER_ADMIN']);
    echo '<div style="width:100%;text-align:center;"><img src="https://www.mayor.hu/skin/mayor/base/img/multiMayorLogo.svg?mayorLock='.$id.'" style="height:120px;" alt="MaYoR"></div>';
    echo '<h1 class=""><sub>M</sub>a<sub>Y</sub><sup>o</sup>R';
    echo ' software update</h1>'."\n";
    echo '<div class="content">'."\n";
	echo '<p>Az automatikus frissítés épp fut, vagy a szolgáltatást a rendszerüzemeltető letiltotta.</p>'."\n";
	echo '<p>The system is down for maintenance.</p>'."\n";
	echo '<p style="font-size:smaller;">'.date('Y-m-d H:i:s').'</p>';
    echo '</div>'."\n";
    echo '<footer><a href="https://www.mayor.hu">mayor.hu</a> &copy; GPL</footer>';
    echo '</body></html>';
} else {
    header('index.php');
}
?>
