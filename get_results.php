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
	// print_r($user_info);exit;
	if($user_info->status == 1){
		$totalRow=$_POST['total_row'];
		$page=$_POST['page'];
		$myResult=get_resutls($user_info->user_id,$totalRow,$page);
		//echo "<pre>";
		echo $myResult;
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

// $result=get_resutls('d35da8b882b4be93da11bf256c76894c');
// echo "<pre>";
// print_r($result);
function get_resutls($user_id,$totalRow,$page){
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	if(empty($totalRow)){
		$rowsPerPage=10;
	}else{
		$rowsPerPage=$totalRow;
	}
	if(empty($page) || $page == 0){
		$page_num=1;
	}else{
		$page_num =$page;
	}
	$offset = ($page_num - 1) * $rowsPerPage;
	
	$query_get_activities = "select distinct source_id from activities 
	where type='ANSWER' and user_id='".$user_id."'
	order by updated_date desc";
	$result_get_activities = mysql_query($query_get_activities);
	if($result_get_activities){
		while($row=mysql_fetch_assoc($result_get_activities)){
			$row['source_id']="'".$row['source_id']."'";
			//$arrQuestionId[]=$row['source_id'];
			$questionId=$questionId.",".$row['source_id'];
		}
		$questionId = substr($questionId, 1);
		if(!empty($questionId)){
			$query_get_activities="select DISTINCT source_id from activities where user_id='".$user_id."' and type='ANSWER' limit 0,100";
			$result_get_activities=mysql_query($query_get_activities);
			if($result_get_activities){
				while($row = mysql_fetch_assoc($result_get_activities)){
					$arrQuestionId[]=$row['source_id'];
				}
			}
			
			$query_get_questions="select q.id, q.user_id, q.question, q.comments, q.category_id, q.hit_rate, q.vout_count, q.time_limit, q.options theOptions, q.location, q.is_private, q.target_id, 
			u.first_name first_name, u.last_name last_name, iu.path user_image, 
			q.created_date, q.updated_date from questions q
			left join users u on u.id = q.user_id
			left join images i on i.id = q.image_id
			left join images iu on iu.id = u.image_id 
			where q.id in(".$questionId.") and q.status='1' and time_limit > now() order by q.updated_date desc limit ".$offset.", ".$rowsPerPage;
			
			$result_get_questions=mysql_query($query_get_questions);
			if($result_get_questions){
				while($row=mysql_fetch_assoc($result_get_questions)){
					
					$query_get_last_option="SELECT o.id, o.title, o.description, i.path option_image, o.created_date, o. updated_date FROM activities a 
					left join options o on o.id = a.data
					left join images i on i.id = o.image_id
					WHERE a.type='ANSWER' and a.user_id='".$user_id."' and a.source_id='".$row['id']."' 
					order by o.updated_date desc limit 1";
					$result_get_last_option=mysql_query($query_get_last_option);
					if($result_get_last_option){
						$arrOption=mysql_fetch_assoc($result_get_last_option);
					}else{
						$arrOption=array();
					}
					
					// echo "<pre>";
					// print_r($arrOption);
					$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
					
					if(!empty($row['comments'])){
						$arrComments=explode(",", $row['comments']);
						$row['total_comments']=count($arrComments);
					}else{
						$row['total_comments']='0';
					}
					
					if(!empty($arrQuestionId)){
						if(in_array($row['id'],$arrQuestionId)){
							$is_vout=1;
						}else{
							$is_vout=0;
						}
					}else{
						$is_vout=0;
					}
					
					$row['is_vout']=$is_vout;
					$row['created_date']=datetimeToTimestamp($row['created_date']);
					$row['updated_date']=datetimeToTimestamp($row['updated_date']);
					$row['time_limit']=datetimeToTimestamp($row['time_limit']);
					if(!empty($arrOption)){
						$arrOption['option_image']="http://".$serverhost."/image_util.php?src=".$arrOption['option_image'];
						$arrOption['created_date']=datetimeToTimestamp($arrOption['created_date']);
						$arrOption['updated_date']=datetimeToTimestamp($arrOption['updated_date']);
						$row['options']=$arrOption;
						
					}
					unset($row['theOptions']);
					unset($row['is_private']);
					unset($row['target_id']);
					$arrQuestios[]=$row;
				}
				$dataGetQuestion['status']="1";
				if(!empty($arrQuestios)){
					$dataGetQuestion['data']=$arrQuestios;
				}else{
					$dataGetQuestion['data']=array();
				}
			}else{
				$dataGetQuestion['status']="0";
				$dataGetQuestion['message']="[Question] ".mysql_error();
			}
		}else{
			$dataGetQuestion['status']="1";
			$dataGetQuestion['data']=array();
		}
	}else{
		$dataGetQuestion['status']="0";
		$dataGetQuestion['message']="[Activity] ".mysql_error();
	}
	return json_encode($dataGetQuestion);
}
?>