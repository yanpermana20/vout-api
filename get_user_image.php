<?php
include 'db_connect.php';
include 'global_function.php';

// $headers = $_SERVER;
// if(!empty($headers['HTTP_TOKEN']) && !empty($headers['HTTP_UUID'])){
	// $token=$headers['HTTP_TOKEN'];
	// $uuid=$headers['HTTP_UUID'];
	// $token_cek=token_cek($token,$uuid);
	// $user_info=json_decode($token_cek);
// 
	// if($user_info->status == 1){
		// $facebook_id=$_REQUEST['facebook_id'];
		// $user_id=$_REQUEST['user_id'];
		// $image_path=get_image_url($user_id,$facebook_id);
		// echo "<img src='".$image_path."'>";
	// }else{
		// echo $token_cek;
	// }
// }else{
	// $data['status']="0";
	// $data['message']="Token and UUID is empty";
// 	
	// echo json_encode($data);
// }
$facebook_id=$_REQUEST['facebook_id'];
$user_id=$_REQUEST['user_id'];
$height = $_REQUEST['h'];
$width = $_REQUEST['w'];
if(!empty($user_id)){
	$string_id="id";
	$id=$user_id;
}
if(!empty($facebook_id)){
	$string_id="facebook_id";
	$id=$facebook_id;
}

$serverhost=$_SERVER['HTTP_HOST']."/api";
$query_get_image="select i.path from users u
left join images i on i.id = u.image_id
where u.".$string_id."='".$id."'";

$result_get_image=mysql_query($query_get_image);
if($result_get_image){
	$image=mysql_fetch_assoc($result_get_image);
	if(!empty($image['path'])){
		$image_url="http://".$serverhost."/image_util.php?w=".$width."&h=".$height."&src=".$image['path'];
		//$image_url="http://".$serverhost."/".$image['path'];
	}else{
		$image_url="";
	}
	
}else{
	echo mysql_error();exit;
}
//echo $image_url;exit;
// Gambar aslinya
$filename = $image_url;
$ext = pathinfo($filename, PATHINFO_EXTENSION);

// ambil ukuran asli image
list($lebar_asli, $tinggi_asli) = getimagesize($filename);

//ukuran thumbnail
if(!empty($image_width)){
	$lebar_canvas=$image_width;
}else{
	$lebar_canvas = $lebar_asli; 
}

if($ext=="png"){
	$current_image = imagecreatefrompng($filename);
}else if($ext=="jpeg"){
	$current_image = imagecreatefromjpeg($filename);
}else if($ext=="jpg"){
	$current_image = imagecreatefromjpeg($filename);
}else{
	$current_image = imagecreatefromgif($filename);
}
$canvas = imagecreatetruecolor($lebar_asli, $tinggi_asli);
imagecopyresized($canvas, $current_image, 0, 0, 0, 0, $lebar_asli, $tinggi_asli, $lebar_asli, $tinggi_asli);


header('Content-type: image/jpeg',100);

imagejpeg($canvas);

imagedestroy($canvas);
?>