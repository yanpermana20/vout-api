<?php
include 'db_connect.php';
include 'global_function.php';

//if(!empty($_REQUEST['email']) || !empty($_REQUEST['facebook_email'])){
	$email = $_REQUEST['email'];
	$facebook_email = $_REQUEST['facebook_email'];
	$user = cekUser($email,$facebook_email);
	echo $user;
// }else{
	// $data['status']="0";
	// $data['message']="Email is empty!";
// 	
	// echo json_encode($data);
// }
// $user = cekUser("","zooatmymouth@yahoo.co.id");
// 
// echo $user;
function cekUser($email,$email_facebook){
	$email_type="email";
	if(!empty($email)){
		$email_type="email";
		$email_data=$email;
	}
	if(!empty($email_facebook)){
		$email_type="email_facebook";
		$email_data=$email_facebook;
	}
	$query="select id from users where ".$email_type."='".$email_data."'";
	$result=mysql_query($query);
	$row=mysql_num_rows($result);
	
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	if($row > 0){
		$data['status']=1;
		$data['data']['email_status']="1";
		$data['data']['email']=$email;
		$data['data']['url']="http://".$serverhost."/login.php";

	}else{
		$user_id=rand_id();
		
		$query_email="select id from email_temp where email='".$email_data."'";
		$result_email=mysql_query($query_email);
		$row_email=mysql_num_rows($result_email);
		if($row_email < 1){
			$query1="insert into email_temp (id, email, status, created_date, updated_date) values ('".$user_id."','".$email_data."','UNREGISTER',now(),now())";
			$result1 = mysql_query($query1);
			if($result1){
				$data['status']="1";
				$data['data']['email_status']="0";
				$data['data']['message']="Email not found";
				$data['data']['email']=$email_data;
				$data['data']['url']="http://".$serverhost."/register.php";
			}else{
				$data['status']="0";
				$data['message']="[Email Temp] ".mysql_error();
				
			}
		}else{
			$query2="update email_temp set updated_date=now() where email='".$email_data."'";
			$result2=mysql_query($query2);
			if($result2){
				$data['status']="1";
				$data['data']['email_status']="0";
				$data['data']['message']="Email not register yet";
				$data['data']['email']=$email_data;
				$data['data']['url']="http://".$serverhost."/register.php";
			}else{
				$data['status']="0";
				$data['message']="[Email Temp] ".mysql_error();
			}
		}	
	}
	
	return json_encode($data);
}
?>