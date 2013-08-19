<?php
include 'db_connect.php';
include 'global_function.php';



//Tanpa Token
$myfriends=getListFriends("9e4d9e99f21f30eb2cdf4db95baea947","F,FA,NF,PA,FR");
echo "<pre>";
print_r($myfriends);

	


function getListFriends($userId, $status){
	echo "<pre>";
	if(empty($status)){
		$dataFriends['status']="1";
		$dataFriends['data']=array();
	}else{
		$serverhost=$_SERVER['HTTP_HOST'];
		$arrStatus=explode(",", $status);
		
		$query="select friends from users where id='".$userId."'";
		$result=mysql_query($query);
		$data=mysql_fetch_array($result);
		$arrFriends=  json_decode($data[0]);
		
		print_r($arrFriends);
		print_r($arrStatus);
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
	
	return $dataFriends;
}
?>