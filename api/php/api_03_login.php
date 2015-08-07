<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_03_login.php performs the login-checks
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
// USER-Login starts
// --------------------------------------------------

$GLOBALS['is-exalot']=0;

$GLOBALS['st']['is-public']=0;
if(strlen($u))
{
   $GLOBALS['_u']=singlerow("SELECT 
   n_u,
   pwd,
   is_allowed,
   n_u_level AS u_level
   FROM {$GLOBALS['pre']}u 
   WHERE uid='{$u}'");
  
   if(isset($GLOBALS['_u']['is_allowed']))
   {
      if($GLOBALS['_u']['is_allowed'])
      {
	if ($GLOBALS['_u']['pwd']==$p)
	{
	   $GLOBALS['n-u']=$GLOBALS['_u']['n_u'];
	   if(isset($GLOBALS['u-level'][$GLOBALS['_u']['u_level']]))
	   {
	      $GLOBALS['u-level']=&$GLOBALS['u-level'][$GLOBALS['_u']['u_level']];
	      $GLOBALS['login']=1;
	      
	      if($GLOBALS['n-u']==$GLOBALS['conf']['n-u-exalot'])
	      {
	         $GLOBALS['is-exalot']=1;
	      }
	   }
	   else
	   {
	      msg('error-internal','the user-level could not be determined: '.$GLOBALS['_u']['u_level']);
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
  $GLOBALS['u-level']=&$GLOBALS['u-level']['anonymous'];
  $GLOBALS['st']['is-public']=1;
}



