<?php

    global $ADAT,$vmPost;

    if ($_GET['show'] == 'hirek') {

	putHirek($ADAT);

    } else {
	echo '
	<h1>Belépés</h1>
	<div class="set">
	<a class="diak" href="'.href('index.php?page=auth&f=login&toPolicy=private&skin=pda&policy=public&toPolicy=private&toSkin=pda&toPSF=naplo:osztalyozo:diak').'">diák</a>
	<a class="szulo" href="'.href('index.php?page=auth&f=login&toPolicy=private&skin=pda&policy=public&toPolicy=parent&toSkin=pda&toPSF=naplo:osztalyozo:diak').'">szülő</a>
	<a class="tanar" href="'.href('index.php?page=auth&f=login&toPolicy=private&skin=pda&policy=public&toPolicy=private&toSkin=pda&toPSF=naplo::naplo').'">tanár</a>';

	if (defined('__FORUM_INSTALLED') && __FORUM_INSTALLED===true)
	    echo '<a class="forum" href="'.href('index.php?page=auth&f=login&toPolicy=private&skin=pda&policy=public&toPolicy=public&toSkin=pda&toPSF=forum::').'">fórum</a>';
	echo '</div>';

	echo '<h1>Szolgáltatások</h1>
	<div class="set">
	<a class="orarend" href="'.href('index.php?page=naplo&sub=orarend&f=orarend').'">órarend</a>
	<a class="helyettesites" href="'.href('index.php?page=naplo&sub=orarend&f=helyettesites').'">helyettesítés</a>
	<a class="hirek" href="'.href('index.php?page=portal&f=portal&show=hirek').'">hírek</a>';
	echo '</div>';
    }

?>
