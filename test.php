<?php
include 'global_function.php';
// require_once "Mail.php";
// $from    = "voutnow@gmail.com";
// $to      = "yan_is_in_da_house@yahoo.co.id";
// $subject = "Hi dari server vout!";
// $body    = "Hi,\n\nHow are you?";
// 
// /* SMTP server name, port, user/passwd */
// $smtpinfo["host"] = "ssl://smtp.gmail.com";
// $smtpinfo["port"] = "465";
// $smtpinfo["auth"] = true;
// $smtpinfo["username"] = "voutnow@gmail.com";
// $smtpinfo["password"] = "voutnow123";
// 
// $headers = array ('From' => $from,'To' => $to,'Subject' => $subject);
// $smtp = &Mail::factory('smtp', $smtpinfo );
// 
// $mail = $smtp->send($to, $headers, $body);
// print_r($mail);
// 
// // if (PEAR::isError($mail)) {
  // // echo("<p>" . $mail->getMessage() . "</p>");
 // // } else {
  // // echo("<p>Message successfully sent!</p>");
 // // }
 
//$sendemail=mail('yan.permana20@gmail.com', 'Subject', 'Your message here.','From: voutnow@gmail.com');
 
$to = 'yan.permana20@gmail.com';
$subject = 'Vout User Activation [No Reply]';
$message = 'http://voutnow.com/activation.php?id='.$user_id.'&email='.$email;
$from = 'voutnow@gmail.com';
$headers = 'From:' . $from;
$sendemail=mail($to,$subject,$message,$headers);
print_r($sendemail);
?>