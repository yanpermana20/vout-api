<?php
include 'db_connect.php';
include 'global_function.php';


//$invite_friend=invite_friend('134b3e68f8ce48f0e9dfe771f51bed00','d35da8b882b4be93da11bf256c76894c','android');
$invite_friend=invite_friend('8216d2b629b515bd6bc6a00eab160987','0c039ccd99ed3a229df3690f812c7e98','iphone');
echo "<pre>";
print_r($invite_friend);

function invite_friend($user_id,$friend_id,$user_agent,$friend_facebook_id){
	$today=date("Y-m-d H:i:s");
	// if($user_agent == "android"){
		// $device="android_id";
	// }
	// if($user_agent == "iphone"){
		// $device="ios_id";
	// }
	$query_get_user_info="select first_name, friends from users where id='".$user_id."'";

	$result_get_user_info=mysql_query($query_get_user_info);
	if($result_get_user_info){
		$data_get_user_info=mysql_fetch_assoc($result_get_user_info);
		// if($user_agent=="android"){
			// $query_device_user="select android_id from token_access where user_id='".$user_id."'";
			// $result_device_user=mysql_query($query_device_user);
			// if($result_device_user){
				// while($row = mysql_fetch_assoc($result_device_user)){
					// $arrDeviceUser[]=$row['android_id'];
				// }
			// }
		// }
		if(!empty($friend_id)){
			$target="id";
			$target_id=$friend_id;
		}
		if(!empty($friend_facebook_id)){
			$target="facebook_id";
			$target_id=$friend_facebook_id;
		}
		$query_get_friend_info="select id, first_name, friends from users where ".$target."='".$target_id."'";
		$result_get_friend_info=mysql_query($query_get_friend_info);
		
		if($result_get_friend_info){
			$data_get_friend_info=mysql_fetch_assoc($result_get_friend_info);
			$friend_id=$data_get_friend_info['id'];
			
			$query_device_friend="SELECT DISTINCT android_id,ios_id FROM token_access WHERE user_id='".$friend_id."' and (android_id<>'' or ios_id<>'')";
			$result_device_friend=mysql_query($query_device_friend);
			if($result_device_friend){
				while($row = mysql_fetch_assoc($result_device_friend)){
					if(!empty($row['android_id'])){
						$arrDeviceFriendAndroid[]=$row['android_id'];
					}
					if(!empty($row['ios_id'])){
						$arrDeviceFriendIos[]=$row['ios_id'];
					}
				}
			}
		
			$newFriendId=rand_id();
			
			$arrFriend['id']=$newFriendId;
			$arrFriend['user_id']=$friend_id;
			$arrFriend['status']="PA";
			$arrFriend['created_date']=$today;
			$arrFriend['updated_date']=$today;
			
			if(empty($data_get_user_info['friends'])){
				$arrUserFriend[0]=$arrFriend;
			}else{
				$arrUserFriend=json_decode($data_get_user_info['friends'],true);
				array_push($arrUserFriend, $arrFriend);
			}
			
			$jsonUserFriend= json_encode($arrUserFriend);
			
			$query_update_user_friend="update users set friends='".$jsonUserFriend."', updated_date=now() where id='".$user_id."'";
			$result_update_user_friend=mysql_query($query_update_user_friend);
			if($result_update_user_friend){
					
				$newFriendId=rand_id();
				
				$arrFriend['id']=$newFriendId;
				$arrFriend['user_id']=$user_id;
				$arrFriend['status']="FR";
				$arrFriend['created_date']=$today;
				$arrFriend['updated_date']=$today;
				
				if(empty($data_get_friend_info['friends'])){
					$arrFriendFriend[0]=$arrFriend;
				}else{
					$arrFriendFriend=json_decode($data_get_friend_info['friends'],true);
					array_push($arrFriendFriend, $arrFriend);
					
				}
				
				$jsonFriendFriend= json_encode($arrFriendFriend);
				
				$query_update_friend_friend="update users set friends='".$jsonFriendFriend."', updated_date=now() where id='".$friend_id."'";
				$result_update_friend_friend=mysql_query($query_update_friend_friend);
				
				$arrRequestMessage['id_user']=$user_id;
				$arrRequestMessage['message']=$data_get_user_info['first_name']." wants to be your friend";
				$pushRequestMessage=json_encode($arrRequestMessage);
				
				if($result_update_friend_friend){
					
					$activityIdInvite = rand_id();
					$queryActivityInvite="insert into activities (id,user_id,destination_user_id,type,created_date,updated_date) 
					values ('".$activityIdInvite."','".$user_id."','".$friend_id."','FRIEND_REQUEST',now(),now())";
					$resultActivityInvite=mysql_query($queryActivityInvite);
					
					if(!empty($arrDeviceFriendAndroid)){
						$pushAndroidResult=android_push_notif($arrDeviceFriendAndroid,"FR",$pushRequestMessage);
						$arrPushAndroidResult=json_decode($pushAndroidResult);
					}
					if(!empty($arrDeviceFriendIos)){
						$pushIosResult=ios_push_notif($arrDeviceFriendIos,"FR",$pushRequestMessage);
					}
			
					$data['status']="1";
					$data['data']['message']="add friend success";
					//$data['data']['push_status']=$arrPushRequestResult;

				}else{
					$data['status']=0;
					$data['message']="[Update Friend Friend]".mysql_error();
				}
			}else{
				$data['status']=0;
				$data['message']="[Update User Friend]".mysql_error();
			}
		}else{
			$data['status']=0;
			$data['message']="[Get Device Friend]".mysql_error();
		}
	}else{
		$data['status']=0;
		$data['message']="[Get Device User] ". mysql_error();
	}

	return json_encode($data);
}
?>