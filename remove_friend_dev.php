<?php
include 'db_connect.php';
include 'global_function.php';
// $headers = $_SERVER;
// if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	// $token=$headers['HTTP_TOKEN'];
	// $uuid=$headers['HTTP_UUID'];
	// $device=$headers['HTTP_DEVICE'];
	// //echo "token=".$token." , UUID=".$uuid;exit;
	// $token_cek=token_cek($token,$uuid);
	// $user_info=json_decode($token_cek);
	// // echo "<pre>";
	// // print_r($user_info);
	// if($user_info->status == 1){
		// $friend_id=$_REQUEST['friend_id'];
		// $remove_friend=remove_friend($user_info->user_id,$friend_id);
		// echo $remove_friend;
	// }else{
		// echo $token_cek;
	// }
// }else{
	// $data['status']=0;
	// $data['message']="Token or UUID is empty!";
// 	
	// echo json_encode($data);
// }

$remove_friend=remove_friend('9e4d9e99f21f30eb2cdf4db95baea947','11326c63b482425f87053df577c963c8');
print_r($remove_friend);
function remove_friend($user_id,$friend_id){
	$query_get_user_info="select first_name, friends from users where id='".$user_id."'";

	$result_get_user_info=mysql_query($query_get_user_info);
	if($result_get_user_info){
		$data_get_user_info=mysql_fetch_assoc($result_get_user_info);
		if(!empty($data_get_user_info['friends'])){
			$arrUserFriends=json_decode($data_get_user_info['friends'],true);
			$num=0;
			foreach ($arrUserFriends as $userFriend) {
				
				if($userFriend['user_id']==$friend_id){
					unset($arrUserFriends[$num]);
					break;
				}
				$num++;
			}
			$arrUserFriendsNew=array();
			$num=0;
			foreach ($arrUserFriends as $userFriend) {
				$arrUserFriendsNew[$num]=$userFriend;
				$num++;
			}
			//echo json_encode($arrUserFriendsNew);exit;
			$jsonUserFriend=json_encode($arrUserFriendsNew);
			$querySetUserFriend="update users set friends='".$jsonUserFriend."' where id='".$user_id."'";
			$resultSetUserFriend=mysql_query($querySetUserFriend);
			if($resultSetUserFriend){
				$getUserInfoStatus=1;
			}else{
				$getUserInfoStatus=0;
			}
		}else{
			$getUserInfoStatus=0;
		}
	}else{
		$getUserInfoStatus=0;
	}
	
	$query_get_friend_info="select first_name, friends from users where id='".$friend_id."'";

	$result_get_friend_info=mysql_query($query_get_friend_info);
	if($result_get_friend_info){
		$data_get_friend_info=mysql_fetch_assoc($result_get_friend_info);
		if(!empty($data_get_friend_info['friends'])){
			$arrFriendFriends=json_decode($data_get_friend_info['friends'],true);
			$num=0;
			foreach ($arrFriendFriends as $friendFriend) {
				
				if($friendFriend['user_id']==$user_id){
					unset($arrFriendFriends[$num]);
					break;
				}
				$num++;
			}
			$arrFriendFriendsNew=array();
			$num=0;
			foreach ($arrFriendFriends as $friendFriend) {
				$arrFriendFriendsNew[$num]=$friendFriend;
				$num++;
			}
			$jsonUserFriend=json_encode($arrFriendFriendsNew);
			$querySetFriendFriend="update users set friends='".$jsonUserFriend."' where id='".$friend_id."'";
			$resultSetFriendFriend=mysql_query($querySetFriendFriend);
			if($resultSetFriendFriend){
				$getfriendInfoStatus=1;
			}else{
				$getfriendInfoStatus=0;
			}
		}else{
			$getfriendInfoStatus=0;
		}
	}else{
		$getfriendInfoStatus=0;
	}
	
	if($getUserInfoStatus==1 && $getfriendInfoStatus==1){
		$data['status']=1;
	}else{
		$data['status']=0;
	}
	
	return json_encode($data);
}

?>