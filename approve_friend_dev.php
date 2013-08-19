<?php
include 'db_connect.php';
include 'global_function.php';

$approve_friend=approve_friend('9e4d9e99f21f30eb2cdf4db95baea947','0e133398839aaa9c8b0a80eba51c9157','iphone');
print_r($approve_friend);
function approve_friend($user_id,$friend_id,$user_agent){
	$today=date("Y-m-d H:i:s");
	if($user_agent == "android"){
		$device="android_id";
	}
	echo "<pre>";
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
		
		// $query_device_user="SELECT android_id,ios_id FROM token_access WHERE user_id='".$user_id."' and (android_id<>'' or ios_id<>'')";
		// $result_device_user=mysql_query($query_device_user);
		// if($result_device_user){
			// while($row = mysql_fetch_assoc($result_device_user)){
				// if(!empty($row['android_id'])){
					// $arrDeviceUserAndroid[]=$row['android_id'];
				// }
				// if(!empty($row['ios_id'])){
					// $arrDeviceUserIos[]=$row['ios_id'];
				// }
			// }
		// }
		$query_get_friend_info="select first_name, friends from users where id='".$friend_id."'";
		$result_get_friend_info=mysql_query($query_get_friend_info);
		
		if($result_get_friend_info){
			$data_get_friend_info=mysql_fetch_assoc($result_get_friend_info);
			// if($user_agent=="android"){
				// $query_device_friend="select android_id from token_access where user_id='".$friend_id."'";
				// $result_device_friend=mysql_query($query_device_friend);
				// if($result_device_friend){
					// while($row = mysql_fetch_assoc($result_device_friend)){
						// $arrDeviceFriend[]=$row['android_id'];
					// }
				// }
			// }
			
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
			
			if(!empty($data_get_user_info['friends'])){
				
				$arrUserFriend=json_decode($data_get_user_info['friends']);
				//print_r($arrUserFriend);
				$num=0;
				foreach ($arrUserFriend as $friend) {
					if($friend->user_id == $friend_id){
						$arrUserFriend[$num]->status = "F";
						$arrUserFriend[$num]->updated_date = $today;
						break;
					}
					$num++;
				}
			}
			//print_r($arrUserFriend);exit;
			$jsonUserFriend= json_encode($arrUserFriend);
			
			$query_update_user_friend="update users set friends='".$jsonUserFriend."', updated_date=now() where id='".$user_id."'";
			$result_update_user_friend=mysql_query($query_update_user_friend);
			if($result_update_user_friend){
				
				if(!empty($data_get_friend_info['friends'])){
					
					$arrFriendFriend=json_decode($data_get_friend_info['friends']);
					$num=0;
					foreach ($arrFriendFriend as $friend) {
						if($friend->user_id == $user_id){
							$arrFriendFriend[$num]->status = "F";
							$arrFriendFriend[$num]->updated_date = $today;
							break;
						}
						$num++;
					}
				}
				$jsonFriendFriend= json_encode($arrFriendFriend);
				
				$query_update_friend_friend="update users set friends='".$jsonFriendFriend."', updated_date=now() where id='".$friend_id."'";
				$result_update_friend_friend=mysql_query($query_update_friend_friend);
				
				$arrRequestMessage['id_user']=$user_id;
				$arrRequestMessage['message']=$data_get_user_info['first_name']." approve your friend request";
				$pushRequestMessage=json_encode($arrRequestMessage);
				
				if($result_update_friend_friend){
					
					$activityIdApprove = rand_id();
					$queryActivityApprove="insert into activities (id,user_id,destination_user_id,type,created_date,updated_date) 
					values ('".$activityIdApprove."','".$user_id."','".$friend_id."','FRIEND_APPROVE',now(),now())";
					$resultActivityApprove=mysql_query($queryActivityApprove);
					
					$queryUpdateActivityApprove="update activities set status='0' where user_id='".$friend_id."' and destination_user_id='".$user_id."' and type='FRIEND_REQUEST'";
					$resultUpdateActivityApprove=mysql_query($queryUpdateActivityApprove);
					
					if(!empty($arrDeviceFriendAndroid)){
						$pushAndroidResult=android_push_notif($arrDeviceFriendAndroid,"FA",$pushRequestMessage);
						$arrPushAndroidResult=json_decode($pushAndroidResult);
					}
					if(!empty($arrDeviceFriendIos)){
						$pushIosResult=ios_push_notif($arrDeviceFriendIos,"FA",$pushRequestMessage);
					}
					
					
					 
					$data['status']="1";
					$data['data']['message']="add friend success";
					
				}else{
					$data['status']="0";
					$data['message']="[Update Friend Friend]".mysql_error();
				}
			}else{
				echo mysql_error();
				$data['status']="0";
				$data['message']="[Update User Friend]".mysql_error();
			}
		}else{
			$data['status']="0";
			$data['message']="[Get Device Friend]".mysql_error();
		}
	}else{
		$data['status']="0";
		$data['message']="[Get Device User] ". mysql_error();
	}

	return json_encode($data);
}
?>