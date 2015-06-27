<?php


// -------------------------------------------------------------------------

// EXALOT <http://exalot.com> digital language for all agents
// Copyright (C) 2014-2015 Ing. Ernst Johann Peterec (http://ernesto-sun.com)

// This program is free software: you can redistribute it and/or modify
// it under the terms of the GNU Affero General Public License as
// published by the Free Software Foundation, either version 3 of the
// License, or (at your option) any later version.

// This program is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU Affero General Public License for more details.

// You should have received a copy of the GNU Affero General Public License
// along with this program.  If not, see <http://www.gnu.org/licenses/>.

// @author  Ernesto (eto) <contact@ernesto-sun.com>
// @create  20150112-eto  
// @update  20150618-eto  

// @brief   

// -------------------------------------------------------------------------


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