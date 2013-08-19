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

	if($user_info->status == 1){
		if(!empty($_POST['title']) && !empty($_POST['image_url'])){
			$title=$_POST['title'];
			$description=$_POST['description'];
			$image_url=$_REQUEST['image_url'];
			$image_source=$_REQUEST['image_source'];
			$category_id=$_REQUEST['category_id'];
			$addOptions=add_options($user_info->user_id,$title,$description,$image_url,$image_source,$category_id);
			echo $addOptions;
		}else{
			$data['status']="0";
			$data['message']="Title or image is empty";
	
			echo json_encode($data);
		}
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

// Tanpa Token
// $theImage=$_FILES['image'];
// //echo "<pre>";print_r($theImage);exit;
// $addOptions=add_options("1","test option pagi","blablabla test Option",'http://localhost/image_util.php?image_id=3c6aa76eb95c37b0086771dd17b950a4&cuk=123',"local");
// echo "<pre>";
// print_r ($addOptions);

function add_options($userId, $title, $description, $image_url, $image_source, $category_id){

	if(!empty($image_source)){
		$imageId = rand_id();
		$query_image="insert into images (id,user_id,source,source_url,created_date,updated_date) values
		('".$imageId."','".$userId."','".$image_source."','".$image_url."',now(),now())";
		$result_image=mysql_query($query_image);
		
		if(!$result_image){
			$data['status']="0";
			$data['message']="[Images] ".mysql_error();
			
			return json_encode($data);
		}
	}else{
		// $arrUrl=parse_url($image_url);
		// parse_str(urldecode($arrUrl['query']), $arrUrl['query']);
// 		
		// $imageId=$arrUrl['query']['image_id'];
	}
	if(!empty($imageId)){
		$optionId = rand_id();
		$queryOption="insert into options (id,user_id,title,description,category_id,image_id,created_date,updated_date)
		values ('".$optionId."','".$userId."','".$title."','".$description."','".$category_id."','".$imageId."',now(),now())";
		$resultOption=mysql_query($queryOption);
		
		if($resultOption){
			$querySelectOption="select id,created_date,updated_date from options where id='".$optionId."'";
			$resultSelectOption=mysql_query($querySelectOption);
			$dataSelectOption=mysql_fetch_assoc($resultSelectOption);
			$dataSelectOption['created_date']=datetimeToTimestamp($dataSelectOption['created_date']);
			$dataSelectOption['updated_date']=datetimeToTimestamp($dataSelectOption['updated_date']);
			$data['status']="1";
			$data['data']=$dataSelectOption;
		}else{
			$data['status']="0";
			$data['message']="[Select Option] ".mysql_error();
		}
	}else{
		$data['status']="0";
		$data['message']="[Image Url not complete] ".$image_url;
	}
	
			
	return json_encode($data);
}
?>