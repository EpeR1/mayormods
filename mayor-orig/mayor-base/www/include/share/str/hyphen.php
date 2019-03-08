<?php

class huHyphen
{
    // member declaration
    public $Patterns = array();
    private $patternFilePath = '../print/share/huhyphn.tex';

    // method declaration
    function __construct() {

	$patterns = 0;
	$mbConv = array(		
	    mb_convert_encoding('ś', 'ISO-8859-2', 'UTF-8') => mb_convert_encoding('ű', 'ISO-8859-2', 'UTF-8'),
	    mb_convert_encoding('Ž', 'ISO-8859-2', 'UTF-8') => mb_convert_encoding('ő', 'ISO-8859-2', 'UTF-8'));

	if (file_exists($this->patternFilePath)) {
	    $fp = fopen($this->patternFilePath, 'r');
	    while ($aLine = fgets($fp, 128)) {
		// deprecated // if (!ereg('[\\%{}]', $aLine)) {
		if (!preg_match('#[\\%{}]#', $aLine)) {
		    // Cork --> ISO-8859-2 kódolás
		    $aLine = strtr($aLine, $mbConv);
		    $letter = false;
		    $key = $value = '';
		    $aLine = chop($aLine);
		    for ($i = 0; $i < strlen($aLine); $i++) {
			// deprecated // if (ereg('[0-9]',$aLine[$i])) {
			if (preg_match('#[0-9]#',$aLine[$i])) {
			    $value .= $aLine[$i];
			    $letter = false;
			} else {
			    if ($letter) $value .= '0';
			    $key .= $aLine[$i];
			    $letter = true;
			} 
		    }
		    if ($letter) $value .= '0';
		    if (!is_array($Patterns[strlen($key)])) $Patterns[strlen($key)] = array();
		    $Patterns[strlen($key)][$key] = $value;
		    $patterns++;
		} else {
		    //if (ereg('\\message\{(.*)\}', $aLine, $reg)) echo $reg[1]."\n";
		}
	    }
	} else { $GLOBALS['alert'][] = 'message:file_not_found:'.($this->patternFilePath); }
	for ($i = 0; $i < count($Patterns); $i++) {
	    if (!is_array($Patterns[$i])) $Patterns[$i] = array();
	}
	$this->Patterns = $Patterns;
    }
    function getPattern($key, $value) {
	$pattern = '';
	if (strlen($value) > strlen($key)) { $pattern = $value[0]; $value = substr($value, 1); }
	for ($i = 0; $i < strlen($key); $i++) {
	    $pattern .= $key[$i];
	    if (isset($value[$i])) $pattern .= $value[$i];
	}
	$pattern = str_replace('0','',$pattern);
	return $pattern;
    }
    public function hyphen($word) {
	$word = mb_convert_encoding($word, 'ISO-8859-2', 'UTF-8');
	if (strlen($word) > 1) {

	    $key = '.'.$word.'.';
	    $value = str_repeat('0', strlen($key));
	    $key = strtr($key, mb_convert_encoding('A-ZÁÄÉÍÓÖŐÚÜŰ', 'ISO-8859-2', 'UTF-8'), mb_convert_encoding('a-záäéíóöőúüű', 'ISO-8859-2', 'UTF-8'));
    	    $pattern = '';
	    for ($i = 1; $i <= strlen($key); $i++) {
		for ($j = 0; $j <= strlen($key)-$i; $j++) {
		    if (is_array($this->Patterns[$i]) && ($pattern = $this->Patterns[$i][ substr($key, $j, $i) ])) {
        		for ($k = 0; $k < strlen($pattern); $k++) {
			    if ($value[$j + $i - strlen($pattern) + $k] < $pattern[$k])
                		$value[$j + $i - strlen($pattern) + $k] = $pattern[$k];
        		}
		    }
		}
	    }
	    $hyphenated = '';
	    for ($i = 0; $i <= strlen($word)-2; $i++) {
		$hyphenated .= $word[$i];
		// deprecated // if (ereg('[13579]', $value[$i+1])) $hyphenated .= '-';
		if (preg_match('#[13579]#', $value[$i+1])) $hyphenated .= '-';
	    }
	    $hyphenated .= substr($word, -1);
	} else {
	    $hyphenated = $word;
	}
	return mb_convert_encoding($hyphenated, 'UTF-8', 'ISO-8859-2');
    }
}

$huHyphen = new huHyphen();
//while gets
//  break if $_=="\n"
//  test.hyphen($_.strip)
//end

//$test->hyphen(trim('állomásfőnök'));
//$test->hyphen(trim('álamigazgatás'));
//$test->hyphen(trim('informatika alapismeretek, programozás'));

?>
