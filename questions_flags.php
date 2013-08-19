<?php
include 'db_connect.php';
include 'global_function.php';
//Dengan Token
// $headers = $_SERVER;
// if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	// $token=$headers['HTTP_TOKEN'];
	// $uuid=$headers['HTTP_UUID'];
	// $token_cek=token_cek($token,$uuid);
	// $user_info=json_decode($token_cek);
// 
	// if($user_info->status == 1){
		// if(!empty($_REQUEST['question_id'])){
			// $question_id=$_REQUEST['question_id'];
			// $description=$_REQUEST['description'];
			// $flaging=questions_flags($user_info->user_id,$question_id,$description);
			// echo $flaging;
		// }else{
			// $data['status']="0";
			// $data['message']="question_id is Empty";
			// echo json_encode($data);exit;
		// }
	// }else{
		// echo $token_cek;
	// }
// }else{
	// $data['status']="0";
	// $data['message']="Token and UUID is empty";
// 	
	// echo json_encode($data);
// }

//Tanpa Token
$flaging=questions_flags('0e133398839aaa9c8b0a80eba51c9157','3469d39facc789c82b0177ae1fefa07f','Questionnya jelek!!!');
echo $flaging;
function questions_flags($userId,$questionId,$description){
	$flaq_id=rand_id();
	$query_flag="insert into questions_flags (id, user_id, question_id, description, created_date, updated_date) 
	values ('".$flaq_id."','".$userId."','".$questionId."','".$description."',now(),now())";
	$result_flag=mysql_query($query_flag);
	if($result_flag){
		
		$to = "yan@deksterteknologi.com";
		$subject = "Vout Flaging Question [No Reply]";
		$message = "Delete Question";
		$today_base64=base64_encode($today);
		$message = "Flaging Question";
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
		
		$data['status']="1";
		$data['message']="Flaging Success";
	}else{
		$data['status']="0";
		$data['message']="Flaging Failed!". mysql_error();
	}
	
	return json_encode($data);
}
?>