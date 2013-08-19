<?php
include 'db_connect.php';
include 'global_function.php';

//Dengan Token
$headers = $_SERVER;
if(!empty($_REQUEST['email'])){
	$email=$_REQUEST['email'];
	
	$forgot_password=forgot_password($email);
	echo $forgot_password;
	
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

// $forgot_password=forgot_password('082305f17f19e04bea325f706bfd14c5');
// echo $forgot_password;

function forgot_password($email_user){
	if(!empty($email_user)){
		$today=date("Y-m-d H:i:s");
		$query_user="select id from users where email='".$email_user."'";
		$result_user=mysql_query($query_user);
		$user_data=mysql_fetch_assoc($result_user);
		$user_id=$user_data['id'];
		//SEND EMAIL CHANGE PASSWORD
		$to = $email_user;
		$subject = "Vout Change Password [No Reply]";
		//$message = "http://voutnow.com/api/user_activations.php?id=".$user_id."&email=".$email;
		$today_base64=base64_encode($today);
		$message = "http://voutnow.com/forgot_password.php?id=".$user_id."&a=".$today_base64;
		$from = "voutnow@gmail.com";
		$headers = "From:" . $from;
		$sendmail=mail($to,$subject,$message,$headers);
		//END SEND EMAIL
		if($sendmail){
			$data['status']="1";
			$data['message']="Send Email Completed!";
		}else{
			$data['status']="0";
			$data['message']="Send Email Failed!";
		}
		
	}else{
		$data['status']="0";
		$data['message']="Forgot Password Failed!, Email is empty";
	}
	
	return json_encode($data);
}
?>