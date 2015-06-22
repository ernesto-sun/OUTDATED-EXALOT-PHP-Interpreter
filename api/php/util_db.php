<?php

class dbs
{

//---------------------------------------------------------------------------
//---------------------------------------------------------------------------
//---------------------------------------------------------------------------

public static $conn=0;
		
//---------------------------------------------------------------------------------
public static function init($conn)
{
	self::$conn = $conn;
}
	
	
//---------------------------------------------------------------------------------
public static function query($sql)
{
	$r = self::$conn->query($sql);
	if ($r===false) 
	{
	  if($GLOBALS['debug'])echo 'SQL-Query: ',$sql,'<br/>MYSQL: ',self::$conn->error;
	  msg('error-internal',
	      'Sorry, a database error occurred.',
	      'query failed: '.substr($sql,0,80).' MYSQL: '.self::$conn->error);
	}
	return $r;
}


//---------------------------------------------------------------------------------
public static function query_array($sql)
{
	$r = self::query($sql);
	$arr=$r->fetch_all(MYSQLI_ASSOC);
	self::free($r);
	return $arr;
}

//---------------------------------------------------------------------------------
public static function idlist($sql,$index_id='id')
{
	$r=array();
	$res = self::query($sql);
	while ($d = $res->fetch_assoc()) 
	{
	   $r[$d[$index_id]]=$d;
	}
	self::free($res);
	return $r;
}

	


//---------------------------------------------------------------------------------
public static function exec($sql)
{
  if(self::$conn->query($sql)===false) 
  {
    if($GLOBALS['debug'])echo 'SQL-Query: ',$sql,'<br/>MYSQL: ',self::$conn->error;
    msg('error-internal',
	'Sorry, a database error occurred.',
	'exec failed: '.substr($sql,0,80).' MYSQL: '.self::$conn->error);
    }
  }

	
//---------------------------------------------------------------------------------
public static function insert($sql)
{
	self::exec($sql);
	return  self::$conn->insert_id;
}

//---------------------------------------------------------------------------------
public static function value($sql)
{
	$row=self::singlerow($sql,MYSQLI_NUM);
	return count($row)?$row[0]:'';
}

//---------------------------------------------------------------------------------
public static function singlerow($sql,$fetch_mode=MYSQLI_ASSOC)
{
	$r = self::$conn->query($sql);
	if ($r===false) 
	{
	  if($GLOBALS['debug'])echo 'SQL-Query: ',$sql,'<br/>MYSQL: ',self::$conn->error;
	  msg('error-internal',
	      'Sorry, a database error occurred.',
	      'singlerow failed: '.substr($sql,0,80).' MYSQL: '.self::$conn->error);
	}
	if (mysqli_num_rows($r) < 1)
	{
	   //echo '<br/><br/>mysqli_num_rows<1';
	   return array();
	}
	if (mysqli_field_count(self::$conn) < 1)
	{
	  //echo '<br/><br/>mysqli_field_count<1';
	  return array();
	}
	$row = mysqli_fetch_array($r,$fetch_mode);
	self::free($r);
	return $row;
}
	

//---------------------------------------------------------------------------------
public static function free($r)
{
	if ($r) $r->close();
}	

	
}  // class

?>