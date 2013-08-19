<?php
include 'db_connect.php';
include 'global_function.php';

//Dengan Token
$headers = $_SERVER;
if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	$token=$headers['HTTP_TOKEN'];
	$uuid=$headers['HTTP_UUID'];
	//echo "token=".$token." , UUID=".$uuid;exit;
	$token_cek=token_cek($token,$uuid);
	$user_info=json_decode($token_cek);
	// echo "<pre>";
	// print_r($user_info);
	if($user_info->status == 1){
		if(!empty($_POST['question_id']) && !empty($_POST['comment'])){
			$question_id=$_POST['question_id'];
			$comment=$_POST['comment'];
			$set_comment=set_comment($user_info->user_id,$question_id,$comment);
			echo $set_comment;
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
// $set_comment=set_comment('1','2','ini yan komen');
// echo "<pre>";
// print_r($set_comment);
function set_comment($userId, $questionId, $comment){
	$query_cek_answer="select id from activities where user_id='".$userId."' and type='ANSWER' and source_id='".$questionId."'";
	$result_cek_answer=mysql_query($query_cek_answer);
	if($result_cek_answer){
		$num_cek_answer=mysql_num_rows($result_cek_answer);
		if($num_cek_answer > 0){
			$comment_id=rand_id();
			$query_comment="insert into comments (id,user_id,comment,created_date,updated_date)
			values ('".$comment_id."','".$userId."','".$comment."',now(),now())";
			$result_comment=mysql_query($query_comment);
			if($result_comment){
				$query_question="select comments from questions where id=".$questionId;
				$result_question=mysql_query($query_question);
				$question=mysql_fetch_assoc($result_question);
				
				//$question_hit_rate=$question['hit_rate']+1;
				if(!empty($question['comments'])){
					$question_comments=$question['comments'].",".$comment_id;
				}else{
					$question_comments=$comment_id;
				}
				$today=date("Y-m-d H:i:s");
				$query_question_update="update questions set  comments='".$question_comments."',
				updated_date='".$today."' where id=".$questionId;
				$result_question_update=mysql_query($query_question_update);
				
				if($result_question_update){
					$query_get_user="select first_name from users where id='".$userId."'";
					$result_get_user=mysql_query($query_get_user);
					$data_get_user=mysql_fetch_assoc($result_get_user);
					$data['status']="1";
					$data['user']['id']=$userId;
					$data['user']['first_name']=$data_get_user['first_name'];
					$data['question']['id']=$questionId;
					$data['comment']['id']=$comment_id;
					$data['comment']['comment']=$comment;
					$data['comment']['created_date']=datetimeToTimestamp($today);
					$data['comment']['updated_date']=datetimeToTimestamp($today);
				}else{
					$data['status']="0";
					$data['message']="[Questions] ".mysql_error();
				}
			}else{
				$data['status']="0";
				$data['message']="[Comment] ".mysql_error();
			}
		}else{
			$data['status']="0";
			$data['message']="You Not Yet Answer The Question";
		}
	}else{
		$data['status']="0";
		$data['message']="[Cek Activity] ".mysql_error();
	}

	return json_encode($data);
}
?>