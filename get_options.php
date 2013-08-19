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
		$get_options=get_options($user_info->user_id);
		echo $get_options;
	}else{
		echo $token_cek;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}

//Tanpa Token
// $get_options=get_options('1');
// echo "<pre>";
// print_r($get_options);

function get_options($userId){
	
	$queryGetOptions="select o.id, i.id as image_id, i.source, i.source_url, o.title, o.description, o.created_date, o.updated_date, i.path as option_image from options o 
	left join images i on i.id = o.image_id where o.user_id='".$userId."' order by o.updated_date desc limit 0, 20";
	$resultGetOptions=mysql_query($queryGetOptions);
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	if($resultGetOptions){
		$numOprion=mysql_num_rows($resultGetOptions);
		if($numOprion > 0){
			while($row = mysql_fetch_assoc($resultGetOptions)){
				if(!empty($row['source_url'])){
					$row['option_image']=$row['source_url'];
				}else{
					if(!empty($row['option_image'])){
						$row['option_image']="http://".$serverhost."/image_util.php?src=".$row['option_image'];
					}else{
						$row['option_image']="";
					}
					
				}
				$row['created_date']=datetimeToTimestamp($row['created_date']);
				$row['updated_date']=datetimeToTimestamp($row['updated_date']);
				unset($row['source']);
				unset($row['source_url']);
				unset($row['image_id']);
				$optionList[]=$row;
		  	}
			$data['status']="1";
			$data['data']=$optionList;
		}else{
			$data['status']="1";
			$data['data']=array();
		}	
	}else{
		$data['status']="0";
		$data['message']="[Get Options] ".mysql_error();
	}
	return json_encode($data);
}
?>