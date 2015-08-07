<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_06_session.php saves the session-metadata optionally, even if
 *  EXALOT-calls are stateless  
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
  db_exec("UPDATE {$GLOBALS['pre']}ses 
  SET cl_lastrequest=NOW(),
  id_con_last={$_SESSION['id-con-last']} 
  WHERE id={$_SESSION['id-ses']}");
}
else
{
  $host=validInput(gethostbyaddr($_SERVER['REMOTE_ADDR']));
  $agent=validInput($_SERVER['HTTP_USER_AGENT']);
  $sessionid=validInput(session_id());

  $sessionID = insert("INSERT INTO {$GLOBALS['pre']}ses
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
  '{$GLOBALS['n-u']}')");
  
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

$GLOBALS['id-ses']=$_SESSION['id-ses'];


