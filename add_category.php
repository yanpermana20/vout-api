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
	// // print_r($user_info);
	// if($user_info->status == 1){
		// $name=$_REQUEST['name'];
		// $add_category=add_category($user_info->user_id,$name);
		// echo $add_category;
	// }else{
		// echo $token_cek;
	// }
// }

//Tanpa Token
$category=$_POST['category'];
$add_category=add_category($category);
echo $add_category;

function add_category($name){
	$category_id=rand_id();
	
	$query_category="insert into categories (id,name,created_date,updated_date) values
	('".$category_id."','".$name."',now(),now())";
	$result_category=mysql_query($query_category);
	if($result_category){
		$data['status']="1";
		$data['id']=$category_id;
		$data['name']=$name;
	}else{
		$data['status']="0";
		$data['message']="[Category] ".mysql_error();
	}
	
	return json_encode($data);
}
?>