<?php

    if ($page != 'naplo') array_unshift($NAV[1], array('page' => 'naplo'));

    if (is_array($MENU['modules']['portal']['sub'][$sub])) foreach ($MENU['modules']['portal']['sub'][$sub] as $_f => $M) {
        $NAV[2][] = array('page' => 'portal', 'sub' => $sub, 'f' => $_f);
    } elseif (is_array($MENU['modules']['portal']))  foreach ($MENU['modules']['portal'] as $_sub => $M)    {
        if ($_sub != 'sub') $NAV[2][] = array('page' => 'portal', 'sub' => $_sub);
    }

?>