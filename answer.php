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
		$questionId=$_REQUEST['question_id'];
		$optionId=$_REQUEST['option_id'];
		$comment=$_REQUEST['comment'];
		$option_rate=$_REQUEST['option_rate'];
		$answer=answer($user_info->user_id,$questionId,$optionId,$comment,$option_rate);
		// $dataAnswer['status']="1";
		// $dataAnswer['data']['message']="User Id=".$user_info->user_id."; Question Id=".$questionId."; Option Id=".$optionId."; Comment=".$comment;
		echo $answer;
	}else{
		echo $token_cek;
	}
}else{
	$data['status']=0;
	$data['message']="Token or UUID is empty!";
	
	echo json_encode($data);
}


//Tanpa Token
// $comment="answer kok ga bisa2 seh...???";
// $answer=answer(1,2,4,$comment);
// echo "<pre>";
// print_r($answer);

function answer($userId, $questionId, $optionId, $comment, $option_rate){
	//$today = uniqid(date("YmdHis"));
	$comment_id=rand_id();
	$activity_id=rand_id();
	$query_get_user_question_id="select user_id from questions where id='".$questionId."'";
	$result_get_user_question_id=mysql_query($query_get_user_question_id);
	if($result_get_user_question_id){
		$user_question_id=mysql_fetch_assoc($result_get_user_question_id);
		$query_activity="insert into activities (id,user_id,destination_user_id,type,source_id,data,created_date,updated_date)
		values ('".$activity_id."','".$userId."','".$user_question_id['user_id']."','ANSWER','".$questionId."','".$optionId."',now(),now())";
		$result_activity=mysql_query($query_activity);
		
		if($result_activity){
			if(!empty($comment)){
				$activity_id1=rand_id();
				$query_comment="insert into comments (id,user_id,comment,option_id,created_date,updated_date)
				values ('".$comment_id."','".$userId."','".$comment."','".$optionId."',now(),now())";	
				$result_comment=mysql_query($query_comment);
				
				if($result_comment){
					$query_activity1="insert into activities (id,user_id,destination_user_id,type,source_id,data,created_date,updated_date)
					values ('".$activity_id1."','".$userId."','".$user_question_id['user_id']."','COMMENT','".$questionId."','".$comment_id."',now(),now())";
					$result_activity1=mysql_query($query_activity1);
						
					if(!$result_activity1){
						$dataAnswer['status']="0";
						$dataAnswer['message']="[Activity Comment] ".mysql_error();
						return json_encode($dataAnswer);
					}
				}else{
					$dataAnswer['status']="0";
					$dataAnswer['message']="[Coments] ".mysql_error();
					return json_encode($dataAnswer);
				}	
			}
			$query_question="select question, vout_count, hit_rate, options_detail, comments, TIMESTAMPDIFF(MINUTE,now(),time_limit) as time_left from questions where id='".$questionId."'";
			$result_question=mysql_query($query_question);
			$question=mysql_fetch_assoc($result_question);
			
			if($question){
				$arrOptions=json_decode($question['options_detail']);
				$arrOptionRate=json_decode($option_rate,true);
				//echo "<pre>";
				
				$num=0;
				$num1=0;
				foreach ($arrOptions as $option) {
					foreach ($arrOptionRate as $theOptionRate) {
						if($arrOptions[$num]->option_id == $theOptionRate['id']){
							$arrOptions[$num]->hit_rate = $arrOptions[$num]->hit_rate + $theOptionRate['delta_hit_rate'];
							$arrOptions[$num]->view_rate = $arrOptions[$num]->view_rate + $theOptionRate['delta_hit_rate'];
							$optionViewRate=$arrOptions[$num]->view_rate;
							$optionHitRate=$arrOptions[$num]->hit_rate;
							$optionPopularity=($optionHitRate / $optionViewRate) * 100;
							break;
						}
					}
					// if($arrOptions[$num]->option_id == $optionId){
						// $arrOptions[$num]->hit_rate = $arrOptions[$num]->hit_rate + 1;
						// $optionViewRate=$arrOptions[$num]->view_rate;
						// $optionHitRate=$arrOptions[$num]->hit_rate;
						// $optionPopularity=($arrOptions[$num]->hit_rate / $arrOptions[$num]->view_rate) * 100;
						// break;
					// }
					$num++;
				}
				
				$theOptionDetail=json_encode($arrOptions);
				
				//Get Rank
				$num=0;
				foreach ($arrOptions as $option) {
					$arrHitRate[]=array('id'=>$option->option_id,'hit_rate'=>$option->hit_rate);
				}
				
				foreach ($arrHitRate as $param => $row) {
					$id[$param]  = $row['id'];
					$hitRate[$param] = $row['hit_rate'];
				}
				
				array_multisort($hitRate, SORT_DESC,$arrHitRate);
				$num=0;
				foreach ($arrHitRate as $hitRate) {
					if($hitRate['id'] == $optionId){
						$rank=$num + 1;
						break;
					}
					$num++;
				}
				//End get rank
				
				$question_vout_count=$question['vout_count'] + 1;
				if(!empty($question['comments'])){
					$question_comments=$question['comments'].",".$comment_id;
				}else{
					$question_comments=$comment_id;
				}
				
				$query_question_update="update questions set vout_count=".$question_vout_count.", comments='".$question_comments."',
				options_detail='".$theOptionDetail."',updated_date=now() where id='".$questionId."'";
				$result_question_update=mysql_query($query_question_update);
				
				if($result_question_update){
					
					$query_get_option="select title,description from options where id='".$optionId."'";
					$result_get_option=mysql_query($query_get_option);
					if($result_get_option){
						$data_get_option=mysql_fetch_assoc($result_get_option);
						
						$arrComment=explode(",", $question_comments);
						foreach ($arrComment as $comment) {
							$comment="'".$comment."'";
							$commentsId=$commentsId.",".$comment;
						}
						$commentsId = substr($commentsId, 1);
						
						$query_get_comments="select c.id, c.comment, c.option_id, c.user_id, u.first_name, u.last_name, u.image_id, i.path user_image, c.created_date, c.updated_date 
							from comments c 
							left join users u on u.id = c.user_id
							left join images i on u.image_id = i.id
							where c.id in(".$commentsId.") and c.status ='1' order by c.updated_date";
						$result_get_comments=mysql_query($query_get_comments);
						if($result_get_comments){
							$serverhost=$_SERVER['HTTP_HOST']."/api";
							while($row = mysql_fetch_assoc($result_get_comments)){
								$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
								$row['created_date']=datetimeToTimestamp($row['created_date']);
								$row['updated_date']=datetimeToTimestamp($row['updated_date']);
								unset($row['image_id']);
								$commentList[]=$row;
						  	}
							
							$data['message']="Answer success";
							$data['question']['id']=$questionId;
							$data['question']['question']=$question['question'];
							$data['question']['hit_rate']=$question['hit_rate'];
						 	$data['question']['vout_count']=$question_vout_count;
							$data['question']['time_left']=$question['time_left'];
							$data['question']['comments']=$commentList;
							$data['option']['id']=$optionId;
							$data['option']['title']=$data_get_option['title'];
							$data['option']['description']=$data_get_option['description'];
							$data['option']['hit_rate']=$optionHitRate;
							$data['option']['view_rate']=$optionViewRate;
							//$data['option']['popularity']=$optionPopularity;
							$data['option']['popularity']=1;
							$data['option']['rank']=$rank;
							
							$dataAnswer['status']="1";
							$dataAnswer['data']=$data;
						}else{
							$dataAnswer['status']="0";
							$dataAnswer['message']="[Comment Get] ".mysql_error();
						}
					}else{
						$dataAnswer['status']="0";
						$dataAnswer['message']="[Option Get] ".mysql_error();
					}

				}else{
					$dataAnswer['status']="0";
					$dataAnswer['message']="[Questions Update] ".mysql_error();
				}
			}else{
				$dataAnswer['status']="0";
				$dataAnswer['message']="[Questions Get] ".mysql_error();
			}
		}else{
			$dataAnswer['status']="0";
			$dataAnswer['message']="[Activity Answer] ".mysql_error();
		}
	}else{
		$dataAnswer['status']="0";
		$dataAnswer['message']="[Get User Question] ".mysql_error();
	}
	
	
	return json_encode($dataAnswer);
}

?>