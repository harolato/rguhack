<?php
header('Content-Type: application/json');

session_start();
if(isset($_SESSION['ip']) && ($_SESSION['last_post'] + 10 > time()) ) die(json_encode(array("messages" => array("Slow down. " . ($_SESSION['last_post'] + 10 - time()) . 's. to wait.'), "status" => false)));

$_SESSION['last_post'] = time();
$_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];

 
// Clean up the input values
foreach($_POST as $key => $value) {
  if(ini_get('magic_quotes_gpc'))
    $_POST[$key] = stripslashes($_POST[$key]);
 
  $_POST[$key] = htmlspecialchars(strip_tags($_POST[$key]));
}
 
// Assign the input values to variables for easy reference
$name = $_POST["subject"];
$email = $_POST["email"];
$message = $_POST["message"];
 
// Test input values for errors
$errors = array();
if(strlen($name) < 2) {
  if(!$name) {
    $errors[] = "You must enter a subject.";
  } else {
    $errors[] = "Subject must be at least 2 characters.";
  }
}
if(!$email) {
  $errors[] = "You must enter an email.";
} else if(!validEmail($email)) {
  $errors[] = "You must enter a valid email.";
}
if(strlen($message) < 10) {
  if(!$message) {
    $errors[] = "You must enter a message.";
  } else {
    $errors[] = "Message must be at least 10 characters.";
  }
}
 
if($errors) {
	$response;
	$response['messages'] = $errors;
	$response['status'] = false;
  die(json_encode($response));
}
 
// Send the email
$to = "rguhack@gmail.com";
$subject = "$name";
$message = "$message";
$headers = "From: $email";
 
mail($to, $subject, $message, $headers);
 
// Die with a success message
$response;
$response['messages'] = "Your message has been sent!";
$response['status'] = true;
die(json_encode($response));
 
// A function that checks to see if
// an email is valid
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}
 
?>