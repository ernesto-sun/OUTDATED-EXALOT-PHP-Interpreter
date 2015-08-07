<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_01_syntax.php checks the definitions directly in a non-recursive loop
 *  and calls sytnax_exp for resolving expressions 
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

// IN:
// * v
// * y 
// * depth
// * x_path
// * x_i     	the counter within of top-x
// * i          the counter within sub
// * i_op       the counter within the global  op-array 
// * c_sub
// * sub

// IN_DEF: ---------------------------------

// * y (alias, def_e, def_e_usage. def_f, def_sub_e, def_sub_p)

// ? n_def

// ? is_optional
// ? is_plural
// ? is_inf

// ? c_min
// ? c_max


// IN_EXP ---------------------------------

// * y one of the following 
// SINGLE: context, var_get, b, noo, x, int, float, e_name
// PLURAL: var_set, p, s, cl, f_usage, e_usage, pile

// SINGLE (sys): p (only op), f_name (only op)

// PLURAL (sys): op, limited, default, privacy, co 
// PLURAL (special): lot, sub, if, each, break, (only each)

// ? is_literal
// ? c_str (only in s)

// ? path (only if !is_plural)

// ? is_not 
// ? is_notnot

// ? is_in_op (rec) 

// ? is_contextual (reverse recursive) all branches that include context

// ? in_each (rec) reference to the nearest each in sup
// ? in_lot (rec)  reference to the nearest lot in sup
// ? in_sub (rec) reference to the nearest sub in sup
// ? in_if (rec) reference to the nearest if in sup
		
// ? is_exp (rec) (marks if it is an explizit expression inside op, lot, sub, each, co)
// ? is_sys (if y is in: op, limited, default, privacy)

// Variables....

// ? var_top (rec) &in	the top-level-container for the expression holding variables
//				variables can happen in pile/f_usage/e_usage, 
//			    	and also in exp of op/lot/sub/each, the first of those 
//				holds the variables
//
// ? var array (        only in_var_top holds a helper array for the variables		  
//	  ? is_set,
//	  ? op ) 


// After semantics op has these values (related to result)

// * n_def 
// ? is_plural 
// ? c_min 
// ? c_max

// Further values of exp to allow/ease execution

// * subr_e     array of &in, only if(isset(each)) to set before execution of single-exp
// * subr_i     array of &in, only if(isset(each))
// Note: each_c will be set once at first execution-run
// Note: each_r will be replaced at the moment of execution 

// * subr_in     array of &in only if(isset(op|lot|sub))

// Note: each without use of @e or @i is 'useless'-errors. So are lot/sub/op without use of @in  
// Note: each, lot, and sub can not include each other -> error
// Note: if without @context in the condition is alarming (dark code, thats never executed)
//       But it can only be checked by @@variable-analyse. But beause sometimes developers
//       like to 'disable code-branches temporarily' that is not checked (yet).       

// ----------------------------------------------------------------------------------------------------
// ----------------------------------------------------------------------------------------------------
// --------------------------------------------------
// Syntax checking of x
// --------------------------------------------------

$x_len=strlen($x);
if($x_len<3)
{
  msg('error-syntax','no input statement given, min_length: 3 characters');
}

if($x[$x_len-1]==',')
{
  // last comma would result into a parse-error  
  $x[$x_len-1]=' ';
}


$startsWithP=0;
if($x[0]=='[')
{
  if($x[1]=='"')
  {
    if($x[2]=='p' && $x[3]=='"')
    {
      $startsWithP=1;
      //ok, statements starts with p
    }
  }
}

if(!$startsWithP)
{
 $x="[\"p\",{$x}]";
}


$x_parsed=json_decode($x,true,$GLOBALS['u-level']['max-exp-in-depth'],JSON_BIGINT_AS_STRING);
if($x_parsed===null)
{
  msg('error-syntax','JSON-parsing failed: '.get_json_error_as_string(json_last_error()).': '.$x);
}

