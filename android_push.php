<?php
// GCM Server URL
$url = 'https://android.googleapis.com/gcm/send';

// Server API
// Didapat dari Google Console
$serverApiKey = "AIzaSyApekOls7OVSxMeTtN48mUca1giicnO1cw";

// Device ID
// Didapat dari device
$reg = "APA91bE35Bd9vAvrJifplTkpu472dbmfMAwIsw7Xi3RA0entLDWy-NeaczfH3FTc3JEn5p23KlYZkpHl5dkIRIhCj2ShJnFZsIljN_0xUnOfTL-4Wk4vjWtSoVesUM6nbwT7A8vTgO8G";


// Data yang hendak di kirim
$data = array(
        'registration_ids' => array($reg),
        'data' => array('name' => 'Yan Ganteng', 'location' => 'Jakarta')
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
echo $response;
?>