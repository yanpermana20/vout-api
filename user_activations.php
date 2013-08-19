<?php
include 'db_connect.php';
include 'global_function.php';

// $user_id=$_REQUEST['id'];
// $email=$_REQUEST['email'];
// $activation=user_activations($user_id,$email);
// echo $activation;
function user_activations($id,$email){
	$query_cek_user="select id from users where id='".$id."' and email ='".$email."'";
	$result_cek_user=mysql_query($query_cek_user);
	if($result_cek_user){
		$num_user=mysql_num_rows($result_cek_user);
		if($num_user > 0){
			$query_activate_user="update users set status ='1' where id='".$id."'";
			$result_update_user=mysql_query($query_activate_user);
			if($result_update_user){
				$data['status']="1";
				$data['message']="Activation Completed!";
			}else{
				$data['status']="0";
				$data['message']="Activation Failed!". mysql_error();
			}
		}else{
			$data['status']="0";
			$data['message']="Activation Failed Id Not Found";
		}
	}else{
		$data['status']="0";
		$data['message']="Activation Failed!". mysql_error();
	}
	return json_encode($data);
}
?>