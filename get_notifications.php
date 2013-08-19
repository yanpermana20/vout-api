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
		$get_notif=get_notification($user_info->user_id);
		echo $get_notif;
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

//Tanpa Token
// $get_notif=get_notification('3');
// echo $get_notif;
function get_notification($user_id){
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	$query_get_notif="select * from activities where destination_user_id='".$user_id."' and status='1'
	order by updated_date desc limit 20";
	
	$result_get_notif=mysql_query($query_get_notif);
	$num=0;
	if($result_get_notif){
		while($row = mysql_fetch_assoc($result_get_notif)){
			if($user_id != $row['user_id']){
				$query_get_user="select u.id, u.first_name, i.path image_path from users u 
				left join images i on i.id = u.image_id 
				where u.id='".$row['user_id']."'";
				
				$result_get_user=mysql_query($query_get_user);
				if($result_get_user){
					$user_info=mysql_fetch_assoc($result_get_user);
					if(!empty($user_info['image_path'])){
						$user_info['user_image']="http://".$serverhost."/image_util.php?src=".$user_info['image_path'];
					}else{
						$user_info['user_image']="";
					}
					
					unset($user_info['image_id']);
				}
				
				$query_get_question="select id, question, created_date, updated_date from questions where id='".$row['source_id']."'";
				$result_get_question=mysql_query($query_get_question);
				if($result_get_question){
					$question=mysql_fetch_assoc($result_get_question);
					$question['created_date']=datetimeToTimestamp($question['created_date']);
					$question['updated_date']=datetimeToTimestamp($question['updated_date']);
				}
				$row['created_date']=datetimeToTimestamp($row['created_date']);
				$row['updated_date']=datetimeToTimestamp($row['updated_date']);
				$notif_list[]=$row;
				$notif_list[$num]['user']=$user_info;
				if(!empty($question)){
					$notif_list[$num]['question']=$question;
				}else{
					$notif_list[$num]['question']['id']="";
					$notif_list[$num]['question']['question']="";
					$notif_list[$num]['question']['url']="";
					$notif_list[$num]['question']['created_date']="";
					$notif_list[$num]['question']['updated_date']="";
				}
				if($row['type']=="ANSWER"){
					$query_get_option="select o.id, o.title, i.path from options o 
					left join images i on i.id = o.image_id where o.id='".$row['data']."'";
					$result_get_option=mysql_query($query_get_option);
					if($query_get_option){
						$option=mysql_fetch_assoc($result_get_option);
					}
					if(!empty($option)){
						$notif_list[$num]['option']=$option;
						$notif_list[$num]['option']['image']="http://".$serverhost."/image_util.php?src=".$option['path'];
					}else{
						$notif_list[$num]['option']['id']="";
						$notif_list[$num]['option']['title']="";
						$notif_list[$num]['option']['image']="";
					}
					
				}
				
				if($row['type']=="COMMENT"){
					$query_get_comment="select id,comment from comments where id='".$row['data']."'";
					$result_get_comment=mysql_query($query_get_comment);
					if($query_get_comment){
						$comment=mysql_fetch_assoc($result_get_comment);
					}
					if(!empty($comment)){
						$notif_list[$num]['comment']=$comment;
					}else{
						$notif_list[$num]['comment']['id']="";
						$notif_list[$num]['comment']['comment']="";
					}
					
				}
				if($row['type']=="TAGGED"){
					if(!empty($question)){
						$notif_list[$num]['question']=$question;
						$notif_list[$num]['question']['url']="http://".$serverhost."/get_questions_detail.php?questionId=".$question['id'];
					}else{
						$notif_list[$num]['question']['id']="";
						$notif_list[$num]['question']['question']="";
						$notif_list[$num]['question']['url']="";
						$notif_list[$num]['question']['created_date']="";
						$notif_list[$num]['question']['updated_date']="";
						
					}
				}
				unset($notif_list[$num]['user_id']);
				unset($notif_list[$num]['destination_user_id']);
				unset($notif_list[$num]['source_id']);
				unset($notif_list[$num]['data']);
				$num++;
			}
		}

		$data['status']="1";
		if(empty($notif_list)){
			$data['data']=array();
		}else{
			$data['data']=$notif_list;
		}
	}else{
		$data['status']="0";
		$data['message']="Get activity ". mysql_error();
	}
	return json_encode($data);
}
?>