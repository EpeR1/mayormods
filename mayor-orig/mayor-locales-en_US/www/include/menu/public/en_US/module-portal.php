<?php
/*
    module: naplo
*/
    $MENU['portal'] = array(array('txt' => 'Fellowship, introduction, accessibility', 'url' => 'index.php?page=portal&sub=info&f=info'));

    $MENU['modules']['portal']['info'] = array(array('txt' => 'Means of contact', 'url' => 'index.php?page=portal&sub=info&f=info'));
    $MENU['modules']['portal']['diaksag'] = array(array('txt' => 'Student life', 'url' => 'index.php?page=portal&sub=diaksag&f=vmgmix'));
    $MENU['modules']['portal']['dok'] = array(array('txt' => 'Student autonomy', 'url' => 'index.php?page=portal&sub=dok&f=diakkepviselok'));
    $MENU['modules']['portal']['bemutatkozas'] = array(array('txt' => 'Introduction', 'url' => 'index.php?page=portal&sub=bemutatkozas&f=konyvtar'));
    $MENU['modules']['portal']['oktatas'] = array(array('txt' => 'Education 2008', 'url' => 'index.php?page=portal&sub=oktatas&f=zenetortenet2008'));
    $MENU['modules']['portal']['szmsz'] = array(array('txt' => 'Regulations', 'url' => 'index.php?page=portal&sub=szmsz&f=tartalom'));
    $MENU['modules']['portal']['pepo'] = array(array('txt' => 'Educational program', 'url' => 'index.php?page=portal&sub=pepo&f=tartalom'));
    $MENU['modules']['portal']['hazirend'] = array(array('txt' => 'Policy', 'url' => 'index.php?page=portal&sub=hazirend&f=hazirend2005'));
    $MENU['modules']['portal']['sub']['bemutatkozas'][] = array(
	array('txt' => 'Library', 'url' => 'index.php?page=portal&sub=bemutatkozas&f=konyvtar')
    );
    $MENU['modules']['portal']['sub']['pepo'][] = array(
	array('txt' => 'Contents', 'url' => 'index.php?page=portal&sub=pepo&f=tartalom'),
	array('txt' => '2. chapter', 'url' => 'index.php?page=portal&sub=pepo&f=2'),
	array('txt' => '3. chapter', 'url' => 'index.php?page=portal&sub=pepo&f=3'),
	array('txt' => '4. chapter', 'url' => 'index.php?page=portal&sub=pepo&f=4'),
	array('txt' => '5. chapter', 'url' => 'index.php?page=portal&sub=pepo&f=5'),
	array('txt' => '6. chapter', 'url' => 'index.php?page=portal&sub=pepo&f=6'),
	array('txt' => '7. chapter', 'url' => 'index.php?page=portal&sub=pepo&f=7'),
	array('txt' => '8. chapter', 'url' => 'index.php?page=portal&sub=pepo&f=8'),
    );
    $MENU['modules']['portal']['sub']['szmsz'][] = array(
	array('txt' => 'Contents', 'url' => 'index.php?page=portal&sub=szmsz&f=tartalom'),
	array('txt' => '2. chapter', 'url' => 'index.php?page=portal&sub=szmsz&f=2'),
	array('txt' => '3. chapter', 'url' => 'index.php?page=portal&sub=szmsz&f=3'),
	array('txt' => '4. chapter', 'url' => 'index.php?page=portal&sub=szmsz&f=4'),
	array('txt' => '5. chapter', 'url' => 'index.php?page=portal&sub=szmsz&f=5'),
    );
    $MENU['modules']['portal']['sub']['diaksag'][] = array(
	array('txt' => 'VMG-mix (by Balázs Alpár and Ábel Bartos)', 'url' => 'index.php?page=portal&sub=diaksag&f=vmgmix'),
    );
    $MENU['modules']['portal']['sub']['oktatas'][] = array(
	array('txt' => 'History of music course 2008', 'url' => 'index.php?page=portal&sub=oktatas&f=zenetortenet2008'),
	array('txt' => 'English translation competition', 'url' => 'index.php?page=portal&sub=oktatas&f=angol2008'),
    );
    $MENU['modules']['portal']['sub']['oktatas/angol'][] = array(
	array('txt' => 'Tunes', 'url' => 'index.php?page=portal&sub=oktatas/angol&f=dalok'),
	array('txt' => 'Lyrics translations', 'url' => 'index.php?page=portal&sub=oktatas/angol&f=sting'),
	array('txt' => 'Harry Potter', 'url' => 'index.php?page=portal&sub=oktatas/angol&f=ph7'),
	array('txt' => 'Christofer Whyte', 'url' => 'index.php?page=portal&sub=oktatas/angol&f=christoferwhyte'),
    );

?>
