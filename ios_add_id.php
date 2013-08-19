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
			$ios_add_id=ios_add_id($user_info->user_id,$uuid,$device_id);
			echo $ios_add_id;
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

// $ios_add_id=ios_add_id('134b3e68f8ce48f0e9dfe771f51bed00','B1D513A4-979D-4A92-9D14-ACFC7F68ADA8','bacd0ed52f8f22985f408523b9afae7c6c049c4af19f479b0341a4b6b83beae2');
// echo "<pre>";
// print_r($ios_add_id);
function ios_add_id($userId, $uuid, $deviceId){
	$query_cek_ios="select id,ios_id from token_access where ios_id='".$deviceId."'";
	$result_cek_ios=mysql_query($query_cek_ios);
	if($result_cek_ios){
		$num_cek_ios=mysql_num_rows($result_cek_ios);
		if($num_cek_ios>0){
			$data_cek_ios=mysql_fetch_assoc($result_cek_ios);
			$query_update_ios="update token_access set ios_id='' where id='".$data_cek_ios['id']."'";
			$result_update_ios=mysql_query($query_update_ios);
			if(!$result_update_ios){
				$data['status']="0";
				$data['message']="[Update ios Failed] ".mysql_error();
				return json_encode($data);
			}
		}
		$query_update_ios="update token_access set ios_id='".$deviceId."' where user_id='".$userId."' and uuid='".$uuid."'";
		$result_update_ios=mysql_query($query_update_ios);
		if($result_update_ios){
			$data['status']="1";
			$data['message']="Update Success";
		}else{
			$data['status']="0";
			$data['message']="[Update Failed] ".mysql_error(); 
		}
	}else{
		$data['status']="0";
		$data['message']="[Cek ios Failed] ".mysql_error();
	}
	
	return json_encode($data);
}
?>