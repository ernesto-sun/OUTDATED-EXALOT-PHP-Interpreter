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