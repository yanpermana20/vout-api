
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Untitled Document</title>
</head>

<body>
<form name="form_upload" method="post" action="image_upload_util_dev.php" enctype="multipart/form-data">
 User Id : <input name="user_id" type="text" /><br />
 Question : <input name="question" type="text" /><br />
 Picture: <input type="file" name="image" />
 <input type="submit" name="upload" value="Upload" />
</form>
<?php
$today=date("Y-m-d H:i:s");
	$year=date("Y");
	$month=date("m");
	$day=date("d");
	$hour=date("H");
	$minute=date("i");
	$secon=date("s");
	$nextMonth=$month+1;
	$nextMonthDate=$year."-".$nextMonth."-".$day." ".$hour.":".$minute.":".$secon;
	echo $today."<br>";
	echo $nextMonthDate;
?>
</body>
</html>
