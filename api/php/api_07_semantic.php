<?php
if(!$is_api_call)die('X');

$GLOBALS['temp']['sql_select_e']="SELECT
id,
n,
n_def,
n_def_non_alias,
is_literal,
is_usage_alias,
p,
e,
r,
l,
v_int,
v_float,
v_str,
v_cl_year,
v_cl,
c_min,
c_max,
is_optional,
is_plural,
is_inf,
c_sub,
c_str,
n_sub1,
n_sub2,
n_sub3,
n_default,
is_limited 
FROM {$GLOBALS['pre']}e AS e ";


$GLOBALS['temp']['sql_select_sub']="SELECT
n_sup,
i,
n,
c_min,
c_max,
is_optional,
is_plural,
is_inf,
e,
l
FROM {$GLOBALS['pre']}sub AS sub ";



$GLOBALS['row-0']=array('id'=>0,
			'n'=>'',
			'n_def'=>'',
			'n_def_non_alias'=>'',
			'is_literal'=>0,
			'is_usage_alias'=>0,
		        'p'=>'',
		        'e'=>'',
		        'r'=>'',
		        'l'=>'',
		        'v_int'=>0,
		        'v_float'=>0.0,
		        'v_str'=>'',
		        'v_cl_year'=>$GLOBALS['conf']['cl-year-min'],
		        'v_cl'=>$GLOBALS['conf']['cl-min'],
		        'c_min'=>1,
		        'c_max'=>1,
		        'is_optional'=>0,
		        'is_plural'=>0,
		        'is_inf'=>0,
		        'c_sub'=>0,
		        'c_str'=>0,
		        'n_sub1'=>'',
		        'n_sub2'=>'',
		        'n_sub3'=>'',
		        'n_default'=>'',
		        'is_limited'=>0,
		        'l_sup'=>'',
		        'subl'=>"''");

		        
//------------------------------------------------------
function is_privacy_see_ok($r)
{
  if(isset($r['n_u_cr']))
  {
    return true;
  }

  return true; // TODO
}

//------------------------------------------------------
function is_co_ok($n_def,$xin)
{
  return true; // TODO
}


