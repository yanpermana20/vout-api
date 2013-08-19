<?php
include 'db_connect.php';
include 'global_function.php';

$headers = $_SERVER;
if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	$token=$headers['HTTP_TOKEN'];
	$uuid=$headers['HTTP_UUID'];
	$token_cek=token_cek($token,$uuid);
	$user_info=json_decode($token_cek);

	if($user_info->status == 1){
		if(!empty($_REQUEST['question_id'])){
			$question_id=$_REQUEST['question_id'];
			$unflaging=questions_unflags($user_info->user_id,$question_id);
			echo $unflaging;
		}else{
			$data['status']="0";
			$data['message']="question_id is Empty";
			echo json_encode($data);exit;
		}
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}
//Tanpa Token
// $flaging=questions_unflags('0e133398839aaa9c8b0a80eba51c9157','3b7ac3bdfc610b7e38af4ee0f0b1d833');
// echo $flaging;
function questions_unflags($userId,$questionId){
	$query_unflag="update questions_flags set status ='0' where user_id='".$userId."' and question_id='".$questionId."'";
	$result_unflag=mysql_query($query_unflag);
	if($result_unflag){
		$data['status']="1";
		$data['message']="Unflaging Success";
	}else{
		$data['status']="0";
		$data['message']="Unflaging Failed!". mysql_error();
	}
	
	return json_encode($data);
}
?>