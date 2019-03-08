<?php

    if (_RIGHTS_OK !== true) die();

    global $Szulok, $szuloId, $attr;

    if ($skin == 'ajax') {
	putSzuloSelect($Szulok, $szuloId, $attr);
    } else echo $skin;

    function putSzuloSelect($Szulok, $value, $attr) {
	formBegin();
        echo '<select name="'.$attr.'">'."\n";
            $SEL = array($value => ' selected="selected" ');
            echo '<option value="NULL"> - </option>'."\n";
            for ($i = 0; $i < count($Szulok['szuloIds']); $i++) {
                $szuloId = $Szulok['szuloIds'][$i];
                echo '<option value="'.$szuloId.'"'.$SEL[$szuloId].'>'.
                    $Szulok[$szuloId]['szuloNev'].'</option>'."\n";
            }
        echo '</select>'."\n";
	formEnd();
    }
?>
