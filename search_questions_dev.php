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
	// // print_r($user_info);exit;
	// if($user_info->status == 1){
		// // $totalRow=$_POST['total_row'];
		// // $page=$_POST['page'];
		// $question=$_POST['question'];
		// $search_question=search_questions($question);
		// echo $search_question;
	// }else{
		// echo $token_cek;
	// }
// }else{
	// $data['status']="0";
	// $data['message']="Token and UUID is empty";
// 	
	// echo json_encode($data);
// }
//$search_question=search_questions("#enak","2,3,0");
$search_question=search_questions("#enak","2,3");
echo "<pre>";
print_r($search_question);
function search_questions($name,$category){
	$serverhost=$_SERVER['HTTP_HOST'];
	
	if(!empty($category)){
		$arrCategory=explode(",", $category);
	
		$numCategory=count($arrCategory);
		if($numCategory>1){
			foreach ($arrCategory as $cat) {
				$cat="'".$cat."'";
				$extQuery= $extQuery."q.category_id=".$cat." or ";
			}
			$extQuery=substr($extQuery,0,-4);
			
		}else{
			foreach ($arrCategory as $cat) {
				$cat="'".$cat."'";
				$extQuery= "q.category_id=".$cat;
			}
		}
		
		$addedQuery="and (".$extQuery.") ";
	}else{
		$addedQuery="";
	}
	
	
	
	$query_search_question="select q.id, q.user_id, q.question, q.hit_rate, q.vout_count, q.time_limit, q.options options_id, q.location, q.is_private, q.target_id, 
			u.first_name first_name, u.last_name last_name, iu.path user_image, 
			q.created_date, q.updated_date from questions q
			left join users u on u.id = q.user_id
			left join images i on i.id = q.image_id
			left join images iu on iu.id = u.image_id 
			where MATCH (q.question) AGAINST ('*".$name."*' IN BOOLEAN MODE) ".$addedQuery." and q.status='1' and q.time_limit > now() order by q.created_date desc";
	//echo $query_search_question;exit;
	$result_search_question=mysql_query($query_search_question);
	if($result_search_question){
		$num_search_question=mysql_num_rows($result_search_question);
		while($row = mysql_fetch_assoc($result_search_question)){
			$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
			$row['created_date']=datetimeToTimestamp($row['created_date']);
			$row['updated_date']=datetimeToTimestamp($row['updated_date']);
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
			
			$rowNumber=0;
			foreach ($arrQuestios as $question) {
				$options=explode(",", $question['options_id']);
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
				//$arrQuestios[$rowNumber]['user_image']="http://".$serverhost."/image_util.php?src=".$arrQuestios[$rowNumber]['user_image'];
				$arrQuestios[$rowNumber]['options']=$get_options_arr;
				$rowNumber++;
			}
			
		}
		$num=0;
		foreach ($arrQuestios as $question1) {
			unset($arrQuestios[$num]['options_id']);
			unset($arrQuestios[$num]['target_id']);
			$num++;
		}
		$data['status']="1";
		if(!empty($arrQuestios)){
			$data['data']=$arrQuestios;
		}else{
			$data['data']=array();
		}
	}else{
		$dataGetQuestion['status']="0";
		$dataGetQuestion['message']="[Question] ".mysql_error();
	}
	
	return $data;
	
}
?>