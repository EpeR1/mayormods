<?php

$HTTP_STATUS = array(
    100 => array('txt'=>'Continue', 				'class'=>'Informational'),
    101 => array('txt'=>'Switching Protocols', 			'class'=>'Informational'),
    200 => array('txt'=>'OK', 					'class'=>'Successful'),
    201 => array('txt'=>'Created', 				'class'=>'Successful'),
    202 => array('txt'=>'Accepted', 				'class'=>'Successful'),
    203 => array('txt'=>'Non-Authoritative Information', 	'class'=>'Successful'),
    204 => array('txt'=>'No Content', 				'class'=>'Successful'),
    205 => array('txt'=>'Reset Content', 			'class'=>'Successful'),
    206 => array('txt'=>'Partial Content', 			'class'=>'Successful'),
    300 => array('txt'=>'Multiple Choices', 			'class'=>'Redirection'),
    301 => array('txt'=>'Moved Permanently', 			'class'=>'Redirection'),
    302 => array('txt'=>'Found', 				'class'=>'Redirection'),
    303 => array('txt'=>'See Other', 				'class'=>'Redirection'),
    304 => array('txt'=>'Not Modified', 			'class'=>'Redirection'),
    305 => array('txt'=>'Use Proxy', 				'class'=>'Redirection'),
    306 => array('txt'=>'(Unused)', 				'class'=>'Redirection'),
    307 => array('txt'=>'Temporary Redirect', 			'class'=>'Redirection'),
    400 => array('txt'=>'Bad Request', 				'class'=>'Client Error'),
    401 => array('txt'=>'Unauthorized', 			'class'=>'Client Error'),
    402 => array('txt'=>'Payment Required', 			'class'=>'Client Error'),
    403 => array('txt'=>'Forbidden', 				'class'=>'Client Error'),
    404 => array('txt'=>'Not Found', 				'class'=>'Client Error'),
    405 => array('txt'=>'Method Not Allowed', 			'class'=>'Client Error'),
    406 => array('txt'=>'Not Acceptable', 			'class'=>'Client Error'),
    407 => array('txt'=>'Proxy Authentication Required', 	'class'=>'Client Error'),
    408 => array('txt'=>'Request Timeout', 			'class'=>'Client Error'),
    409 => array('txt'=>'Conflict', 				'class'=>'Client Error'),
    410 => array('txt'=>'Gone', 				'class'=>'Client Error'),
    411 => array('txt'=>'Length Required', 			'class'=>'Client Error'),
    412 => array('txt'=>'Precondition Failed', 			'class'=>'Client Error'),
    413 => array('txt'=>'Request Entity Too Large', 		'class'=>'Client Error'),
    414 => array('txt'=>'Request-URI Too Long', 		'class'=>'Client Error'),
    415 => array('txt'=>'Unsupported Media Type', 		'class'=>'Client Error'),
    416 => array('txt'=>'Requested Range Not Satisfiable', 	'class'=>'Client Error'),
    417 => array('txt'=>'Expectation Failed', 			'class'=>'Client Error'),
    500 => array('txt'=>'Internal Server Error', 		'class'=>'Server Error'),
    501 => array('txt'=>'Not Implemented', 			'class'=>'Server Error'),
    502 => array('txt'=>'Bad Gateway', 				'class'=>'Server Error'),
    503 => array('txt'=>'Service Unavailable', 			'class'=>'Server Error'),
    504 => array('txt'=>'Gateway Timeout', 			'class'=>'Server Error'),
    505 => array('txt'=>'HTTP Version Not Supported', 		'class'=>'Server Error')
);

/**
 * REST hívás JSON kódolt adatokkal
 *     $url - a resource azonsosító
 *     $verb - GET (lekérdezés), POST (módosítás), DELETE (törlés)
 *     $params - azátadandó paraméterek asszociatív tömbje
**/

function restRequest($url, $verb, $params) {
    $header = array(
	'Accept: text/plain',
	'Content-Type: application/json',
    );
    $params['name'] = 'Naplo';
    $forcePostParams = ($verb != 'GET'); // A SuliX megvalósítás POST paraméterként (tehát a message body-ban) várja a paramétereket
    return rest_helper($url, array(json_encode($params, JSON_FORCE_OBJECT)), $verb, 'json', $header, $forcePostParams);
}

/**
 * Általános REST hívás megadható metódussal, visszatérési érték:
 *   $ret [http]
 *	    [status]
 *	    [header][-indexed-]
 *	  [result][-asszoc-]
**/
function rest_helper($url, $params = null, $verb = 'GET', $format = 'json', $header = array(), $forcePostParams = false) {

    $cparams = array(
	'http' => array(
    	    'method' => $verb,
	    'header' => implode("\r\n", $header),
    	    'ignore_errors' => true
	)
    );
    if ($params !== null) {
	$params = http_build_query($params);
	if ($verb == 'POST' || $forcePostParams === true) {
    	    $cparams['http']['content'] = $params;
	} else {
    	    $url .= '?' . $params;
	}
    }

    $context = stream_context_create($cparams);
    $fp = fopen($url, 'rb', false, $context);

    if (!$fp) {
	$res = false;
    } else {
	$meta = stream_get_meta_data($fp);
	$ret['http']['status'] = explode(' ', $meta['wrapper_data'][0])[1];
	for ($i = 1; $i < count($meta['wrapper_data']); $i++) {
	    $tmp = explode(': ', $meta['wrapper_data'][$i]);
	    $ret['http']['header'][ $tmp[0] ] = $tmp[1];
	}
	//var_dump($meta['wrapper_data']);
	$res = stream_get_contents($fp);
    }

    if ($res === false) {
	throw new Exception("$verb $url failed: $php_errormsg");
    }

    switch ($format) {
	case 'json':
    	    $ret['result'] = json_decode($res, true);
    	    if ($ret['result'] === null) {
    		throw new Exception("failed to decode $res as json");
    	    }
    	    return $ret;

	case 'xml':
    	    $ret['result'] = simplexml_load_string($res);
    	    if ($ret['result'] === null) {
    		throw new Exception("failed to decode $res as xml");
    	    }
    	    return $ret;
    }
    $ret['result'] = $res;
    return $ret;
}

/*

// http://wezfurlong.org/blog/2006/nov/http-post-from-php-without-curl/
// Az eredeti kód...

function rest_helper($url, $params = null, $verb = 'GET', $format = 'json')
{
  $cparams = array(
    'http' => array(
      'method' => $verb,
      'ignore_errors' => true
    )
  );
  if ($params !== null) {
    $params = http_build_query($params);
    if ($verb == 'POST') {
      $cparams['http']['content'] = $params;
    } else {
      $url .= '?' . $params;
    }
  }

  $context = stream_context_create($cparams);
  $fp = fopen($url, 'rb', false, $context);
  if (!$fp) {
    $res = false;
  } else {
    // If you're trying to troubleshoot problems, try uncommenting the
    // next two lines; it will show you the HTTP response headers across
    // all the redirects:
     $meta = stream_get_meta_data($fp);
     var_dump($meta['wrapper_data'][0]);
    $res = stream_get_contents($fp);
  }

  if ($res === false) {
    throw new Exception("$verb $url failed: $php_errormsg");
  }

  switch ($format) {
    case 'json':
      $r = json_decode($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as json");
      }
      return $r;

    case 'xml':
      $r = simplexml_load_string($res);
      if ($r === null) {
        throw new Exception("failed to decode $res as xml");
      }
      return $r;
  }
  return $res;
}

*/


?>