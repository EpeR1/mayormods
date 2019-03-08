<?php
/*
    module: naplo
*/

   $MENU['naplo'] = array(
	array('txt' => 'Online Register',	'url' => 'index.php?page=naplo')
    );

    // A menüpontok sorrendjének beállítása - ettől még nem jelenik meg semmi :)
    $MENU['modules']['naplo'] = array(
	'haladasi' => array(),
	'osztalyozo' => array(),
	'hianyzas' => array(),
	'bejegyzesek' => array(),
	'tanev' => array(),
	'intezmeny' => array(),
	'admin' => array(),
    );

    if (__DIAK) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Progress register', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Grades', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=diak'));
	$MENU['modules']['naplo']['hianyzas'] =  array(array('txt' => 'Register of absencies', 'url' => 'index.php?page=naplo&sub=hianyzas&f=diak'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Entries', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['diakTankorJelentkezes'] = array(array('txt' => 'Application', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankorJelentkezes'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Requests', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'dolgozat' => array(array('txt' => 'Tests')),
	    'bizonyitvany' => array(array('txt' => 'Report')),
	    'stat' => array(array('txt' => 'Statistics')),
	);
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Timetable', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => 'Unused classrooms', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	    array('txt' => 'Workplan', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv')
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'New request')),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Change schoolyear', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => 'Progress statistics')),
	);
	if(__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] =  array(array('txt' => 'Messenger', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));

    } elseif (__TANAR) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Progress register', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Grades', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Register of absencies', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Entries', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Requests', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Timetable', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => 'Unused classrooms', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	    array('txt' => 'Workplan', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv'),
	    array('txt' => 'PTA', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(
	    array('txt' => 'Change schoolyear', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'),
	    array('txt' => 'Students in studygroup', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak'),
	    array('txt' => 'Student\'s studygroups', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankor'),
	    array('txt' => 'Student\'s selected studygroups', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankorJelentkezes'),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'tanarOrarend' => array(array('txt' => 'Summarized teacher\'s timetable')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'New request')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Studygroup\'s grades')),
	    'diak' => array(array('txt' => 'Student\'s grades')),
	    'dolgozat' => array(array('txt' => 'Tests')),
	    'bizonyitvany' => array(array('txt' => 'Report')),
	    'stat' => array(array('txt' => 'Statistics')),
	);
	if (__OSZTALYFONOK === true) $MENU['modules']['naplo']['sub']['osztalyozo']['targySorrend'] = array(array('txt' => 'Order of subjects'));
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => 'List of entries')),
	    'ujBejegyzes' => array(array('txt' => 'New entry')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => 'Progress statistics')),
	);
	$MENU['modules']['naplo']['sub']['hianyzas'] = array(
	    'osztaly' => array(array('txt' => 'Class\' absency summarizer')),
	    'diak' => array(array('txt' => 'Student\'s absencies (calendar view)')),
	);
	if(__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] =  array(array('txt' => 'Messenger', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));

    } elseif (__TITKARSAG === true) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Progress register', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Grades', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Register of absencies', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Requests', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['tanev'] = array(
	    array('txt' => 'Timetable', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'),
	    array('txt' => 'Unused classrooms', 'url' => 'index.php?page=naplo&sub=tanev&f=szabadTerem'),
	    array('txt' => 'Workplan', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv'),
	    array('txt' => 'PTA', 'url' => 'index.php?page=naplo&sub=tanev&f=fogadoOra'),
	);
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Institution details', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'tanarOrarend' => array(array('txt' => 'Summarized teacher\'s timetable')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'New request')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Studygroup\'s grades')),
	    'diak' => array(array('txt' => 'Student\'s grades')),
	    'dolgozat' => array(array('txt' => 'Tests')),
	    'bizonyitvany' => array(array('txt' => 'Report')),
	    'stat' => array(array('txt' => 'Statistics')),
	    'targySorrend' => array(array('txt' => 'Order of subjects')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'stat' => array(array('txt' => 'Progress statistics')),
	);
	$MENU['modules']['naplo']['sub']['intezmeny'] = array(
	    'valtas' => array(array('txt' => 'Change institution')),
//	    'osztaly' => array(array('txt' => 'Classes')),
	    'diak' => array(array('txt' => 'Students')),
//	    'tanar' => array(array('txt' => 'Teachers')),
//	    'munkakozosseg' => array(array('txt' => 'Associations')),
//	    'tankor' => array(array('txt' => 'Studygroups')),
//	    'tankorTanar' => array(array('txt' => 'Stygroup teachers')),
//	    'tankorDiak' => array(array('txt' => 'Studygroup students')),
//	    'diakTankor' => array(array('txt' => 'Student\'s studygroups')),
//	    'tankorSzemeszter' => array(array('txt' => 'Studygroup\' timetable')),
	);
    }
    if (__VEZETOSEG===true) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Progress register', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Grades', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Register of absencies', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Entries', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['tanev'][] = array('txt' => 'Schoolyear details', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv');
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Requests', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Studygroups', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak'));

/*
//	$MENU['modules']['naplo']['intezmeny'] = array(
	    array('txt' => 'Students in studygroup', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak'),
	    array('txt' => 'Stygroup teachers', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorTanar'),
	    array('txt' => 'Change schoolyear', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'),
	    array('txt' => 'Student\'s studygroups', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankor'),
	    array('txt' => 'Application', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankorJelentkezes'),
	);
*/
	$MENU['modules']['naplo']['sub']['intezmeny'] = array (
	    'tankorDiak' => array(array('txt' => 'Students in studygroup', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorDiak')),
	    'tankorTanar' => array(array('txt' => 'Stygroup teachers', 'url' => 'index.php?page=naplo&sub=intezmeny&f=tankorTanar')),
	    'diakTankor' => array(array('txt' => 'Student\'s studygroups', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankor')),
	    'diakTankorJelentkezes' => array(array('txt' => 'Application', 'url'=> 'index.php?page=naplo&sub=intezmeny&f=diakTankorJelentkezes')),
	    'valtas' => array(array('txt' => 'Change schoolyear', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas')),
	    'tankorCsoport' => array(array('txt' => 'Studygroups*', 'url' => 'index.php?page=naplo&sub=tanev&f=tankorCsoport')),
	    'tankorBlokk' => array(array('txt' => 'Studygroup blocks*', 'url' => 'index.php?page=naplo&sub=tanev&f=tankorBlokk')),

	);


	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'New request')),
	);
	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'helyettesites' => array(array('txt' => 'Substitutions')),
	    'pluszora' => array(array('txt' => 'Extra lesson')),
	    'osszevonas' => array(array('txt' => 'Merging')),
	    'specialis' => array(array('txt' => 'Special day')),
	    'elmaradas' => array(array('txt' => 'Progress delays')),
	    'stat' => array(array('txt' => 'Progress statistics')),
	    'elszamolas' => array(array('txt' => 'Accounting')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Studygroup\'s grades')),
	    'diak' => array(array('txt' => 'Student\'s grades')),
	    'dolgozat' => array(array('txt' => 'Tests')),
	    'bizonyitvany' => array(array('txt' => 'Report')),
	    'stat' => array(array('txt' => 'Statistics')),
	    'targySorrend' => array(array('txt' => 'Order of subjects')),
	);
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => 'List of entries')),
	    'ujBejegyzes' => array(array('txt' => 'New entry')),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'orarend' => array(array('txt' => 'Timetable')),
	    'szabadTerem' => array(array('txt' => 'Unused classrooms')),
	    'munkaterv' => array(array('txt' => 'Yearly workplan')),
	    'tankorCsoport' => array(array('txt' => 'Studygroups arrays (...)')),
	    'tankorBlokk' => array(array('txt' => 'Studygroup blocks')),
	    'fogadoOra' => array(array('txt' => 'PTA')),
	    'tanarOrarend' => array(array('txt' => 'Summarized teacher\'s timetable')),
	);
	$MENU['modules']['naplo']['sub']['hianyzas'] = array(
	    'osztaly' => array(array('txt' => 'Class\' absency summarizer')),
	    'diak' => array(array('txt' => 'Student\'s absencies (calendar view)')),
	);

    }
    if (__NAPLOADMIN) {
	$MENU['modules']['naplo']['haladasi'] = array(array('txt' => 'Progress register', 'url' => 'index.php?page=naplo&sub=haladasi&f=haladasi'));
	$MENU['modules']['naplo']['osztalyozo'] = array(array('txt' => 'Grades', 'url' => 'index.php?page=naplo&sub=osztalyozo&f=tankor'));
	$MENU['modules']['naplo']['hianyzas'] = array(array('txt' => 'Register of absencies', 'url' => 'index.php?page=naplo&sub=hianyzas&f=osztaly'));
	$MENU['modules']['naplo']['bejegyzesek'] =  array(array('txt' => 'Entries', 'url' => 'index.php?page=naplo&sub=bejegyzesek&f=bejegyzesek'));
	$MENU['modules']['naplo']['tanev'] = array(array('txt' => 'Timetable', 'url' => 'index.php?page=naplo&sub=tanev&f=orarend'));
	$MENU['modules']['naplo']['tanev'][] = array('txt' => 'Schoolyear details', 'url' => 'index.php?page=naplo&sub=tanev&f=munkaterv');
	$MENU['modules']['naplo']['intezmeny'] = array(array('txt' => 'Institution details', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas'));
	$MENU['modules']['naplo']['admin'] = array(array('txt' => 'Admin', 'url' => 'index.php?page=naplo&sub=admin&f=import'));
	$MENU['modules']['naplo']['hibabejelento'] =  array(array('txt' => 'Requests', 'url' => 'index.php?page=naplo&sub=hibabejelento&f=admin'));
	if(__UZENO_INSTALLED===true)
	    $MENU['modules']['naplo']['uzeno'] =  array(array('txt' => 'Messenger', 'url' => 'index.php?page=naplo&sub=uzeno&f=uzeno'));

	$MENU['modules']['naplo']['sub']['haladasi'] = array(
	    'helyettesites' => array(array('txt' => 'Substitutions')),
	    'pluszora' => array(array('txt' => 'Extra lesson')),
	    'osszevonas' => array(array('txt' => 'Merging')),
	    'specialis' => array(array('txt' => 'Special day')),
	    'elmaradas' => array(array('txt' => 'Progress delays')),
	    'stat' => array(array('txt' => 'Progress statistics')),
	    'elszamolas' => array(array('txt' => 'Accounting')),
	);
	$MENU['modules']['naplo']['sub']['hibabejelento'] = array(
	    'hibabejelento' => array(array('txt' => 'New request')),
	);
	$MENU['modules']['naplo']['sub']['hianyzas'] = array(
	    'osztaly' => array(array('txt' => 'Class\' absency summarizer')),
	    'diak' => array(array('txt' => 'Student\'s absencies (calendar view)')),
	);
	$MENU['modules']['naplo']['sub']['osztalyozo'] = array(
	    'tankor' => array(array('txt' => 'Studygroup\'s grades')),
	    'diak' => array(array('txt' => 'Student\'s grades')),
	    'dolgozat' => array(array('txt' => 'Tests')),
	    'bizonyitvany' => array(array('txt' => 'Report')),
	    'stat' => array(array('txt' => 'Statistics')),
	    'targySorrend' => array(array('txt' => 'Order of subjects')),
	);
	$MENU['modules']['naplo']['sub']['bejegyzesek'] = array(
	    'bejegyzesek' => array(array('txt' => 'List of entries')),
	    'ujBejegyzes' => array(array('txt' => 'New entry')),
	);
	$MENU['modules']['naplo']['sub']['tanev'] = array(
	    'orarend' => array(array('txt' => 'Timetable')),
	    'szabadTerem' => array(array('txt' => 'Unused classrooms')),
	    'helyettesites' => array(array('txt' => 'Substitutions')),
	    'munkaterv' => array(array('txt' => 'Yearly workplan')),
	    'tankorCsoport' => array(array('txt' => 'Studygroups arrays (...)')),
	    'tankorBlokk' => array(array('txt' => 'Studygroup blocks')),
	    'orarendTankor' => array(array('txt' => 'Timetable-studygroup organizer')),
	    'orarendUtkozes' => array(array('txt' => 'Timetable collision')),
	    'orarendLoad' => array(array('txt' => 'Timetable loader')),
	    'fogadoOra' => array(array('txt' => 'PTA')),
	    'tanarOrarend' => array(array('txt' => 'Summarized teacher\'s timetable')),
	    // 'intezmeny' => array(array('txt' => 'Institution chooser', 'url' => 'index.php?page=naplo&sub=intezmeny&f=valtas')),
	);
	$MENU['modules']['naplo']['sub']['intezmeny'] = array(
	    'valtas' => array(array('txt' => 'Change institution')),
	    'osztaly' => array(array('txt' => 'Classes')),
	    'diak' => array(array('txt' => 'Students')),
	    'tanar' => array(array('txt' => 'Teachers')),
	    'munkakozosseg' => array(array('txt' => 'Associations')),
	    'tankor' => array(array('txt' => 'Studygroups')),
	    'tankorTanar' => array(array('txt' => 'Stygroup teachers')),
	    'tankorDiak' => array(array('txt' => 'Studygroup students')),
	    'diakTankor' => array(array('txt' => 'Student\'s studygroups')),
	    'diakTankorJelentkezes' => array(array('txt' => 'Studygroup application')),
	    'tankorSzemeszter' => array(array('txt' => 'Studygroup\' timetable')),
	);
	$MENU['modules']['naplo']['sub']['admin'] = array(
		'intezmenyek' => array(array('txt' => 'Institutions')),
		'tanevek' => array(array('txt' => 'Schoolyears')),
		'szemeszterek' => array(array('txt' => 'Terms')),
		'import' => array(array('txt' => 'Import')),
		'azonositok' => array(array('txt' => 'Generate ID')),
	);
    }

?>
