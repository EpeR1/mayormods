<?php
    global $ADAT;

    putLevelIgazgato($ADAT);

    // phtml
    function putLevelIgazgato($ADAT) {
	formBegin();
	echo '<h1>PDF generálás?</h1>Generáljunk pdf-eket? (Lassan fut!)<input type="checkbox" name="generatePDF" value="1" /><br/>';
	echo '<h1>Email küldés</h1>';
	echo '<input type="submit" />';
	for ($i=0; $i<count($ADAT['OM']); $i++) {
	    $_om = $ADAT['OM'][$i];
//	    echo '<input type="checkbox" name="sendMAIL[]" value="'.$_om.'" checked="checked" />';
	    echo $i.". ";
	    echo '<input type="checkbox" name="sendMAIL[]" value="'.$_om.'" />';
	    echo $_om;
	    var_dump($ADAT['iskola'][$_om]['email']);
	    $file= $file = _EV.'_'.$_om;
	    echo '<a href="'.href('index.php?page=session&f=download&download=true&dir=felveteli/levelIgazgato&file='.$file.'.pdf').'">PDF</a>';
	    echo '<br/>';
	    if ($i%5==0) echo '<hr/>';
	}
	echo '<input type="submit" />';
	formEnd();
    }

?>