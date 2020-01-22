<?php

    require_once('include/modules/naplo/share/szemeszter.php');

    // -- 2009-2010
    /* 
	A zaroJegy tábla mezői:
	zaroJegyId diakId targyId evfolyam felev jegy jegyTipus megjegyzes modositasDt hivatalosDt

	@param: $jegyek[index] = assoc array, melyben a módosuló jegy adatai szerepelnek
			zaroJegyId - ha módosítani vagy törölni kell egy jegyet
			diakId targyId evfolyam felev jegy jegyTipus megjegyzes
			evfolyamJel (opcionális, de ez a helyes irány)
			hivatalosDt | tanev, szemeszter - ekkor a szemszter zarasDt-je lesz a hivatalosDt
			- a modositasDt mindig az aktualis dátum - nem paraméter
			delete - töröljük a megadott jegyet

	!! MÓDOSÍTANDÓ, ÁTÍRÁS MÉG NEM TÖRTÉNT%! !!
    */
    function zaroJegyBeiras($jegyek,$olr='') {

	/* NOTE!
	    A függvénynek jó lenne foglalkoznia azzal is, hogy beírható-e ez a jegy már, vagy ez ugyanaz-e?
	    Honnan lehet ezt eldönteni vajon? Egyáltalán el lehet-e az új táblastrukturában?
	*/

        // jogosultságok ellenőrzése a hívó függvény feladata!
	// a megfelelő bemenő paraméterek előállítása a hívó függvény feladata

	if ($olr!='') $lr = $olr; else $lr = db_connect('naplo_intezmeny');

        for ($i = 0; $i < count($jegyek); $i++) {
            $J = $jegyek[$i];
	    if ($J['zaroJegyId']!='' || $J['jegy']!='') { // vagy zaroJegyId vagy jegy érték legyen legalább!!!
/** Ez volt ....
		// Ha csak dátum van, akkor a dátum előtt kezdődő utolsó szemeszterhez rendeljük a jegyet
		if ($J['dt'] != '' && $J['tanev'] == '') {
		    $tmp = _generateTanevSzemeszter($J['dt']);
		    if (!is_array($tmp)) {
			$_SESSION['alert'][] = 'warning:wrong_data:A szemeszter meghatározás nem sikerült.:zaroJegyBeiras';
			unset($J['tanev']); unset($J['szemeszter']);
		    } else {
			$J['tanev'] = $tmp['tanev'];
			$J['szemeszter'] = $tmp['szemeszter'];
		    }
		}
**/
		// Ha tanev, szemeszter van csak megadva, akkor a szemeszter zarasDt a hivatalosDt
		if ($J['hivatalosDt'] == '') {
		    if ($J['tanev'] != '' && $J['szemeszter'] != '') {
			$TA = getTanevAdat($J['tanev']);
			$J['hivatalosDt'] = $TA['szemeszter'][$J['szemeszter']]['zarasDt'];
		    } else {
			$_SESSION['alert'][] = 'message:empty_fields:zaroJegyBeiras:hivatalosDt,tanev,szemeszter';
			unset($J);
			continue;
		    }
		}
		$szA = getSzemeszterByDt($J['hivatalosDt'], -1);
		// Ha nincs megadva évfolyam, akkor kitaláljuk - ha lehet
//		if ($J['evfolyam']=='') {
//		    // miért nem a hivatalosDt-t nézzük?
//		    if ($J['tanev'] == '') $J['tanev'] = $szA['tanev'];
//		    if ($J['szemeszter'] == '') $J['szemeszter'] = $szA['szemeszter'];
//		    $J['evfolyam'] = _generateEvfolyam($J['diakId'], $J['tanev'], $J['szemeszter']);
//		    if ($J['evfolyam']===false) {
//			$_SESSION['alert'][] = 'message:wrong_data:Ez a zárójegy nem rögzíthető!!!:zaroJegyBeiras';
//			unset($J);
//		    }
//		}
		// Ha nincs megadva evfolyamJel, akkor kitaláljuk - ha lehet
		if ($J['evfolyamJel'] == '' || $J['evfolyam'] == '') {
		    // A hivatalosDt alapján
		    $evfAdat = getEvfolyamAdatByDiakId($J['diakId'], $J['hivatalosDt'], $J['tanev'], $csakHaEgyertelmu = true);
		    if (is_array($evfAdat)) {
			$J['evfolyam'] = $evfAdat['evfolyam'];
			$J['evfolyamJel'] = $evfAdat['evfolyamJel'];
		    }
		}
		// Ha nincs megadva felev, akkor a hivatalosDt szemesztere
		if ($J['felev']=='') $J['felev'] = $szA['szemeszter'];
		
        	$v = array($J['hivatalosDt'],$J['diakId'],$J['targyId'],$J['evfolyam'],$J['evfolyamJel'],$J['felev'],$J['jegy'],$J['jegyTipus'],$J['megjegyzes']);
        	$q = '';

                    if ($J['zaroJegyId']!='') { // megadott zaroJegyId, módosítsuk a bejegyzést

                        if ($J['delete'] == 'true') { // string, nem logikai! - törölhetjük
                            $q = "DELETE FROM zaroJegy WHERE zaroJegyId=".intval($J['zaroJegyId']);
                            $v = null;
                        } elseif ($J['jegy']!='') { // ha van jegy megadva
			    // NEM REPLACE, UPDATE, különben a megszorítások miatt cascade törlésre kerülnek a vizsgák!!! HIBA!
			    if ($J['megjegyzes'] != 'dicséret' && $J['megjegyzes'] != 'figyelmeztető') {
				$q = "UPDATE zaroJegy SET modositasDt=NOW(),hivatalosDt='%s',diakId=%u,targyId=%u,evfolyam=%u,evfolyamJel='%s',felev=%u,
				    jegy='%s',jegyTipus='%s',megjegyzes='%s' 
				  WHERE zaroJegyId=%u AND (jegy!='%s' OR jegyTipus!='%s' OR megjegyzes!='%s')";
			    } else {
				$q = "UPDATE zaroJegy SET modositasDt=NOW(),hivatalosDt='%s',diakId=%u,targyId=%u,evfolyam=%u,evfolyamJel='%s',felev=%u,
				    jegy='%s',jegyTipus='%s',megjegyzes='%s' 
				  WHERE zaroJegyId=%u AND (jegy!='%s' OR jegyTipus!='%s' OR megjegyzes!='%s' OR megjegyzes IS NULL)";
			    }
                            $v[] = $J['zaroJegyId'];
                            $v[] = $J['jegy'];
                            $v[] = $J['jegyTipus'];
                            $v[] = $J['megjegyzes'];
                        } // különben nem írjuk be

                    } elseif ($J['jegy']!='') { // nincs megadva zaroJegyId, ámbár probléma lehet, hátha van ilyen jegye mégis (konkurrens kliensek)
			// ugyanakkor az index létrehozás nem biztos hogy nyomravezető. Megoldás, ha a több bejegyzés megjelenik
			if ($J['megjegyzes'] != 'dicséret' && $J['megjegyzes'] != 'figyelmeztető') {
                    	    $q = "INSERT INTO zaroJegy (modositasDt,hivatalosDt,diakId,targyId,evfolyam,evfolyamJel,felev,jegy,jegyTipus,megjegyzes)
                              VALUES (NOW(),'%s',%u,%u,%u,'%s',%u,'%s','%s',NULL)";
			} else {
                    	    $q = "INSERT INTO zaroJegy (modositasDt,hivatalosDt,diakId,targyId,evfolyam,evfolyamJel,felev,jegy,jegyTipus,megjegyzes)
                              VALUES (NOW(),'%s',%u,%u,%u,'%s',%u,'%s','%s','%s')";
			}
                    }
		$results[] = db_query($q, array('modul' => 'naplo_intezmeny','values' => $v, 'fv' => 'zaroJegyBeiras', 'result' => 'insert'), $lr);
	    }
        }

	if ($olr=='') db_close($lr); //+++ MISSING hibakezelés, tranzakciókezelés???
	if (is_array($results) && count($results) == 1) return $results[0]; // vizsga oldal használja lastInsertId miatt
	else return false;

    }

    // Tanev/Szemeszter zárónapjával meghatározva
    function generateDiakEvfolyamJel($diakId,$tanev,$szemeszter) { return _generateEvfolyamJel($diakId,$tanev,$szemeszter); }

    function _generateEvfolyamJel($diakId,$tanev,$szemeszter) { // returns INTEGER || FALSE --> STRING || FALSE
	
	/*
	    milyen evfolyamos?
	    diakId-->(tanev/szemeszter.zaroDt)osztaly-->evfolyamJel

	    + kiegészítés: diák osztályai és tankör osztályainak metszete 1 osztály kell hogy legyen.
	    - getTankorOsztalyai($tankorId, $SET = array('result' => 'id'), $olr='');
	    - ezt sajnos nem tudujk itt meghívni!
	*/
	// getSzemeszterAdat
	$q = "SELECT zarasDt FROM szemeszter WHERE tanev=%u AND szemeszter=%u";
	$v = array($tanev,$szemeszter);
	$zarasDt = db_query($q, array('fv'=>'inner_generateEvfolyam','modul'=>'naplo_intezmeny','result'=>'value', 'values'=>$v));
	// melyik osztályba járt?
	$OSZTALYOK = getDiakOsztalya($diakId,array('tanev'=>$tanev,'tolDt'=>$zarasDt,'igDt'=>$zarasDt));
	if (count($OSZTALYOK)===1) { // ha több osztályba is jár, de azok évfolyamjele azonos, akkor nem kellene hibával kilépni...
	    $_osztalyId = $OSZTALYOK[0]['osztalyId'];
	    $OA = getOsztalyAdat($_osztalyId);	    
	    return $OA['evfolyamJel'];
	} else {
	    $_SESSION['alert'][] = '::nem tudom kitalálni az évfolyamot (db osztály: '.count($OSZTALYOK).", diakId: $diakId, tanev: $tanev, szemeszter: $szemeszter)";
	    return false;
	}

    }

    function _generateTanevSzemeszter($dt) {


	$q = "SELECT tanev,szemeszter FROM szemeszter WHERE kezdesDt < '%s' ORDER BY tanev DESC, szemeszter DESC LIMIT 1";
	$v = array($dt);
	return db_query($q, array('fv' => '_generateTanevSzemeszter', 'modul' => 'naplo_intezmeny', 'result' => 'record', 'values' => $v));

    }
?>
