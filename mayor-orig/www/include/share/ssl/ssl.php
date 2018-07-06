<?php
/*

    Küldés:

    Fogadás:
	skin=rpc
	    rights.php
		require_once ssl.php
		$_POST[senderNodeId] beolvasás
		$RPC construct...
		setRemoteNodeId
		processRequest (sessionKey, request)
	    *-pre.php
		getIncomingRequest ($request['func'])
		prepareReply
		    _encodeRequest
		setMyResponse
	    skin-rpc/base.phtml
		global $RPC
		echo $RPC->getResponse()

    Küldés:
	    __construct
		getSSLKeyPair
		_genSessionKey
	    setRemoteNodeId
		_setRemotePublicKey
		    getSslPublicKeyByNodeId
	    setRemoteHost (HOST, publicKey)
		_setRemotePublicKey
	    setRequestTarget (PSF)
	    sendRequest
		_prepareRequest
		_encodeRequest($ADAT)
		    AES::encrypt !! json, base64 - nincs AES :(
		    _sessionKeyEncode
		_curlGet --> response
		_decodeRequest !! jelenleg csak json...
		_sessionKeyDecode
		json_decode - AES::decrypt
*/

    function getSslKeyPair() {
	$q = "SELECT * FROM mayorSsl";
	$r = db_query($q, array('fv'=>'getSslKeyPair','modul'=>'login','result'=>'record'));
	if ($r=='') {
		$SSLKeyPair = generateSSLKeyPair();
		$secret = sha1(mt_rand(100000000000000,999999999999999));
		$q = "INSERT INTO mayorSsl (privateKey,publicKey,secret) VALUES ('%s','%s','%s')";
		$values = array($SSLKeyPair['privateKey'],$SSLKeyPair['publicKey'],$secret);
		$r = db_query($q, array('fv'=>'getSslKeyPair','modul'=>'login', 'values'=>$values));
		return $SSLKeyPair;
	} else {
	    return $r;
	}
    }

    function generateSSLKeyPair() {
        $SSL_KEY_PAIR=openssl_pkey_new();
        // Get private key
        openssl_pkey_export($SSL_KEY_PAIR, $privatekey);
        // Get public key
        $publickey=openssl_pkey_get_details($SSL_KEY_PAIR);                                                                                                                                                
        $publickey=$publickey["key"];                                                                                                                                                                                               
	return array('fv'=>'geneateSslKeyPair','privateKey'=>$privatekey,'publicKey'=>$publickey);                        
    }
    function setNodeId($nodeId, $publicKey) {
	$q = "UPDATE mayorSsl SET nodeId=%u where publicKey='%s'";
	$v = array($nodeId, $publicKey);
	return db_query($q, array('debug'=>false,'fv'=>'setNodeId','modul'=>'login', 'values'=>$v));
    }

    function getSslPublicKey() {
	$SSLKeyPair = getSslKeyPair();
	return $SSLKeyPair['publicKey'];
    }

    function getSslPublicKeyByNodeId($nodeId) {
	if (is_numeric($nodeId)) {
	    $q = "SELECT publicKey FROM mayorKeychain WHERE valid=1 AND nodeId='%u'";
	    $values = array($nodeId);
	    $result = db_query($q, array('debug'=>false,'fv'=>'getSslPublicKeyByNodeId','modul'=>'login', 'values'=>$values,'result'=>'value'));
	    if ($result=='') return false;
	    else return $result;
	} else {
	    return false;
	}
    }

    function getPublicDataFromLocalKeychain($nodeId) {
	if (is_numeric($nodeId)) {
	    // lekérdezés a helyi adatbázisból
	    $q = "SELECT * FROM mayorKeychain WHERE valid=1 AND nodeId='%u'";
	    $values = array($nodeId);
	    $result = db_query($q, array('debug'=>false,'fv'=>'getPublicDataFromLocalKeychain','modul'=>'login', 'values'=>$values,'result'=>'record'));
	    if ($result=='') return false;
	    else return $result;
	} elseif (is_null($nodeId)) {
	    // Az összes eltárolt node adatának lekérdezése
	    $q = "SELECT * FROM mayorKeychain WHERE valid=1 ORDER BY nodeTipus DESC, nev";
	    $result = db_query($q, array('debug'=>false,'fv'=>'getPublicDataFromLocalKeychain','modul'=>'login','result'=>'indexed'));
	    if ($result=='') return false;
	    else return $result;	    
	}
    }

    function addPublicDataToLocalKeychain($DATA) {
	// egy új rekord felvétele...
	$fields = array_keys($DATA);
	$values = array_values($DATA);
	$q = "INSERT INTO mayorKeychain (".implode(',', array_fill(0, count($fields), "%s")).") VALUES (".implode(',', array_fill(0, count($values), "'%s'")).")";
	$v = array_merge($fields, $values);
	$r = db_query($q, array('debug'=>false,'func'=>'addPublicDataToLocalKeychain','modul'=>'login','values'=>$v,'result'=>'insert'));
	return $r;
    }
    function removeNodeFromLocalKeychain($nodeId) {
	$q = "DELETE FROM mayorKeychain WHERE nodeId=%u";
	$v = array($nodeId);
	$r = db_query($q, array('debug'=>false,'func'=>'removeNodeFromLocalKeychain','modul'=>'login','values'=>$v));
	return $r;	
    }
    function sendPublicRequest($data) {
	if (defined('_DEVEL') && _DEVEL===true) $host = 'localhost';
	else $host = 'www.mayor.hu';
	$url = "https://$host/index.php?page=portal&sub=regisztracio&f=regisztracio&skin=ajax";
	$salt_name='MS_'.sha1('portal_regisztracio_regisztracio');
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // a választ feldolgozzuk
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // a helyi gépen nekem ez kellett :(
            curl_setopt($ch, CURLOPT_HEADER, 0);
//          curl_setopt($ch, CURLOPT_TIMEOUT,60);
            curl_setopt($ch, CURLOPT_USERAGENT, "MaYoR-registration (php; cURL)");
            curl_setopt($ch, CURLOPT_VERBOSE, true);
	    // Cookie
	    curl_setopt($ch, CURLOPT_COOKIE, $salt_name.'=portal_regisztracio_regisztracio');
            // POST
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
         
            $response = curl_exec($ch);
            $INFO = curl_getinfo($ch); // ha kell
            if ($INFO['http_code'] == 200) { // minden ok
                //dump($INFO['url']);
                //dump("response:",$response);
            } else {
                echo '<a href="'.$url.'">URL</a>';
                dump($INFO['http_code'],$response,$INFO);
                throw new Exception($INFO['http_code']);
            }
            curl_close($ch);

        return $response;
    }
    
