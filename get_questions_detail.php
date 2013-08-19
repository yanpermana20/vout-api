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
		if(!empty($_REQUEST['questionId'])){
			$questionId=$_REQUEST['questionId'];
			$questionDetail=getQuestionDetail($questionId);
			//echo "<pre>";
			echo $questionDetail;
		}
	}else{
		echo $token_cek;
	}
}else{
	$data['status']=0;
	$data['message']="Token or UUID is empty!";
	
	echo json_encode($data);
}

//Tanpa Token

// if(!empty($_GET['questionId'])){
	// $questionId=$_GET['questionId'];
	// $questionDetail=getListQuestion($questionId);
	// //echo "<pre>";
	// echo $questionDetail;
// }
// $questionDeatail=getQuestionDetail(5);
// echo "<pre>";
// print_r($questionDeatail) ;

function getQuestionDetail($questionId){
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	$query="select q.id, q.user_id, q.question, q.location, q.hit_rate, q.vout_count, q.time_limit, q.image_id, q.options_detail, q.comments, q.is_private, q.target_id, q.status, q.created_date, q.updated_date,
	u.first_name, u.last_name, iu.path user_image
	from questions q 
	left join users u on u.id = q.user_id
	left join images i on i.id = q.image_id
	left join images iu on iu.id = u.image_id
	where q.id='".$questionId."'";
	
	$result=mysql_query($query);
	if($result){
		$data=mysql_fetch_assoc($result);
		$hit_rate=$data['hit_rate'] + 1;
		$data['hit_rate']=$hit_rate;
		//Update HIT RATE QUESTION
		$query_update_question="update questions set hit_rate=".$hit_rate.",updated_date=now() where id='".$questionId."'";
		$result_update_question=mysql_query($query_update_question);
		if($result_update_question){
			//GET OPTIONS
			//echo "<pre>";
			$theOptions=json_decode($data['options_detail']);
			//print_r($theOptions);
			foreach ($theOptions as $option) {
				$optionsId[]=$option->option_id;
			}
			
			//print_r($optionsId);exit;
			foreach ($optionsId as $option) {
				$option="'".$option."'";
				$listOptions=$listOptions.",".$option;
			}
			$listOptions = substr($listOptions, 1);
				$query2="select o.id,o.title,o.description,i.id as image_id, i.path as option_image, o.created_date, o.updated_date from options o 
				left join images i on i.id = o.image_id
				where o.id in(".$listOptions.")";
				$result2=mysql_query($query2);
				
				if($result2){
					
					while($row = mysql_fetch_assoc($result2)){
						if(!empty($row['option_image'])){
							$row['option_image']="http://".$serverhost."/image_util.php?src=".$row['option_image']."&q=100";
						}else{
							$row['option_image']="";
						}
						
						$row['created_date']=datetimeToTimestamp($row['created_date']);
						$row['updated_date']=datetimeToTimestamp($row['updated_date']);
						$optionList[]=$row;
				  	}
				  	$arrOptionsDetail=json_decode($data['options_detail'],true);
						
					$num=0;
					foreach ($optionList as $options) {
						foreach ($arrOptionsDetail as $optionsDeatail) {
							if($options['id']==$optionsDeatail['option_id']){
								$optionList[$num]['hit_rate']=$optionsDeatail['hit_rate'];
								$optionList[$num]['view_rate']=$optionsDeatail['view_rate'];
								$optionList[$num]['weight']=$optionsDeatail['weight'];
								break;
							}
						}
						$num++;
					}
				  	$data['options']=$optionList;
					
					$rowNumber=0;
					foreach ($commentList as $comment) {
						if(!empty($comment['image_path'])){
							$commentList[$rowNumber]['image_path']="http://".$serverhost."/".$comment['image_path']."&q=100";
						}else{
							$commentList[$rowNumber]['image_path']="";
						}
						
						$rowNumber++;
					}
					if(!empty($data['user_image'])){
						$data['user_image']="http://".$serverhost."/image_util.php?src=".$data['user_image']."&q=100";
					}else{
						$data['user_image']=="";
					}
					$data['time_limit']=datetimeToTimestamp($data['time_limit']);
					$data['created_date']=datetimeToTimestamp($data['created_date']);
					$data['updated_date']=datetimeToTimestamp($data['updated_date']);
					
					unset($data['options_detail']);
					
					$dataQuestionDetail['status']="1";
					$dataQuestionDetail['data']=$data;
					
				}else{
					$dataQuestionDetail['status']="0";
					$dataQuestionDetail['message']="[Option] ".mysql_error();
				}
			
		}else{
			$dataQuestionDetail['status']="0";
			$dataQuestionDetail['message']="[Question-HitRate] ".mysql_error();
		}
	}else{
		$dataQuestionDetail['status']="0";
		$dataQuestionDetail['message']="[Question] ".mysql_error();
	}
	
	return json_encode($dataQuestionDetail);
}

function count_probabilities($arrOptions){
	
	$tHr=0;
	$tVr=0;
	$i=0;
	foreach ($arrOptions as $option) {
		$tHr=$tHr + $option->hit_rate;
		$tVr=$tVr + $option->view_rate;
		$arrOptions[$i]->prob = $option->weight;
		$i++;
	}

	$i=0;
	while ($i < count($arrOptions)){
		if($tHr > 0) {
			$arrOptions[$i]->prob = ($tHr - $arrOptions[$i]->hit_rate) * $arrOptions[$i]->weight;
			
		}
		if($tVr > 0) {
			$arrOptions[$i]->prob = ($tVr - $arrOptions[$i]->view_rate) * $arrOptions[$i]->weight;			
	
		}
		
		$i++;
	}
	return $arrOptions;
}

function weighted_random_simple($values, $weights){ 
    $count = count($values); 
    $i = 0; 
    $n = 0; 
    $num = mt_rand(0, array_sum($weights)); 
    while($i < $count){
        $n += $weights[$i]; 
        if($n >= $num){
            break;
        }
        $i++;
    } 
    $result['value']=$values[$i];
    $result['index']=$i;
    return $result;
}
?>