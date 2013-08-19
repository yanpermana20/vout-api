<?php
include 'config.php';
$con=mysql_connect($host,$username,$password);

if($con){
	mysql_select_db($dbname);
	//echo "Database connected";
}else{
	echo "Database not connected";
	exit;
}
?>