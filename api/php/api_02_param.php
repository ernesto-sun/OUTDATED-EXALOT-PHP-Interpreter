<?php
if(!$is_api_call)die('X');

// ----------------------------------------------------
// params...

$em=0;
$u='';
$p='';     // password-check will be different using HMAC (of course)
$x='';     // password-check will be different using HMAC (of course)
$con=0;   // get conversation-id


switch($GLOBALS['st']['method'])
{
	case 'get':
		if(isset($_GET['i']))$GLOBALS['st']['i']=(int)$_GET['i'];
		if(isset($_GET['c']))$GLOBALS['st']['c']=(int)$_GET['c'];
		if(isset($_GET['t']))$GLOBALS['st']['t']=validInput($_GET['t']);
		if(isset($_GET['u']))$u=validInput($_GET['u']);
		if(isset($_GET['p']))$p=$_GET['p'];
		if(isset($_GET['x']))$x=trim($_GET['x']); 
		if(isset($_GET['con']))$con=(int)$_GET['con'];
	     break;
	case 'post':
		if(isset($_REQUEST['i']))$GLOBALS['st']['i']=(int)$_REQUEST['i'];
		if(isset($_REQUEST['c']))$GLOBALS['st']['c']=(int)$_REQUEST['c'];
		if(isset($_REQUEST['t']))$GLOBALS['st']['t']=validInput($_REQUEST['t']);
		if(isset($_REQUEST['con']))$con=(int)$_REQUEST['con'];
		
		if(isset($_REQUEST['u']))$u=validInput($_REQUEST['u']);
		else
		{
		  msg('error-unauthorized','invalid login','POST requires a username, param u');
		}

		if(isset($_REQUEST['p']))$p=$_REQUEST['p'];
		$x=trim(file_get_contents('php://input'));

	break;
}



?>