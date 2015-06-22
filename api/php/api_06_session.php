<?php
if(!$is_api_call)die('X');

// --------------------------------------------------
// SESSION-Handling starts
// --------------------------------------------------

$createNewCon=0;

session_start();

if (isset($_SESSION['id-ses']))
{
  if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ip'])
  {	
	session_destroy();
	msg('error-unauthorized','invalid login','ATTENTION: ip-address has changed');
  }
  dbs::exec("UPDATE {$GLOBALS['pre']}ses 
  SET cl_lastrequest=NOW(),
  id_con_last={$_SESSION['id-con-last']} 
  WHERE id={$_SESSION['id-ses']}");
}
else
{
  $host=validInput(gethostbyaddr($_SERVER['REMOTE_ADDR']));
  $agent=validInput($_SERVER['HTTP_USER_AGENT']);
  $sessionid=validInput(session_id());

  $sessionID = dbs::insert("INSERT INTO {$GLOBALS['pre']}ses
  (cl_cr,
  cl_lastrequest,
  ip,
  host,
  agent,
  sessionid,
  n_u_agent,
  is_allowed,
  n_u) VALUES 
  (NOW(),
  NOW(),
  '{$_SERVER['REMOTE_ADDR']}',
  '{$host}',
  '{$agent}',
  '{$sessionid}',
  '',
  1,
  '{$GLOBALS['context']['n-u']}')");
  
  if ($sessionID < 1)
  {
	session_destroy();
	msg('error-unauthorized','invalid login','ATTENTION: session could not be created');
  }
  
  $_SESSION['id-ses'] = $sessionID;
  $_SESSION['ip'] = $_SERVER['REMOTE_ADDR'];
  $_SESSION['id-con-last']=0;
  $createNewCon=1;
}

$GLOBALS['context']['id-ses']=$_SESSION['id-ses'];


?>