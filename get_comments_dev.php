<?php
include 'db_connect.php';
include 'global_function.php';

//Dengan Token
// $headers = $_SERVER;
// if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	// $token=$headers['HTTP_TOKEN'];
	// $uuid=$headers['HTTP_UUID'];
	// //echo "token=".$token." , UUID=".$uuid;exit;
	// $token_cek=token_cek($token,$uuid);
	// $user_info=json_decode($token_cek);
	// // echo "<pre>";
	// // print_r($user_info);
	// if($user_info->status == 1){
		// $questionId=$_REQUEST['question_id'];
		// $get_comment=get_comments($questionId);
		// echo $get_comment;
// 		
	// }else{
		// echo $token_cek;
	// }
// }else{
	// $data['status']=0;
	// $data['message']="Token or UUID is empty!";
// 	
	// echo json_encode($data);
// }

//Tanpa Token
$get_comment=get_comments('440f4cf1b941ee896d03da4ce3367b8e');
echo "<pre>";
print_r($get_comment);

function get_comments($question_id){
	$query_qet_comment_id="select id, question, comments from questions where id='".$question_id."'";
	$result_qet_comment_id=mysql_query($query_qet_comment_id);
	if($result_qet_comment_id){
		$data_qet_comment_id=mysql_fetch_assoc($result_qet_comment_id);
		$arrCommentId=explode(",", $data_qet_comment_id['comments']);
		
		foreach ($arrCommentId as $comment) {
			$comment="'".$comment."'";
			$commentsId=$commentsId.",".$comment;
		}
		$commentsId = substr($commentsId, 1);
		$query_get_comments="select c.id, c.comment, c.option_id, c.user_id, u.first_name, u.last_name, u.image_id, i.path user_image, c.created_date, c.updated_date from comments c 
			left join users u on u.id = c.user_id
			left join images i on u.image_id = i.id
			where c.id in(".$commentsId.") and c.status ='1' order by c.updated_date";
		$result_get_comments=mysql_query($query_get_comments);
		if($result_get_comments){
			$serverhost=$_SERVER['HTTP_HOST'];
			while($row = mysql_fetch_assoc($result_get_comments)){
				$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
				$row['created_date']=datetimeToTimestamp($row['created_date']);
				$row['updated_date']=datetimeToTimestamp($row['updated_date']);
				unset($row['image_id']);
				$commentList[]=$row;
		  	}
			$question['id']=$data_qet_comment_id['id'];
			$question['question']=$data_qet_comment_id['question'];
			$data['status']=1;
			$data['data']['question']=$question;
			$data['data']['comments']=$commentList;
		}else{
			$data['status']="0";
			$data['message']="[Comment Get] ".mysql_error();
		}
	}else{
		$data['status']="0";
		$data['message']="[Questions Get] ".mysql_error();
	}
	
	return json_encode($data);
}
?>