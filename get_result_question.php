<?php
include 'db_connect.php';
include 'global_function.php';
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
		if(!empty($_REQUEST['question_id'])){
			$questionId=$_REQUEST['question_id'];
			$get_result_question=get_result_question($questionId);
			//echo "<pre>";
			echo $get_result_question;
		}
	}else{
		echo $token_cek;
	}
}else{
	$data['status']=0;
	$data['message']="Token or UUID is empty!";
	
	echo json_encode($data);
}

//$get_result_question=get_result_question('0b34ba4c21f39f3df8102f34239ea3f7');
function get_result_question($questionId){
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	$query_get_question="select q.id, q.user_id, q.question, q.location, q.hit_rate, q.vout_count, q.time_limit, q.options_detail, q.comments, q.created_date, q.updated_date,
	u.first_name, u.last_name, iu.path user_image
	from questions q 
	left join users u on u.id = q.user_id
	left join images iu on iu.id = u.image_id
	where q.id='".$questionId."'";
	
	$result_get_question=mysql_query($query_get_question);
	
	if($result_get_question){
		$data_get_question=mysql_fetch_assoc($result_get_question);
		$arr_options_detail=json_decode($data_get_question['options_detail']);
		$data_get_question['time_limit']=datetimeToTimestamp($data_get_question['time_limit']);
		$data_get_question['created_date']=datetimeToTimestamp($data_get_question['created_date']);
		$data_get_question['updated_date']=datetimeToTimestamp($data_get_question['updated_date']);
		$data_get_question['user_image']="http://".$serverhost."/image_util.php?src=".$data_get_question['user_image'];
		
		$num=0;
		foreach ($arr_options_detail as $option) {
			$query_get_option="select o.id, o.title, o.description, i.path option_image from options o
			left join images i on i.id = o.image_id 
			where o.id='".$option->option_id."'";
			$result_get_option=mysql_query($query_get_option);
			if($result_get_option){
				$data_get_option=mysql_fetch_assoc($result_get_option);
				$data_get_option['option_image']="http://".$serverhost."/image_util.php?src=".$data_get_option['option_image'];
				$data_get_question['options'][$num]=$data_get_option;
				
			}else{
				$data['status']="0";
				$data['message']="[Option] ".mysql_error();
				
				return json_encode($data);
			}
			$arrHitRate[]=array('id'=>$option->option_id,'hit_rate'=>$option->hit_rate);
			$optionPopularity=($option->hit_rate / $option->view_rate) * 100;
			$data_get_question['options'][$num]['hit_rate']=$option->hit_rate;
			$data_get_question['options'][$num]['view_rate']=$option->view_rate;
			$data_get_question['options'][$num]['weight']=$option->weight;
			$data_get_question['options'][$num]['popularity']=$optionPopularity;
			$data_get_question['options'][$num]['created_date']=datetimeToTimestamp($option->created_date);
			$data_get_question['options'][$num]['updated_date']=datetimeToTimestamp($option->updated_date);
			$num++;
		}
		foreach ($arrHitRate as $param => $row) {
			$id[$param]  = $row['id'];
			$hitRate[$param] = $row['hit_rate'];
		}
		array_multisort($hitRate, SORT_DESC,$arrHitRate);
		$num=0;
		foreach ($data_get_question['options'] as $option) {
			$num_rank=0;
			foreach ($arrHitRate as $hitRate) {
				if($hitRate['id'] == $option['id']){
					$rank=$num_rank + 1;
					break;
				}
				$num_rank++;
			}
			$data_get_question['options'][$num]['rank']=$rank;
			$num++;
		}
		unset($data_get_question['option_detail']);
		unset($data_get_question['comments']);
		
		$data['status']="1";
		$data['data']=$data_get_question;	
	}else{
		$data['status']="0";
		$data['message']="[Question] ".mysql_error();
	}
	return json_encode($data);
}

?>