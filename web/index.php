<?php

$fak = getenv('FL_API'); // flickr api key
$fas = getenv('FL_SEC'); // flickr api secret
$url = getenv('APP_URL'); // with trailing slash - used for return url

$ret_oauth_token = $_GET['oauth_token'];
$ret_oauth_verifier = $_GET['oauth_verifier'];
$ret_oauth_sec = $_GET['ots'];

function oa_enc($s){
    // encode urls to oauth standard
    $s = rawurlencode($s);
    $s = str_replace('%7E', '~', $s);
    return($s);
}

function signreq($cs,$ct,$method,$url,$params){
    // get signature for a flickr request
    // make key
    $key = $cs.'&'.$ct;
    
    // encode other bits
    $method = oa_enc($method);
    $url = oa_enc($url);
    $params = oa_enc($params);
    
    // build string
    $basestring = $method.'&'.$url.'&'.$params;
    error_log('basestring '.$basestring);
    // sign
    $signature = base64_encode(hash_hmac('sha1', $basestring, $key, true));
    
    return($signature);
}

if ($ret_oauth_verifier == ""){
  // start oauth flow
  error_log('starting oauth');
  
  // request token
  $r_nonce = md5(time()-51);
  $r_time = time();

  $params = 'oauth_callback='.oa_enc($url.'index.php').'&oauth_consumer_key='.$fak.'&oauth_nonce='.$r_nonce.'&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$r_time.'&oauth_version=1.0';
  error_log('params '.$params);
  $tsk = ''; // not known  
  $r_sig = signreq($fas,$tsk,'GET','https://www.flickr.com/services/oauth/request_token',$params);
  error_log('sig '.$r_sig);
  
  $url = 'https://www.flickr.com/services/oauth/request_token?'.$params.'&oauth_signature='.$r_sig;  
  error_log('url '.$url);
  
  // fetch data
  $curl = curl_init();
  curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $url
  ));
  $fl_resp = curl_exec($curl);
  curl_close($curl);
  
  // process returned data
  /*
  [oauth_callback_confirmed] => true
  [oauth_token] => x
  [oauth_token_secret] => y
  */
  
  $fl_data = array();
  $fl_proc = parse_str($fl_resp, $fl_data);

  if ($fl_data['oauth_callback_confirmed'] == 'true'){
    $fl_auth_url = 'https://www.flickr.com/services/oauth/authorize?oauth_token='.$fl_data['oauth_token'];
    echo('<br>OAuth Secret - put this in the environment variable called OTS: '.$fl_data['oauth_token_secret']);
    echo('<br>Then visit: '.$fl_auth_url);
  }
  else {
    echo('Oauth failed');
  };  
}
else {
  // have returned with token and verifier
  // now exchange for an Access Token
  
  error_log('continuing oauth');
  error_log('oa_tok '.$ret_oauth_token);
  error_log('oa_ver '.$ret_oauth_verifier);
  error_log('oa_tok_sec '.getenv('OTS'));
  
  $r_nonce = md5(time()-51);
  $r_time = time();
  
  $params = 'oauth_consumer_key='.$fak.'&oauth_nonce='.$r_nonce.'&oauth_signature_method=HMAC-SHA1&oauth_timestamp='.$r_time.'&oauth_token='.$ret_oauth_token.'&oauth_verifier='.$ret_oauth_verifier.'&oauth_version=1.0';
  error_log('params '.$params);
  $tsk = getenv('OTS'); 
  $r_sig = signreq($fas,$tsk,'GET','https://www.flickr.com/services/oauth/access_token',$params);
  
  $url = 'https://www.flickr.com/services/oauth/access_token?'.$params.'&oauth_signature='.$r_sig;  
  error_log('url '.$url);
  
  // fetch data
  $curl = curl_init();
  curl_setopt_array($curl, array(
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $url
  ));
  $fl_resp = curl_exec($curl);
  curl_close($curl);
  
  // parse
  $fl_data = array();
  $fl_proc = parse_str($fl_resp, $fl_data);
  
  // output user tokens
  print_r($fl_data);
  
}

?>