//------------------------------------------------------
function &get_row_e_by_n($n)
{
   if(isset($GLOBALS['data-def'][$n])) return $GLOBALS['data-def'][$n];
   if(isset($GLOBALS['data-exp'][$n])) return $GLOBALS['data-exp'][$n];
   if(isset($GLOBALS['data-new'][$n])) return $GLOBALS['data-new'][$n];
   if(isset($GLOBALS['data-temp'][$n])) return $GLOBALS['data-temp'][$n];
   
   $GLOBALS['data-temp'][$n]=dbs::singlerow("{$GLOBALS['temp']['sql_select_e']} 
      WHERE is_now=1 AND n='{$n}'");

   return $GLOBALS['data-temp'][$n];
}

//------------------------------------------------------
function exist_e($n)
{
  return (dbs::value("SELECT 1 FROM {$GLOBALS['pre']}e 
      WHERE is_now=1 AND n='{$n}'")=='1');
}

		        


//------------------------------------------------------
function is_sub_rec_of($n_def,$n)
{
   if($n_def==$n)return 1;

   if(isset($GLOBALS['data-new'][$n]))
   {
      //handle in cache
      //print_r($GLOBALS['data-new'][$n]['sup']);
      return (array_search($n_def,$GLOBALS['data-new'][$n]['sup'])?1:0);      
   }
   else
   {
      return (dbs::value("SELECT 1 FROM {$GLOBALS['pre']}sup 
      WHERE n='{$n}' 
      AND n_sup='{$n_def}'")?1:0);
   }
}



		        
		        
//------------------------------------------------------
//------------------------------------------------------ PRE-CHECKS/loads Begin
//------------------------------------------------------

if($GLOBALS['temp']['i-context'])
{
  include('php/api_07_context.php');
}

if($GLOBALS['temp']['i-path'])
{
  include('php/api_07_path.php');
}

if($GLOBALS['temp']['i-sys'])
{
  include('php/api_07_sys.php');
}


$GLOBALS['temp']['i-list-def-exist']=0;
$GLOBALS['temp']['i-list-def-missing']=0;
$GLOBALS['data-def']=array();

if($GLOBALS['temp']['i-list-def']>0)
{
  $GLOBALS['data-def']=dbs::idlist("{$GLOBALS['temp']['sql_select_e']} 
  WHERE is_now=1 AND n IN ({$GLOBALS['temp']['n-list-def']})",'n');

  $GLOBALS['temp']['i-list-def-exist']=count($GLOBALS['data-def']);
  $GLOBALS['temp']['i-list-def-missing']=$GLOBALS['temp']['i-list-def'] - $GLOBALS['temp']['i-list-def-exist'];
}

if($GLOBALS['temp']['i-list-new']<$GLOBALS['temp']['i-list-def-missing'])
{
  msg('error-syntax','EXA-resolving failed: Too many unknown definitions used.');
}


if($GLOBALS['temp']['i-list-new']>0)
{
  $cc_new_error_info=dbs::value("SELECT GROUP_CONCAT(n) AS info 
    FROM {$GLOBALS['pre']}e 
    WHERE n IN ({$GLOBALS['temp']['n-list-new']}) AND is_now=1 GROUP BY is_now");

  if(strlen($cc_new_error_info)>0)
  {
    msg('error-syntax','EXA-resolving failed: Some new definition-names already exist:'.$cc_new_error_info);
  }
}


$GLOBALS['temp']['i-list-exp-exist']=0;
$GLOBALS['temp']['i-list-exp-missing']=0;
$GLOBALS['data-exp']=array();

if($GLOBALS['temp']['i-list-exp']>0)
{
  $GLOBALS['data-exp']=dbs::idlist("{$GLOBALS['temp']['sql_select_e']} 
  WHERE is_now=1 AND n IN ({$GLOBALS['temp']['n-list-exp']})",'n');

  $GLOBALS['temp']['i-list-exp-exist']=count($GLOBALS['data-exp']);
  $GLOBALS['temp']['i-list-exp-missing']=$GLOBALS['temp']['i-list-exp'] - $GLOBALS['temp']['i-list-exp-exist'];
}

if($GLOBALS['temp']['i-list-new'] < $GLOBALS['temp']['i-list-exp-missing'])
{
  msg('error-syntax','EXA-resolving failed: Too many unknown definitions used in expressions.');
}

if(!$GLOBALS['login']&&$GLOBALS['temp']['i-list-exp-missing']>0)
{
  msg('error-syntax','EXA-resolving failed: Unknown entity-names in expressions.');
}

$GLOBALS['data-temp']=array();
$GLOBALS['data-new']=array();		        
		        

//------------------------------------------------------
//------------------------------------------------------ PRE-CHECKS/loads End
//------------------------------------------------------

// here we have the operations one after the other. (leafs come first, done by symtax)


for($i=1;$i<=$GLOBALS['in-i'];$i++)
{
  echo "\n\r<br/>IN: y:'{$GLOBALS['in'][$i]['y']}'; v:'{$GLOBALS['in'][$i]['v']}' --------- <br/>/n/r";

  if(isset($GLOBALS['in'][$i]['n_def']))
  {
    // --------------------------------------- ---------------------------------
    // --------------------------------------- DEF BEGIN --------------------
    // --------------------------------------- ---------------------------------
    // can be: alias, def_e, def-f, def-e-usage

    // check new name in DB
    if(dbs::value("SELECT 1 FROM {$GLOBALS['pre']}e WHERE n='{$GLOBALS['in'][$i]['v']}' AND is_now=1"))
    {
      msg('error-semantic','EXA-resolving failed: The new name for def/alias already exists: '.$GLOBALS['in'][$i]['v'],$GLOBALS['in'][$i]);
    }
    
    $r=$GLOBALS['row-0'];
    
    $r['n']=$GLOBALS['in'][$i]['v'];
    $r['n_def']=$GLOBALS['in'][$i]['n_def'];
    
    switch($GLOBALS['in'][$i]['y'])
    {
      case 'alias': //---------------------------------------------------------
      case 'def_e_usage':

	// check if n_def exists
	$GLOBALS['in'][$i]['r_def']=&get_row_e_by_n($GLOBALS['in'][$i]['n_def']);
	
	if(count($GLOBALS['in'][$i]['r_def'])<1)
	{
	    msg('error-semantic','EXA-resolving failed: The definition entity for def/alias could not be found: '.$GLOBALS['in'][$i]['n_def'],$GLOBALS['in'][$i]);
	}

	if($GLOBALS['in'][$i]['r_def']['is_limited'])
	{
	  if($GLOBALS['in'][$i]['r_def']['n_u_cr']!=$GLOBALS['context']['n-u'])
	  {
	    msg('error-semantic','EXA-resolving failed: Definition is limited by the creator user.',$GLOBALS['in'][$i]);
	  }
	}

	if(!is_privacy_see_ok($GLOBALS['in'][$i]['r_def']))
	{
	  msg('error-semantic','EXA-resolving failed: Condition-check failed for def/alias: '.$r['n'],'Privacy-check failed.'.$GLOBALS['in'][$i]);
	}
	
	switch($GLOBALS['in'][$i]['r_def']['l'])
	{
	  case 'alias':
	    $r['n_def_non_alias']=$GLOBALS['in'][$i]['r_def']['n_def_non_alias'];
	    
	    $GLOBALS['in'][$i]['r_def_non_alias']=&get_row_e_by_n($r['n_def_non_alias']);
	    if(count($GLOBALS['in'][$i]['r_def_non_alias'])<1)
	    {
		msg('error-semantic','EXA-resolving failed: The def-non-alias for alias could not be found',$GLOBALS['in'][$i]);
	    }
	    
	  break;
	  case 'def':
	    $r['n_def_non_alias']=$GLOBALS['in'][$i]['r_def']['n_def'];
	  break;
	  default:
	    msg('error-semantic','EXA-resolving failed: An alias can only be made of a definition or an alias.',$GLOBALS['in'][$i]);

	}

	$magic_stop=0;
	
	if(isset($GLOBALS['in'][$i]['is_plural']))
	{
	  // this is a p-alias or e-usage-definition
	  $r['n_sub1']=$r['n_def'];
	  $r['p']='p';
	  $r['e']=$r['r']='e';  

	  switch($r['n'])
	  {
	    case 'e':
	    case 'f':
	    case 'p':
	      // hardcore-magic: make efp, hide p-n
	      $r['n_def']=$r['n_def_non_alias']=$r['n'];
	      $r['sub']=array(0=>0); // so that sub is not build
	      $r['l']='def';
	      $r['sup']=array();
	      $magic_stop=1;
	    break;
	    default:

	      $r['n_def']=$r['n_def_non_alias']="p-{$r['n_def']}";
	      
	      if(!exist_e($r['n_def']))
	      {
		if(!isset($GLOBALS['data-new'][$r['n_def']]))
		{
		  $r_p=$GLOBALS['row-0'];
		  $r_p['n']=$r['n_def'];
		  $r_p['n_def']=$r_p['n_def_non_alias']=$r_p['r']=$r_p['e']=$r_p['p']='p';
		  $r_p['l']='def';
		  $r_p['n_sub1']=$r['n_sub1'];
		  $r_p['is_plural']=$r_p['is_optional']=$r_p['is_inf']=1;
		  $r_p['c_min']=$r_p['c_max']=0;
		  
		  $GLOBALS['data-new'][$r['n_def']]=$r_p;
		}
		
	      }
	      
	      $r['sup']=array(1=>$r['n_def_non_alias']);
	      
	      break;
	  }
	  	  
	  $r['is_plural']=1;
	  $r['c_min']=$GLOBALS['in'][$i]['c_min'];
	  $r['c_max']=$GLOBALS['in'][$i]['c_max'];
	  $r['is_optional']=($r['c_min']==0?1:0);
	  $r['is_inf']=($r['c_max']==0?1:0);

	}
	else
	{
	  $r['p']=$GLOBALS['in'][$i]['r_def']['p'];
	  $r['e']=$GLOBALS['in'][$i]['r_def']['e'];
	  $r['r']=$GLOBALS['in'][$i]['r_def']['r'];
	  
	  $r['n_def_non_alias']=$GLOBALS['in'][$i]['r_def']['n_def_non_alias'];
	  
// 	  $r['n_sub1']=$GLOBALS['in'][$i]['r_def']['n_sub1'];
// 	  $r['n_sub2']=$GLOBALS['in'][$i]['r_def']['n_sub2'];
// 	  $r['n_sub3']=$GLOBALS['in'][$i]['r_def']['n_sub3'];


	  switch($r['n'])
	  {
	    case 'b':
	    case 'x':
	    case 'noo':
	      // hardcore-magic: make bit stand-alone, hide defined and undefined
	      $r['n_def_non_alias']='b';
	      $r['sub']=array(0=>0); // so that sub is not build
	      $r['l']='def';
	      $magic_stop=1;
	    break;
	    default:
	      if(!isset($GLOBALS['in'][$i]['r_def']['sup']))
	      {
		$GLOBALS['in'][$i]['r_def']['sup']=dbs::idlist("SELECT n_sup FROM {$GLOBALS['pre']}sup
		WHERE n='{$GLOBALS['in'][$i]['r_def']['n']}' 
		ORDER BY c_root",'c_root');
	      }
	      $r['sup']=$GLOBALS['in'][$i]['r_def']['sup'];
	      $r['sup'][count($GLOBALS['in'][$i]['r_def']['sup'])+1]=$GLOBALS['in'][$i]['r_def']['n'];
	  }
	}
	
	if($magic_stop)break;
        
        if($GLOBALS['in'][$i]['c_sub']<1)
	{
	  // this is an alias
	   $r['l']='alias';
	   
	   $r['c_sub']=$GLOBALS['in'][$i]['r_def']['c_sub'];
	   $r['n_sub1']=$GLOBALS['in'][$i]['r_def']['n_sub1'];
	   $r['n_sub2']=$GLOBALS['in'][$i]['r_def']['n_sub2'];
	   $r['n_sub3']=$GLOBALS['in'][$i]['r_def']['n_sub3'];
	   
	   break; // this was an alias
	}
	
	// ------------------------------ FROM HERE ON ONLY def_e_usage
	$r['l']='def';
	
	if($GLOBALS['in'][$i]['c_sub']>$GLOBALS['in'][$i]['r_def']['c_sub'])
	{
	  msg('error-semantic','EXA-resolving failed: too many sub-entities given for definition',$GLOBALS['in'][$i]);
	}

	if(!isset($GLOBALS['in'][$i]['r_def']['sub']))
	{
	  $GLOBALS['in'][$i]['r_def']['sub']=dbs::idlist("{$GLOBALS['temp']['sql_select_sub']} 
	  WHERE n_sup='{$GLOBALS['in'][$i]['r_def']['n_def_non_alias']}' AND is_now=1
	  ORDER BY i",'i');
	}
	
	$r['sub']=array(0=>0);
        
        
	for($j=1;$j<=$GLOBALS['in'][$i]['c_sub'];$j++)
	{
	  $is_plural=0;
	  $c_min=1;
	  $c_max=1;
	  $n=$GLOBALS['in'][$i]['sub'][$j]['y']; // default for literal below
	  $e=$n; // default for literal below
	  $l='def'; // default for literal below
	  
	  switch($n)
	  {
	    case 'def_sub_p':
	  	    
	      if(!$GLOBALS['in'][$i]['r_def']['sub'][$j]['is_plural'])
	      {
		msg('error-semantic','EXA-resolving failed: a p given in definition but sub-entity is not defined plural',$GLOBALS['in'][$i]);
	      }
	      
	      $c_p=$GLOBALS['in'][$i]['sub'][$j]['c_sub'];
	    
	      if($c_p<$GLOBALS['in'][$i]['r_def']['sub'][$j]['c_min'])
	      {
		msg('error-semantic','EXA-resolving failed: the number of elements in p is smaller than minimum: '.$GLOBALS['in'][$i]['r_def']['sub'][$j]['c_min'],$GLOBALS['in'][$i]);
	      }

	      if(!$GLOBALS['in'][$i]['r_def']['sub'][$j]['is_inf'])
	      {
		if($c_p>$GLOBALS['in'][$i]['r_def']['sub'][$j]['c_max'])
		{
		  msg('error-semantic','EXA-resolving failed: the number of elements in p is greater than maximum: '.$GLOBALS['in'][$i]['r_def']['sub'][$j]['c_max'],$GLOBALS['in'][$i]);
		}
	      }	
	      
	      $n=$GLOBALS['in'][$i]['r_def']['sub'][$j]['n'];
	      $GLOBALS['in'][$i]['sub'][$j]['n_def']=$n;
	      
	      for($k=1;$k<=$c_p;$k++)
	      {
		$n_p=$GLOBALS['in'][$i]['sub'][$j]['sub'][$k]['y']; // for literals and such
		if($n_p=='e_name')$n_p=$GLOBALS['in'][$i]['sub'][$j]['sub'][$k]['v'];
		
		if(!is_sub_rec_of($n,$n_p))
		{
		  msg('error-semantic','EXA-resolving failed: entity used in p in definition does not match',$GLOBALS['in'][$i]);
		}
	      }
	      
	      $is_plural=1;
	      $c_min=$c_max=$c_p;
	      $e='p';
	      $l='usage';
	      
	    break;
	    case 'def_sub_e':
	      
	      $n=$GLOBALS['in'][$i]['sub'][$j]['v'];
	      $n_def=$GLOBALS['in'][$i]['r_def']['sub'][$j]['n'];

	      if(!is_sub_rec_of($n_def,$n))
	      {
		msg('error-semantic','EXA-resolving failed: entity used in definition does not match',$GLOBALS['in'][$i]);
	      }
	      
	      //print_r($GLOBALS['in'][$i]['r_def']);
	      if($GLOBALS['in'][$i]['r_def']['sub'][$j]['l']=='usage')
	      {
		  msg('error-semantic','EXA-resolving failed: The definition-sub-entity must be alias or definition.',$GLOBALS['in'][$i]);
	      }
	      
	      $e='e';
	      $l='def';
	      
	    break;
	    case 'b':
	    case 'x':
	    case 'noo':
	      $e='e';
	      $l='def';
	    case 'sl':
	    case 'c':
	    case 'int':
	    case 'float':
              // check for type literal
              $n_def=$GLOBALS['in'][$i]['r_def']['sub'][$j]['n'];
	      if(!is_sub_rec_of($n,$n_def))
	      {
		msg('error-semantic','EXA-resolving failed: a literal used ub definition does not match',$GLOBALS['in'][$i]);
	      }
	    break;
	  }
	  
	  // build sub for result
	  $r['sub'][]=array('n_sup'=>$r['n'],
	      'i'=>$j,
	      'n'=>$n,
	      'c_min'=>$c_min,
	      'c_max'=>$c_max,
	      'is_optional'=>($c_min==0?1:0),
	      'is_plural'=>$is_plural,
	      'is_inf'=>($c_max==0?1:0),
	      'e'=>$e,
	      'l'=>$l);
	}
   
	if($GLOBALS['in'][$i]['r_def']['c_sub']>$GLOBALS['in'][$i]['c_sub'])
	{
	  for($j=$GLOBALS['in'][$i]['c_sub']+1;$j<=$GLOBALS['in'][$i]['r_def']['c_sub'];$j++)
	  {
	    if($GLOBALS['in'][$i]['r_def']['sub'][$j]['is_optional'])
	    {
	      $GLOBALS['in'][$i]['c_sub']+=1;
	      $GLOBALS['in'][$i]['sub'][]=array('v'=>'noo','y'=>'noo');
	    }
	    else
	    {
	      msg('error-semantic','EXA-resolving failed: Some non-optional sub-entites are missing for definition',$GLOBALS['in'][$i]);
	    }
	  }
	}
	
	if(!is_co_ok($GLOBALS['in'][$i]['r_def']['n'],$r))
	{
	  msg('error-semantic','EXA-resolving failed: Condition-check failed for definition',$GLOBALS['in'][$i]);
	}
	
	// TODO: here comes the e-usage-match
	      
      break;  
      case 'def_e': //---------------------------------------------------------
        // create e-row

        $r['p']=$r['r']=$r['e']='e';
	$r['l']='def';
	
	$r['sup']=array(1=>'e');
	
	for($j=1;$j<=$GLOBALS['in'][$i]['c_sub'];$j++)
	{
	
	  if($GLOBALS['in'][$i]['sub'][$j]['r']['l']=='usage')
	  {
	      msg('error-semantic','EXA-resolving failed: e-definition-sub-entity must be alias or definition.',$GLOBALS['in'][$i]);
	  }
	
	  // sub must be def_sub_e
	  $is_plural=0;
	  $c_min=1;
	  $c_max=1;

	  if(isset($GLOBALS['in'][$i]['sub'][$j]['is_plural']))
	  {
	    $is_plural=1;
	    $c_min=$GLOBALS['in'][$i]['sub'][$j]['c_min'];
	    $c_max=$GLOBALS['in'][$i]['sub'][$j]['c_max'];
	  }
	  
	  $r['sub'][]=array('n_sup'=>$r['n'],
	      'i'=>$j,
	      'n'=>$GLOBALS['in'][$i]['sub'][$j]['v'],
	      'c_min'=>$c_min,
	      'c_max'=>$c_max,
	      'is_optional'=>($c_min==0?1:0),
	      'is_plural'=>$is_plural,
	      'is_inf'=>($c_max==0?1:0),
	      'e'=>'e',
	      'l'=>'def');
	 }
 
      break;  
      case 'def_f': //---------------------------------------------------------
        // create f-row

        $r['p']='e';
	$r['r']=$r['e']=$r['n_def_non_alias']='f';
	$r['l']='def';

	$r['sup']=array(0=>0);

	if(isset($GLOBALS['in'][$i]['sub'][1]['is_plural']))
	{
	  $r['is_plural']=1;
	  $r['c_min']=$GLOBALS['in'][$i]['sub'][1]['c_min'];
	  $r['c_max']=$GLOBALS['in'][$i]['sub'][1]['c_max'];
	}

	$GLOBALS['in'][$i]['r_fin']=&get_row_e_by_n($r['n_sub1']=$GLOBALS['in'][$i]['sub'][1]['v']);
	if(count($GLOBALS['in'][$i]['r_fin'])<1)
	{
	  msg('error-semantic','EXA-resolving failed: the input-param for function-definition was not found',$GLOBALS['in'][$i]);
	}

	
	if(isset($GLOBALS['in'][$i]['sub'][2]['is_plural']))
	{
	  $r['is_plural_fo']=1;
	  $r['c_min_fo']=$GLOBALS['in'][$i]['sub'][2]['c_min'];
	  $r['c_max_fo']=$GLOBALS['in'][$i]['sub'][2]['c_max'];
	}
	
	$GLOBALS['in'][$i]['r_fo']=&get_row_e_by_n($r['n_sub2']=$GLOBALS['in'][$i]['sub'][2]['v']);
	if(count($GLOBALS['in'][$i]['r_fo'])<1)
	{
	  msg('error-semantic','EXA-resolving failed: the output-param for function-definition was not found',$GLOBALS['in'][$i]);
	}
        
      break;  
      default:
	msg('error-syntax','invalid case in definition',$GLOBALS['in'][$i]);
      break;
    }

    $GLOBALS['in'][$i]['r']=$r;
    $GLOBALS['data-new'][$r['n']]=&$GLOBALS['in'][$i]['r'];
    
    // --------------------------------------- ---------------------------------
    // --------------------------------------- DEF END --------------------
    // --------------------------------------- ---------------------------------
  }
  elseif($GLOBALS['in'][$i]['c_sub']==0)
  {
    // --------------------------------------- ---------------------------------
    // --------------------------------------- SINGLE BEGIN --------------------
    // --------------------------------------- ---------------------------------
    // can be: def_sub_e, def_sub_p, context, var_get, b, x, noo, s, cl, int, float, e-name, 
    // only in op: p, f-name 

    switch($GLOBALS['in'][$i]['y'])
    {
      case 'p': //---------------------------------------------------------
        // this only happens in op, second parameter (by syntax-check)
        // do nothing
        // also n_def is not set here
      break;
      case 'f-name': //---------------------------------------------------------
        // this only happens in op, first parameter (by syntax-check)
        // function must be a new-defined one in this statement
        
	if(!isset($GLOBALS['data-new'][$GLOBALS['in'][$i]['v']]))
	{
	  msg('error-semantic','EXA-resolving failed: an op must be defined at the same statement as the function.',$GLOBALS['in'][$i]);
	}
	// no setting of n_def needed, only used in op
      break;
      case 'b': //---------------------------------------------------------
      case 'x':
      case 'noo':
	// nothing to do
	$GLOBALS['in'][$i]['n_def']='b';
      break;  
      case 's': //---------------------------------------------------------
      case 'cl':
      case 'int':
      case 'float':
	// nothing to do
	$GLOBALS['in'][$i]['n_def']=$GLOBALS['in'][$i]['y'];
      break;
      case 'def_sub_p': //---------------------------------------------------------
       // Note: check for sub-entities is done inside def_e_usage
       // Nothing else to do here
      break;
      case 'context': //--------------------------------------------------------- 
        // include context-resolution
               
	get_context($GLOBALS['in'][$i]);
      break; 
      case 'var_get': //---------------------------------------------------------
        // read variable
        
        $in_val=&$GLOBALS['in'][$i]['var_top']['var'][$GLOBALS['in'][$i]['v']]['in'];
        
	$GLOBALS['in'][$i]['n_def']=$in_val['n_def'];
	if(isset($in_val['r']))$GLOBALS['in'][$i]['r']=&$in_val['r'];

	if(isset($in_val['is_plural']))
	{
	  $GLOBALS['in'][$i]['is_plural']=1;
	  $GLOBALS['in'][$i]['c_min']=$in_val['c_min'];
	  $GLOBALS['in'][$i]['c_max']=$in_val['c_max'];
	}
	
      break; 
      case 'e_name': 
      case 'def_sub_e': //---------------------------------------------------------
	// check if entity exists

	$r=$GLOBALS['in'][$i]['r']=&get_row_e_by_n($GLOBALS['in'][$i]['v']);
	if(count($r)<1)
	{
	    msg('error-semantic','EXA-resolving failed: The single entity could not be found',$GLOBALS['in'][$i]);
	}
	
	if($r['l']=='usage')
	{
	  // usage may not happen for definition, but is checked at definition-sub-check
	  $GLOBALS['in'][$i]['n_def']=$r['n_def'];
	}
	else
	{
	  $GLOBALS['in'][$i]['n_def']=$r['n'];
	}
	
	if(!is_privacy_see_ok($r))
	{
	  msg('error-semantic','EXA-resolving failed: Condition-check failed for single entity: '.$r['n'],'Privacy-check failed.'.$GLOBALS['in'][$i]);
	}
	
	// condition-check not need for single entity
	
      break;
      default:
	msg('error-syntax','invalid case in single entity',$GLOBALS['in'][$i]);
    }
    
    if(isset($GLOBALS['in'][$i]['path']))
    {
      $onlyCheckPath=0;
      if(isset($GLOBALS['in'][$i]['in_each'])||
	 isset($GLOBALS['in'][$i]['in_sub'])||
	 isset($GLOBALS['in'][$i]['in_lot'])||
	 isset($GLOBALS['in'][$i]['in_if'])||
	 isset($GLOBALS['in'][$i]['is_in_op']))
      {
	$onlyCheckPath=1;
      }
      resolve_path($GLOBALS['in'][$i],$onlyCheckPath);    
    }
  
    // --------------------------------------------------------------------------
    // --------------------------------------- SINGLE END --------------------
    // --------------------------------------------------------------------------
  }
  else
  {
    // -------------------------------------------------------------------------
    // --------------------------------------- PLURAL BEGIN --------------------
    // --------------------------------------------------------------------------
    // can be: var_set, p, if, each, lot, sub, f_usage, e_usage,
    // oly in each: break
    
    if(isset($GLOBALS['in'][$i]['is_sys']))
    {
      //handled as sys: op, limited, default, privacy 
      resolve_sys($GLOBALS['in'][$i]);
    }
    else
    {
      switch($GLOBALS['in'][$i]['y'])
      {
	case 'var_set': //---------------------------------------------------------
	  // set variable to first sub-entity
	  
	  $GLOBALS['in'][$i]['var_top']['var'][$GLOBALS['in'][$i]['v']]['in']=&$GLOBALS['in'][$i]['sub'][1];
	  
	  $GLOBALS['in'][$i]['n_def']=$GLOBALS['in'][$i]['sub'][1]['n_def'];

	  if(isset($GLOBALS['in'][$i]['sub'][1]['r']))$GLOBALS['in'][$i]['r']=&$GLOBALS['in'][$i]['sub'][1]['r'];
	  
	  if(isset($GLOBALS['in'][$i]['sub'][1]['is_plural']))
	  {
	    $GLOBALS['in'][$i]['is_plural']=1;
	    $GLOBALS['in'][$i]['c_min']=$GLOBALS['in'][$i]['sub'][1]['c_min'];
	    $GLOBALS['in'][$i]['c_max']=$GLOBALS['in'][$i]['sub'][1]['c_max'];
	  }
	break;
	case 'p': //---------------------------------------------------------
	    // p are checked within the context they are allowed: def_e, f-usage and e_usage 
	
	break;
	case 'pile': //---------------------------------------------------------

	  $in_last=&$GLOBALS['in'][$i]['sub'][$GLOBALS['in'][$i]['c_sub']];
	  $GLOBALS['in'][$i]['n_def']=$in_last['n_def'];
	  if(isset($in_last['r']))
	  {
	    $GLOBALS['in'][$i]['r']=&$in_last['r'];
	  }

	  if(isset($in_last['is_plural']))
	  {
	    $GLOBALS['in'][$i]['is_plural']=1;
	    $GLOBALS['in'][$i]['c_min']=$in_last['c_min'];
	    $GLOBALS['in'][$i]['c_max']=$in_last['c_max'];
	  }
	  
	break;
	case 'if': //---------------------------------------------------------
	  // TODO also support CASE
	  // check both sides individually (true/false)
	  // perform the if
	  // Note: DARK CODE can happen inside a if in the case thats never reached, by 'if false'
	  // Note: if is not allowed in lot/sub, because they are filters anyway. (Indirect if can happen)
	  
	  
	break;
	case 'break': //---------------------------------------------------------
	  // break the each-loop,
	  // to (help) avoid broken p-blocks a break can only happen 'in if inside each'
	  // Note: DARK CODE can happen inside a block after a if-true-break;
	  
	  
	  
	break;
	case 'each': //---------------------------------------------------------
	  // perform the each-loop,
	  // the resulting type is given by init-parameter [2]
	  // the exp must fit the resulting type, but can be more specific
	  // the exp [3] was checked but not executed so far.
	  // Note: each is not allowed in lot/sub, to keep them flat filters. (Indirect each can happen)
	  // Note: lot/sub are not allowed in each, because they are ment to be input for an each (indirectly it can happen)
	  
	break;
	case 'lot': //---------------------------------------------------------
	  // perform the lot-call,
	  // for e-def/e-alias mainly
	  // can not happen on p and e-usage
	  // can also happen on f (allows filtering both in and out)
	break;
	case 'sub': //---------------------------------------------------------
	  // perform the sub-call,
	  // for p mainly
	  // can not happen on f
	  // can also be used for e, but the result is nontrivial to type then
	  // decission: better not allow sub for e (when having @1@2 path also) ?!
	break;
	case 'e_usage': //---------------------------------------------------------
	  // match the entity
	  
	  $GLOBALS['in'][$i]['r_e']=&get_row_e_by_n($GLOBALS['in'][$i]['v']);
	  if(count($GLOBALS['in'][$i]['r_e'])<1)
	  {
	      msg('error-semantic','EXA-resolving failed: entity could not be found',$GLOBALS['in'][$i]);
	  }
		  
	  if($GLOBALS['in'][$i]['r_e']['e']!='e')
	  {
	      msg('error-internal','EXA-resolving failed: a non-e was tried to be matched',$GLOBALS['in'][$i]);
	  }
	  
	  if($GLOBALS['in'][$i]['r_e']['l']=='alias')
	  {
	    $GLOBALS['in'][$i]['r_e_non_alias']=&get_row_e_by_n($GLOBALS['in'][$i]['r_e']['n_def_non_alias']);
	    if(count($GLOBALS['in'][$i]['r_e_non_alias'])<1)
	    {
	      msg('error-semantic','EXA-resolving failed: The expression non-alias-definition could not be found',$GLOBALS['in'][$i]);
	    }
	    
	    if(!is_privacy_see_ok($GLOBALS['in'][$i]['r_e_non_alias']))
	    {
	      // TODO not sure if this check is needed, because if alias can be seen, def can also?!
	      msg('error-semantic','EXA-resolving failed: Condition-check failed for expression','Privacy-check failed.'.$r);
	    }
	    
	  }
	  else
	  {
	    $GLOBALS['in'][$i]['r_e_non_alias']=&$GLOBALS['in'][$i]['r_e'];
	  }

	  if($GLOBALS['in'][$i]['c_sub']>$GLOBALS['in'][$i]['r_e_non_alias']['c_sub'])
	  {
	    msg('error-semantic','EXA-resolving failed: too many sub-entities for entity-definition',$GLOBALS['in'][$i]);
	  }
	  
	  
	  if(!isset($GLOBALS['in'][$i]['r_e_non_alias']['sub']))
	  {
	    $GLOBALS['in'][$i]['r_e_non_alias']['sub']=dbs::idlist("{$GLOBALS['temp']['sql_select_sub']} 
	    WHERE n_sup='{$GLOBALS['in'][$i]['r_e_non_alias']['n']}' AND is_now=1
	    ORDER BY i",'i');
	  }
	  
	  if($GLOBALS['in'][$i]['c_sub']<$GLOBALS['in'][$i]['r_e_non_alias']['c_sub'])
	  {
	    if(!$GLOBALS['in'][$i]['r_e_non_alias']['sub'][$GLOBALS['in'][$i]['c_sub']+1]['is_optional'])
	    {
	      msg('error-semantic','EXA-resolving failed: missing non-optional parameters for entity-use',$GLOBALS['in'][$i]);
	    }
	  }
	  
	  for($j=1;$j<=$GLOBALS['in'][$i]['c_sub'];$j++)
	  {
	    if($GLOBALS['in'][$i]['sub'][$j]['is_plural'])
	    {
	      if(!$GLOBALS['in'][$i]['r_e_non_alias']['sub'][$j]['is_plural'])
	      {
		msg('error-semantic','EXA-resolving failed: plural sub-entity given, but definition is non-plural',$GLOBALS['in'][$i]);
	      }
	      
	      if($GLOBALS['in'][$i]['sub'][$j]['c_min']<$GLOBALS['in'][$i]['r_e_non_alias']['sub'][$j]['c_min'])
	      {
		msg('error-semantic','EXA-resolving failed: plural sub-entity has too few sub-entities',$GLOBALS['in'][$i]);
	      }

	      if($GLOBALS['in'][$i]['r_e_non_alias']['sub'][$j]['c_max']>0)
	      {
		if($GLOBALS['in'][$i]['sub'][$j]['c_max']>$GLOBALS['in'][$i]['r_e_non_alias']['sub'][$j]['c_max'])
		{
		  msg('error-semantic','EXA-resolving failed: plural sub-entity has too many sub-entities',$GLOBALS['in'][$i]);
		}
	      }
	      
	      //check each element in plural
	      for($k=1;$k<=$GLOBALS['in'][$i]['sub'][$j]['c_sub'];$k++)
	      {
		$n=$GLOBALS['in'][$i]['sub'][$j]['sub'][$k]['n_def'];
		$n_def=$GLOBALS['in'][$i]['r_e_non_alias']['sub'][$j]['n_def'];    
		if(!is_sub_rec_of($n_def,$n))
		{
		  msg('error-semantic','EXA-resolving failed: a sub-entity in p does not match the definition',$GLOBALS['in'][$i]);
		}
	      }	  
	    }
	    else
	    {
	      // sub-entity is single
	      if(!is_sub_rec_of($GLOBALS['in'][$i]['r_e_non_alias']['sub'][$j]['n_def'],$GLOBALS['in'][$i]['sub'][$j]['n_def']))
	      {
		msg('error-semantic','EXA-resolving failed: sub-entity does not match the definition',$GLOBALS['in'][$i]);
	      }
	    }
	  }
	  
	  for($j=$GLOBALS['in'][$i]['c_sub']+1;$j<=$GLOBALS['in'][$i]['r_e_non_alias']['c_sub'];$j++)
	  {
	    $GLOBALS['in'][$i]['c_sub']+=1;
	    $GLOBALS['in'][$i]['sub'][$j]=array('v'=>'noo','y'=>'noo');
	  }	  
	  
	  $GLOBALS['in'][$i]['n_def']=$GLOBALS['in'][$i]['r_e_non_alias']['n_def'];
	  
	break;
	case 'f_usage': //---------------------------------------------------------
	  // match the function
	  // EXECUTE IT

	  $GLOBALS['in'][$i]['r_f']=&get_row_e_by_n($GLOBALS['in'][$i]['v']);
	  if(count($GLOBALS['in'][$i]['r_f'])<1)
	  {
	      msg('error-semantic','EXA-resolving failed: function could not be found',$GLOBALS['in'][$i]);
	  }
		  
	  if($GLOBALS['in'][$i]['r_f']['e']!='f')
	  {
	      msg('error-internal','EXA-resolving failed: a non-function was tried to be executed',$GLOBALS['in'][$i]);
	  }
	  
	  if($GLOBALS['in'][$i]['c_sub']>1)
	  {
	    if(!$GLOBALS['in'][$i]['r_f']['is_plural'])
	    {
	      msg('error-semantic','EXA-resolving failed: single-param-function used with several parameters.',$GLOBALS['in'][$i]);
	    }
	    $in_fin=&$GLOBALS['in'][$i];
	  }
	  else
	  {
	    $in_fin=&$GLOBALS['in'][$i][1];
	  }


	  if($GLOBALS['in'][$i]['r_f']['is_plural'])
	  {  
	    if(!$GLOBALS['in'][$i]['r_f']['is_inf'])
	    {
	      if($in_fin['c_sub']>$GLOBALS['in'][$i]['r_f']['c_max'])
	      {
		msg('error-semantic','EXA-resolving failed: function used with with too many parameters.',$GLOBALS['in'][$i]);
	      }
	    }

	    if($in_fin['c_sub']<$GLOBALS['in'][$i]['r_f']['c_min'])
	    {
	      msg('error-semantic','EXA-resolving failed: function used with with too few parameters.',$GLOBALS['in'][$i]);
	    }
	  }
	
	  
	  for($j=1;$j<=$in_fin['c_sub'];$j++)
	  {
	    if(!is_sub_rec_of($GLOBALS['in'][$i]['r_f']['n_sub1'],$in_fin['sub'][$j]['n_def']))
	    {
	      msg('error-semantic','EXA-resolving failed: input used in for function not match the definition',$GLOBALS['in'][$i]);
	    }
	  }
		  
	  
	  $GLOBALS['in'][$i]['n_def']=$GLOBALS['in'][$i]['r_f']['n_sub2'];
	  if($GLOBALS['in'][$i]['r_f']['is_plural_fo'])
	  {
	    $GLOBALS['in'][$i]['is_plural']=$GLOBALS['in'][$i]['r_f']['is_plural_fo'];
	    $GLOBALS['in'][$i]['c_min']=$GLOBALS['in'][$i]['r_f']['c_min_fo'];
	    $GLOBALS['in'][$i]['c_max']=$GLOBALS['in'][$i]['r_f']['c_max_fo'];
	  }
	  
	break;
	default:
	  msg('error-internal','invalid case in a plural entity',$GLOBALS['in'][$i]);

      }
    }
      
    // --------------------------------------------------------------------------
    // --------------------------------------- PLURAL END --------------------
    // --------------------------------------------------------------------------
  }
  
  
  if(isset($GLOBALS['in'][$i]['is_not'])||isset($GLOBALS['in'][$i]['is_notnot']))
  {
    $GLOBALS['in'][$i]['n_def']='b';
  }
  

}

?>