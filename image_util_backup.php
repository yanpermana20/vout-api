<?php
include 'db_connect.php';
// $image_id=$_REQUEST['image_id'];
// $image_width=$_REQUEST['image_width'];
// $image_crop=$_REQUEST['image_crop'];
// $query_image="select path from images where id='".$image_id."'";
// $result_image=mysql_query($query_image);
// 
// if($result_image){
	// $data_image=mysql_fetch_assoc($result_image);
// }else{
	// echo mysql_error();exit;
// }
$facebook_id=$_REQUEST['facebook_id'];
$user_id=$_REQUEST['user_id'];
if(!empty($user_id)){
	$string_id="id";
	$id=$user_id;
}
if(!empty($facebook_id)){
	$string_id="facebook_id";
	$id=$facebook_id;
}

$serverhost=$_SERVER['HTTP_HOST'];
$query_get_image="select i.path from users u
left join images i on i.id = u.image_id
where u.".$string_id."='".$id."'";

$result_get_image=mysql_query($query_get_image);
if($result_get_image){
	$image=mysql_fetch_assoc($result_get_image);
	if(!empty($image['path'])){
		//$image_url="http://".$serverhost."/image_util.php?src=".$image['path'];
		$image_url="http://".$serverhost."/".$image['path'];
	}else{
		$image_url="";
	}
	
}else{
	echo mysql_error();exit;
}
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


//$canvas = imagecreatetruecolor($lebar_canvas, $lebar_canvas);
if($ext=="png"){
	$current_image = imagecreatefrompng($filename);
}else if($ext=="jpeg"){
	$current_image = imagecreatefromjpeg($filename);
}else if($ext=="jpg"){
	$current_image = imagecreatefromjpeg($filename);
}else{
	$current_image = imagecreatefromgif($filename);
}

if($image_crop==1){
	$canvas = imagecreatetruecolor($lebar_canvas, $lebar_canvas);
	if($lebar_asli > $tinggi_asli){
		$tinggi_crop = $lebar_canvas;
		$x = round(($lebar_asli/2) - ($tinggi_asli/2));
		if($tinggi_asli > $tinggi_crop){
			$temp=$tinggi_asli / $tinggi_crop;
			$lebar_crop=round($lebar_asli / $temp);
		}else{
			$temp=$tinggi_crop / $tinggi_asli;
			$lebar_crop=round($lebar_asli * $temp);
		}
		imagecopyresized($canvas, $current_image, 0, 0, $x, 0, $lebar_crop, $tinggi_crop, $lebar_asli, $tinggi_asli);
	}else{	
		$lebar_crop = $lebar_canvas;
		$y = round(($tinggi_asli/2) - ($lebar_asli/2));
		if($lebar_asli > $lebar_crop){
			$temp=$lebar_asli / $lebar_crop;
			$tinggi_crop=round($tinggi_asli / $temp);
		}else{
			$temp=$lebar_crop / $lebar_asli;
			$tinggi_crop=round($tinggi_asli * $temp);
			
		}
		imagecopyresized($canvas, $current_image, 0, 0, 0, $y, $lebar_crop, $tinggi_crop, $lebar_asli, $tinggi_asli);
	}
}else{
	if($lebar_asli < $lebar_canvas){
		$temp= $lebar_canvas / $lebar_asli;
		$tinggi_canvas = round($tinggi_asli * $temp);
		$canvas = imagecreatetruecolor($lebar_canvas, $tinggi_canvas);
	    
	}else{
		$temp = $lebar_asli / $lebar_canvas;
		$tinggi_canvas = round($tinggi_asli / $temp);
		$canvas = imagecreatetruecolor($lebar_canvas, $tinggi_canvas);
	}

	imagecopyresized($canvas, $current_image, 0, 0, 0, 0, $lebar_canvas, $tinggi_canvas, $lebar_asli, $tinggi_asli);
}

header('Content-type: image/jpeg');

imagejpeg($canvas);

imagedestroy($canvas);

?>