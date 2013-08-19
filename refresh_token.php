<?php
include 'db_connect.php';
include 'global_function.php';

$headers = $_SERVER;
if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	$token=$headers['HTTP_TOKEN'];
	$uuid=$headers['HTTP_UUID'];
	$refresh_token=refresh_token($token,$uuid);
	echo $refresh_token;
}else{
	$data['status']=0;
	$data['message']="Token or UUID is empty!";
	
	echo json_encode($data);
}

// $refresh_token=refresh_token("7d6c06e1ff85dfe1c19106ec8555ee88","123");
// echo $refresh_token;
function refresh_token($token, $uuid){
	$query="select id from users where access_token='".$token."' and UUID='".$uuid."'";
	$result=mysql_query($query);
	$row=mysql_num_rows($result);

	$serverhost=$_SERVER['HTTP_HOST']."/api";
	if($row > 0){
		$user=mysql_fetch_assoc($result);
		$token=md5(uniqid());
		
		$today=date("Y-m-d H:i:s");
		$year=date("Y");
		$month=date("m");
		$day=date("d");
		$hour=date("H");
		$minute=date("i");
		$secon=date("s");
		if($month<12){
			$nextMonth=$month+1;
		}else{
			$nextMonth=1;
			$year=$year+1;
		}
		$nextMonthDate=$year."-".$nextMonth."-".$day." ".$hour.":".$minute.":".$secon;
		
		$query_users_token_update="update users set access_token='".$token."', access_expires='".$nextMonthDate."', uuid='".$uuid."', updated_date=now() where id=".$user['id'];
		$result_users_token_update=mysql_query($query_users_token_update);
		
		if($result_users_token_update){
			$data['status']=1;
			$data['data']['user_id']=$user['id'];
			$data['data']['access_token']=$token;
			$data['data']['access_expires']=datetimeToTimestamp($nextMonthDate);
			$data['data']['UUID']=$uuid;
		}else{
			$data['status']=0;
			$data['message']="Refresh Token Failed! - ".mysql_error();
		}
				
	}else{
		$data['status']=0;
		$data['message']="Refresh Token or UUID not Found";
	}
	return json_encode($data);
}
?>