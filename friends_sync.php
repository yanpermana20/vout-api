<?php
include 'db_connect.php';
include 'global_function.php';
//Dengan Token
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
		// if(!empty($_REQUEST['friends_id'])){
			$friendsId = $_REQUEST['friends_id'];
			$sync=friends_sync($user_info->user_id,$friendsId);
			echo $sync;
		// }else{
			// $data['status']="0";
			// $data['message']="id friends is empty";
// 	
			// echo json_encode($data);
		// }
		
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

// $sync=friends_sync('d35da8b882b4be93da11bf256c76894c','2,3,4,139c2d0b7cac8533672e969605f71b05,c68427d49e84385fab961c4b9eeaf51c,5');
// echo "<pre>";
// print_r($sync);
function friends_sync($user_id, $user_friends){
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	$arrFriendsId=array();
	$arrPostFriend=explode(",", $user_friends);
	$query_get_friends="select friends from users where id='".$user_id."'";
	$result_get_friends=mysql_query($query_get_friends);
	if($result_get_friends){
		$friends=mysql_fetch_assoc($result_get_friends);
		$arrFriends=json_decode($friends['friends']);
		$num=0;
		//print_r($arrFriends);
		foreach ($arrFriends as $friend) {		
			//if($friend->status=="F"){
				$arrFriendsId[$num]=$friend->user_id;
				$num++;
			//}	
		}
		
		$friendsRemove=array_diff($arrPostFriend, $arrFriendsId);
		$friendsNew=array_diff($arrFriendsId, $arrPostFriend);
		
		foreach ($friendsRemove as $friend) {
			
			$friend="'".$friend."'";
			$friend_id_remove = $friend_id_remove.",".$friend;
			
		}
		$friend_id_remove = substr($friend_id_remove, 1);

		foreach ($friendsNew as $friend) {
			
			$friend="'".$friend."'";
			$friend_id_New = $friend_id_New.",".$friend;
			
		}
		$friend_id_New = substr($friend_id_New, 1);
		
		if(!empty($friend_id_remove)){
			$query_friend_remove="select u.id, u.email, u.email_facebook, u.facebook_id, u.first_name, u.last_name, i.path as user_image from users u 
			left join images i on i.id = u.image_id 
			where u.id in(".$friend_id_remove.") order by u.first_name";
			$result_friend_remove=mysql_query($query_friend_remove);

			if($result_friend_remove){
				$row_num_remove=mysql_num_rows($result_friend_remove);

				if($row_num_remove > 0){
					while($row = mysql_fetch_assoc($result_friend_remove)){
						// foreach ($arrFriends as $friend) {		
							// if($friend->user_id==$row['id']){
								// $row['status']=$friend->status;
								// break;
							// }	
						// }
						$row['status']="NF";
						if(!empty($row['user_image'])){
							$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
						}else{
							$row['user_image']="";
						}
						if(empty($row['facebook_id'])){
							$row['facebook_id']="";
						}
						if(empty($row['email'])){
							$row['email']=$row['email_facebook'];
							unset($row['email_facebook']);
						}
						
						$friendList[]=$row;
						
				  	}

				}
			}else{
				$dataFriends['status']="0";
				$dataFriends['message']="[Friends] ".mysql_error();
				
				return $dataFriends;
			}
		}
		if(!empty($friend_id_New)){
			$query_friend_new="select u.id, u.email, u.email_facebook, u.facebook_id, u.first_name, u.last_name, i.path as user_image from users u 
			left join images i on i.id = u.image_id 
			where u.id in(".$friend_id_New.") order by u.first_name";
			$result_friend_new=mysql_query($query_friend_new);
			
			if($result_friend_new){
				$row_num_new=mysql_num_rows($result_friend_new);
				if ($row_num_new > 0){
						while($row = mysql_fetch_assoc($result_friend_new)){
							foreach ($arrFriends as $friend) {		
								if($friend->user_id==$row['id']){
									$row['status']=$friend->status;
									break;
								}	
							}
							
							
							if(!empty($row['user_image'])){
								$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
							}
							if(empty($row['facebook_id'])){
								$row['facebook_id']="";
							}
							if(empty($row['email'])){
								$row['email']=$row['email_facebook'];
								unset($row['email_facebook']);
							}
							
							$friendList[]=$row;
						
				  	}
				}
				
			}else{
				$dataFriends['status']="0";
				$dataFriends['message']="[Friends] ".mysql_error();
				
				return $dataFriends;
			}
		}
		$dataFriends['status']="1";
		if(!empty($friendList)){
			$dataFriends['data']=$friendList;
		}else{
			$dataFriends['data']=array();
		}
		
	}else{
		
		$dataFriends['status']="0";
		$dataFriends['message']="[Friends] ".mysql_error();
	}
	return json_encode($dataFriends);
}
?>