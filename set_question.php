<?php
include 'db_connect.php';
include 'global_function.php';

//Dengan Token
$headers = $_SERVER;
if(!empty($headers['HTTP_ACCESS_TOKEN']) && !empty($headers['HTTP_UUID'])){
	$token=$headers['HTTP_ACCESS_TOKEN'];
	$uuid=$headers['HTTP_UUID'];
	//echo "token=".$token." , UUID=".$uuid;exit;
	$token_cek=token_cek($token,$uuid);
	$user_info=json_decode($token_cek);
	// echo "<pre>";
	// print_r($user_info);
	if($user_info->status == 1){
		if(!empty($_POST['user_id']) && $_POST['question']){
			$theImage=$_FILES['image'];
			$userId=$_POST['user_id'];
			$question=$_POST['question'];
			$timeLimit=$_POST['time_limit'];
			$location=$_POST['location'];
			
			$setQusetion=setQuestion($user_info->user_id,$question,$image_path,$theImage,$timeLimit,$location);
			echo $setQusetion;
		}
	}else{
		echo $token_cek;
	}
}

//Tanpa Token
// if(!empty($_POST['user_id']) && $_POST['question']){
	// $theImage=$_FILES['image'];
	// $userId=$_POST['user_id'];
	// $question=$_POST['question'];
	// $timeLimit=$_POST['time_limit'];
	// $location=$_POST['location'];
// 	
	// $setQusetion=setQuestion($userId,$question,$image_path,$theImage,$timeLimit,$location);
	// echo $setQusetion;
// }


// echo "<pre>";
// print_r (setQuestion("aa","apa namanya?",$imageku,$image_path,"2012-11-10 11:12:01","-6.24579,106.803345"));



function setQuestion($userId, $question, $image, $path, $timeLimit, $location){
	
	if(!empty($image)){
		
		list($pWidth, $pHeight) = getimagesize($image['tmp_name']);
		$fileName = $image['name'];  
 		$fileSize = $image['size'];
		$fileType = explode("/",$image['type']); 
 		$fileError = $image['error'];
		
		if($fileSize > 0 || $fileError == 0){
			$uniqId=uniqid();
			$imageFullPath=$path.$fileName;
			if(file_exists($imageFullPath)){
				$imageFullPath=$path.$uniqId.$fileName;
				$fileName=$uniqId.$fileName;
			}
			$move = move_uploaded_file($_FILES['picture']['tmp_name'], $imageFullPath);
			if($move){  
 				$msg= "File sudah diupload to ".$imageFullPath;
				$imageId = rand_id();
				$queryImage="insert into images (id, user_id, name, path, size, dimensions, created_date, updated_date)
				values ('".$imageId."','".$userId."','".$fileName."','".$imageFullPath."','".$fileSize."','".$pWidth."x".$pHeight."',now(),now())";
				$resultImage=mysql_query($queryImage);
				if($resultImage){
					$questionId=rand_id();
					$timeLimit=date("Y-m-d H:i:s", $timeLimit);
					if(empty($timeLimit)){
						$queryQuestion="insert into questions (id,user_id,question,location,image_id,created_date,updated_date)
						values ('".$questionId."','".$userId."','".$question."','".$location."','".$imageId."',now(),now())";						
					}
					if(empty($location)){
						$queryQuestion="insert into questions (id,user_id,question,time_limit,image_id,created_date,updated_date)
						values ('".$questionId."','".$userId."','".$question."','".$timeLimit."','".$imageId."',now(),now())";	
					}
					if(empty($timeLimit) && empty($location)){
						$queryQuestion="insert into questions (id,user_id,question,image_id,created_date,updated_date)
						values ('".$questionId."','".$userId."','".$question."','".$imageId."',now(),now())";
					}
					if(!empty($timeLimit) && !empty($location)){
						$queryQuestion="insert into questions (id,user_id,question,location,time_limit,image_id,created_date,updated_date)
						values ('".$questionId."','".$userId."','".$question."','".$location."','".$timeLimit."','".$imageId."',now(),now())";	
					}
					
					$resultQuestion=mysql_query($queryQuestion);
					
					if($resultQuestion){
						$activityId = rand_id();
						$queryActivity = "insert into activities (id,user_id,type,source_id,created_date,updated_date) 
						values ('".$activityId."','".$userId."','QUESTION','".$questionId."',now(),now())";
						
						$resultActivity=mysql_query($queryActivity);
						
						if($resultQuestion){
							$querySelectQuestion="select * from questions where id='".$questionId."'";
							$resultSelectQuestion=mysql_query($querySelectQuestion);
							$dataSelectQuestion=mysql_fetch_assoc($resultSelectQuestion);
							$data=$dataSelectQuestion;
						}else{
							$data = array('error' => mysql_error());
						}
					}else{
						$data = array('error' => mysql_error());
					}
				}
 			}else{  
 				$data = array('error' => 'Upload pict failed!');
 			}  
		}
	}else{
		$questionId=rand_id();
		$timeLimit=date("Y-m-d H:i:s", $timeLimit);
		if(empty($timeLimit)){
			$queryQuestion="insert into questions (id,user_id,question,location,created_date,updated_date)
			values ('".$questionId."','".$userId."','".$question."','".$location."',now(),now())";						
		}
		if(empty($location)){
			$queryQuestion="insert into questions (id,user_id,question,time_limit,created_date,updated_date)
			values ('".$questionId."','".$userId."','".$question."','".$timeLimit."',now(),now())";	
		}
		if(empty($timeLimit) && empty($location)){
			$queryQuestion="insert into questions (id,user_id,question,created_date,updated_date)
			values ('".$questionId."','".$userId."','".$question."',now(),now())";
		}
		if(!empty($timeLimit) && !empty($location)){
			$queryQuestion="insert into questions (id,user_id,question,location,time_limit,created_date,updated_date)
			values ('".$questionId."','".$userId."','".$question."','".$location."','".$timeLimit."',now(),now())";	
		}
		
		$resultQuestion=mysql_query($queryQuestion);
		
		if($resultQuestion){
			$activityId = rand_id();
			$queryActivity = "insert into activities (id,user_id,type,source_id,created_date,updated_date) 
			values ('".$activityId."','".$userId."','QUESTION','".$questionId."',now(),now())";
			
			$resultActivity=mysql_query($queryActivity);
			
			if($resultQuestion){
				$querySelectQuestion="select * from questions where id='".$questionId."'";
				$resultSelectQuestion=mysql_query($querySelectQuestion);
				$dataSelectQuestion=mysql_fetch_assoc($resultSelectQuestion);
				$data=$dataSelectQuestion;
			}else{
				$data = array('error' => mysql_error());
			}
		}else{
			$data = array('error' => mysql_error());
		}
	}
	return json_encode($data);
}
?>