<?php
include 'db_connect.php';
include 'global_function.php';

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
		$category=get_categories();
		echo $category;
		
	}else{
		echo $token_cek;
	}
}else{
	$data['status']=0;
	$data['message']="Token or UUID is empty!";
	
	echo json_encode($data);
}


function get_categories(){
	$query_get_categories="select * from categories";
	$result_get_categories=mysql_query($query_get_categories);
	
	if($result_get_categories){
		while($row = mysql_fetch_assoc($result_get_categories)){
			$categoriList[]=$row;
		}
		$data['status']="1";
		$data['data']=$categoriList;
	}else{
		$data['status']="0";
		$data['message']="[Get Category] ".mysql_error();
	}
	
	return json_encode($data);
}
?>