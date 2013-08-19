<?php
include 'db_connect.php';
include 'global_function.php';

if(!empty($_REQUEST['email']) && !empty($_REQUEST['password'])){
	$email = $_REQUEST['email'];
	$password = $_REQUEST['password'];
	$uuid= $_SERVER['HTTP_UUID'];
	$register = register($email,$password,$uuid);
	echo $register;
}else{
	$data['status']="0";
	$data['message']="Email or Password is empty!";
	
	echo json_encode($data);
}

// $register=register("rombeng@yahoo.co.id","jancuk","123456789qwertyuiop");
// echo $register;
function register($email, $password, $uuid){
	$user_id=rand_id();
	$token=rand_id();
	$email=antiInjec($email);
	$password=antiInjec($password);
	
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
	
	$query="insert into users (id, email, password, created_date, updated_date) 
	values ('".$user_id."','".$email."','".md5($password)."',now(),now())";
	$result=mysql_query($query);
	if($result){

		//SEND EMAIL ACTIVATION
		$to = $email;
		$subject = "Vout User Activation [No Reply]";
		//$message = "http://voutnow.com/api/user_activations.php?id=".$user_id."&email=".$email;
		$today_base64=base64_encode($today);
		$message = "http://voutnow.com/activation.php?id=".$user_id."&email=".$email."&a=".$today_base64;
		$from = "voutnow@gmail.com";
		$headers = "From:" . $from;
		$sendmail=mail($to,$subject,$message,$headers);
		//END SEND EMAIL

		if($sendmail){
			$data['status']="1";
			$data['message']="Register Completed!";
		}else{
			$data['status']="0";
			$data['message']="Send Email Failed!";
		}
		// $token_access_id=rand_id();
		// $query_token="insert into token_access (id, user_id, uuid, token, expires, created_date, updated_date)
		// values ('".$token_access_id."','".$user_id."','".$uuid."','".$token."','".$nextMonthDate."',now(),now())";
		// $result_token=mysql_query($query_token);
		// if($result_token){
			// $data_result= array('user_id' => $user_id, 'email' => $email,'access_token' => $token, 'access_expires' => datetimeToTimestamp($nextMonthDate),
				// 'UUID' => $uuid);
			// $data['status']=1;
			// $data['message']="Register Completed!";
			// $data['data']=$data_result;
			// $queryDeleteEmailTemp="delete from email_temp where email='".$email."'";
			// $resultDeleteEmailTemp=mysql_query($queryDeleteEmailTemp);
		// }else{
			// $data['status']="0";
			// $data['message']="Register Failed!". mysql_error();
		// }
	}else{
		$data['status']="0";
		$data['message']="Register Failed!". mysql_error();
	}
	return json_encode($data);
}


?>