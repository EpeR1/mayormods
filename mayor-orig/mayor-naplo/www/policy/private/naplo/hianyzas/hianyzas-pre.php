<?php

    if (__DIAK) header('Location: '.location('index.php?page=naplo&sub=hianyzas&f=diak'));
    elseif (__TITKARSAG) header('Location: '.location('index.php?page=naplo&sub=hianyzas&f=info'));
    else header('Location: '.location('index.php?page=naplo&sub=hianyzas&f=osztaly'));

?>
