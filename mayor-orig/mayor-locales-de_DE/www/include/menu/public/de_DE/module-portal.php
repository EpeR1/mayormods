<?php
/*
    module: naplo
*/
    $MENU['portal'] = array(array('txt' => 'Studenten, Vorstellung, Erreichbarkeit', 'url' => 'index.php?page=portal&sub=info&f=info'));

    $MENU['modules']['portal']['info'] = array(array('txt' => 'Erreichbarkeit', 'url' => 'index.php?page=portal&sub=info&f=info'));
    $MENU['modules']['portal']['diaksag'] = array(array('txt' => 'Studentenleben', 'url' => 'index.php?page=portal&sub=diaksag&f=vmgmix'));
    $MENU['modules']['portal']['dok'] = array(array('txt' => 'Studenten Selbstverwaltung', 'url' => 'index.php?page=portal&sub=dok&f=diakkepviselok'));
    $MENU['modules']['portal']['bemutatkozas'] = array(array('txt' => 'Vorstellung', 'url' => 'index.php?page=portal&sub=bemutatkozas&f=konyvtar'));
    $MENU['modules']['portal']['szmsz'] = array(array('txt' => 'SzMSz', 'url' => 'index.php?page=portal&sub=szmsz&f=tartalom'));
    $MENU['modules']['portal']['pepo'] = array(array('txt' => 'Pädagogisches Programm', 'url' => 'index.php?page=portal&sub=pepo&f=tartalom'));
    $MENU['modules']['portal']['hazirend'] = array(array('txt' => 'Hausregeln', 'url' => 'index.php?page=portal&sub=hazirend&f=hazirend2005'));
    $MENU['modules']['portal']['sub']['bemutatkozas'][] = array(
	array('txt' => 'Bibliothek', 'url' => 'index.php?page=portal&sub=bemutatkozas&f=konyvtar')
    );
    $MENU['modules']['portal']['sub']['pepo'][] = array(
	array('txt' => 'Inhalt', 'url' => 'index.php?page=portal&sub=pepo&f=tartalom'),
	array('txt' => '2. Kapitel', 'url' => 'index.php?page=portal&sub=pepo&f=2'),
	array('txt' => '3. Kapitel', 'url' => 'index.php?page=portal&sub=pepo&f=3'),
	array('txt' => '4. Kapitel', 'url' => 'index.php?page=portal&sub=pepo&f=4'),
	array('txt' => '5. Kapitel', 'url' => 'index.php?page=portal&sub=pepo&f=5'),
	array('txt' => '6. Kapitel', 'url' => 'index.php?page=portal&sub=pepo&f=6'),
	array('txt' => '7. Kapitel', 'url' => 'index.php?page=portal&sub=pepo&f=7'),
	array('txt' => '8. Kapitel', 'url' => 'index.php?page=portal&sub=pepo&f=8'),
    );
    $MENU['modules']['portal']['sub']['szmsz'][] = array(
	array('txt' => 'Inhalt', 'url' => 'index.php?page=portal&sub=szmsz&f=tartalom'),
	array('txt' => '2. Kapitel', 'url' => 'index.php?page=portal&sub=szmsz&f=2'),
	array('txt' => '3. Kapitel', 'url' => 'index.php?page=portal&sub=szmsz&f=3'),
	array('txt' => '4. Kapitel', 'url' => 'index.php?page=portal&sub=szmsz&f=4'),
	array('txt' => '5. Kapitel', 'url' => 'index.php?page=portal&sub=szmsz&f=5'),
    );
    $MENU['modules']['portal']['sub']['diaksag'][] = array(
	array('txt' => 'VMG-mix(Balázs Alpár, Ábel Bartos)', 'url' => 'index.php?page=portal&sub=diaksag&f=vmgmix'),
    );

?>
