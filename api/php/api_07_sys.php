<?php


//----------------------------------------------------------------------
function resolve_sys(&$in)
{  
  $r=$GLOBALS['row-0'];
  
  $r['n_def']=$r['n_def_non_alias']=$in['v'];
  $r['p']=$r['e']=$r['r']='e';
  $r['l']='usage';
  
  switch($in['y'])
  {
    case 'op': //---------------------------------------------------------
      // define the operation for a new f,
 
      $f_name=$in['sub'][1]['v'];
      $f_plural=$in['sub'][2]['v']; // must be an integer or p >=0
      
      $in['r_f']=&$GLOBALS['data-new'][$in['v']]; // must be a newly defined function
      
      if($f_plural=='p')
      {
	if(!$in['r_f']['is_plural'])
	{
	  msg('error-semantic','EXA-resolving failed: a plural op can only be defined on a function with plural input',$in);
	}
	$r['is_plural']=1;
	$r['c_min']=$r['c_max']=0;
      }
      elseif(is_numeric($f_plural))
      {
	if(!$in['r_f']['is_plural'])
	{
	  msg('error-semantic','EXA-resolving failed: index-numbers can only be used on plural op',$in);
	}

	if($f_plural>$in['r_f']['c_max'])
	{
	  msg('error-semantic','EXA-resolving failed: the int input-param for plural is to great for definition. max: '.$in['r_f']['c_max'],$in);
	}	

	if($f_plural<$in['r_f']['c_min'])
	{
	  msg('error-semantic','EXA-resolving failed: the int input-param for plural is to little for definition. min: '.$in['r_f']['c_min'],$in);
	}	

	$r['is_plural']=1;
	$r['c_min']=$r['c_max']=$f_plural;
      }
      else
      {
	// single entity
	if($in['r_f']['is_plural'])
	{
	  msg('error-semantic','EXA-resolving failed: a plural op can only be defined by index-numbers or p',$in);
	}

	if(!is_sub_rec_of($in['r_f']['n_sub1'],$f_plural))
	{
	  msg('error-semantic','EXA-resolving failed: the input-param defined at op does not match the defintion',$in);
	}
	$r['c_min']=$r['c_max']=1;
      }

      $r['n']="op-{$f_name}-{$f_plural}";
      if(isset($GLOBALS['data-new'][$r['n']]))
      {
	  msg('error-semantic','EXA-resolving failed: this op was already defined in this statement',$in);
      }
            
      $r['n_sub1']=$f_name;
      $r['n_sub2']=$f_plural;  // attention, this is actually not always a name but also int
      $r['n_sub3']=$in['r_f']['n_sub2'];

      if(!is_sub_rec_of($in['r_f']['n_sub2'],$in['sub'][3]))
      {
	msg('error-semantic','EXA-resolving failed: the op-expression does not match the function-defintion',$in);
      }
      
      $in['n_def']='op';
      if(isset($in['r_f']['is_plural_fo']))
      {
	$in['is_plural']=$in['r_f']['is_plural_fo'];
	$in['c_min']=$in['r_f']['c_min_fo'];
	$in['c_max']=$in['r_f']['c_max_fo'];
      }
    break;  
    case 'default': //---------------------------------------------------------
      // perform system-operation,

      $in['r_e']=&$GLOBALS['data-new'][$in['sub'][1]['v']]; // must be a newly defined entity
  
      $n="default-{$in['r_e']['n']}";
      
      if(isset($GLOBALS['data-new'][$n]))
      {
	msg('error-semantic','EXA-resolving failed: The default-entity was already saved.',$in);
      }
      
      $r['n']=$n;
      $r['n_def']='default';

      $r['n_sub1']=$in['r_e']['n'];
      $r['n_sub2']=$in['sub'][2]['v'];
      
      if(!is_sub_rec_of($r['n_sub1'],$r['n_sub2']))
      {
	msg('error-semantic','EXA-resolving failed: the default value does not match the entity',$in);
      }
      
      $in['r_e']['n_default']=$r['n_sub2'];

    break;
    case 'privacy': //---------------------------------------------------------
      // perform system-operation,
      // privacy can be used several times (update-operation)

      $in['r_e']=&get_row_e_by_n($in['sub'][1]['v']); 
      if(!count($in['r_e']))
      {
	msg('error-semantic','EXA-resolving failed: entity not found for privacy-rule.',$in);
      }
  
      $n="privacy-{$in['r_e']['n']}";

      if(isset($in['r_e']['n_u_cr']))
      {
	if($in['r_e']['n_u_cr']!=$GLOBALS['context']['n-u'])
	{
	  msg('error-semantic','EXA-resolving failed: Only the creator-user can set privacy-rules.',$in);
	}
      }
      
      $r['n']=$n;
      $r['n_def']='privacy';

      $r['n_sub1']=$in['r_e']['n'];
      
      
    break;
    case 'limited': //---------------------------------------------------------
      // perform system-operation,
      
      $in['r_e']=&$GLOBALS['data-new'][$in['sub'][1]['v']]; // must be a newly defined entity
      
      $n="limited-{$in['r_e']['n']}";
      
      if(isset($GLOBALS['data-new'][$n]))
      {
	msg('error-semantic','EXA-resolving failed: limited-expression given twice on entity',$in);
      }

      $r['n']=$n;
      $r['n_def']='limited';
      
      $r['n_sub1']=$in['r_e']['n'];
    
      $in['r_e']['is_limited']=1;
            
    break;
    default:
      msg('error-internal','invalid case in a sys:'.$in['y'],$in);
    break;
  }
  
  $in['r']=$r;
  $GLOBALS['data-new'][$r['n']]=&$in['r'];
  
}

?>