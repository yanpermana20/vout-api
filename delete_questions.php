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

	if($user_info->status == 1){
		if(!empty($_REQUEST['question_id'])){
			$question_id=$_REQUEST['question_id'];
			$delete_question=delete_questions($user_info->user_id,$question_id);
			echo $delete_question;
		}else{
			$data['status']="0";
			$data['message']="QuestionId is empty";
		}
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}
//test case
// if(!empty($_REQUEST['user_id']) && !empty($_REQUEST['question_id'])){
	// $user_id=$_REQUEST['user_id'];
	// $question_id=$_REQUEST['question_id'];
	// // echo "user_id=".$user_id;
	// // echo "<br>";
	// // echo "question_id=".$question_id;exit;
	// $del=delete_questions($user_id,$question_id);
	// echo $del;
// }else{
	// $data['status']="0";
	// $data['message']="QuestionId or UserId is empty";
	// echo json_encode($data);
// }

//Plain test
// $del=delete_questions(1,1);
// echo $del;
function delete_questions($userId,$questionId){
	$query_get_question="select id from questions where id='".$questionId."' and user_id='".$userId."'";
	$result_get_question=mysql_query($query_get_question);
	if($result_get_question){
		$data_get_question=mysql_num_rows($result_get_question);
		if($data_get_question > 0){
			$query_inactive_question="update questions set status='0' where id='".$questionId."'";
			$result_inactive_question=mysql_query($query_inactive_question);
			if($result_inactive_question){
				$data['status']="1";
				$data['message']="Success delete question";
			}else{
				$data['status']="0";
				$data['message']="[Inactive Question] ".mysql_error();
			}
		}else{
			$data['status']="0";
			$data['message']="QuestionId and UserId not match";
		}
	}else{
		$data['status']="0";
		$data['message']="[Get Question] ".mysql_error();
	}
	return json_encode($data);
}
?>