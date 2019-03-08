<?php

    if (_RIGHTS_OK !== true) die();

    // A függvény a base.phtml-ben lett definiálva
    //ajaxUpdaterForm('proba','index.php?page=naplo&sub=tools&f=ajax','alma=piros&narancs=sárga','post');

    $userId = __USERTANARID;
    $today = date('Y-m-d');

    ajaxUpdaterForm('proba','index.php?page=naplo&sub=tanev&f=orarend',array('dt'=>$today,'tolDt'=>$today),'post');
    
    echo '<form id="ajaxUpdaterForm" class="onSubmitUpdate" method="post" action="'.href("index.php?page=naplo&sub=osztalyozo&f=jegy").'">'."\n"; // -- OBSOLETE

    echo '<input type="text" name="jegyId" value="">';
//    echo '<input type="button" class="onClickUpdate" value="Frissít">';
    echo '<input type="submit" class="" value="Frissít">';

    echo '</form>';

echo '<hr />';

    echo '<form method="post" action="/debug.php" class="onChangeRequest" id="requestForm" >';
    echo '<input type="text" name="a" value="" id="aa" />';
    echo '<input type="text" name="b" value="bbb" id="bb" />';
    echo '<input type="submit" value="OK" />';
    echo '</form>'; // -- OBSOLETE

?>
