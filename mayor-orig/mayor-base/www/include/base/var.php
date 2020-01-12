<?php
    /*
	Példa: 
	$szuletesiEv = readVariable($_POST['sze'], 'numeric unsigned', null, array(), '1900<$return && $return<2100');
    */
    function readVariable( $IN, $type = '', $default = null, $allowOnly = array(), $condition = null ) {

	if (is_array($IN)) {
	    $return = array();
	    for ($i = 0; $i < count($IN); $i++) {
		$_var = readVariable($IN[$i], $type, $default, $allowOnly, $condition);
		if (isset($_var)) $return[] = $_var;
	    }
	} else {
	  switch ($type) {
	    case 'numeric':
		$return = (isset($IN) && is_numeric($IN)) ? intval($IN) : $default;
		break;
	    case 'id':
	    case 'numeric unsigned':
		$return = (isset($IN) && is_numeric($IN) && $IN >= 0) ? intval($IN) : $default;
		break;
	    case 'float':
		$return = (isset($IN) && is_numeric($IN)) ? floatval($IN) : $default;
		break;
	    case 'float unsigned':
		$return = (isset($IN) && is_numeric($IN) && $IN >= 0) ? floatval($IN) : $default;
		break;
	    case 'datetime':
                $return = (isset($IN) && $IN != '' && strtotime($IN) !== false && strtotime($IN) > 0) ? $IN : $default;
                break;
	    case 'date':
                $return = (isset($IN) && $IN != '' && strtotime($IN) !== false && strtotime($IN) > 0) ? date('Y-m-d',strtotime($IN)) : $default;
                break;		
	    case 'enum':
	    case 'emptystringnull':
	    case 'string':
		if(get_magic_quotes_gpc()) $IN = stripslashes($IN); // -- DEPRECATED (mindig false)
		$return = (isset($IN) && $IN != '') ? $IN : $default;
		break;
    	    case 'notempty':
		$return = (isset($IN) && $IN != '') ? true : false;
		break;
	    case 'bool':
		// Ha nincs beállítva, akkor mindenképp true lenne így: 
		// $return = (isset($IN) && ($IN === 'false' || !$IN)) ? false : true;
		$return = (isset($IN)) ? (($IN === 'false' || !$IN)? false : true) : $default;
		break;
	    case 'strictstring':
		$return = (isset($IN)) ? preg_replace("/[^a-zA-Z0-9_\-]/i",'',$IN) : $default;
		break;
	    case 'html':
		$return = (isset($IN)) ? preg_replace("/[^a-zA-Z0-9\ \.\,?_|:;űáäéúőóüöíŰÁÄÉÚŐÓÜÖÍ\-]/i",'',$IN) : $default;
		break;
	    case 'hexa':
		$return = preg_replace("/[^0-9a-fA-F]/i",'',$IN);
		break;
	    case 'number':
		$return = preg_replace("/[^0-9]/",'',$IN);
		break;
	    case 'sql':
		$return = db_escape_string($IN);
		if ($return === false) $return = $default;
		break;
	    case 'path':
		$return = ( preg_match('#^([a-z]|[A-Z]|[0-9])([0-9]|[a-z]|[A-Z]|/|_|-)*$#', $IN ) != false ) ? $IN : $default;
		break;
	    case 'regexp':
		$return = ( preg_match( "#$allowOnly[0]#", $IN ) == 1 ) ? $IN : $default;
		break;
	    case 'regreplace':
		$return = preg_replace( '#'.$allowOnly[0].'#i', '', $IN );
		break;
	    case 'mail':
	    case 'email':
		$return = filter_var($IN, FILTER_VALIDATE_EMAIL);
		break;
	    case 'userAccount':
		$return = (preg_match("#([a-z]|[A-Z]|[0-9]| |\.|,|_|[űáäéúőóüöíŰÁÄÉÚŐÓÜÖÍäÄ]|-|@)*$#", $IN) != false) ? $IN : $default;
		break;
	    default:
		$return = $IN;
		$_SESSION['alert'][] = 'message:unknown_type:'.$type.':readVariable';
		break;
	  }
	  if ($return !== $default && !in_array($type,array('regexp','regreplace')) && count($allowOnly) > 0) $return = (in_array($return, $allowOnly)) ? $return : $default;
	  if (isset($condition) && $return != $default) $return = (eval("return $condition;")) ? $return : $default;
	}

	return $return;
    }

    function in_date_interval($dt,$tolDt,$igDt) {
	$accept = false;
	if ($dt == date('Y-m-d', strtotime($tolDt))) // ha a kezdőnapra esik
	    $accept = true;
	if ($dt == date('Y-m-d', strtotime($igDt))) // ha a végnapra esik
	    $accept = true;
	return (
	    $accept 
	    || (
		(is_null($tolDt) || strtotime($tolDt)<=strtotime($dt))
		&& (is_null($igDt) || strtotime($dt)<=strtotime($igDt))
	    )
	);
    }

    // FIGYELEM! Csak megbízható belső kódban használjuk
    function reindex($THIS, $HOW) {
        // ha nincs megadva, hogy hogy, adjuk vissza az eredeti tömböt
        if (!is_array($HOW) || count($HOW)==0) return $THIS;

        $RET = array();
        for ($i=0; $i<count($THIS); $i++) {
            $val = '$RET';
            foreach ( $HOW as $key ) {
                $val .= '[$THIS[$i]["'.$key.'"]]';
            }
            $val .='[]=$THIS[$i];';
            $fail = @eval($val); // not used
        }
        return $RET;
    }

    function dump() {

	$ARGS=func_get_args();
	echo '<pre>';
	call_user_func_array('var_dump', $ARGS);
	echo '<hr /></pre>';

    }

?>
