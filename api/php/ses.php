<?php
if (!isset($GLOBALS["indexOk"]) || !$GLOBALS["indexOk"]) die();

session_start();

if (isset($_SESSION['id_ses']))
{
  if ($_SERVER['REMOTE_ADDR'] != $_SESSION['ip'])
  {	
      
  }
  dbs::query("UPDATE ".dbs::p()."ses SET cl_lastrequest=NOW() WHERE id=".$_SESSION['id_ses']);
}
else
{
  $sessionID = dbs::insert("INSERT INTO ".dbs::p()."ses(cl_cr,cl_lastrequest,ip,host,agent,sessionid) VALUES (NOW(),NOW(),'".validInput($_SERVER['REMOTE_ADDR'])."','".validInput(gethostbyaddr($_SERVER['REMOTE_ADDR']))."','".validInput($_SERVER['HTTP_USER_AGENT'])."', '".validInput(session_id())."')");
					  
  if ($sessionID < 1)
  {
	  session_destroy();
	  reloadAndDie();
  }

  $_SESSION['id_ses'] = $sessionID;
  $_SESSION['ip'] = $_SERVER['REMOTE_ADDR']; 
}

?>