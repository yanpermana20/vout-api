<?php
include 'db_connect.php';
include 'global_function.php';
//echo antiInjec($_POST['email']);exit;
// $headers = $_SERVER;
// echo "<pre>";
// print_r($headers);

if(!empty($_REQUEST['email']) && !empty($_REQUEST['password'])){
	$headers = $_SERVER;
	$email=antiInjec($_REQUEST['email']);
	$password=antiInjec($_REQUEST['password']);
	$uuid=$_SERVER['HTTP_UUID'];
	$login=login($email,$password,$uuid);
	//echo "<pre>";
	echo $login;
}else{
	$data['status']=0;
	$data['message']="Email or Password is empty!";
	
	echo json_encode($data);
}

// $login=login("randi.waranugraha@gmail.com","admin","e810532c-13a4-440d-9e45-d04005b0f84");
// echo $login;
function login($email, $password, $uuid){
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
	
	if(!empty($email) && !empty($password)){
		$query_user="select * from users where email='".$email."' and password='".md5($password)."'";
		$result_user=mysql_query($query_user);
		$row_user=mysql_num_rows($result_user);
		
		if($row_user > 0){
			$user=mysql_fetch_assoc($result_user);
			if($user['status']=='1'){
				$query_cek_token="select * from token_access where user_id='".$user['id']."' and uuid='".$uuid."'";

				$result_cek_token=mysql_query($query_cek_token);
				if($result_cek_token){
					$token_row=mysql_num_rows($result_cek_token);
					if($token_row > 0){
						$token_access=mysql_fetch_assoc($result_cek_token);
						
						if(empty($token_access['token'])){
							$token=rand_id();
							$query_update_token="update token_access set token='".$token."', expires='".$nextMonthDate."' where user_id='".$user['id']."' and uuid='".$uuid."'";
							$result_update_token=mysql_query($query_update_token);
							if($result_update_token){
								$data['status']=1;
								
								$data_user['access_token']=$token;
								$data_user['access_expires']=datetimeToTimestamp($nextMonthDate);
								
							}else{
								$data['status']=0;
								$data['message']="[Update-Token] ".mysql_error();
							}
						}else{
							$data['status']=1;
							
							$data_user['access_token']=$token_access['token'];
							$data_user['access_expires']=datetimeToTimestamp($token_access['expires']);
						}
					}else{
						$token_access_id=rand_id();
						$token=rand_id();
						
						
						$query_add_token="insert into token_access (id, user_id, uuid, token, expires, created_date, updated_date)
						values ('".$token_access_id."','".$user['id']."','".$uuid."','".$token."','".$nextMonthDate."',now(),now())";
						$result_add_token=mysql_query($query_add_token);
						
						if($result_add_token){
							$data['status']=1;
							
							$data_user['access_token']=$token;
							$data_user['access_expires']=datetimeToTimestamp($nextMonthDate);
							
						}else{
							$data['status']=0;
							$data['message']="[Insert-Token] ".mysql_error();
						}
					}
				}
			}else{
				$data['status']=0;
				$data['message']="Please check your email for activation";
			}
		}else{
			$data['status']=0;
			$data['message']="Check Your Email or Password";
		}
	}else{
		$data['status']=0;
		if(empty($email) && empty($password)){
			$data['message']="Email and Password is empty";
		}else if(empty($email)){
			$data['message']="Email is empty";
		}else if(empty($password)){
			$data['message']="Password is empty";
		}
	}
	
	if($data['status']==1){
		
		$query_user1="select u.id as user_id, u.email, u.first_name, u.last_name, u.first_login, u.location, i.id user_image from users u 
		left join images i on i.id = u.image_id 
		where u.id='".$user['id']."'";
		$result_user1=mysql_query($query_user1);
		
		if($result_user1){
			$user1=mysql_fetch_assoc($result_user1);
		
			$data_user['user_id']=$user1['user_id'];
			$data_user['email']=$user1['email'];
			if(!empty($user1['first_name'])){
				$data_user['first_name']=$user1['first_name'];
			}else{
				$data_user['first_name']="";
			}
			if(!empty($user1['last_name'])){
				$data_user['last_name']=$user1['last_name'];
			}else{
				$data_user['last_name']="";
			}
			
			$data_user['first_login']=$user1['first_login'];
			$data_user['uuid']=$uuid;
			
			if(!empty($user1['user_image'])){
				$data_user['user_image']=$user1['last_name'];
			}
			$data['data']=$data_user;
		}else{
			$data['status']=0;
			$data['message']="[Users-Select] ".mysql_error();
		}
		
		
	}
	return json_encode($data);
}
?>