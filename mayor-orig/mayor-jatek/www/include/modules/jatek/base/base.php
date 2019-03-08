<?php

    function egyszamszavaz($het,$szam) {

	if (!is_numeric($szam) || $szam<=0 || $szam>=200) return false;
	if (!is_numeric($het) || $het==0) $het = getEgyszamHet();

	/* a kulcs ellenőrzés miatt nem szavazhat 2* */
	$q = "INSERT INTO egyszam (ev,het,szam,userAccount) VALUES (year(now()),$het,$szam,'"._USERACCOUNT."')";
	
	_my_query($q,array('db'=>'jatek'));

    }

    function getEgyszamHet() {
	return date('W');
    }

    function egyszamSzavazott($het='') { //:bool

	if (!is_numeric($het) || $het==0) $het = getEgyszamHet();

	/* a kulcs ellenőrzés miatt nem szavazhat 2* */
	$q = "SELECT count(*) AS db FROM egyszam WHERE ev=year(now()) AND het=$het AND userAccount='"._USERACCOUNT."'";

	$db = _my_value_query($q,array('db'=>'jatek'));

	return ($db==1)?true:false;

    }

    function getEgyszamEredmeny($het) {

	if (!is_numeric($het) || $het==0) $het = getEgyszamHet()-1; // évváltás!
	if ($het==0) {
	    //kérdezd le a year-1 max(het)
	    $q = "SELECT max(het) FROM egyszam WHERE ev = year(now())-1";
	    $het = _my_value_query($q,array('db'=>'jatek'));
	}
	
	/* a kulcs ellenőrzés miatt nem szavazhat 2* */
	$q = "SELECT count(*) AS db,szam FROM egyszam WHERE ev=year(now()) AND het=$het  GROUP BY szam ORDER BY szam";
	return _my_assoc_query($q,'szam',array('db'=>'jatek'));

    }

    function getEgyszamNyertes($het) {

	if (!is_numeric($het) || $het==0) $het = getEgyszamHet(); // évváltás!
	
	/* a kulcs ellenőrzés miatt nem szavazhat 2* */
	$q = "SELECT szam,count(*) AS db,userAccount FROM egyszam WHERE ev=year(now()) AND het=$het GROUP BY szam HAVING db=1 ORDER BY szam LIMIT 1";
	$RET = _my_query($q,array('db'=>'jatek'));
	return $RET[0];

    }

?>
