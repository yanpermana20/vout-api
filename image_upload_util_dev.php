<?php
include 'db_connect.php';
include 'global_function.php';

//Dengan Token

$theImage=$_FILES['image'];
$upload=upload_image('882398e63033d68b36cc019ac62c0eec',$theImage);
echo $upload;

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