//print_r($x_parsed[$i_raw]);
//echo '<br/><br/>--------------------<br/><br/>';

		        
$GLOBALS['in']=array(0=>0);
$GLOBALS['in-top']=array(0=>0);
$GLOBALS['in-i']=0;
$i=0;
$c_x_top=count($x_parsed);

if(array_keys($x_parsed) !== range(0,$c_x_top-1))
{
  msg('error-syntax','EXA-parsing failed: JSON-object given on top-level, but only JSON-arrays allowed (only sequential)');
}  

include('php/api_04_syntax_exp.php');

for($i_raw=1;$i_raw<$c_x_top;$i_raw++)
{
  if(isset($x_parsed[$i_raw][0]))
  {
    $n=$x_parsed[$i_raw][0];
    
    if(is_array($n))
    {
      $c_n=count($n);
      
      if(array_keys($n) !== range(0,$c_n-1))
      {
	msg('error-syntax','EXA-parsing failed: JSON-object given in top-expression, but only JSON-arrays allowed (only sequential)');
      }  
    
      // def or alias
      $i++;
      
      $GLOBALS['i-xin']=$i;
      $GLOBALS['in-top'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>1,
		       'x_path'=>':'.$i,
		       'x_i'=>$i,
		       'i'=>$i,
		       'c_sub'=>count($x_parsed[$i_raw])-1,
		       'sub'=>array(0=>0));
      	    
    //---------------------------------------------------------------------- DEF/ALIAS
	 
      if(!$GLOBALS['login']||$GLOBALS['st']['method']!='post')
      {
	msg('error-unauthorized','only logged in users can create definitions','method was not POST');
      }
      
      
      if($c_n!=2)
      {
	msg('error-syntax','EXA-parsing failed: The first element of an expression must be an entity-id or a definition_head');
      }

      // ---------------------- First Preg-Check of names

      if($GLOBALS['in-top'][$i]['c_sub']==0) 
      {
	$GLOBALS['temp']['c-alias']+=1;
	$GLOBALS['in-top'][$i]['y']='alias';

// 	$ok=preg_match('/^[a-z][a-z0-9\^\-\+]*$/',$n[0]);
// 	if(!$ok)
// 	{
// 	    msg('error-syntax','EXA-parsing failed: The first element of an alias-definition must be an entity-name (optional: with index): '.preg_last_error());
// 	}

      }
      else
      {
	$GLOBALS['temp']['c-def']+=1;

	if(strlen($n[0])==1)
	{
	  switch($n[0][0])
	  {
	    case 'e':
	      $GLOBALS['in-top'][$i]['y']='def_e';
	      if($GLOBALS['in-top'][$i]['c_sub']<2)
	      {
		msg('error-semantic','EXA-resolving failed: an e-definition needs 2 sub-entities at least');
	      }
	    break;
	    case 'f':
	      $GLOBALS['in-top'][$i]['y']='def_f';

	      if($GLOBALS['in-top'][$i]['c_sub']!=2)
	      {
		msg('error-semantic','EXA-resolving failed: function_definition needs 2 entities (input and output).');
	      }
	      
	      if(count($x_parsed[$i_raw][1])>1)
	      {
		msg('error-semantic','EXA-resolving failed: a function-input-param can only contain ONE valid definition-name (optionally with index)');
	      }    

	      if(count($x_parsed[$i_raw][2])>1)
	      {
		msg('error-semantic','EXA-resolving failed: a function-output-param can only contain ONE valid definition-name (optionally with index)');
	      }    
	    break;
	    case 'p':
	    case 'x':
	    case 'u':
	    case 'p':
	      if(!$GLOBALS['is-exalot'])
	      {
		msg('error-semantic','EXA-resolving failed: definitions can not be made p,x,u and other core-entities');
	      }
            break;
	    default:
	      $GLOBALS['in-top'][$i]['y']='def_e_usage';
	    break;
	  }
	} // end single character definition
        else 
        {
            // any other definition must be def_e_usage
            $GLOBALS['in-top'][$i]['y']='def_e_usage';
	}
	
	$subl='';
	for($j=1;$j<=$GLOBALS['in-top'][$i]['c_sub'];$j++)
	{
	  $GLOBALS['in-top'][$i]['sub'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>2,
		       'x_path'=>$GLOBALS['in-top'][$i]['x_path'].':'.$j,
		       'x_i'=>$i,
		       'i'=>$j,
		       'c_sub'=>0,
		       'sub'=>array(0=>0));
	  	  
	  if(is_array($x_parsed[$i_raw][$j]))
	  {
	    $GLOBALS['in-top'][$i]['sub'][$j]['v']=$x_parsed[$i_raw][$j][0];

	    switch($x_parsed[$i_raw][$j][0])
	    {
	      case 's':
		$GLOBALS['in-top'][$i]['sub'][$j]['y']='s';
		if (is_array($x_parsed[$i_raw][$j][1]))
		{
		  msg('error-syntax','EXA-parsing failed: An array given where a string is expected');
		}
		$GLOBALS['in-top'][$i]['sub'][$j]['v']=validInput($x_parsed[$i_raw][$j][1]);
	      break;
	      case 'cl':
		$GLOBALS['in-top'][$i]['sub'][$j]['v']=validate_cl_from_x($x_parsed[$i_raw][$j]);
		$GLOBALS['in-top'][$i]['sub'][$j]['y']='cl';
	      break;
	      case 'p':
		$GLOBALS['in-top'][$i]['sub'][$j]['c_sub']=$c_p=count($x_parsed[$i_raw][$j]);
		
		$GLOBALS['in-top'][$i]['sub'][$j]['y']='def_sub_p';
	    
		if($GLOBALS['in-top'][$i]['y']!='def_e_usage')
		{
		  msg('error-semantic','EXA-resolving failed: Only a non-direct e-definition (usage) can include plurals p');
		}
	    
		if(array_keys($x_parsed[$i_raw][$j]) !== range(0,$c_p-1))
		{
		  msg('error-syntax','EXA-parsing failed: JSON-object given as sub-definition of entity, but only JSON-arrays allowed (only sequential)');
		}
		
		for($k=1;$k<$c_p;$k++)
		{
		  $GLOBALS['in-top'][$i]['sub'][$j]['sub'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>3,
		       'x_path'=>$GLOBALS['in-top'][$i]['sub'][$j]['x_path'].':'.$k,
		       'x_i'=>$i,
		       'i'=>$k,
		       'c_sub'=>0,
		       'sub'=>array(0=>0));
	  	  
		  $GLOBALS['in'][]=&$GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]; 
		  $GLOBALS['in-i']++;
		  $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['in_i']=$GLOBALS['in-i'];
		  
		
		  $dum=$x_parsed[$i_raw][$j][$k];
		  if(is_array($dum))
		  {
		    $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['is_literal']=1;
		    $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['y']=$dum[0];
		    
		    switch($dum[0])
		    {
		      case 's':
			if (is_array($dum[1]))
			{
			  msg('error-syntax','EXA-parsing failed: An array given where a string is expected!');
			}
			$GLOBALS['in-top'][$i]['sub'][$j]['v']=validInput($dum[1]);
			break;
		      case 'cl':
			$GLOBALS['in-top'][$i]['sub'][$j]['v']=validate_cl_from_x($dum);
			break;
		      break;
		      default:
			msg('error-syntax','EXA-parsing failed: A p as sub-definition can only include single entities');
		    }
		  }
		  elseif(is_numeric($dum))
		  {
		    $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['is_literal']=1;
		    $number=(float)$x_parsed[$i_raw];
		    //ok
		    if ((int) $number == $number) 
		    {
		      $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['y']='int';
		      $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['v']=(int)$number;
		    }
		    else
		    {
		      $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['y']='float';
		      $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['v']=$number;
		      //float
		    }	      
		  }
		  else
		  {
		    $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['y']='e_name';
		    $c_dum=strlen($dum);
		    if($c_dum>255)
		    {
		      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is too long');
		    }
		    
		    if($c_dum<1)
		    {
		      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is no valid ');
		    }
		    
		    $n_parts=explode('-',$dum);
		    foreach($n_parts as $n_part)
		    {
		      if(empty($n_part))
		      {
			msg('error-syntax','EXA-parsing failed: at a entity-name inside a p inside a definition (invalid use of -, not allowed at beginning or end and only once)');
		      }

		      if(!ctype_lower($n_part))
		      {
			msg('error-syntax','EXA-parsing failed: invalid characters in entity-name inside p inside definition, only a-z are allowed, and -');
		      }
		    }		
		    // get as name withOUT index, because in p
		    $GLOBALS['in-top'][$i]['sub'][$j]['sub'][$k]['v']=$dum;
		  }
		}
		break;
		default:
		  msg('error-semantic','EXA-resolving failed: definition-sub-elements can only include single entities');
		break;
	     }
	  } // end if is_array
	  elseif(is_numeric($x_parsed[$i_raw][$j]))
	  {
	    if($GLOBALS['in-top'][$i]['y']!='def_e_usage')
	    {
	      msg('error-semantic','EXA-resolving failed: Only a non-direct e-definition (usage) can include a literal');
	    }
	  
	    $GLOBALS['in-top'][$i]['sub'][$j]['is_literal']=1;
	    $number=(float)$x_parsed[$i_raw];
	    //ok
	    if ((int) $number == $number) 
	    {
	      $GLOBALS['in-top'][$i]['sub'][$j]['y']='int';
	      $GLOBALS['in-top'][$i]['sub'][$j]['v']=(int)$number;
	    }
	    else
	    {
	      $GLOBALS['in-top'][$i]['sub'][$j]['y']='float';
	      $GLOBALS['in-top'][$i]['sub'][$j]['v']=$number;
	      //float
	    }	      
	  }
	  else
	  {
	    // can be e-name with index
	    $GLOBALS['in-top'][$i]['sub'][$j]['y']='def_sub_e';
	    
	    $exp1=explode('^',$x_parsed[$i_raw][$j]);
	    $c_exp1=count($exp1);
	    if($c_exp1>1)
	    {
	      if($c_exp1>2)
	      {
		msg('error-syntax','The character ^ can only be used once to identify a index/range in a sub-entity');
	      }
	      
	      $exp2=explode('-',$exp1[1]);
	      $c_exp2=count($exp2);

	      if($c_exp2>1)
	      {
		if($c_exp2>2)
		{
		  msg('error-syntax','character - can only be used once after ^ in sub-entity of definition');
		}
		
		$GLOBALS['in-top'][$i]['sub'][$j]['c_max']=$dum=(int)$exp2[1];
		if(''.$dum!=$exp2[1]||$dum<0)
		{
		  msg('error-syntax','second index-paramater (max) is not valid in sub-entity of definition');
		}
	      }
	      else
	      {
		//check for +
		if($exp2[0][count($exp2[0])-1]=='+')
		{
		  $exp2[0]=substr($exp2[0],0,-1);
		  $GLOBALS['in-top'][$i]['sub'][$j]['c_max']=0;
		}
	      }

	      $GLOBALS['in-top'][$i]['sub'][$j]['c_min']=$dum=(int)$exp2[0];
	      if(''.$dum!=$exp2[0])
	      {
		msg('error-syntax','first index-paramater (min) is not valid');
	      }
	      
	      if($dum==0)
	      {
		if(!isset($GLOBALS['in-top'][$i]['sub'][$j]['c_max']))
		{
		  msg('error-syntax','if the min-index is set to 0, the max-index must be set as well');
		}
		
		$GLOBALS['in-top'][$i]['sub'][$j]['is_optional']=1;
	      }
	      else
	      {
		$GLOBALS['in-top'][$i]['sub'][$j]['is_optional']=0;
		
		if(!isset($GLOBALS['in-top'][$i]['sub'][$j]['c_max']))
		{
		  $GLOBALS['in-top'][$i]['sub'][$j]['c_max']=$GLOBALS['in-top'][$i]['sub'][$j]['c_min'];
		}
	      }
		
	      if($GLOBALS['in-top'][$i]['sub'][$j]['c_max']==0)
	      {
		$GLOBALS['in-top'][$i]['sub'][$j]['is_plural']=1;
		$GLOBALS['in-top'][$i]['sub'][$j]['is_inf']=1;
	      }
	      elseif($GLOBALS['in-top'][$i]['sub'][$j]['c_max']>1)
	      {
		$GLOBALS['in-top'][$i]['sub'][$j]['is_plural']=1;
	      }
	    }
	    
	    $c_n=strlen($exp1[0]);
	    if($c_n>255)
	    {
	      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is too long');
	    }
	    
	    if($c_n<1)
	    {
	      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is no valid ');
	    }
	    
	    $n_parts=explode('-',$exp1[0]);
	    foreach($n_parts as $n_part)
	    {
	      if(empty($n_part))
	      {
		msg('error-syntax','EXA-parsing failed: at a entity-name inside a definition (invalid use of -, not allowed at beginning or end and only once)');
	      }

	      if(!ctype_lower($n_part))
	      {
		msg('error-syntax','EXA-parsing failed: invalid characters in entity-name inside definition, only a-z are allowed, and -');
	      }
	    }
	    $GLOBALS['in-top'][$i]['sub'][$j]['v']=$exp1[0];
            
            for($jj=1;$jj<$j;$jj++)
            {
              if($GLOBALS['in-top'][$i]['sub'][$jj]['v']==$GLOBALS['in-top'][$i]['sub'][$j]['v'])
              {
		msg('error-syntax','EXA-parsing failed: an e-definition can not have the same sub-entity twice');
              }
            }
            
	  } // if is sub e-name
	  
	  $GLOBALS['in'][]=&$GLOBALS['in-top'][$i]['sub'][$j];
	  $GLOBALS['in-i']++;
	  $GLOBALS['in-top'][$i]['sub'][$j]['in_i']=$GLOBALS['in-i'];
	  
	  if($GLOBALS['in-top'][$i]['y']=='def-e')
	  {
	    if(strpos($subl,"'{$GLOBALS['in-top'][$i]['sub'][$j]['v']}'"))
	    {
	      msg('error-syntax','EXA-parsing failed: The sub-entities in a e-definition must be unique.');
	    }
	    $subl.=",'{$GLOBALS['in-top'][$i]['sub'][$i]['n']}'";
	  }
	}
      } // end if is-def (not alias)

      // ----------------------------------- check for index

      $exp1=explode('^',$n[0]);
      $c_exp1=count($exp1);
            
      if($c_exp1>1)
      {
	if($c_exp1>2)
	{
	  msg('error-syntax','The character ^ can only be used once to identify a index/range');
	}
	
	$exp2=explode('-',$exp1[1]);
	$c_exp2=count($exp2);	
	    
	if($c_exp2>1)
	{
	  if($GLOBALS['in-top'][$i]['y']!='alias')
	  {
	    msg('error-syntax','Only an alias can have a range as index');
	  }

	  if($c_exp2>2)
	  {
	    msg('error-syntax','character - can only be used once after ^');
	  }
	  
	  $GLOBALS['in-top'][$i]['c_max']=$dum=(int)$exp2[1];
	  if(''.$dum!=$exp2[1]||$dum<0)
	  {
	    msg('error-syntax','second index-paramater (max) is not valid');
	  }
	}
	else
	{
	  //check for +
	  $dum=strlen($exp2[0])-1;
	  if(isset($exp2[0][$dum])&&$exp2[0][$dum]=='+')
	  {
	    if($GLOBALS['in-top'][$i]['y']!='alias')
	    {
	      msg('error-syntax','only an alias can have an +inf-range as index');
	    }
	  
	    $exp2[0]=substr($exp2[0],0,-1);
	    $GLOBALS['in-top'][$i]['c_max']=0;
	  }
	}

	$GLOBALS['in-top'][$i]['c_min']=$dum=(int)$exp2[0];
	if(''.$dum!=$exp2[0])
	{
	  msg('error-syntax','first index-paramater (min) is not valid');
	}
	
	if($dum==0)
	{
	  if($GLOBALS['in-top'][$i]['y']!='alias')
	  {
	    msg('error-syntax','the index of a definition must be greater than zero');
	  }
	  
	  if(!isset($GLOBALS['in-top'][$i]['c_max']))
	  {
	    msg('error-syntax','if the min-index is set to 0, the max-index must be set as well');
	  }
	  
	  $GLOBALS['in-top'][$i]['is_optional']=1;
	}
	else
	{
	  $GLOBALS['in-top'][$i]['is_optional']=0;
	  
	  if(!isset($GLOBALS['in-top'][$i]['c_max']))
	  {
	    $GLOBALS['in-top'][$i]['c_max']=$GLOBALS['in-top'][$i]['c_min'];
	  }
	}
	  
	if($GLOBALS['in-top'][$i]['c_max']==0)
	{
	  $GLOBALS['in-top'][$i]['is_plural']=1;
	  $GLOBALS['in-top'][$i]['is_inf']=1;
	}
	elseif($GLOBALS['in-top'][$i]['c_max']>1)
	{
	  $GLOBALS['in-top'][$i]['is_plural']=1;
	}
	
	$n[0]=$exp1[0];
      }
	

      //------------------------- check of first definition name
      $c_n_def=strlen($n[0]);

      if($c_n_def>255)
      {
	msg('error-syntax','EXA-parsing failed: The first element of definition_head is too long');
      }
      
      if($c_n_def<1)
      {
	msg('error-syntax','EXA-parsing failed: The first element of definition_head is no valid string');
      }

	
      $n_parts=explode('-',$n[0]);
      foreach($n_parts as $n_part)
      {
	if(empty($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: at the first name of definition (invalid use of -, not allowed at beginning or end and only once)');
	}
	
	if(!ctype_lower($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: invalid characters in first name of definition, only a-z are allowed, and -');
	}
      }

      if($GLOBALS['in-top'][$i]['y']=='alias')
      {
	switch($n[0])
	{
	  case 'b':
	  case 'limited':
	  case 'privacy':
	  case 'co':
	  case 'p':
	  case 'f':
	  case 'g':
	  case 'op':
	  case 'u':
	  case 'noo':
	  case 'x':
	  case 'st':
	  case 'con':
	    if(!$GLOBALS['is-exalot'])
	    {
	      msg('error-syntax','EXA-parsing failed: protected core-entities can not be used for an alias, ');
	    }
	  default:
	    //ok
	  break;  
	}
      }
      
      if($c_n_def>1)
      {
	if($n[0][1]=='-')
	{
	  switch($n[0][0])
	  {	
	      case 'p':
	      case 'f':
	      case 'e':
		msg('error-syntax','definitions with p-, e- or f- can not be used');
	      break;
	  }
	}
      }
	
      $GLOBALS['in-top'][$i]['n_def']=$n[0];

      //------------------------- check of second definition name

      $c_n=strlen($n[1]);

      if($c_n>255)
      {
	msg('error-syntax','EXA-parsing failed: The second element of definition_head is too long');
      }

      if($c_n<1)
      {
	msg('error-syntax','EXA-parsing failed: The name of new definition/alias is no valid string');
      }

      if($n[1][0]=='#')
      {
	if($GLOBALS['in-top'][$i]['y']!='def_f')
	{
	  msg('error-syntax','EXA-parsing failed: only function-definitions can start with #');
        }
        $GLOBALS['in-top'][$i]['v']=substr($n[1],1);
      }
      else 
      {
	if($GLOBALS['in-top'][$i]['y']=='def_f')
	{
	  msg('error-syntax','EXA-parsing failed: a function-definition must start with #');
        }
        $GLOBALS['in-top'][$i]['v']=$n[1];
      }
      
      $n_parts=explode('-',$GLOBALS['in-top'][$i]['v']);
      foreach($n_parts as $n_part)
      {
	if(empty($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: at the new name of definition (invalid use of -, not allowed at beginning or end and only once)');
	}
	
	if(!ctype_lower($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: invalid characters in second name of definition, only a-z are allowed (lowercase), and -');
	}
      }

      if($GLOBALS['in-top'][$i]['y']=='def_f')
      {
        $GLOBALS['in-top'][$i]['v']='#'.$GLOBALS['in-top'][$i]['v'];  // add again after character-check
      }
      
      // ---------------------- Check if definiton already defined

      if(strpos($GLOBALS['temp']['n-list-def'],"'{$GLOBALS['in-top'][$i]['n_def']}'"))
      {
	//already in definition_name-list, is ok
      }
      else
      {
	$GLOBALS['temp']['n-list-def'].=",'{$GLOBALS['in-top'][$i]['n_def']}'";
	$GLOBALS['temp']['i-list-def']+=1;
      }

      if(strpos($GLOBALS['temp']['n-list-new'],"'{$GLOBALS['in-top'][$i]['v']}'"))
      {
	msg('error-syntax','EXA-parsing failed: the new definition name \''.$GLOBALS['in-top'][$i]['v'].'\' is already defined in this statement');
      }

      if(strlen($GLOBALS['in-top'][$i]['v'])<2)
      {
	if(!$GLOBALS['is-exalot'])
	{
	     msg('error-syntax','EXA-parsing failed: a new definition name is too short (min: 2 char) ');
	}
      }
      
      if(isset($GLOBALS['in-top'][$i]['v'][1]) && $GLOBALS['in-top'][$i]['v'][1]=="-")
      {
	switch($GLOBALS['in-top'][$i]['v'][0])
	{
	    case '#':
	    case 'f':
	    case 'p':
	    case 'e':
	    case 'b':
	    case 'n':
	    case 's':
	    case 'x':
	    case 'u':
	    case 'g':
	      if(!$GLOBALS['is-exalot'])
	      {
		  msg('error-syntax','EXA-parsing failed: a '.$GLOBALS['in-top'][$i]['v'][0].'-definition can not be made explicitely. ');
	      }
	      break;
	}
      }
	
      $GLOBALS['temp']['n-list-new'].=",'{$GLOBALS['in-top'][$i]['v']}'";
      $GLOBALS['temp']['i-list-new']+=1;
    //----------------------------------------------------------------------

      $GLOBALS['in'][]=&$GLOBALS['in-top'][$i];
      $GLOBALS['in-i']++;
      $GLOBALS['in-top'][$i]['in_i']=$GLOBALS['in-i'];

    
    }  // end if $n is_array
    else
    {
      // exp
      if($n[0]=='/'&&$n[1]=='/')
      {
	  // ignore comment
      }
      else
      {
	$i++;
	$GLOBALS['i-xin']=$i;
	$GLOBALS['in-top'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>0, // is increased soon inside ckeckExp
		       'x_path'=>':'.$i,
		       'x_i'=>$i,
		       'i'=>$i,
		       'c_sub'=>0,
		       'sub'=>array(0=>0));
	
	checkEXP($x_parsed[$i_raw],$GLOBALS['in-top'][$i]);

	$GLOBALS['temp']['c-exp-top']+=1;
	
      }
    }
  }
}



