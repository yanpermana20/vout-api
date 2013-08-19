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
		if(!empty($_POST['first_name']) && !empty($_POST['last_name'])){
			$first_name=$_POST['first_name'];
			$last_name=$_POST['last_name'];
			$location=$_POST['location'];
			$image_url=$_POST['image_url'];
			$image_id=$_POST['image_id'];

			$set_profile=set_profiles($user_info->user_id,$first_name,$last_name,$location,$image_url,$image_id);
			echo $set_profile;
		}else{
			$data['status']="0";
			$data['message']="First Name or Last Name is Empty";
			echo json_encode($data);exit;
		}
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}
// $set_profile=set_profiles("1","Bento","Ganteng","Jakarta",'http://localhost/image_util.php?image_id=3c6aa76eb95c37b0086771dd17b950a4&cuk=123');
// echo $set_profile;
function set_profiles($user_id,$first_name,$last_name,$location,$image_url,$image_id){
	//image_id special case for iphone
	$update_string="";
	if(!empty($first_name)){
		$update_string=$update_string.",first_name='".$first_name."' ";
	} 
	if(!empty($last_name)){
		$update_string=$update_string.",last_name='".$last_name."' ";
	}
	if(!empty($location)){
		$update_string=$update_string.",location='".$location."' ";
	}
	if(!empty($image_url)){
		$arrUrl=parse_url($image_url);
		parse_str(urldecode($arrUrl['query']), $arrUrl['query']);
		$imageId=$arrUrl['query']['image_id'];
		$update_string=$update_string.",image_id='".$imageId."' ";
	}
	if(!empty($image_id)){
		$update_string=$update_string.",image_id='".$image_id."' ";
	}
	$update_string = substr($update_string, 1);
	
	$query_set_profile="update users set ".$update_string.",updated_date=now(), first_login='0' where id='".$user_id."'";
	
	$result_set_profile=mysql_query($query_set_profile);
	
	if($result_set_profile){
		$query_get_user_name="select first_name, middle_name, last_name from users where id='".$user_id."'";
		$result_get_user_name=mysql_query($query_get_user_name);
		if($result_get_user_name){
			$data_get_user_name=mysql_fetch_assoc($result_get_user_name);
			if(!empty($data_get_user_name['first_name'])){
				$name=$name.$data_get_user_name['first_name'];
			}
			if(!empty($data_get_user_name['middle_name'])){
				$name=$name." ".$data_get_user_name['middle_name'];
			}
			if(!empty($data_get_user_name['last_name'])){
				$name=$name." ".$data_get_user_name['last_name'];
			}
			if(!empty($name)){
				$query_update_user_name="update users set name='".$name."', first_login='0' where id='".$user_id."'";
				$result_update_user_name=mysql_query($query_update_user_name);
				if($result_update_user_name){
					$data['status']="1";
					$data['data']['message']="Set Profile Success";
				}
			}else{
				$data['status']="1";
				$data['data']['message']="Set Profile Success";
			}
		}else{
			$data['status']="0";
			$data['message']="[Get Profile Name] ".mysql_error();
		}
	}else{
		$data['status']="0";
		$data['message']="[Set Profile] ".mysql_error();
	}
	
	
	return json_encode($data);
}
?>