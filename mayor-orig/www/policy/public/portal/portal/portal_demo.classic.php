<?php

    global $ADAT,$vmPost;

    $TXT['japszotar'] ='
<!-- MaYoR-じしょ search form -->
<form name="jisho" style="font-size:10px" method="post" action=" http://szotar.vmg.sulinet.hu/index.php?p=dict" target="_blank">
<select name="lang" style="font-size:10px; width:110px; background-color: #eeeeee;">
<option value="hu">magyar</option>
<option value="jp">japán</option>
<option value="en">angol</option>
</select>
<br/>
<input name="word" value="" style="width: 90px; font-size:10px;" type="text" />
<input src="skin/classic/module-portal/img/ok_button.gif" name="sent" value="Mehet" type="image" />
</form>
<!-- end of MaYoR-じしょ -->';


    echo '<table class="portalMain">';
    echo '<tr><td class="bal">';

	putDoboz('MaYoR-じしょ',$TXT['japszotar'],array('header-color'=>'#bb0088'));

    echo '</td>';
    echo '<td class="kozep">';

	putHirek($ADAT);

    echo '</td>';
    echo '<td class="jobb">';

	putDoboz('Névnapok','Ma: '.$ADAT['nevnap']['ma'].'<br/>Holnap: '.$ADAT['nevnap']['holnap'],array('header-color'=>'rgb(138,128,238)'));


	if (is_array($ADAT['kerdoiv']['kerdes']) && count($ADAT['kerdoiv']['kerdes']) > 0)
	    putDoboz('Kérdőív', genKerdoiv($ADAT['kerdoiv']), array('header-color'=>'rgb(238,82,38)'));

    echo '</td>';
    echo '</tr>';
    echo '</table>';

?>
