<?php
if(!$GLOBALS['is_api_call'])die('X');

function get_context(&$in)
{

  switch($in['v'])
  {
    case 'in':
      // only in op/lot/sub   	READ-ONLY  (@out not supported yet)

      if(isset($in['in_lot']))
      {
	$in['n_def']=$in['in_lot']['sub'][1]['n_def'];
      }
      elseif(isset($in['in_sub']))
      {
	$in['n_def']=$in['in_sub']['sub'][1]['n_def'];
      }
      else
      {
	// op can be retrieved easily because is root-element
	$in_op=&$GLOBALS['in-top'][$in['x_i']];
	$fname=$in_op['sub'][1]['v'];
	if(!isset($GLOBALS['data-new'][$fname]))
	{
	    msg('error-semantic','EXA-resolving failed: the function for this op needs to be defined before',$in);
	}
	$in['n_def']=$GLOBALS['data-new'][$fname]['sub'][1]['n_def'];
      }
    break;
    case 'e':
      // only in each	READ-ONLY	the actual entity
      //get nearest each-op and the input-param from there
      $in['n_def']=$in['in_each']['sub'][1]['n_def'];
    break;
    case 'r':
      // only in each	READ-WRITE 	the output of the each
      // get nearest each-op and the init-paramter from there
      $in['n_def']=$in['in_each']['sub'][2]['n_def'];
    break;
    case 'i':
      // only in each	READ-ONLY	the actual index 
      $in['n_def']='uint';
    break;
    case 'c':
      // only in each	READ-ONLY	the count of elements	
      $in['n_def']='uint';
    break;
    case 'u':
      // protected user-view	READ-ONLY 

      $in['y']='e_usage';
      $in['n_def']='context-u';
      
  // [['e','context-u'],
  //   'name-nick^0-1',
  //   'name-full^0-1',
  //   'trust^0-1',
  //   'feeling^0+',
  //   'region^0+',
  //   'relation^0+',
  //   'location^0-1',
  //   'cl-birth^0-1',
  //   'cl-register^0-1']

	
    
    break;
    case 'agent':
      // protected agent-view	READ-ONLY
      $in['y']='e_usage';
      $in['n_def']='context-agent';
    break;
    case 'con':
      // protected con-view		READ-ONLY
      $in['y']='e_usage';
      $in['n_def']='context-con';
    break;
    case 'st':
      // protected statement-view	READ-ONLY
      $in['y']='e_usage';
      $in['n_def']='context-statement';
    break;
    case 'g':
      // protected group-view	READ-WRITE (depending on user-permissions)
      $in['y']='e_usage';
      $in['n_def']='context-group';
      //$in['c_sub']=4;
      //$in['sub']=array(0=>0)
    break;
    case 'service':
      // useful data about the current-exalot-service-execution	READ-ONLY
      $in['y']='e_usage';
      $in['n_def']='context-service';
      $in['c_sub']=4;
      $in['sub']=array(0=>0,
		       1=>array('y'=>'s',
				'is_literal'=>1,
				'n_def'=>'name',
				'c_str'=>strlen($GLOBALS['conf']['service']),
				'v'=>$GLOBALS['conf']['service'],
				'c_sub'=>0,
				'sub'=>array(0=>0)),
		       2=>array('y'=>'s',
				'is_literal'=>1,
				'n_def'=>'s-version',
				'c_str'=>strlen($GLOBALS['conf']['version']),
				'v'=>$GLOBALS['conf']['version'],
				'c_sub'=>0,
				'sub'=>array(0=>0)),
		       3=>array('y'=>'s',
				'is_literal'=>1,
				'n_def'=>'s-version-alias',
				'c_str'=>strlen($GLOBALS['conf']['version-alias']),
				'v'=>$GLOBALS['conf']['version-alias'],
				'c_sub'=>0,
				'sub'=>array(0=>0)),
		       4=>array('y'=>'s',
				'is_literal'=>1,
				'n_def'=>'s-os',
				'c_str'=>strlen(PHP_OS),
				'v'=>PHP_OS,
				'c_sub'=>0,
				'sub'=>array(0=>0)));
	  
    break;
    case 'now':	
	// timestamp of first now-usage	READ-ONLY
      
	if(!isset($GLOBALS['temp']['v-now']))
	{
	  $cl=strToClock(dbs::value($GLOBALS['conf']['mysql-before-5-6-4']?
				    'SELECT NOW()':
				    'SELECT sysdate(3)')); // to ensure being at db-time
	
	  $GLOBALS['temp']['v-now']=$cl;
	}

	$in['y']='cl';
	$in['v']=$GLOBALS['temp']['v-now'];
	$in['n_def']='cl';
	$in['is_literal']=1;
      
    break;
    case 'random':
      // a random number in float between 0 and 100 (PHP-random)	READ-ONLY

      $in['v']=100*mt_rand(1,mt_getrandmax()-1)/mt_getrandmax();
      $in['y']=$in['n_def']='float';
    break;
    default:
      msg('error-internal','invalid case in a context',$in);
    break;
  }  
}
     
?>  