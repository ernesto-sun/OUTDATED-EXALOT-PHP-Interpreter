<?php
if(!$is_api_call)die('X');


//------------------------------------------
function msg($msg,$details,$debug='',$debug2='')
{
  if(is_array($debug))$debug=json_encode($debug,0,6);
  if(is_array($debug2))$debug2=json_encode($debug2,0,6);
  $debug.=$debug2;
  $debug=preg_replace('/\s+/',' ',substr($debug,0,200));
  $details=preg_replace('/\s+/',' ',substr($details,0,200));

  $s=0;
  if(isset($GLOBALS['msg'][$msg]))
  {
     $title=$GLOBALS['msg'][$msg]['t'];
     $desc=$GLOBALS['msg'][$msg]['d'];
     $s=(int)$GLOBALS['msg'][$msg]['s'];
     $type=$GLOBALS['msg'][$msg]['y'];
     $combi="{$title}: {$desc}: {$details}";
     switch($type)
     {
	case 'error':
	  if(!$s)$s=501;
 	  $writeToDB=1;
	  $die=1;
	  break;
	case 'warning':
	  header("Warning: {$combi}");
	  $writeToDB=1;
	  break;
	case 'notice':
	  //header("Notice: {$combi}");
	  $writeToDB=1;
	  break;
	default:
	  $type='error';
	  header("Not Implemented: invalid message: {$combi}",1,$s);
	  $debug.=', msg-type not recognized: '.$type;
	  $writeToDB=1;
	  $die=1;
	  break;
   }
  }
  else
  {
    header("Not Implemented: undefined message: {$msg}: {$details}",1,501);
    $debug.=", ATTENTION: message undefined: {$msg}";
    $type='error';
    $writeToDB=1;
    $die=1;
  }

  $bt=debug_backtrace();
  $caller = array_shift($bt);
  $debug="{$caller['file']} Line: {$caller['line']}: {$debug}";

  if($GLOBALS['debug'])
  {
    header("Message: {$GLOBALS['msg'][$msg]['d']}: {$details}");
    echo $debug;
    header("Debug: {$debug}");
  }
  
  if ($writeToDB)
  {
    
    $details=validDB($details);
    $debug=validDB($debug);
    
    $GLOBALS['temp']['i-msg']+=1;
    dbs::exec("INSERT INTO {$GLOBALS['pre']}msg(
    id_st_o,
    i,
    n_u,
    n_u_agent,
    id_ses,
    id_con,
    id_st,
    id_x,
    n_msg_template,
    msg,
    description,
    msg_debug) VALUES
    ({$GLOBALS['context']['id-st-o']},
    {$GLOBALS['temp']['i-msg']},
    '{$GLOBALS['context']['n-u']}',
    '{$GLOBALS['context']['n-u-agent']}',
    {$GLOBALS['context']['id-ses']},
    {$GLOBALS['context']['id-con']},
    {$GLOBALS['context']['id-st']},
    {$GLOBALS['context']['id-x']},
    '{$msg}',
    '{$type}',
    '{$details}',
    '{$debug}')");
  }

  if($GLOBALS['debug'])
  {
    echo '<br/><br/>---------------------------<br/><br/>message: ',$msg;
    echo '<br/><br/>---------------------------<br/><br/>type: ',$type;
    echo '<br/><br/>---------------------------<br/><br/>details: ',$details;
    echo '<br/><br/>---------------------------<br/><br/>debug: ',$debug;
  
    debug_dump_all();
  }
  
  if($s>0)
  {
    header('Error: {$combi}',1,$s);
  }
  
  if($die)
  {
    die();
  }
}


// -----------------------------------------------------------
// DEBUG-Output starts 
// -----------------------------------------------------------

