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
		$status = $_REQUEST['status'];
		$myfriends=getListFriends($user_info->user_id,$status);
		echo $myfriends;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

//Tanpa Token
// $myfriends=getListFriends(1,"F");
// echo "<pre>";
// print_r($myfriends);

	


function getListFriends($userId, $status){
	if(empty($status)){
		$dataFriends['status']="1";
		$dataFriends['data']=array();
	}else{
		$serverhost=$_SERVER['HTTP_HOST']."/api";
		$arrStatus=explode(",", $status);
		
		$query="select friends from users where id='".$userId."'";
		$result=mysql_query($query);
		$data=mysql_fetch_array($result);
		$arrFriends=  json_decode($data[0]);
		
		foreach ($arrFriends as $friend) {
			// if($friend->status == $status){
				// $friend->user_id="'".$friend->user_id."'";
				// $friend_id = $friend_id.",".$friend->user_id;
			// }
			
			foreach ($arrStatus as $stat) {
				if($friend->status == $stat){
					$friend_user_id="'".$friend->user_id."'";
					$friend_id = $friend_id.",".$friend_user_id;
				}
			}
		}
		$friend_id = substr($friend_id, 1);
		if(!empty($friend_id)){
			$query1="select u.id, u.email, u.email_facebook, u.facebook_id, u.first_name, u.last_name, i.path as user_image from users u 
			left join images i on i.id = u.image_id 
			where u.id in(".$friend_id.") order by u.first_name";
			$result1=mysql_query($query1);
			
			if($result1){
				while($row = mysql_fetch_assoc($result1)){
					foreach ($arrFriends as $friend) {
						if($friend->user_id == $row['id']){
							$row['status']=$friend->status;
							break;
						}
					}
					
					if(!empty($row['user_image'])){
						$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
					}else{
						$row['user_image']="";
					}
					//$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
					if(empty($row['facebook_id'])){
						$row['facebook_id']="";
					}
					if(empty($row['email'])){
						$row['email']=$row['email_facebook'];
						unset($row['email_facebook']);
					}
					
					$friendList[]=$row;
					
			  	}
				
				$dataFriends['status']="1";
				$dataFriends['data']=$friendList;
			}else{
				$dataFriends['status']="0";
				$dataFriends['message']="[Friends] ".mysql_error();
			}
		}else{
			$dataFriends['status']="1";
			$dataFriends['data']=array();
		}
		
	}
	
	return json_encode($dataFriends);
}
?>