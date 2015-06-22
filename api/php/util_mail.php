<?php
if (!isset($GLOBALS["indexOk"]) || !$GLOBALS["indexOk"]) die();

$gnmail=0;
function initMail($account="db@ecovillage.org",
									$password="",
									$host="mail.ecovillage.org",
									$fromReply="GEN-IS (Information Service)")
{
	global $gnmail;
	require_once('3p/phpmailer/class.phpmailer.php');

	$gnmail= new PHPMailer();

	$gnmail->IsSMTP(); // telling the class to use SMTP
	$gnmail->SMTPDebug  = 1;                     // enables SMTP debug information (for testing)
																						// 1 = errors and messages
																						// 2 = messages only
	$gnmail->SMTPAuth   = true;                  // enable SMTP authentication
	$gnmail->SMTPSecure = "tls";

	$gnmail->Host       = $host; // sets the SMTP server
	$gnmail->Port       = 587;                    // set the SMTP port for the GMAIL server
	$gnmail->Username   = $account;         // SMTP account username
	$gnmail->Password   = $password;        // SMTP account password

	$gnmail->SetFrom($account, $fromReply);

	$gnmail->AddReplyTo($account,$fromReply);

	$gnmail->ContentType = 'text/plain'; 
	$gnmail->IsHTML(false);
}

//---------------------------------------------------------------
function sendMail($receiver,$subject,$content)
{
	global $gnmail;

	$gnmail->Subject = $subject;
  $gnmail->Body=$content;
	$gnmail->ClearAddresses();
	$gnmail->AddAddress($receiver);

	if(!$gnmail->Send()) 
	{
		echo "Mailer Error to ".$receiver.": " . $receiver->ErrorInfo;
	} 
	usleep(400000);
}


?>