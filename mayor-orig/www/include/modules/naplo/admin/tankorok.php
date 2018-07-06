<?php

	function updateTankor($file, $MEZO_LISTA, $KULCS_MEZOK, $mezo_elvalaszto = '	', $rovatfej = false) {


		if (!file_exists($file)) {
			$_SESSION['alert'][] = 'message:file_not_found:'.$file;
			return false;
		}

		if (!is_array($MEZO_LISTA)) {
			$_SESSION['alert'][] = 'message:wrong_parameter:MEZO_LISTA';
			return false;
		}

		if (!is_array($KULCS_MEZOK)) {
			$_SESSION['alert'][] = 'message:wrong_parameter:KULCS_MEZOK';
			return false;
		}

		// A frissítendő attribútumok listája
		$attrList = array_values(array_filter($MEZO_LISTA));

		$fp = fopen($file,'r');
		if (!$fp) {
			$_SESSION['alert'][] = 'message:file_open_error:'.$file;
			return false;
		}

		$lr = db_connect('naplo_intezmeny', array('fv' => 'updateTankor'));
		if (!$lr) {
			fclose($fp);
			return false;
		}

		// Az első sor kihagyása
		if ($rovatfej) $sor = fgets($fp,1024);

		// TárgyId-k átváltása
		$keyNev = array_search('targyNev',$MEZO_LISTA);
		if (!$keyNev && $keyNev !== 0) $keyNev = false;
		if ($keyNev) {
			$keyId = array_search('targyId',$MEZO_LISTA);
			if (!$keyId && $keyId !== 0) {	// Ha nincs targyId, akkor felvezzük a mező listába
				$keyId = count($MEZO_LISTA);
				$MEZO_LISTA[] = 'targyId';
			}
			$MEZO_LISTA[$keyNev] = '';		// A targyNev nem játszik szerepet többet, csak a targyId
			$targyak = array();				// $targyNev --> $targyId átalakítás tárolása
			$attrList = array_values(array_filter($MEZO_LISTA)); // az attrList újragenerálása (targyNev helyett targyId)
			if (in_array($keyNev,$KULCS_MEZOK)) {	// Ha a targyNev kulcs lenne, akkor legyen helyette a tagyId a kulcs (ha már eleve benne volt a targyId, akkor most kétszer lesz benne - nem baj!)
				$k = array_search($keyNev,$KULCS_MEZOK);
				$KULCS_MEZOK[$k] = $keyId;
			}
		}
		
		while ($sor = fgets($fp,1024)) {

			$adatSor = explode($mezo_elvalaszto,chop($sor));
			$update = $hianyzoTargyId = false;

			// targyId megállapítása a targyNev alapján
			if (
				$keyNev !== false			// Ha van targyNev mező
				&& $adatSor[$keyNev] != ''	// és nem üres
				&& $adatSor[$keyId] == ''	// de nincs megadva targyId
				
			) {
			    if (!isset($targyak[$adatSor[$keyNev]])) { // Ha még nem kérdeztük le a targyId-t
				$q = "SELECT targyId FROM targy WHERE targyNev='%s'";
				$v = array($adatSor[$keyNev]);
				$targyak[$adatSor[$keyNev]] = db_query($q, array(
				    'fv' => 'updateTankor', 'modul' => 'naplo_intezmeny', 'result' => 'value', 'values' => $v
				), $lr);
				if (!$targyak[$adatSor[$keyNev]]) {
				    $_SESSION['alert'][] = 'message:wrong_data:tárgyNév:'.$adatSor[$keyNev].':'.$num.':'.$sor;
				    $hianyzoTargyId = true;
				}
			    }
			    $adatSor[$keyId] = $targyak[$adatSor[$keyNev]];
			}
			// Innentől ha lehetett, akkor a targyNev le lett cserélve targyId-re - minden megy a sima frissítés szerint

			// keresési feltétel összerakása
			$where = '';
			for ($i=0; $i<count($KULCS_MEZOK); $i++) {
				if ($adatSor[$KULCS_MEZOK[$i]] != '') {
					$where .= ' AND '.$MEZO_LISTA[$KULCS_MEZOK[$i]]."='".$adatSor[$KULCS_MEZOK[$i]]."' ";
				}
			}
			if ($where != '') {
				$where = substr($where,5);
				$q = 'SELECT COUNT(*) FROM tankor WHERE '.$where;
				$num = db_query($q, array('fv' => 'updateTankor', 'modul' => 'naplo_intezmeny', 'result' => 'value'), $lr);
				$update = ($num > 0);
			}
			if ($update) {
				$UPDATE = array();
				for ($i = 0; $i < count($MEZO_LISTA); $i++) {
					if (
						$MEZO_LISTA[$i] != ''
			    		and $adatSor[$i] != ''
			    		and !in_array($i,$KULCS_MEZOK)
					) {
						$UPDATE[] = $MEZO_LISTA[$i]."='".$adatSor[$i]."'";
					}
				}
				if (count($UPDATE) > 0) {
					$q = 'UPDATE tankor SET '.implode(',',$UPDATE).' WHERE '.$where;
					$r = db_query($q, array('fv' => 'updateTankor', 'modul' => 'naplo_intezmeny'), $lr);
				}
			} elseif (!$hianyzoTargyId) {
				$value = array();
				for ($i=0; $i<count($MEZO_LISTA); $i++) {
					if ($MEZO_LISTA[$i] != '') $value[] .= $adatSor[$i];
				}
				$VALUES[] = "('".implode("','",$value)."')";
			}

		} // while
		if (count($VALUES) > 0) {
			$q = 'INSERT INTO tankor ('.implode(",",$attrList).') VALUES '.implode(",\n",$VALUES);
			$r = db_query($q, array('fv' => 'updateTankor', 'modul' => 'naplo_intezmeny'), $lr);
		}

		db_close($lr);

		fclose($fp);

	}


?>
