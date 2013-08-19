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
	// echo "<pre>";
	// print_r($user_info);
	if($user_info->status == 1){
		$name=$_POST['name'];
		$peopleid=$_REQUEST['people_id'];
		$facebook_id=$_REQUEST['facebook_id'];
		$search_people=search_people($user_info->user_id,$name,$peopleid,$facebook_id);
		echo $search_people;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

//Tanpa Token
// $name=$_REQUEST['name'];
// $peopleid=$_REQUEST['people_id'];
// $search_people=search_people("2","","","");
// echo $search_people;
function search_people($userId,$name,$peopleId,$facebook_id){
	if(!empty($name)){
		$query_get_friends="select friends from users where id='".$userId."'";
		$result_get_friends=mysql_query($query_get_friends);
		if($result_get_friends){
			$data_get_friends=mysql_fetch_assoc($result_get_friends);
			$arrFriends=json_decode($data_get_friends['friends']);
			
			$query_search_user="SELECT u.id, u.email, u.facebook_id, u.first_name, u.last_name, i.path as user_image FROM users u
			left join images i on i.id = u.image_id 
			WHERE MATCH (u.name) AGAINST ('*".$name."*' IN BOOLEAN MODE)";
			$result_search_user=mysql_query($query_search_user);
			
			$serverhost=$_SERVER['HTTP_HOST']."/api";
			if($result_search_user){
				$num_search_user=mysql_num_rows($result_search_user);
				if($num_search_user>0){
					while($row = mysql_fetch_assoc($result_search_user)){
						if($row['id'] != $userId){
							$row['status']="NF";
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
							$arrUser[]=$row;
						}
					}
				}else{
					$arrUser=array();
				}
				$data['status']="1";
				$data['data']=$arrUser;
				
			}else{
				$data['status']="0";
				$data['message']="[Search User] ".mysql_error();
			}
		}else{
			$data['status']="0";
			$data['message']="[User Get Friends] ".mysql_error();
		}
	}
	if(!empty($peopleId)){
		$query_search_user="SELECT u.id, u.email, u.facebook_id, u.first_name, u.last_name, i.path as user_image FROM users u
		left join images i on i.id = u.image_id 
		WHERE u.id='".$peopleId."'";
		$result_search_user=mysql_query($query_search_user);
		$serverhost=$_SERVER['HTTP_HOST'];
		if($result_search_user){
			$num_search_user=mysql_num_rows($result_search_user);
			if($num_search_user>0){
				while($row = mysql_fetch_assoc($result_search_user)){
					if(!empty($row['user_image'])){
						$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
					}else{
						$row['user_image']="";
					}
					$arrUser[]=$row;
				}
			}else{
				$arrUser=array();
			}
			$data['status']="1";
			$data['data']=$arrUser;
		}else{
			$data['status']="0";
			$data['message']="[Search User] ".mysql_error();
		}
	}
	if(!empty($facebook_id)){
		$query_search_user="SELECT u.id, u.email, u.facebook_id, u.first_name, u.last_name, i.path as user_image FROM users u
		left join images i on i.id = u.image_id 
		WHERE u.facebook_id='".$facebook_id."'";
		
		$result_search_user=mysql_query($query_search_user);
		$serverhost=$_SERVER['HTTP_HOST'];
		if($result_search_user){
			$num_search_user=mysql_num_rows($result_search_user);
			if($num_search_user>0){
				while($row = mysql_fetch_assoc($result_search_user)){
					if(!empty($row['user_image'])){
						$row['user_image']="http://".$serverhost."/image_util.php?src=".$row['user_image'];
					}else{
						$row['user_image']="";
					}
					$arrUser[]=$row;
				}
			}else{
				$arrUser=array();
			}
			$data['status']="1";
			$data['data']=$arrUser;
		}else{
			$data['status']="0";
			$data['message']="[Search User] ".mysql_error();
		}
	}
	
	if(empty($arrUser)){
		$arrUser=array();
		$data['status']="1";
		$data['data']=$arrUser;
	}
	return json_encode($data);
}
?>