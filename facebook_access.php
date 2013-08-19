<?php
include 'db_connect.php';
include 'global_function.php';

$headers = $_SERVER;
if(!empty($headers['HTTP_UUID'])){
	$facebook_user_info=$_REQUEST['facebook_user_info'];
	$uuid=$headers['HTTP_UUID'];
	
	$facebook_access=facebook_access($facebook_user_info,$uuid);
	echo $facebook_access;
}else{
	$data['status']=0;
	$data['message']="UUID is empty!";
	
	echo json_encode($data);
}
// $a='{
    // "last_name": "Tambunan",
    // "id": "1121826477",
    // "first_name": "Jaka",
    // "email": "jnop06@yahoo.com",
    // "middle_name": "Putra Lesmana",
    // "name": "Jaka Putra Lesmana Tambunan"
// }';
// $facebook_access=facebook_access($a,"123","1234567890","123");
// echo $facebook_access;

function facebook_access($facebook_user_info,$uuid){
	$user_id=rand_id();
	$email=antiInjec($email);
	$facebook_info=json_decode($facebook_user_info);
	
	if(!empty($facebook_info->email)){
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
		
		$query_cek_email="select id from users where email_facebook='".$facebook_info->email."'";
		$result_cek_email=mysql_query($query_cek_email);
		if($result_cek_email){
			$users=mysql_fetch_assoc($result_cek_email);
			$row=mysql_num_rows($result_cek_email);
		
			if($row==0){
				
				$query_insert_user="insert into users (id,email_facebook,facebook_info,facebook_id,name,first_name,middle_name,last_name,created_date,updated_date) 
				values ('".$user_id."','".$facebook_info->email."','".$facebook_user_info."','".$facebook_info->facebook_id."','".$facebook_info->name."','".$facebook_info->first_name."','".$facebook_info->middle_name."','".$facebook_info->last_name."',now(),now())";
				
				$result_insert_user=mysql_query($query_insert_user);
				
				if($result_insert_user){
					$token_access_id=rand_id();
					$token=rand_id();
					
					$query_get_first_login="select first_login from users where email_facebook='".$facebook_info->email."'";
					$result_get_first_login=mysql_query($query_get_first_login);
					if($result_get_first_login){
						$first_login=mysql_fetch_assoc($result_get_first_login);
					}
					
					$query_token="insert into token_access (id, user_id, uuid, token, expires, created_date, updated_date)
					values ('".$token_access_id."','".$user_id."','".$uuid."','".$token."','".$nextMonthDate."',now(),now())";
					$result_token=mysql_query($query_token);
					
					if($result_token){
							$data_user = array('user_id' => $user_id, 'access_token' => $token, 'first_login' => $first_login['first_login'], 'access_expires' => datetimeToTimestamp($nextMonthDate),
							'UUID' => $uuid);
							$data['status']=1;
							$data['data']=$data_user;
					}else{
						$data['status']="0";
						$data['message']="Create Token Failed ". mysql_error();
					}
				}else{
					$data['status']=0;
					$data['message']="Register Failed! - ".mysql_error();
				}	
			}else{
				if($facebook_info->is_registered=="0"){
					$query_update_user="update users set facebook_info='".$facebook_user_info."', facebook_id='".$facebook_info->facebook_id."',name='".$facebook_info->name."', 
					first_name='".$facebook_info->first_name."', middle_name='".$facebook_info->middle_name."', last_name='".$facebook_info->last_name."', 
					updated_date=now() where email_facebook='".$facebook_info->email."'";
					$result_update_user=mysql_query($query_update_user);
				}
				
				$query_get_first_login="select first_login from users where email_facebook='".$facebook_info->email."'";
				$result_get_first_login=mysql_query($query_get_first_login);
				if($result_get_first_login){
					$first_login=mysql_fetch_assoc($result_get_first_login);
				}
				
				$query_cek_token="select * from token_access where user_id='".$users['id']."' and uuid='".$uuid."'";
				$result_cek_token=mysql_query($query_cek_token);
				
				if($result_cek_token){
					$token_row=mysql_num_rows($result_cek_token);
					if($token_row > 0){
						$token_access=mysql_fetch_assoc($result_cek_token);
						if(empty($token_access['token'])){
							$token=rand_id();
							$query_update_token="update token_access set token='".$token."', expires='".$nextMonthDate."' where user_id='".$users['id']."' and uuid='".$uuid."'";
							$result_update_token=mysql_query($query_update_token);
							if($result_update_token){
								$data_user = array('user_id' => $users['id'], 'access_token' => $token, 'access_expires' => datetimeToTimestamp($nextMonthDate),
								'UUID' => $uuid);
								$data['status']=1;
								$data['data']=$data_user;
								
							}else{
								$data['status']=0;
								$data['message']="[Update-Token] ".mysql_error();
							}
						}else{
							$data_user = array('user_id' => $users['id'], 'access_token' => $token_access['token'], 'first_login' => $first_login['first_login'], 'access_expires' => datetimeToTimestamp($token_access['expires']),
							'UUID' => $uuid);
							$data['status']=1;
							$data['data']=$data_user;
						}
					}else{
						$token_access_id=rand_id();
						$token=rand_id();
						
						
						$query_add_token="insert into token_access (id, user_id, uuid, token, expires, created_date, updated_date)
						values ('".$token_access_id."','".$users['id']."','".$uuid."','".$token."','".$nextMonthDate."',now(),now())";
						$result_add_token=mysql_query($query_add_token);
						
						if($result_add_token){
							$data_user = array('user_id' => $users['id'], 'access_token' => $token, 'first_login' => $first_login['first_login'], 'access_expires' => datetimeToTimestamp($nextMonthDate),
							'UUID' => $uuid);
							$data['status']=1;
							$data['data']=$data_user;
						}else{
							$data['status']=0;
							$data['message']="[Insert-Token] ".mysql_error();
						}
					}
				}
			}
		}else{
			$data['status']=0;
			$data['message']="Register Failed! - ".mysql_error();
		}
	}else{
		$data['status']=0;
			$data['message']="Register Failed! - Facebook email is empty";
	}
	
	return json_encode($data);
}
?>