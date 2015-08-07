<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api.php is the only entry-point to the PHP-version of EXALOT server
 *  it supports various formats, e.g. 'Native EXALOT' and RESTful
 * 
 *  @see <http://exalot.com>
 *  
 *  @author  Ernesto Sun <contact@ernesto-sun.com>
 *  @version 20150112-eto
 *  @since 20150112-eto
 * 
 *  @copyright (C) 2014-2015 Ing. Ernst Johann Peterec <http://ernesto-sun.com>
 *  @license AGPL 3 <http://www.gnu.org/licenses/agpl.txt>
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


ob_start();

$is_api_call=1;
$GLOBALS['is_api_call']=1;

// ---------------------------------------------------
include('php/_auto/global.php');
// ---------------------------------------------------

// ---------------------------------------------------
$GLOBALS['debug']=file_exists('./debug.php');  
// ---------------------------------------------------

include 'php/util.php';
include('php/util_error.php');

// --------------------------------------------------- DB-CONNECTION
include 'config.php';

$GLOBALS['db'] = new mysqli($GLOBALS['conf']['mysql-host'], 
		   $GLOBALS['conf']['mysql-user'],
		   $GLOBALS['conf']['mysql-pwd'], 
		   $GLOBALS['conf']['mysql-db'],
		   $GLOBALS['conf']['mysql-port']);
if ($GLOBALS['db']->connect_error) 
{

    msg('error-internal',
	'Sorry, a database error occurred.',
	'db-connect failed: '.$GLOBALS['db']->connect_error);
}		

unset($GLOBALS['conf']['mysql-pwd']);   // DB-Password isn't needed any more 
$GLOBALS['pre']=$GLOBALS['conf']['mysql-prefix']; //faster access


$GLOBALS['db']->query('SET NAMES \'utf8\'');

include 'php/util_db.php';

// ---------------------------------------------------


// ---------------------------------------------------
if($GLOBALS['debug'])
{
	if(isset($_GET['daemon']))
	{
		include 'php/daemon.php';

		if($_GET['daemon']=='daemon_create_me')daemon_create_me();
		if($_GET['daemon']=='daemon_create_lang')daemon_create_lang();
		die('Daemon done!');
	}
}
// ---------------------------------------------------


$header=apache_request_headers();

// HTTP-method

$GLOBALS['st']['method']=trim(strtolower($_SERVER['REQUEST_METHOD']));
switch($GLOBALS['st']['method'])
{
	case 'get':
	case 'post':
		break;
	default:
	      msg('error-http-method','only GET and POST are supported for now');
}


include './php/api_01_header.php';

include './php/api_02_param.php';

include './php/api_03_login.php';


if(!$GLOBALS['login'])$con=0; // overwrite conversation if not loggedin 

$max_c=$GLOBALS['u-level']['max-sub-c'];
if($GLOBALS['st']['c']>$max_c)$GLOBALS['st']['c']=$max_c;
if($GLOBALS['st']['c']<1)$GLOBALS['st']['c']=$GLOBALS['u-level']['default-sub-c'];

if($GLOBALS['st']['i']<1)$GLOBALS['st']['i']=0;  // index smaller 0 does not make sense


if(strlen($GLOBALS['st']['t']))
{
  // validate theme
}
else
{
  $GLOBALS['st']['t']=$GLOBALS['conf']['default-theme'][$GLOBALS['st']['accept']]; 
}

include './php/api_04_syntax.php';


$x=validDB($x); // !! this is important here to make cache search valid SQL, must be after parsing
$x_len=strlen($x); // !! and renewal of lenth 

include './php/api_05_cache.php';

include './php/api_06_session.php';

if(!$GLOBALS['login'])
{
   $con=$_SESSION['id-con-last'];
   $GLOBALS['n-u']='';
}

include './php/api_07_semantic.php';

include './php/api_08_con.php';

include './php/api_09_st.php';


// TODO: Disable that...
if($GLOBALS['debug']&&false) 
{
  db_exec('DELETE FROM exa_e WHERE id>1');
  db_exec('DELETE FROM exa_sub WHERE id>1');
  db_exec('TRUNCATE exa_sup');
  db_exec('TRUNCATE exa_subl');
  db_exec('TRUNCATE exa_tx');
  db_exec('TRUNCATE exa_x');
}
  
include './php/api_10_in.php';
      
      
debug_dump_all();
     
      
						
// -----------------------------------------------------------
// Writing result to user
// -----------------------------------------------------------


if(isset($_GET['jsoncallback']))
{
  echo $_GET['jsoncallback'], '(',$resultStr,')';
}
else
{
  echo $resultStr;
}

http_response_code($_RES[0]['state']);

header('Content-Length:'. ob_get_length());  // security not to have cut the output
ob_end_flush();


exit;



