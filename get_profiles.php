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
		// if(!empty($_POST['user_id'])){
			// $user_id=$_POST['user_id'];
		// }else{
			// $user_id=$user_info->user_id;
		// }
		$people_id=$_POST['people_id'];
		$facebook_id=$_POST['facebook_id'];
		$get_profiles=get_profiles($user_info->user_id,$people_id,$facebook_id);
		echo $get_profiles;
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

// $get_profiles=get_profiles("c68427d49e84385fab961c4b9eeaf51c","","");
// print_r($get_profiles);
function get_profiles($user_id,$people_id,$facebook_id){
	if(!empty($user_id)){
		if(!empty($people_id) || !empty($facebook_id)){
			if($people_id == $user_id){
				$friend_status="";
			}else{
				if(!empty($people_id)){
					$the_people_id=$people_id;
				}
				if(!empty($facebook_id)){
					$query_get_people_id="select id from users where facebook_id='".$facebook_id."'";
					$result_get_people_id=mysql_query($query_get_people_id);
					if($result_get_people_id){
						$data_get_people_id=mysql_fetch_assoc($result_get_people_id);
						$the_people_id=$data_get_people_id['id'];
					}
				}
				$query_get_friends="select friends from users where id='".$user_id."'";
				$result_get_friends=mysql_query($query_get_friends);
				if($result_get_friends){
					$data_get_friends=mysql_fetch_assoc($result_get_friends);
					$jsonFriends=$data_get_friends['friends'];
					$arrFriends=json_decode($jsonFriends);
					foreach ($arrFriends as $friend) {
						if($friend->user_id == $the_people_id){
							$friend_status=$friend->status;
							break;
						}
					}
					if(empty($friend_status)){
						$friend_status="NF";
					}
				}
			}
			
		}else{
			$friend_status="";
			$string_id="id";
			$id=$user_id;
		}	
	}
	if(!empty($people_id)){
		$string_id="id";
		$id=$people_id;
	}
	if(!empty($facebook_id)){
		$string_id="facebook_id";
		$id=$facebook_id;
	}
	
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	$query_user_info="select u.id, u.email, u.email_facebook, u.first_name, u.last_name, u.location, i.path as user_image, i.id as user_image_id, u.created_date, u.updated_date from users u 
		left join images i on i.id = u.image_id 
		where u.".$string_id."='".$id."'";
		
	$result_user_info=mysql_query($query_user_info);
	if($result_user_info){
		$data_user_info=mysql_fetch_assoc($result_user_info);
		if(empty($data_user_info['email'])){
			$data_user_info['email']=$data_user_info['email_facebook'];
		}
		if(empty($data_user_info['location'])){
			$data_user_info['location']="";
		}
		if(empty($data_user_info['first_name'])){
			$data_user_info['first_name']="";
		}
		if(empty($data_user_info['last_name'])){
			$data_user_info['last_name']="";
		}
		$data_user_info['user_image']="http://".$serverhost."/image_util.php?src=".$data_user_info['user_image']."&image_id=".$data_user_info['user_image_id'];
		//if(!empty($friend_status)){
			$data_user_info['status']=$friend_status;
		//}
		$data_user_info['created_date']=datetimeToTimestamp($data_user_info['created_date']);
		$data_user_info['updated_date']=datetimeToTimestamp($data_user_info['updated_date']);
		unset($data_user_info['email_facebook']);
	
		$data['status']="1";
		$data['data']=$data_user_info;
	}else{
		$data['status']="0";
		$data['message']="[Get User Info] ".mysql_error();
	}
	return json_encode($data);
}
?>