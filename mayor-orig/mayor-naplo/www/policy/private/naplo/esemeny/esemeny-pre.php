<?php

    if (__DIAK) header('Location: '.location('index.php?page=naplo&sub=esemeny&f=jelentkezes'));
    elseif (__NAPLOADMIN) header('Location: '.location('index.php?page=naplo&sub=esemeny&f=ujEsemeny'));
    else header('Location: '.location('index.php?page=naplo&sub=esemeny&f=esemenyDiak'));

?>
