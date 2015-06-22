<?php


//----------------------------------------------------------------------
function resolve_path(&$in,$onlyCheckPath)
{
  $c_path=count($in['path']);

  $in_path=&$in;
  
  for($m=0;$m<$c_path;$m++)
  {
    if(is_numeric($in['path'][$m]))
    {
      $v_path=(int)$in['path'][$m];
      
      if(!isset($in_path['is_literal']))
      {
	//literal-subs are handled specially below
	
	$ok_sub=0;
	if(isset($in_path['sub']))
	{
	  if(isset($in_path['sub'][$v_path]))
	  {
	    $in_path=&$in_path['sub'][$v_path];
	    $ok_sub=1;
	  }
	}
	
	if(!$ok_sub)
	{
	  $n_path=dbs::value("SELECT n FROM {$GLOBALS['pre']}sub 
	  WHERE n_sup='{$in_path['v']}' AND i='{$v_path}' AND is_now=1");

	  if(empty($n_path))
	  {
	    msg('error-semantic','EXA-resolving failed: entity by path could not be found.',$in);
	  }

	  $in_path=array('v'=>$n_path,'y'=>'e_usage','c_sub'=>0,'sub'=>array(0=>0));
	 
	  $r=$in_path['r']=&get_row_e_by_n($n_path);
	  if(count($r)<1)
	  {
	      msg('error-semantic','EXA-resolving failed: entity inside a path could not be found',$in);
	  }	 
	 
	  if(!is_privacy_see_ok($r))
	  {
	    msg('error-semantic','EXA-resolving failed: Condition-check failed for entity inside a path','Privacy-check failed.'.$in);
	  }
	  
	  $ok_sub=1;
	}
      }
      else
      {
	  // here we are literal
	$c_path_sub=$c_path-$m;
	    
	switch($in_path['y'])
	{
	  case 's':
	    if($c_path_sub>1)
	    {
	      msg('error-semantic','EXA-resolving failed: string does only allow one path-level, resulting in char',$in);
	    }
	    
	    if($v_path>$in_path['c_str'])
	    {
	      msg('error-semantic',"EXA-resolving failed: the string is too short for the path-index: {$v_path} str-len: {$in_path['c_str']}");
	    }
	    
	    $in_path['v']=$in_path['v'][$v_path-1];
	    $in_path['c_str']=1;
	    $in_path['n_def']='s';
	  break;
	  case 'int':
	    if($c_path_sub>1)
	    {
	      msg('error-semantic','EXA-resolving failed: int does only allow one path-level, resulting in bit',$in);
	    }
	    
	    if($v_path>63)
	    {
	      msg('error-semantic','EXA-resolving failed: Invalid path. only 64-bit integers are allowed.',$in);
	    }
	    
	    $isx=0;
	    $dum=decbin($in_path['v']);
	    if(isset($dum[$v_path])&&$dum[$v_path])$isx=1;
	    $in_path['v']=$in_path['y']=($isx?'x':'noo');
	    $in_path['n_def']='b';
	  break;
	  case 'float':
	      msg('error-semantic','EXA-resolving failed: float does not allow sub-path (IEEE-format)',$in);
	  break;
	  case 'cl':
	  	  
	    if($c_path_sub>3)
	    {
	      msg('error-semantic','EXA-resolving failed: cl does only allow three more path-levels in total',$in);
	    }
	    switch($v_path)
	    {
	      case 1:
		$year=$in_path['v']['year'];
		$date=explode(' ',$in_path['v']['cl'])[0];
		$exp_date=explode('-',$date);

		if($c_path_sub<2)
		{
		  //date
		  $in_path['v']='cl-date';
		  $in_path['y']='e-usage';
		  $in_path['n_def']='cl-date';
		  $in_path['c_sub']='3';
		  $in_path['sub']=array(0=>0,
				    1=>array('v'=>$year,'y'=>'int','n_def'=>'int','c_sub'=>0,'sub'=>array(0=>0)),
				    2=>array('v'=>$exp_date[1],'y'=>'int','n_def'=>'int','c_sub'=>0,'sub'=>array(0=>0)),
				    3=>array('v'=>$exp_date[2],'y'=>'int','n_def'=>'int','c_sub'=>0,'sub'=>array(0=>0)));
		}
		else
		{
		  $m++;
		  
		  $in_path['y']='int';
		  $in_path['n_def']='int';

		  switch($in['path'][$m])
		  {
		    case 1:
		      // year
		      $in_path['v']=$year;
		    break;
		    case 2:
		      // month
		      $in_path['v']=$exp_date[1];
		    break;
		    case 3:
		      // day
		      $in_path['v']=$exp_date[2];
		    break;
		    default:
		      msg('error-semantic','EXA-resolving failed: invalid cl-path within date-part. valid is e.g.: the month is @1@2',$in);
		  }
		}
	      break;
	      case 2:
		$time=explode(' ',$in_path['v']['cl'])[1];
		$exp_time=explode(':',$time);

		if($c_path_sub<2)
		{
		  $in_path['v']='cl-time';
		  $in_path['y']='e-usage';
		  $in_path['n_def']='cl-time';
		  $in_path['c_sub']='3';
		  $in_path['sub']=array(0=>0,
				    1=>array('v'=>$exp_time[0],'y'=>'int','n_def'=>'int','c_sub'=>0,'sub'=>array(0=>0)),
				    2=>array('v'=>$exp_time[1],'y'=>'int','n_def'=>'int','c_sub'=>0,'sub'=>array(0=>0)),
				    3=>array('v'=>$exp_time[2],'y'=>'int','n_def'=>'int','c_sub'=>0,'sub'=>array(0=>0)));
		}
		else
		{
		  $m++;
		  
		  $in_path['y']='int';
		  $in_path['n_def']='int';
		  
		  switch($in['path'][$m])
		  {
		    case 1:
		      $in_path['v']=$exp_time[0];
		    break;
		    case 2:
		      // minute
		      $in_path['v']=$exp_time[1];
		    break;
		    case 3:
		      // second
		      $in_path['v']=$exp_time[2];
		    break;
		    default:
		      msg('error-semantic','EXA-resolving failed: invalid cl-path within time-part. valid is e.g.: the minute is @2@2',$in);
		  }
		}
	      break;
	      case 3:
		//ms
		$in_path['y']='int';
		$in_path['n_def']='int';
		$in_path['v']=$in_path['v']['ms'];
	      break;
	      default:
		msg('error-semantic','EXA-resolving failed: invalid cl-path. valid is e.g.: the year is @1@1 and the second is @2@3.',$in);

	    }
	  break;
	  default:
	      msg('error-semantic','EXA-resolving failed: this literal does not allow a path',$in);
	
	} // end select 
      
      } // end if !is_literal, else
    }
    else
    {
      // TODO textual path-elements not supporte yet (diabled in syntax)
    }
    
  } // end for $m
    
  //Set row to the last found element of the path (overwrite ok because it's a leaf)
    
  if(isset($in_path['r']))$in['r']=$in_path['r'];
  if(isset($in_path['n_def']))
  {
    $in['n_def']=$in_path['n_def'];
    if(isset($in_path['is_plural']))
    {
      $in['is_plural']=1;
      $in['c_min']=$in_path['c_min'];
      $in['c_max']=$in_path['c_max'];
    }
  }
  else
  {
    if(isset($in['r']))
    {
      if($in['r']['l']=='usage')
      {
	// usage may not happen for definition, but is checked at definition-sub-check
	$in['n_def']=$in['r']['n_def'];
      }
      else
      {
	$in['n_def']=$in['r']['n'];
      }  

      if($in['r']['is_plural'])
      {
	$in['is_plural']=1;
	$in['c_min']=$in['r']['c_min'];
	$in['c_max']=$in['r']['c_max'];
      }
    }
    else
    {
      msg('error-semantic','EXA-resolving failed: path-result could not be determined',$in);
    }
  }
  
  if(!$onlyCheckPath||true)
  {
    $in['v']=$in_path['v'];
    $in['y']=$in_path['y'];
    $in['sub']=$in_path['sub'];
    $in['c_sub']=$in_path['c_sub'];
  }  
  
  
}

  
?>  