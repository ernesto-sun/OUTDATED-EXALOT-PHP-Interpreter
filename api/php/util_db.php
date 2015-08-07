<?php

/**
 *  EXALOT digital language for all agents
 *
 *  util_db.php contains some DB-helper-functions
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

	
//---------------------------------------------------------------------------------
function query($sql)
{
	$r = $GLOBALS['db']->query($sql);
	if ($r===false) 
	{
	  if($GLOBALS['debug'])echo 'SQL-Query: ',$sql,'<br/>MYSQL: ',$GLOBALS['db']->error;
	  msg('error-internal',
	      'Sorry, a database error occurred.',
	      'query failed: '.substr($sql,0,80).' MYSQL: '.$GLOBALS['db']->error);
	}
	return $r;
}


//---------------------------------------------------------------------------------
function query_array($sql)
{
	$r = query($sql);
	$arr=$r->fetch_all(MYSQLI_ASSOC);
	$r->close();
	return $arr;
}

//---------------------------------------------------------------------------------
function idlist($sql,$index_id='id')
{
	$r=array();
	$res = query($sql);
	while ($d = $res->fetch_assoc()) 
	{
	   $r[$d[$index_id]]=$d;
	}
	$res->close();
	return $r;
}

//---------------------------------------------------------------------------------
function idvlist($sql,$index_id,$v_id)
{
	$r=array();
	$res = query($sql);
	while ($d = $res->fetch_assoc()) 
	{
	   $r[$d[$index_id]]=$d[$v_id];
	}
	$res->close();
	return $r;
}



//---------------------------------------------------------------------------------
function db_exec($sql)
{
  if($GLOBALS['db']->query($sql)===false) 
  {
    if($GLOBALS['debug'])echo 'SQL-Query: ',$sql,'<br/>MYSQL: ',$GLOBALS['db']->error;
    msg('error-internal',
	'Sorry, a database error occurred.',
	'exec failed: '.substr($sql,0,80).' MYSQL: '.$GLOBALS['db']->error);
    }
  }

	
//---------------------------------------------------------------------------------
function insert($sql)
{
	db_exec($sql);
	return  $GLOBALS['db']->insert_id;
}

//---------------------------------------------------------------------------------
function value($sql)
{
	$row=singlerow($sql,MYSQLI_NUM);
	return count($row)?$row[0]:'';
}

//---------------------------------------------------------------------------------
function singlerow($sql,$fetch_mode=MYSQLI_ASSOC)
{
	$r = $GLOBALS['db']->query($sql);
	if ($r===false) 
	{
	  if($GLOBALS['debug'])echo 'SQL-Query: ',$sql,'<br/>MYSQL: ',$GLOBALS['db']->error;
	  msg('error-internal',
	      'Sorry, a database error occurred.',
	      'singlerow failed: '.substr($sql,0,80).' MYSQL: '.$GLOBALS['db']->error);
	}
	if (mysqli_num_rows($r) < 1)
	{
	   //echo '<br/><br/>mysqli_num_rows<1';
	   return array();
	}
	if (mysqli_field_count($GLOBALS['db']) < 1)
	{
	  //echo '<br/><br/>mysqli_field_count<1';
	  return array();
	}
	$row = mysqli_fetch_array($r,$fetch_mode);
	$r->close();
	return $row;
}
	

