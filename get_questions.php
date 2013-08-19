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
		$myQuestions=getListQuestion($user_info->user_id,$totalRow,$page);
		//echo "<pre>";
		echo $myQuestions;
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

//Tanpa Token
// if(!empty($_GET['userId']) ){
	// $userId=$_GET['userId'];
	// $myQuestions=getListQuestion($userId);
	// //echo "<pre>";
	// echo $myQuestions;
// 
// }


// $myQuestions=getListQuestion('d35da8b882b4be93da11bf256c76894c');
// echo "<pre>";
// print_r($myQuestions);

function getListQuestion($userId,$totalRow,$page){
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
	
	$query="select friends from users where id='".$userId."'";
	$result=mysql_query($query);
	if($result){
		$data=mysql_fetch_assoc($result);
		$arrFriends=  json_decode($data['friends']);
	
		foreach ($arrFriends as $friend) {
			if($friend->status=="F"){
				$friend->user_id = "'".$friend->user_id."'";
				$friendId = $friendId.",".$friend->user_id;
			}
		}
		$friendId = substr($friendId, 1);
		if(empty($friendId)){
			$questionCreatorId="'".$userId."'";
		}else{
			$questionCreatorId=$friendId.",'".$userId."'";
		}
		
		$query1="select source_id from activities where user_id in(".$questionCreatorId.") and type='QUESTION'";
		$result1=mysql_query($query1);
		
		if($result1){
			$numActivity=mysql_num_rows($result1);
			
			if($numActivity > 0){
				while($row = mysql_fetch_assoc($result1)){
					$row['source_id']="'".$row['source_id']."'";
					$questionId=$questionId.",".$row['source_id'];
				}
				
				$questionId = substr($questionId, 1);
				
				$query2="select q.id, q.user_id, q.question, q.hit_rate, q.vout_count, q.time_limit, q.options, q.location, q.is_private, q.target_id, 
						u.first_name first_name, u.last_name last_name, iu.path user_image, 
						q.created_date, q.updated_date from questions q
						left join users u on u.id = q.user_id
						left join images i on i.id = q.image_id
						left join images iu on iu.id = u.image_id 
						where q.id in(".$questionId.") and q.status='1' and time_limit > now() order by q.created_date desc limit ".$offset.", ".$rowsPerPage;
				$result2=mysql_query($query2);
				//echo $query2;exit;
				if($result2){
					while($row = mysql_fetch_assoc($result2)){
							
						$row['created_date']=datetimeToTimestamp($row['created_date']);
						$row['updated_date']=datetimeToTimestamp($row['updated_date']);
						if(!empty($row['time_limit'])){
							if(strtotime($row['time_limit']) > strtotime(date('Y-m-d H:i:s'))){
								
								$row['time_limit']=datetimeToTimestamp($row['time_limit']);	
					
								if($row['is_private']==1){
									$targetId=explode(",", $row['target_id']);
									foreach ($targetId as $target) {
										if($target == $userId){
											$arrQuestios[]=$row;
										}
									}
									if($row['user_id']==$userId){
										$arrQuestios[]=$row;
									}
								}else{
									$arrQuestios[]=$row;
								}
							}
						}else{
							if($row['is_private']==1){
								$targetId=explode(",", $row['target_id']);
								foreach ($targetId as $target) {
									if($target == $userId){
										$arrQuestios[]=$row;
									}
								}
								if($row['user_id']==$userId){
									$arrQuestios[]=$row;
								}
							}else{
								$arrQuestios[]=$row;
							}
						}	
					}
					$serverhost=$_SERVER['HTTP_HOST']."/api";
					//$root=explode("/", $_SERVER['PHP_SELF']);
					
					$rowNumber=0;
					foreach ($arrQuestios as $question) {
						$options=explode(",", $question['options']);
						$optionRand=array_rand($options, 2);
	
						$theOptions="";
						foreach ($optionRand as $optionR) {
							$option="'".$options[$optionR]."'";
							$theOptions=$theOptions.",".$option;
						}
						$theOptions = substr($theOptions, 1);
						
						$query_get_option="select o.id, o.title, o.description, i.id image_id, i.path image_path, o.created_date, o.updated_date from options o 
						left join images i on o.image_id = i.id where o.id in(".$theOptions.") and o.status='1'";
						
						$result_get_options=mysql_query($query_get_option);
						$get_options_arr="";
						while($row = mysql_fetch_assoc($result_get_options)){
							$row1['id']=$row['id'];
							$row1['title']=$row['title'];
							$row1['description']=$row['description'];
							if(!empty($row['image_path'])){
								$row1['option_image']="http://".$serverhost."/image_util.php?src=".$row['image_path'];
							}else{
								$row1['option_image']="";
							}
							$row1['created_date']=datetimeToTimestamp($row['created_date']);
							$row1['updated_date']=datetimeToTimestamp($row['updated_date']);
							$get_options_arr[]=$row1;
							
						}
						
						$nextUrl="http://".$serverhost."/get_questions_detail.php?questionId=".$question['id'];
						$arrQuestios[$rowNumber]['url']=stripslashes($nextUrl);
						if(!empty($arrQuestios[$rowNumber]['user_image'])){
							$arrQuestios[$rowNumber]['user_image']="http://".$serverhost."/image_util.php?src=".$question['user_image'];
						}else{
							$arrQuestios[$rowNumber]['user_image']=="";
						}
						
		
						$arrQuestios[$rowNumber]['options']=$get_options_arr;
						$rowNumber++;
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
	}else{
		$dataGetQuestion['status']="0";
		$dataGetQuestion['message']="[Friends] ".mysql_error();
	}
	
	return json_encode($dataGetQuestion);
}
?>