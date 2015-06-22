<?php
if(!$is_api_call)die('X');

// --------------------------------------------------
// CONVERSATION -Handling starts
// --------------------------------------------------


if($con>0)
{
  $GLOBALS['context']['_con']=dbs::singlerow("SELECT 
  n_u,
  con
  FROM {$GLOBALS['pre']}con 
  WHERE id='{$con}'");

  if(count($GLOBALS['context']['_con'])<1)
  {
     msg('error-unauthorized','invalid login','given conversation-is is not valid');
  }
  
  if($GLOBALS['context']['_con']['con']=='uu')
  {
    //this conversatiom is user-to-user
    //check if user is allowed to add a stetement hier
    if(!$GLOBALS['login'])
    {
	msg('error-unauthorized','invalid login','no user logged in to access a uu-conversation');
    }
    
    $uu_u=dbs::singlerow("SELECT 
      id,
      is_accepted
      FROM {$GLOBALS['pre']}con_uu_u 
      WHERE id_con={$con} 
      AND n_u='{$GLOBALS['context']['n-u']}'");
      
    if(count($uu_u)>0)
    {
      if($uu_u['is_accepted'])
      {
	//ok
	$okCon=1;
      }
      else
      {
	msg('error-unauthorized','invalid login','user not accepted in uu-conversation');
      }
    }
    else
    {
      // missing row
      // call file to create a valid entry in con_uu_u and rework g-cache if needed
      include 'php/api_create_con_uu_u.php';
    }
  }
  else
  {
    //this conversatiom is user-to-system 
    //check if user is creator
    
    if($GLOBALS['login'])
    {
     if($GLOBALS['context']['_con']['n_u']!=$GLOBALS['context']['n-u'])
      {
	msg('error-unauthorized','invalid login','user is not allowed at conversation');
      }
      else
      {
       //ok
	$okCon=1;
      }
    }
    else
    {
      // this is only allowed with session, from there the last-conversation-id
      if(strlen($GLOBALS['context']['_con']['n_u'])<1)
      {
	$okCon=1;
      }
      else
      {
	msg('error-unauthorized','invalid login','non-logged in user tries to access uc- or sys-conversation of other user');
      }
    }
  }

  if($okCon)
  {
    $GLOBALS['context']['id-con']=$con;
    $GLOBALS['context']['con']=$GLOBALS['context']['_con']['con'];

    dbs::exec("UPDATE {$GLOBALS['pre']}con SET 
    c_st=c_st+1,
    cl_last=NOW(),
    id_ses={$GLOBALS['context']['id-ses']}");					    
  }
  else
  {
    msg('error-unauthorized','invalid login','conversation/user-check not successful');
  }
}
else
{
  // create con
  $_SESSION['id-con-last']=$GLOBALS['context']['id-con']=dbs::insert("INSERT INTO {$GLOBALS['pre']}con(
  i,
  con,
  n_u,
  cl_start,
  cl_last,
  n_def,
  is_present,
  id_ses) VALUES ( 
  0,
  'uc',
  '{$GLOBALS['context']['n-u']}',
  NOW(),
  NOW(),
  '',
  1,
  {$GLOBALS['context']['id-ses']})");	    
}
 

 
?>