//function base64url_encode($data) { 
//  return rtrim(strtr(base64_encode($data), '+/', '-_'), '='); 
//} 
//function base64url_decode($data) { 
//  return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT)); 
//}
function random_str($length, $keyspace = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ')
{
    $str = '';
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $str .= $keyspace[random_int(0, $max)];
    }
    return $str;
}
    /* symmetric cryptographic module */
    class AES {

	public function __construct() { }
	public function encrypt($data, $key) {
	    $data = urlencode($data);

//	    $return = $data;
//	    $return = $key . $data;
//	    $return = openssl_encrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA);
//	    $return = db_query("SELECT aes_encrypt('%s','%s')",array('fv'=>'class AES','result'=>'value','modul'=>'login','values'=>array($data,$key)));

	    if (function_exists('mcrypt_encrypt')) {
		$return = mcrypt_encrypt(MCRYPT_RIJNDAEL_128,$key,$data,MCRYPT_MODE_CBC,"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
	    } else {
		$_SESSION['alert'][] = 'info:mcrypt függvény nem található (tipp! telepítsd a php5-mcrypt csomagot a szerverre)';
	    }
	    return base64_encode($return);
	}
	public function decrypt($data,$key) {
	    $data = base64_decode($data);

//	    $return = $data;
//	    $return = substr($data,strlen($key));
//	    $return = openssl_decrypt($data, 'aes-256-cbc', $key, OPENSSL_RAW_DATA);
//	    $return = db_query("SELECT aes_decrypt('%s','%s')",array('fv'=>'class AES','result'=>'value','modul'=>'login','values'=>array($data,$key)));
	    if (function_exists('mcrypt_decrypt')) {
		$return = mcrypt_decrypt(MCRYPT_RIJNDAEL_128,$key,$data,MCRYPT_MODE_CBC,"\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0\0");
	    } else {
		$_SESSION['alert'][] = 'info:mcrypt függvény nem található (tipp! telepítsd a php5-mcrypt csomagot a szerverre)';
	    }
	    return trim(urldecode($return));
	}
    }

    class Interconnect {

	/*  A: küldő, B: fogadó használja  */

	private $sessionKey;	// egy kommunikációhoz használt session kulcs
	private $KP;		// a saját kulcspárom
	private $nodeId;
	private $privateKey;
	private $publicKey;
	private $remotePublicKey;
	private $remoteNodeId;
	private $remoteHost;
	private $psf;
	private $myRequest;		// a küldendő kérés (object)
	private $myResponse;		// a küldendő válasz (object)
	private $incomingRequest;	// a beérkező kérés (object)
	private $incomingResponse;	// a beérkező válasz (object)
	private $status;		// a művelet eredményességéek visszajelzése, hibaok kódja...
	private $controllerNodeId = '09862967'; // Ez van jelenleg az adatbázisban...

	/* Konstruktor */
	public function __construct() {
	    $this->sessionKey  = $this->_genSessionKey();
	    $this->KP = getSSLKeyPair();
	    $this->nodeId = $this->KP['nodeId'];
	    $this->privateKey = $this->KP['privateKey'];
	    $this->publicKey = $this->KP['publicKey'];
	    $this->status = 'ok:created';
	    $this->psf = 'page=rpc&f=rpc';
	}
	/* Privát metódusok */
//        private function _yconv($get) {
//            $get = str_replace(' ','+',$get);   // hm. erre miért van szükség??? autokonverzió?
//            $get = str_replace('\/','/',$get);  // hm. erre miért van szükség??? autokonverzió?
//            $get = str_replace('\\','',$get);  // hm. erre miért van szükség??? autokonverzió?\"'
//            return $get;
//        }
	private function _curlGet($data) {
	    $host = $this->remoteHost;
	    $url = $this->remoteHost."/index.php?skin=rpc&".$this->psf;
	    //dump('url',$url);
	    $ch = curl_init();
	    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // a választ feldolgozzuk
	    curl_setopt($ch, CURLOPT_URL, $url);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYSTATUS, false);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
	    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false); // a helyi gépen nekem ez kellett :(
	    curl_setopt($ch, CURLOPT_HEADER, 0); 
//	    curl_setopt($ch, CURLOPT_TIMEOUT,60);
	    curl_setopt($ch, CURLOPT_USERAGENT, "MaYoR-interconnect (php; cURL)");
	    curl_setopt($ch, CURLOPT_VERBOSE, true);
	    // POST
	    $data['senderNodeId'] = $this->nodeId;
	    curl_setopt($ch, CURLOPT_POST, true);
	    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

	    $response = curl_exec($ch);
	    $INFO = curl_getinfo($ch); // ha kell
	    if ($INFO['http_code'] == 200) { // minden ok
		//dump($INFO['url']);
		//dump($response);
	    } else {
		echo '<a href="'.$url.'">URL</a>';
		dump($response,$INFO);
		throw new Exception($INFO['http_code']);
	    }
	    curl_close($ch);
	    return $response;
	}
	private function _genSessionKey() {
	    return base64_encode(pack('N6', mt_rand(), mt_rand(), mt_rand(),mt_rand(), mt_rand(), mt_rand()));
	    //return random_str( 32 );
	}
	private function _sessionKeyEncode() {
	    $crypttext = '';
	    $res = openssl_public_encrypt($this->sessionKey, $crypttext, $this->remotePublicKey); // majd a távoli publikus kulccal
    	    return base64_encode($crypttext);
	}
	private function _sessionKeyDecode($in) {
	    $decodedtext = '';
    	    $res = openssl_private_decrypt(base64_decode($in), $decodedtext, $this->privateKey); // a saját privát kulccsal
    	    return $decodedtext;
	}
	private function _genHash($in) {
	    // hash generálás
	    return hash('sha256', $in, false);
	}
	private function _hashEncode($in) {
	    // a mi privát kulcsunkkal...
    	    $res = openssl_private_encrypt($in, $encodedHash, $this->privateKey); // a saját privát kulccsal
    	    return base64_encode($encodedHash);
	}
	private function _hashDecode($in) {
	    // a remotePublic-kal
	    $res = openssl_public_decrypt(base64_decode($in), $decodedHash, $this->remotePublicKey); // majd a távoli publikus kulccal
    	    return $decodedHash;
	}
	private function _verifyHash($PACKED) {
	    return ($this->_genHash($PACKED['details']))===($this->_hashDecode($PACKED['hashEncoded']));
	}
	private function _packData($DATA) { // --> array(details|sessionKeyEncoded)
	    $ADAT['details'] = AES::encrypt(json_encode($DATA),$this->sessionKey); // implicit base64_encode
	    $ADAT['hashEncoded'] = $this->_hashEncode($this->_genHash($ADAT['details'])); 
	    $ADAT['sessionKeyEncoded'] = $this->_sessionKeyEncode(); // implicit base64_encode
	    $ADAT['status'] = $this->status;
	    return $ADAT;
	}
	private function _unpackData($PACKED) { // packed[details] --> object !! feltesszük, hogy már be van állítva a sessionKey, ellenőrizve van a hash!
	    return json_decode(AES::decrypt($PACKED['details'],$this->sessionKey), true);
	}
	private function _encodeRequest($IN = array()) { // HTTP GET paraméter
	    return urlencode(json_encode($IN)); 
	}
	private function _decodeRequest($IN) { // HTTP GET paraméter
	    return json_decode($IN, true);
	}
	private function _encodeResponse($IN=array()) { // HTTP - tartalom
	    return json_encode($IN);
	}
	private function _decodeResponse($IN=array()) { // HTTP - tartalom
	    return json_decode($IN, true);
	}
	/* Publikus metódusok */
	public function setRequestTarget($target) {
	    if ($target == 'controller') $this->psf='page=rpc&sub=controller&f=rpc';
	    else if ($target == 'naplo') $this->psf='page=rpc&sub=naplo&f=rpc';
	    else $this->psf='page=rpc&f=rpc'; // alap funkciók
	}
	public function getRegistrationDataByNodeId($nodeId) { // feltesszük, hogy valid
	    $origRemoteNodeId = $this->remoteNodeId;
	    $this->setRemoteHostByNodeId($this->controllerNodeId);
	    $ret = $this->sendRequest(array('func'=>'getPublicDataByNodeId', 'nodeId'=>$nodeId));
	    $this->setRemoteHostByNodeId($origRemoteNodeId);

	    return $ret;
	}
	public function getPublicDataByNodeId($nodeId) {
	    if (defined('_DEVEL') && _DEVEL===true) {
		// A helyi gép adatait adjuk meg
		$ret = array('nodeId'=>$this->nodeId, 'url'=>'https://localhost','publicKey'=>$this->publicKey);
	    } else {
		if ($nodeId == '') $nodeId = $this->controllerNodeId;
		// Adott nodeId adatainak lekérdezése a helyi adatbázisból
		$ret = getPublicDataFromLocalKeychain($nodeId);
		if ($ret === false) {
		    $RPC = new Interconnect();
		    $RPC->setRequestTarget('controller');
		    $RPC->setRemoteHostByNodeId($this->controllerNodeId);
		    $ret2 = $RPC->sendRequest(array('func'=>'getPublicDataByNodeId', 'nodeId'=>$nodeId));
		    $ret = $ret2['nodeData'];
		    foreach (array(
			'regId','dij','utemezes','egyebTamogatas','szamlazasiCim','szamlaHelyseg','szamlaIrsz','szamlaKozteruletNev',
			'szamlaKozteruletJelleg','szamlaHazszam'
		    ) as $field) {
			unset($ret[$field]);
		    }
		    if (is_array($ret)) addPublicDataToLocalKeychain($ret);
/*
		} elseif (false) {
		    // Adott nodeId adatainak lekérdezése a www.mayor.hu-tól (controller) Interconnect-tel
		    $origRemoteNodeId = $this->remoteNodeId;
		    $origPsf = $this->psf;
		    $this->setRequestTarget('controller');
		    $this->setRemoteHostByNodeId($this->controllerNodeId);
		    $ret2 = $this->sendRequest(array('func'=>'getPublicDataByNodeId', 'nodeId'=>$nodeId));
		    $ret = $ret2['nodeData'];
		    foreach (array(
			'regId','dij','utemezes','egyebTamogatas','szamlazasiCim','szamlaHelyseg','szamlaIrsz','szamlaKozteruletNev',
			'szamlaKozteruletJelleg','szamlaHazszam'
		    ) as $field) {
			unset($ret[$field]);
		    }
		    if (is_array($ret)) addPublicDataToLocalKeychain($ret);
		    if ($origRemoteNodeId!='') $this->setRemoteHostByNodeId($origRemoteNodeId);		    
		    $this->psf = $origPsf;
*/
		}
	    }
	    return $ret;
    	}
	public function setRemoteHostByNodeId($nodeId) {
	    $rData = $this->getPublicDataByNodeId($nodeId);
	    if (is_array($rData)) {
		$this->remoteHost = $rData['url'];
		$this->remoteNodeId = $rData['nodeId'];
		$this->remotePublicKey = $rData['publicKey'];
		$this->status = 'ok:remoteHost';
	    } else {
		$this->remoteHost = ''; // controller
		$this->remoteNodeId = $this->controllerNodeId;
		$this->remotePublicKey = $rData['publicKey'];
		$this->status = 'ok:remoteHostController';
	    }
	}
	/* A oldal */
	public function sendRequest($ADAT = array()) {
	    $PACKED = $this->_packData($ADAT);
	    $this->myRequest = $PACKED;
	    // $this->myRequest = $this->_encodeRequest($PACKED);

	    $response = $this->_curlGet($this->myRequest);

	    $decodedResponse = $this->_decodeResponse($response);
	    if ($this->sessionKey === $this->_sessionKeyDecode($decodedResponse['sessionKeyEncoded'])) {
		if ($this->_verifyHash($decodedResponse)) {
		    $this->incomingResponse = $this->_unpackData($decodedResponse);
		    $this->status = 'ok:success response';
		} else {
		    $this->incomingResponse = false;
		    $this->status = 'error:wrong response hash';
		}
	    } else {
		$this->status = 'error:wrong response sessionKey ('.($this->sessionKey).' != '.($this->_sessionKeyDecode($decodedResponse['sessionKeyEncoded'])).') response: '.$response;
		$this->incomingResponse = false;
	    }
	    return $this->incomingResponse;
	}
	/* B oldal! */
	public function processRequest() { 	// rights.php
	    $PACKED = $_POST;
	    $this->sessionKey = $this->_sessionKeyDecode($PACKED['sessionKeyEncoded']);
	    if ($this->_verifyHash($PACKED)) {
		$this->incomingRequest = $this->_unpackData($PACKED);
openlog("MaYoR Interconnect", LOG_PID | LOG_PERROR, LOG_LOCAL0);
syslog(LOG_WARNING, "Data unpacked: ".(json_encode($this->incomingRequest))." {$_SERVER['REMOTE_ADDR']} ({$_SERVER['HTTP_USER_AGENT']})");
closelog();
		$this->status = 'ok:success request';
	    } else {
		$this->incomingRequest = false;
		$this->status = 'error:wrong request hash';		
	    }
	    return $this->incomingRequest;
	}
	public function setResponse($DATA) { // ez kell a pre-be
	    $this->myResponse = $this->_encodeResponse($this->_packData($DATA));
	}
	public function sendResponse() { // skin=rpc - csak ki kell írnunk az elküldendő adatsort - ezt a skin csinálja
	    echo $this->myResponse;
	}
	public function getIncomingRequest() { return $this->incomingRequest; } // a pre-ben

	public function getPublicKey() { return $this->publicKey; }
	public function getSessionKey() { return $this->sessionKey; }
	public function getStatus() { return $this->status; }
	public function getRemoteNodeId() { return $this->remoteNodeId; }
	public function getControllerNodeId() { return $this->controllerNodeId; }
	public function getNodeId() { return $this->nodeId; }
    }


?>
