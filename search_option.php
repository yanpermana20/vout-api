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
		$title=$_POST['title'];
		$category_id=$_POST['category_id'];
		$search_option=search_option($user_info->user_id,$title,$category_id);
		echo $search_option;
	}
}else{
	$data['status']="0";
	$data['message']="Token and UUID is empty";
	
	echo json_encode($data);
}
//Tanpa Token
// $search_option=search_option("3","it");
// echo $search_option;
function search_option($userId,$title,$categoryId){
	$query_search_option="select o.id, i.id as image_id, i.source, i.source_url, o.title, o.description, o.created_date, o.updated_date, i.path as option_image from options o 
	left join images i on i.id = o.image_id 
	where MATCH (o.title) AGAINST ('*".$title."*' IN BOOLEAN MODE)
   	and o.category_id='".$categoryId."'
   	and o.is_private='0'
	union
select o.id, i.id as image_id, i.source, i.source_url, o.title, o.description, o.created_date, o.updated_date, i.path as option_image from options o 
	left join images i on i.id = o.image_id 
	where MATCH (o.title) AGAINST ('*".$title."*' IN BOOLEAN MODE)
	and o.category_id='".$categoryId."'
   	and o.user_id='".$userId."' 
	order by updated_date desc";
	
	$serverhost=$_SERVER['HTTP_HOST']."/api";
	$result_search_option=mysql_query($query_search_option);
	if($result_search_option){
		$num_search_option=mysql_num_rows($result_search_option);
		if($num_search_option>0){
			while($row = mysql_fetch_assoc($result_search_option)){
				if(!empty($row['source_url'])){
					$row['option_image']=$row['source_url'];
				}else{
					$row['option_image']="http://".$serverhost."/image_util.php?src=".$row['option_image'];
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
		$data['message']="[Search Options] ".mysql_error();
	}
	
	return json_encode($data);
}
?>