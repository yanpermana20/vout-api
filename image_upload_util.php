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
	// echo "<pre>";
	// print_r($user_info);
	if($user_info->status == 1){
		if(!empty($_FILES['image'])){
			$theImage=$_FILES['image'];
			$upload=upload_image($user_info->user_id,$theImage);
			echo $upload;
		}else{
			$data['status']="0";
			$data['message']="Image is empty";
			echo json_encode($data);exit;
		}
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}
// $theImage=$_FILES['image'];
// $upload=upload_image($theImage);
// echo $upload;

function upload_image($user_id,$image){
	if(!empty($image)){
		list($pWidth, $pHeight) = getimagesize($image['tmp_name']);
		$fileName = $image['name'];  
 		$fileSize = $image['size'];
		$fileType = explode("/",$image['type']); 
 		$fileError = $image['error'];
		
		$path="images/";
		if($fileSize > 0 || $fileError == 0){
			$uniqId=uniqid();
			$imageFullPath=$path.$fileName;
			if(file_exists($imageFullPath)){
				$fileName=$uniqId.$fileName;
				$imageFullPath=$path.$fileName;
				
			}
			$move = move_uploaded_file($image['tmp_name'], $imageFullPath);
			if($move){
				$msg= "Upload Image Success to ".$imageFullPath;
				$imageId = rand_id();
				$queryImage="insert into images (id, user_id, name, path, size, dimensions, created_date, updated_date)
				values ('".$imageId."','".$user_id."','".$fileName."','".$imageFullPath."','".$fileSize."','".$pWidth."x".$pHeight."',now(),now())";
				$resultImage=mysql_query($queryImage);
				
				if($resultImage){
					$serverhost=$_SERVER['HTTP_HOST']."/api";
					$data['status']="1";
					$data['data']=array("id" => $imageId, "image_url" => "http://".$serverhost."/image_util.php?src=".$imageFullPath."&image_id=".$imageId);
				}else{
					$data['status']="0";
					$data['message']="[Images Insert] ".mysql_error();
				}
			}else{
				$data['status']="0";
				$data['message']="Upload Failed";
			}
		}else{
			$data['status']="0";
			$data['message']="File Error";
		}
	}else{
		$data['status']="0";
		$data['message']="No Image Found";
	}
	
	return json_encode($data);
}
?>