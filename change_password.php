<?php
include 'db_connect.php';
include 'global_function.php';
// if(!empty($_REQUEST['user_id']) && !empty($_REQUEST['password'])){
// 	
	// $user_id=$_REQUEST['user_id'];
	// $password=$_REQUEST['password'];
	// $change_password=change_password($user_id, $password);
	// echo $change_password;
// }else{
	// echo "User Id or Password is Empty";
// }
function change_password($user_id, $new_password){
	
	$query_change_password="update users set password=md5('".$new_password."') where id='".$user_id."'";
	$result_change_password=mysql_query($query_change_password);
	if($result_change_password){
		$data['status']="1";
		$data['message']="Change Password Success";
	}else{
		$data['status']="0";
		$data['message']="Change Password Failed!". mysql_error();
	}
	
	return json_encode($data);
}
?>