// --------------------------------------------------------
function print_r_flat($var, $levels=2, $level_current=0)
{
  if(is_array($var))
  {
    if($level_current==$levels)
    {
    
      echo 'Array(';
      if(isset($var['n']))echo '["',$var['n'],'"]';
      echo ' ... count: ',count($var),')';
    }
    else
    {
    
      echo 'Array
',str_repeat(' ',$level_current*4),'(
';

      foreach($var as $key => $val)
      {
	echo str_repeat(' ',$level_current*4),'  [',$key,'] => ';
	print_r_flat($val,$levels,$level_current+1);
	echo '
';
      }
      echo str_repeat(' ',$level_current*4),')
';
    }
  }
  else
  {
    echo $var;
  }
}


//--------------------------------------
function debug_dump_all()
{
  echo '\n\r
  \n\r
  ';
  if(isset($GLOBALS['in']))
  {
    echo '\n\rIN...\n\r';
    print_r_flat($GLOBALS['in']);
  }  
  

  if(isset($GLOBALS['in-top']) && false)
  {
    echo '\n\rOP-TOP...\n\r';
    print_r($GLOBALS['in-top']);
  }

  if (isset($GLOBALS['data-new']))
  {
    echo '\n\r<br/>DATA-NEW....<br/>\n\r';
    print_r($GLOBALS['data-new']);
  }
  
  if (isset($GLOBALS['data-def']))
  {
    echo '\n\r<br/>DATA-DEF....<br/>\n\r';
    print_r($GLOBALS['data-def']);
  }

  
  if (isset($GLOBALS['data-exp']))
  {
      echo '\n\r<br/>DATA-EXP....<br/>\n\r';
    print_r($GLOBALS['data-exp']);
  } 
   
  if (isset($GLOBALS['data-temp']))
  {
    echo '\n\r<br/>DATA-TEMP....<br/>\n\r';
    print_r($GLOBALS['data-temp']);
  }

  echo '

  temp....

  ';
  print_r($GLOBALS['temp']);
  echo '


  st....

  ';
  print_r($GLOBALS['st']);
  echo '


  context....

  ';
  print_r($GLOBALS['context']);
  die('Aha');

}



//--------------------------------------
function validText($txt) // for html-textes
{
  return htmlspecialchars($txt,ENT_COMPAT,'UTF-8');
}

//--------------------------------------
function validOutput($txt)  // for text-fields
{
  //if (strlen($txt) > 0) echo '</br>Aha: ',$txt,'</br>';
  return $txt;
}


//--------------------------------------
function validDB($txt)
{
  global $conn;
  return mysqli_real_escape_string($conn,stripslashes($txt));
}

//--------------------------------------
function validInput($txt, $forSQL = true, $htmlentities = true)
{
	if (is_array($txt))
	{
	   msg('error-internal','Internal Error: An array given where a string is expected! May be uncatched syntax-error.');
	}
	else
	{
		global $conn;
	
		//$txt=rawurldecode($txt)
		$txt=stripslashes($txt);
		if($htmlentities)$txt=htmlentities($txt);
		$txt=strip_tags($txt);
		//$txt = str_ireplace('script', 'blocked', $txt);
		$txt = mysqli_real_escape_string($conn,$txt);
	}
	return $txt;
}

//--------------------------------------
function validLink($link)
{
  if (strpos($link, 'http') !== 0)
  {
    $link = 'http://'.$link;
  }
  return $link;
}

//--------------------------------------
function reloadAndDie()
{
header('Refresh: 0; URL=index.php');
ob_end_flush();
die();
}

//--------------------------------------
function isValidEmail($txt)
{
return true; // todo
}

//--------------------------------------
function isValidUsername($txt)
{
return true; // todo
}

//---------------------------------------------
function getBaseDir()
{
  return $_SERVER['DOCUMENT_ROOT'].getBaseDirOnly();
}

//---------------------------------------------
function getBaseDirOnly()
{
  return dirname($_SERVER['PHP_SELF']).'/';
}

//---------------------------------------------
function getcwd_clean()
{
	return str_replace('\\','/',getcwd()).'/';
}

//---------------------------------------------
function getBaseURI()
{
  $base = $_SERVER['HTTP_HOST'];
  if (strpos($base,'http://') !== 0) $base = 'http://'.$base;
  return $base.getBaseDirOnly();
}


//---------------------------------------------
function getTimeStamp()
{
 return date('Ymd_H_s');
}

//---------------------------------------------
// function strToTime($str) 
// 
// returns array('cl_year'=>int,
//		  'cl'=>datetime,
//		  'tz'=>str,  (code of timezone in small letters)
//		  'ms'=>int)
//
//
// Allowed formats
// *  Sun, 06 Nov 1994 08:49:37 GMT  ; RFC 822, updated by RFC 1123
// *  Sun Nov 6 08:49:37 1994        ; ANSI C's asctime() format
// *  22050-10-27 14:56:23 345 art   ; EXALOT clock format (last number is milliseconds)
// 
//
// Variations of EXALOT clock format
// 
// * 1900-8-23 2:25
// * 1900-8-23 2:25:24 345
// * 1900-8-23
// * 0-1-1
// * -2500
//
//  Note: Support for wider year-range has to be implemented. 

//---------------------------------------------
function strToClock($str,$makeUTC=0)
{
  $str = strtr(trim($str),array('  '=>' ',
				'.'=>' ',
				'/'=>'-'));

  $c_str=strlen($str);

  $ex_date=array();
  $ex_time=array();
  $year=0;
  $month=0;
  $day=0;
  $hour=0;
  $tz='';
  $ms=0;
  $cl='';
  
  $syntax_bad=0;

  if($c_str>63)
  {
    $c_str=63;
    $str=substr($str,0,63);
    $syntax_bad=1;
  }
  
  if($c_str<1)
  {
    $year=0;
    $syntax_bad=1;
  }
  elseif($c_str<8)
  {
     $ex_date=explode('-',$str);
  }
  else
  {
     $ex=explode(' ',$str);
     $c_ex=count($ex);
     if($c_ex<2)
     {
        // only one block must be exa-format with date-only
	$ex_date=explode('-',$ex[0]);
     }
     else
     {  
	if($ex[0][0]=='-'||$ex[0][0]==''.(int)$ex[0][0]) // compare first character of first block only
	{
	  // first element is a number, so it's a EXA-format with 2 blocks at least
	  $ex_date=explode('-',$ex[0]);

	  if($c_ex==2)
	  {	  
	    // first must be date
	    $ex_time=explode(':',$ex[1]);
	    if(count($ex_time)<2)
	    {
	      // only one element
	      if($ex_time[0][0]==''.(int)$ex_time[0][0])
	      {
	        // must be hour
	        $hour=(int)$ex_time[0];
	      }
	      else
	      {
	        // must be tz
	        $tz=$ex_time[0];
	      }
	      $ex_time=array(); // reset it not to be parsed later
	    }
	  }
	  else
	  {
	    // we have 3 exa-parts, second must be time, third can be ms or timezone
	    $ex_time=explode(':',$ex[1]);
	    
	    if(isset($ex[2])&&$ex[2]!='')
	    {
	      if($ex[2][0]==''.(int)$ex[2][0])
	      {
		$ms=(int)$ex[2];
	      }
	      else
	      {
		$tz=$ex[2];  
	      }
	    }

	    if(isset($ex[3])&&$ex[3]!='')
	    {
	      if($ex[3][0]==''.(int)$ex[3][0])
	      {
		if(!$ms)$ms=(int)$ex[3];
	      }
	      else
	      {
		if(!$tz)$tz=$ex[3];  
	      }
	    }
	    
	    if(isset($ex[4]))
	    {
	      $syntax_bad=1;      
	    }
	  }
	}
	else
	{
		
	  if(is_numeric($ex[1][0]))
	  {
	     // Format: Sun, 06 Nov 1994 08:49:37 GMT
	      $tz=$ex[$c_ex-1];
	      if($tz[0]==''.(int)$tz[0])
	      {
		// this is not a timezone because number
		$tz='';
	      }
	      $c_ex_0=strlen($ex[0]);
	      $c_tz=strlen($tz);
	      
	      $dummy=substr($str,$c_ex_0+1,$c_str-$c_ex_0-$c_tz-2);
	      //echo 'StrtomTime RFC: ''.$dummy.''\r\n';
	      $cl=strtotime($dummy);
	      if(!$cl)
	      {
		$syntax_bad=1;
		$cl=strtotime($GLOBALS['conf']['cl-min']);
	      }
	  }
	  else
	  {
	    // Sun Nov 6 08:49:37 1994
	    $dummy=substr($str,strlen($ex[0])+1);
	    $cl=strtotime($dummy);
	    if(!$cl)
	    {
	      $syntax_bad=1;
	      $cl=strtotime($GLOBALS['conf']['cl-min']);
	    }
	  }
        }
     }
   }

  $year_db=$year;

  if($tz)
  {
    $tz=validInput(strtolower(trim($tz)));
    if(strlen($tz)>5)
    {
      $tz=trim(substr($tz,0,5));
    }
    
    if(!isset($GLOBALS['tz'][$tz]))
    {
      $syntax_bad=1;
      $tz='utc';
    }
  }
  else
  {
    $tz='utc';
  }

  $diff_utc=0;
  if($makeUTC)
  {
     if($tz!='utc')
     {
	$diff_utc=$GLOBALS['tz'][$tz];
	$tz='utc';
     }
  }

  $cl_str='';
  if($cl)
  {
    if($diff_utc!=0)
    {
      $cl+=$diff_utc*3600;
    }
    $year_db=(int)date('Y',$cl);
    $cl=date('Y-m-d H:i:s',$cl);
    $cl_str=$cl;
  }
  else
  {
    if(count($ex_date)>0)
    {
      $basis=0;
      if($ex_date[0]=='')$basis=1;
      
      if($ex_date[$basis])
      {
	$syntax_bad|=empty($ex_date[$basis]);
	$year=(int)$ex_date[$basis];
      }

      if(isset($ex_date[$basis+1]))
      {
	$syntax_bad|=empty($ex_date[$basis+1]);
	$month=(int)$ex_date[$basis+1];
      }

      if(isset($ex_date[$basis+2]))
      {
	$syntax_bad|=empty($ex_date[$basis+2]);
	$day=(int)$ex_date[$basis+2];
      }
      
      if($basis)$year*=-1;
    }
 
  
    if(!$month)$month=1;
    if(!$day)$day=1;
    
    $min=0;
    $sec=0;
    
    
    if(count($ex_time)>0)
    {
      $hour=(int)$ex_time[0];
      $syntax_bad|=empty($ex_time[0]);
      
      if(isset($ex_time[1]))
      {
	$syntax_bad|=empty($ex_time[1]);
	$min=(int)$ex_time[1];
      }

      if(isset($ex_time[2]))
      {
	$syntax_bad|=empty($ex_time[2]);
	$sec=(int)$ex_time[2];
      }
    }

    $year_db=$year;
    
    if($year<$GLOBALS['conf']['cl-year-min-db'])
    {
      $year_db=$GLOBALS['conf']['cl-year-min-db'];
      if($year<$GLOBALS['conf']['cl-year-min'])
      {
	$year=$GLOBALS['conf']['cl-year-min'];
	$syntax_bad=1;
      }
    }
    elseif($year>$GLOBALS['conf']['cl-year-max-db'])
    {
      $year_db=$GLOBALS['conf']['cl-year-max-db'];
      if($year>$GLOBALS['conf']['cl-year-max'])
      {
	$year=$GLOBALS['conf']['cl-year-max'];
	$syntax_bad=1;
      }
    }
    
    if($month>12)
    {
      $month=12;
      $syntax_bad=1;
    }
    elseif($month<1)
    {
      $month=1;
      $syntax_bad=1;
    }

    if($day>31)
    {
      $day=31;
      $syntax_bad=1;
    }
    elseif($day<1)
    {
      $day=1;
      $syntax_bad=1;
    }
    
    if($hour>23)
    {
      $hour=23;
      $syntax_bad=1;
    }
    elseif($hour<0)
    {
      $hour=0;
      $syntax_bad=1;
    }

    
    if($min>59)
    {
      $min=59;
      $syntax_bad=1;
    }
    elseif($min<0)
    {
      $min=0;
      $syntax_bad=1;
    }
    
    if($sec>59)
    {
      $sec=59;
      $syntax_bad=1;
    }
    elseif($sec<0)
    {
      $sec=0;
      $syntax_bad=1;
    }
    
    if($year_db>2037||$year_db<1971)
    {
      if($diff_utc!=0)
      {
	$addMinute=$diff_utc*60;
	$addHour=(int)($addMinute/60);
	$addMinute-=$addHour*60;

	$min+=$addMinute;
	$hour+=$addHour;
	if($min>59)
	{
	  $min-=59;
	  $hour+=1;
	}

	if($hour>23)
	{
	  $hour=$hour-23;
	  $day+=1;
	  if($day>$GLOBALS['day_per_month'][$month])
	  {
	    $month+=1;
	    $day=1;
	    if($month>24)
	    {
	      if($year==$year_db)$year_db+=1;
	      $year+=1;
	      $month=1;
	    }
	  }
	}
	
	if($min<0)
	{
	  $min=59-$min;
	  $hour-=1;
	}

	if($hour<0)
	{
	  $hour=24+$hour;
	  $day-=1;
	  if($day<1)
	  {
	    $month-=1;
	    if($month<1)
	    {
	      if($year==$year_db)$year_db-=1;
	      $year-=1;
	      $month=12;
	    }
	    $day=$GLOBALS['day_per_month'][$month];
	  }
	}
      } // end utc-calc
     
      $cl=sprintf('%04d-%02d-%02d %02d:%02d:%02d',$year_db,$month,$day,$hour,$min,$sec);
      if($year==$year_db)
      {
	$cl_str=$cl;
      }
      else
      {
	$cl_str=sprintf('%04d-%02d-%02d %02d:%02d:%02d',$year,$month,$day,$hour,$min,$sec);
      }
    }
    else
    {
      $cl=mktime($hour,$min,$sec,$month,$day,$year_db);
      if($diff_utc!=0)
      {
	$cl+=$diff_utc*3600;
      }
      $cl=date('Y-m-d H:i:s',$cl);
      $cl_str=$cl;
    }
  }

  if($ms<0)
  {
    $ms=0;
  }
  else
  {
    if($ms>999)
    {
      $syntax_bad=1;
      $ms=(int)substr(''.$ms,0,3); // take first 3 letters becasue value was microseconds instead of milliseconds
    }
    $cl_str.=sprintf(' %03d',$ms);
  }
  
  if($tz!=''&&$tz!='utc')
  {
    $cl_str.=" {$tz}";
  }
  
  return array('year'=>$year,
	  'cl'=>$cl,
	  'str'=>$cl_str,
	  'tz'=>$tz,
	  'ms'=>$ms,
	  'syntax_bad'=>$syntax_bad);
}



//------------------------------------------
function rp($v)
{
	return htmlspecialchars(stripslashes($v),ENT_COMPAT,'UTF-8',false);
}

//------------------------------------------
function param($v)
{
	return (isset($_GET[$v])?$_GET[$v]:$_POST[$v]);	
}

//------------------------------------------
function isparam($v)
{
	return isset($_GET[$v])||isset($_POST[$v]);	
}


//------------------------------------------
function get_json_error_as_string($err)
{
	switch ($err) 
	{
        case JSON_ERROR_NONE:
            return 'JSON_ERROR_NONE - No errors';
        break;
        case JSON_ERROR_DEPTH:
            return 'JSON_ERROR_DEPTH - Maximum stack depth exceeded';
        break;
        case JSON_ERROR_STATE_MISMATCH:
            return 'JSON_ERROR_STATE_MISMATCH - Underflow or the modes mismatch';
        break;
        case JSON_ERROR_CTRL_CHAR:
            return 'JSON_ERROR_CTRL_CHAR - Unexpected control character found';
        break;
        case JSON_ERROR_SYNTAX:
            return 'JSON_ERROR_SYNTAX - Syntax error, malformed JSON';
        break;
        case JSON_ERROR_UTF8:
            return 'JSON_ERROR_UTF8 -Malformed UTF-8 characters, possibly incorrectly encoded';
        break;
        default:
            return '??? - Unknown error';
        break;
    }
}

?>