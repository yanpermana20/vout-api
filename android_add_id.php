<?php
include 'db_connect.php';
include 'global_function.php';

//Dengan Token
$headers = $_SERVER;
if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	$token=$headers['HTTP_TOKEN'];
	$uuid=$headers['HTTP_UUID'];

	$token_cek=token_cek($token,$uuid);
	$user_info=json_decode($token_cek);
	// echo "<pre>";
	// print_r($user_info);
	if($user_info->status == 1){
		if(!empty($_POST['device_id'])){
			$device_id=$_POST['device_id'];
			$android_add_id=android_add_id($user_info->user_id,$uuid,$device_id);
			echo $android_add_id;
		}else{
			$data['status']="0";
			$data['message']="Device ID is empty";
			echo json_encode($data);exit;
		}
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

// $android_add_id=android_add_id('2','qwe123ada12343');
// echo "<pre>";
// print_r($android_add_id);
function android_add_id($userId, $uuid, $deviceId){
	$query_cek_android="select id,android_id from token_access where android_id='".$deviceId."'";
	$result_cek_android=mysql_query($query_cek_android);
	if($result_cek_android){
		$num_cek_android=mysql_num_rows($result_cek_android);
		if($num_cek_android>0){
			$data_cek_android=mysql_fetch_assoc($result_cek_android);
			$query_update_android="update token_access set android_id='' where id='".$data_cek_android['id']."'";
			$result_update_android=mysql_query($query_update_android);
			if(!$result_update_android){
				$data['status']="0";
				$data['message']="[Update Android Failed] ".mysql_error();
				return json_encode($data);
			}
		}
		$query_update_android="update token_access set android_id='".$deviceId."' where user_id='".$userId."' and uuid='".$uuid."'";
		$result_update_android=mysql_query($query_update_android);
		if($result_update_android){
			$data['status']="1";
			$data['message']="Update Success";
		}else{
			$data['status']="0";
			$data['message']="[Update Failed] ".mysql_error(); 
		}
	}else{
		$data['status']="0";
		$data['message']="[Cek Android Failed] ".mysql_error();
	}
	
	return json_encode($data);
}
?>