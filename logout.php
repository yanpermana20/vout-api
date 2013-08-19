<?php
include 'db_connect.php';
include 'global_function.php';
// 
$headers = $_SERVER;
if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	$token=$headers['HTTP_TOKEN'];
	$uuid=$headers['HTTP_UUID'];
	$device=$headers['HTTP_DEVICE'];
	//echo "token=".$token." , UUID=".$uuid;exit;
	$token_cek=token_cek($token,$uuid);
	$user_info=json_decode($token_cek);
	// echo "<pre>";
	// print_r($user_info);
	if($user_info->status == 1){
		
			$logout=logout($uuid,$device);
			echo $logout;
		
	}else{
		echo $token_cek;
	}
}else{
	$data['status']=0;
	$data['message']="Token or UUID is empty!";
	
	echo json_encode($data);
}

// $logout=logout('1','APA91bGkhBvYpsgEjEoCGExXa8BdLZ4nR3i8Xiu6lPXRR5cRT0lV-XxvXL1c9cDxmjIpiG2eyqjZFW_tuwjU7yZBWEO1TjqpIo1ZsLH8n6sJGmlyMRX_cLMp6dIfBUcnQqflbg_UMRzR','android');
// print_r($logout);
function logout($uuid, $userAgent){
	// if($userAgent == "android"){
		// $device="android_id";
	// }
	// if($userAgent == "iphone"){
		// $device="ios_id";
	// }
	
	// $query_update_user="update token_access set ".$device." = '' where uuid='".$uuid."'";
// 		
	// $result_update_user=mysql_query($query_update_user);
	// if($result_update_user){
		// $data['status']="1";
	// }else{
		// $data['status']="0";
		// $data['message']="[Update User] ". mysql_error();
	// }
	
	$query_delete_token="delete from token_access where uuid='".$uuid."'";
		
	$result_delete_token=mysql_query($query_delete_token);
	if($result_delete_token){
		$data['status']="1";
	}else{
		$data['status']="0";
		$data['message']="[Update User] ". mysql_error();
	}
	
	
	return json_encode($data);
}
?>