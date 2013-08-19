<?php

function rand_id(){
	$today = uniqid().uniqid(date("YmdHis"));

	return md5($today);
}

function antiInjec($string) {
	$string1 = stripslashes($string);
	$string2 = strip_tags($string1);
	$string3 = mysql_real_escape_string($string2);
	return $string3;
}

function datetimeToTimestamp($str) {

    list($date, $time) = explode(' ', $str);
    list($year, $month, $day) = explode('-', $date);
    list($hour, $minute, $second) = explode(':', $time);
    
    $timestamp = mktime($hour+1, $minute, $second, $month, $day, $year);
    
    return $timestamp;
}

function token_cek($token, $uuid){
	$query="select * from token_access where token='".$token."' and uuid='".$uuid."'";
	
	$result=mysql_query($query);
	$row=mysql_num_rows($result);
	
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	if($row > 0){
		$user=mysql_fetch_assoc($result);
		//if(strtotime($user['access_expires']) > strtotime(date('Y-m-d H:i:s'))){
			$data = array('status' => "1", 'message' => "Acess Token Valid", 'user_id' => $user['user_id'],
			'access_token' => $user['token'], 'access_expires' => $user['expires'], 'UUID' => $user['uuid']);
		// }else{
			// $data = array('status' => "0", 'message' => "Token Expired", 'url' => "http://".$serverhost."/vout/refresh_token.php");
		// }		
	}else{
		$data = array('status' => "0", 'message' => "Token or UUID not valid", 'url' => "http://".$serverhost."/login.php");
	}
	return json_encode($data);
}

function android_push_notif($userId,$type,$message){
	// GCM Server URL
	$url = 'https://android.googleapis.com/gcm/send';

	// Server API
	// Didapat dari Google Console
	$serverApiKey = "AIzaSyApekOls7OVSxMeTtN48mUca1giicnO1cw";
	
	$reg = $userId;//->array format
	
	$data = array(
        'registration_ids' => $reg,
        'data' => array('type' => $type, 'data' => $message)
	);
		
	// Header
	$headers = array(
			'Content-Type:application/json',
			'Authorization:key=' . $serverApiKey
		);
	
	$message = json_encode($data);
	
	$ch = curl_init();
	
	curl_setopt_array($ch, array(
			CURLOPT_URL => $url,
			CURLOPT_HTTPHEADER => $headers,
			CURLOPT_POST => true,
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POSTFIELDS => $message
		));
	
	$response = curl_exec($ch);
	
	curl_close($ch);
	
	// Debugging
	return $response;	
}

function ios_push_notif($userId,$type,$message){
	$deviceToken=$userId; // ARRAY FORMAT
	// Put your private key's passphrase here:
	$passphrase = 'Vout890';
	
	////////////////////////////////////////////////////////////////////////////////
	
	$ctx = stream_context_create();
	stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
	stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);
	
	// Open a connection to the APNS server
	$fp = stream_socket_client(
		'ssl://gateway.sandbox.push.apple.com:2195', $err,
		$errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);
	
	if (!$fp)
		exit("Failed to connect: $err $errstr" . PHP_EOL);
	
	//echo 'Connected to APNS' . PHP_EOL;
	
	// Create the payload body
	$arrMessage = json_decode($message);
	$body['aps'] = array(
		'alert' => $arrMessage->message,
		'message' => $message,
		'sound' => 'default',
		'type' => $type,
		'badge' => '1'
		);
	
	// Encode the payload as JSON
	$payload = json_encode($body);
	
	foreach ($deviceToken as $token) {
	  // Build the binary notification
		$msg = chr(0) . pack('n', 32) . pack('H*', $token) . pack('n', strlen($payload)) . $payload;
		
		// Send it to the server
		$result = fwrite($fp, $msg, strlen($msg));
		
		// if (!$result)
			// echo 'Message not delivered' . PHP_EOL;
		// else
			// echo 'Message successfully delivered' . PHP_EOL;
	}
	
	fclose($fp);
}
?>