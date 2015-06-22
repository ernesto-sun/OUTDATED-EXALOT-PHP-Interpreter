<?php
if(!$is_api_call)die('X');

// --------------------------------------------------
// USER-Login starts
// --------------------------------------------------

$GLOBALS['context']['is-exalot']=0;

$GLOBALS['st']['is-public']=0;
if(strlen($u))
{
   $GLOBALS['context']['_u']=dbs::singlerow("SELECT 
   n_u,
   pwd,
   is_allowed,
   n_u_level AS u_level
   FROM {$GLOBALS['pre']}u 
   WHERE uid='{$u}'");
  
   if(isset($GLOBALS['context']['_u']['is_allowed']))
   {
      if($GLOBALS['context']['_u']['is_allowed'])
      {
	if ($GLOBALS['context']['_u']['pwd']==$p)
	{
	   $GLOBALS['context']['n-u']=$GLOBALS['context']['_u']['n_u'];
	   if(isset($GLOBALS['u-level'][$GLOBALS['context']['_u']['u_level']]))
	   {
	      $GLOBALS['context']['u-level']=&$GLOBALS['u-level'][$GLOBALS['context']['_u']['u_level']];
	      $GLOBALS['login']=1;
	      
	      if($GLOBALS['context']['n-u']==$GLOBALS['conf']['n-u-exalot'])
	      {
	         $GLOBALS['context']['is-exalot']=1;
	      }
	   }
	   else
	   {
	      msg('error-internal','the user-level could not be determined: '.$GLOBALS['context']['_u']['u_level']);
	   }
 	}
	else
	{
	  msg('error-unauthorized','invalid login','password invalid');
	}
      }
      else
      {
	msg('error-unauthorized','invalid login','user is not allowed');
      }
   }
   else
   {
     msg('error-unauthorized','invalid login','username unknown');
   }
}
else
{
  $GLOBALS['context']['u-level']=&$GLOBALS['u-level']['anonymous'];
  $GLOBALS['st']['is-public']=1;
}




?>