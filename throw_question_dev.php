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
	// // echo "<pre>";
	// // print_r($user_info);
	// if($user_info->status == 1){
		// if(!empty($_REQUEST['question']) && !empty($_REQUEST['options'])){
			// $question=$_REQUEST['question'];
			// $timeLimit=$_REQUEST['time_limit'];
			// $location=$_REQUEST['location'];
			// $options=$_REQUEST['options'];
			// $targets=$_REQUEST['targets'];
			// $category_id=$_REQUEST['category_id'];
			// $throwQusetion=throwQuestion($user_info->user_id,$question,$timeLimit,$location,$options,$targets,$category_id);
			// echo $throwQusetion;
		// }else{
			// $data['status']="0";
			// $data['message']="Question or Options empty | Question=".$_REQUEST['question'].",Options=".$_REQUEST['options'].", targets:".$_REQUEST['targets'];
// 	
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
// echo "<pre>";
$throwQuestion=throwQuestion("af52c2a9a687ee79f9898700c146be61",'apa namanya?',"1355237241","","1,2");
echo $throwQuestion;

function throwQuestion($userId, $question, $timeLimit, $location, $options, $targets, $category_id){
	$arrOption = explode(",",$options);
	$numOption = count($arrOption);
	if($numOption >= 2){
		if(empty($timeLimit)){
			$timeLimit=NULL;
		}else{
			$timeLimit=date("Y-m-d H:i:s", strtotime ("+".$timeLimit." hour"));
		}	
		if(empty($location)){
			$location=NULL;
		}
		$is_private=0;
		if(!empty($targets)){
			$is_private=1;
		}else{
			$is_private=0;
		}
		
		$num=0;
		foreach ($arrOption as $option) {
			$arrOptiondetail[$num]['id']=$num;
			$arrOptiondetail[$num]['option_id']=$option;
			$arrOptiondetail[$num]['hit_rate']=0;
			$arrOptiondetail[$num]['view_rate']=0;
			$arrOptiondetail[$num]['weight']=1;
			$arrOptiondetail[$num]['created_date']=date("Y-m-d H:i:s");
			$arrOptiondetail[$num]['updated_date']=date("Y-m-d H:i:s");
			$num++;
		}
		$theOptionDetail = json_encode($arrOptiondetail);
		$questionId=rand_id();
		
		//Escape String
		$question = mysql_real_escape_string($question);
						
		$queryQuestion="insert into questions (id,user_id,question,location,time_limit,category_id,options,options_detail,is_private,target_id,created_date,updated_date)
		values ('".$questionId."','".$userId."','".$question."','".$location."','".$timeLimit."','".$category_id."','".$options."','".$theOptionDetail."','".$is_private."','".$targets."',now(),now())";
		
		$resultQuestion=mysql_query($queryQuestion);
		
		if($resultQuestion){
			foreach ($arrOption as $option) {
				$option="'".$option."'";
				$listOption=$listOption.",".$option;
			}
			$listOption = substr($listOption, 1);
			
			$activityId = rand_id();
			$queryActivity = "insert into activities (id,user_id,type,source_id,created_date,updated_date) 
			values ('".$activityId."','".$userId."','QUESTION','".$questionId."',now(),now())";
			
			$resultActivity=mysql_query($queryActivity);
			if($resultActivity){
				if(!empty($targets)){
					
					$arrTargets=explode(",", $targets);
					foreach ($arrTargets as $target) {
						$activityIdTarget = rand_id();
						$queryActivityTarget="insert into activities (id,user_id,destination_user_id,type,source_id,created_date,updated_date) 
						values ('".$activityIdTarget."','".$userId."','".$target."','TAGGED','".$questionId."',now(),now())";
						$resultActivityTarget=mysql_query($queryActivityTarget);
						
						$target="'".$target."'";
						$listTarget=$listTarget.",".$target;

					}
					$listTarget = substr($listTarget, 1);
					$query_device_friend="SELECT DISTINCT android_id,ios_id FROM token_access WHERE user_id in(".$listTarget.") and (android_id<>'' or ios_id<>'')";
					$result_device_friend=mysql_query($query_device_friend);
					if($result_device_friend){
						while($row = mysql_fetch_assoc($result_device_friend)){
							if(!empty($row['android_id'])){
								$arrDeviceFriendAndroid[]=$row['android_id'];
							}
							if(!empty($row['ios_id'])){
								$arrDeviceFriendIos[]=$row['ios_id'];
							}
						}
					}
					$arrPushMessage['id_user']=$user_id;
					$arrPushMessage['question_id']=$questionId;
					$arrPushMessage['message']="TAGGED you";
					$pushMessage=json_encode($arrPushMessage);
					
					if(!empty($arrDeviceFriendAndroid)){
						$pushAndroidResult=android_push_notif($arrDeviceFriendAndroid,"FA",$pushMessage);
						$arrPushAndroidResult=json_decode($pushAndroidResult);
					}
					if(!empty($arrDeviceFriendIos)){
						$pushIosResult=ios_push_notif($arrDeviceFriendIos,"FA",$pushRequestMessage);
					}
				}
				$querySelectQuestion="select id,user_id,question,location,created_date,updated_date from questions where id='".$questionId."'";
				$resultSelectQuestion=mysql_query($querySelectQuestion);
				$dataSelectQuestion=mysql_fetch_assoc($resultSelectQuestion);
				$dataSelectQuestion['created_date']=datetimeToTimestamp($dataSelectQuestion['created_date']);
				$dataSelectQuestion['updated_date']=datetimeToTimestamp($dataSelectQuestion['updated_date']);
				$data['status']="1";
				$data['data']=$dataSelectQuestion;
			}else{
				$data['status']="0";
				$data['message']="[Activity] ".mysql_error();
			}
		}else{
			$data['status']="0";
			$data['message']="[Question] ".mysql_error();
		}
	}else{
		$data['status']="0";
		$data['message']="Options less than 2";
	}
	
	return json_encode($data);
}
?>