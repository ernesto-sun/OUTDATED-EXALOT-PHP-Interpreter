<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_08_con.php keeps conversations together. If at api_02_param.php a 
 *  con-id is given, checks are performed and the statement is assigned
 *  to the correct conversation. 
 * 
 *  @see <http://exalot.com>
 *  
 *  @author  Ernesto Sun <contact@ernesto-sun.com>
 *  @version 20150112-eto
 *  @since 20150112-eto
 * 
 *  @copyright (C) 2014-2015 Ing. Ernst Johann Peterec <http://ernesto-sun.com>
 *  @license AGPL <http://www.gnu.org/licenses/agpl.txt>
 *
 *  EXALOT is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU Affero General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  EXALOT is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU Affero General Public License for more details.
 *
 *  You should have received a copy of the GNU Affero General Public License
 *  along with EXALOT. If not, see <http://www.gnu.org/licenses/agpl.txt>.
 *
 */


/**
 *
*/




if(!$is_api_call)die('X');

// --------------------------------------------------
// CONVERSATION -Handling starts
// --------------------------------------------------


if($con>0)
{
  $GLOBALS['_con']=singlerow("SELECT 
  n_u,
  con
  FROM {$GLOBALS['pre']}con 
  WHERE id='{$con}'");

  if(count($GLOBALS['_con'])<1)
  {
     msg('error-unauthorized','invalid login','given conversation-id is not valid');
  }
  
  if($GLOBALS['_con']['con']=='uu')
  {
    //this conversatiom is user-to-user
    //check if user is allowed to add a stetement hier
    if(!$GLOBALS['login'])
    {
	msg('error-unauthorized','invalid login','no user logged in to access a uu-conversation');
    }
    
    $uu_u=singlerow("SELECT 
      id,
      is_accepted
      FROM {$GLOBALS['pre']}con_uu_u 
      WHERE id_con={$con} 
      AND n_u='{$GLOBALS['n-u']}'");
      
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
     if($GLOBALS['_con']['n_u']!=$GLOBALS['n-u'])
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
      if(strlen($GLOBALS['_con']['n_u'])<1)
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
    $GLOBALS['id-con']=$con;
    $GLOBALS['con']=$GLOBALS['_con']['con'];

    db_exec("UPDATE {$GLOBALS['pre']}con SET 
    c_st=c_st+1,
    cl_last=NOW()
    WHERE id={$con}");					    
  }
  else
  {
    msg('error-unauthorized','invalid login','conversation/user-check not successful');
  }
}
else
{
  // create con
  $_SESSION['id-con-last']=$GLOBALS['id-con']=insert("INSERT INTO {$GLOBALS['pre']}con(
  i,
  con,
  n_u,
  cl_start,
  cl_last,
  n_def,
  is_present,
  c_st,
  id_ses) VALUES ( 
  0,
  'uc',
  '{$GLOBALS['n-u']}',
  NOW(),
  NOW(),
  '',
  1,
  1,
  {$GLOBALS['id-ses']})");	    
}
 

 
