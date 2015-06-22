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

// @file    api_04_syntax.php
// @brief   syntax-parsing of input (all definition-parsing is defined in this file)     

// @author  Ernesto (eto) <contact@ernesto-sun.com>
// @create  20150112-eto  
// @update  20150618-eto  

// -------------------------------------------------------------------------



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

// PLURAL (sys): op, limited, default, privacy, 
// PLURAL (special): lot, sub, if, each, break, (only each)

// ? is_literal
// ? c_str (only in s)

// ? path (only if !is_plural)

// ? is_not 
// ? is_notnot

// ? is_in_op (rec) 

// ? in_each (rec) reference to the nearest each in sup
// ? in_lot (rec)  reference to the nearest lot in sup
// ? in_sub (rec) reference to the nearest sub in sup
// ? in_if (rec) reference to the nearest if in sup
		
// ? is_exp (rec) (marks if it is an explizit expression inside op, lot or sub)
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
if($x_len<5)
{
  msg('error-syntax','no input statement given, min_length: 5 char');
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


$x_parsed=json_decode($x,true,$GLOBALS['context']['u-level']['max-exp-in-depth'],JSON_BIGINT_AS_STRING);
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
  msg('error-syntax','EXA-parsing failed: JSON-object given on top-level, but only JSON-arrays allowed (only sequential)',$in);
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
	msg('error-syntax','EXA-parsing failed: JSON-object given in top-expression, but only JSON-arrays allowed (only sequential)',$in);
      }  
    
      // def or alias
      $i++;
      $GLOBALS['context']['i-xin']=$i;
      $GLOBALS['in-top'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>1,
		       'x_path'=>'@'.$i,
		       'x_i'=>$i,
		       'i'=>$i,
		       'c_sub'=>count($x_parsed[$i_raw])-1,
		       'sub'=>array(0=>0));
      
      $in=&$GLOBALS['in-top'][$i];
	    
    //---------------------------------------------------------------------- DEF/ALIAS
	 
      if(!$GLOBALS['login']||$GLOBALS['st']['method']!='post')
      {
	msg('error-unauthorized','only logged in users can create definitions','method was not POST');
      }
      
      
      if($c_n!=2)
      {
	msg('error-syntax','EXA-parsing failed: The first element of an expression must be an entity-id or a definition_head',$in);
      }

      // ---------------------- First Preg-Check of names

      if($in['c_sub']==0) 
      {
	$GLOBALS['temp']['c-alias']+=1;
	$in['y']='alias';

// 	$ok=preg_match('/^[a-z][a-z0-9\^\-\+]*$/',$n[0]);
// 	if(!$ok)
// 	{
// 	    msg('error-syntax','EXA-parsing failed: The first element of an alias-definition must be an entity-name (optional: with index): '.preg_last_error(),$in);
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
	      $in['y']='def_e';
	    break;
	    case 'f':
	      $in['y']='def_f';

	      if($in['c_sub']!=2)
	      {
		msg('error-semantic','EXA-resolving failed: function_definition needs 2 entities (input and output).',$in);
	      }
	      
	      if(count($x_parsed[$i_raw][1])>1)
	      {
		msg('error-semantic','EXA-resolving failed: a function-input-param can only contain ONE valid definition-name (optionally with index)',$in);
	      }    

	      if(count($x_parsed[$i_raw][2])>1)
	      {
		msg('error-semantic','EXA-resolving failed: a function-output-param can only contain ONE valid definition-name (optionally with index)',$in);
	      }    
	    break;
	    case 'p':
	    case 'x':
	    case 'u':
	    case 'st':
	    case 'con':
	    case 'default':
	    case 'limited':
	    case 'privacy':
	    case 'co':
	    case 'p':
	    case 'op':
	      if(!$GLOBALS['context']['is-exalot'])
	      {
		msg('error-semantic','EXA-resolving failed: definitions can not be made p,x,u and other core-entities',$in);
	      }
	    default:
	      $in['y']='def_e_usage';
	    
// 	      $ok=preg_match('/^[a-z][a-z\-]*[\^[0-9]+]?$/',$n[0]);
// 	      if(!$ok)
// 	      {
// 		  msg('error-syntax','EXA-parsing failed: The first element of an definition must be a definition-name without index: '.preg_last_error(),$in);
// 	      }
	      
	    break;
	  }
	} // end single character definition
	
	$subl='';
	for($j=1;$j<=$in['c_sub'];$j++)
	{
	  $in['sub'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>2,
		       'x_path'=>$in['x_path'].'@'.$j,
		       'x_i'=>$i,
		       'i'=>$j,
		       'c_sub'=>0,
		       'sub'=>array(0=>0));
	  
	  $in_sub=&$in['sub'][$j];
	  
	  if(is_array($x_parsed[$i_raw][$j]))
	  {
	    $in_sub['v']=$x_parsed[$i_raw][$j][0];

	    switch($x_parsed[$i_raw][$j][0])
	    {
	      case 's':
		$in_sub['y']='s';
		if (is_array($x_parsed[$i_raw][$j][1]))
		{
		  msg('error-syntax','EXA-parsing failed: An array given where a string is expected',$in);
		}
		$in_sub['v']=validInput($x_parsed[$i_raw][$j][1]);
	      break;
	      case 'cl':
		$in_sub['v']=validate_cl_from_x($x_parsed[$i_raw][$j]);
		$in_sub['y']='cl';
	      break;
	      case 'p':
		$in_sub['c_sub']=$c_p=count($x_parsed[$i_raw][$j]);
		
		$in_sub['y']='def_sub_p';
	    
		if($in['y']!='def_e_usage')
		{
		  msg('error-semantic','EXA-resolving failed: Only a non-direct e-definition (usage) can include plurals p',$in);
		}
	    
		if(array_keys($x_parsed[$i_raw][$j]) !== range(0,$c_p-1))
		{
		  msg('error-syntax','EXA-parsing failed: JSON-object given as sub-definition of entity, but only JSON-arrays allowed (only sequential)',$in);
		}
		
		for($k=1;$k<$c_p;$k++)
		{
		  $in_sub['sub'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>3,
		       'x_path'=>$in_sub['x_path'].'@'.$k,
		       'x_i'=>$i,
		       'i'=>$k,
		       'c_sub'=>0,
		       'sub'=>array(0=>0));
	  	  
		  $in_sub_sub=&$in_sub['sub'][$k];
		  
		  $GLOBALS['in'][]=$in_sub_sub; // direct adding because this is a leaf (e in p in def)
		  $GLOBALS['in-i']++;
		  $in_sub_sub['i_op']=$GLOBALS['in-i'];
		  
		
		  $dum=$x_parsed[$i_raw][$j][$k];
		  if(is_array($dum))
		  {
		    $in_sub_sub['is_literal']=1;
		    $in_sub_sub['y']=$dum[0];
		    
		    switch($dum[0])
		    {
		      case 's':
			if (is_array($dum[1]))
			{
			  msg('error-syntax','EXA-parsing failed: An array given where a string is expected!',$in);
			}
			$in_sub['v']=validInput($dum[1]);
			break;
		      case 'cl':
			$in_sub['v']=validate_cl_from_x($dum);
			break;
		      break;
		      default:
			msg('error-syntax','EXA-parsing failed: A p as sub-definition can only include single entities',$in);
		    }
		  }
		  elseif(is_numeric($dum))
		  {
		    $in_sub_sub['is_literal']=1;
		    $number=(float)$x_parsed[$i_raw];
		    //ok
		    if ((int) $number == $number) 
		    {
		      $in_sub_sub['y']='int';
		      $in_sub_sub['v']=(int)$number;
		    }
		    else
		    {
		      $in_sub_sub['y']='float';
		      $in_sub_sub['v']=$number;
		      //float
		    }	      
		  }
		  else
		  {
		    $in_sub_sub['y']='e_name';
		    $c_dum=strlen($dum);
		    if($c_dum>255)
		    {
		      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is too long',$in);
		    }
		    
		    if($c_dum<1)
		    {
		      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is no valid ',$in);
		    }
		    
		    $n_parts=explode('-',$dum);
		    foreach($n_parts as $n_part)
		    {
		      if(empty($n_part))
		      {
			msg('error-syntax','EXA-parsing failed: at a entity-name inside a p inside a definition (invalid use of -, not allowed at beginning or end and only once)',$in);
		      }

		      if(!ctype_lower($n_part))
		      {
			msg('error-syntax','EXA-parsing failed: invalid characters in entity-name inside p inside definition, only a-z are allowed, and -',$in);
		      }
		    }		
		    // get as name withOUT index, because in p
		    $in_sub_sub['v']=$dum;
		  }
		}
		break;
		default:
		  msg('error-semantic','EXA-resolving failed: definition-sub-elements can only include single entities',$in);
		break;
	     }
	  } // end if is_array
	  elseif(is_numeric($x_parsed[$i_raw][$j]))
	  {
	    if($in['y']!='def_e_usage')
	    {
	      msg('error-semantic','EXA-resolving failed: Only a non-direct e-definition (usage) can include a literal',$in);
	    }
	  
	    $in_sub['is_literal']=1;
	    $number=(float)$x_parsed[$i_raw];
	    //ok
	    if ((int) $number == $number) 
	    {
	      $in_sub['y']='int';
	      $in_sub['v']=(int)$number;
	    }
	    else
	    {
	      $in_sub['y']='float';
	      $in_sub['v']=$number;
	      //float
	    }	      
	  }
	  else
	  {
	    // can be e-name with index
	    $in_sub['y']='def_sub_e';
	    
	    $exp1=explode('^',$x_parsed[$i_raw][$j]);
	    $c_exp1=count($exp1);
	    if($c_exp1>1)
	    {
	      if($in['y']=='def_e_usage')
	      {
		msg('error-semantic','EXA-resolving failed: A non-direct e-definition (usage) can not have a index',$in);
	      }
	    
	      if($c_exp1>2)
	      {
		msg('error-syntax','The character ^ can only be used once to identify a index/range in a sub-entity',$in);
	      }
	      
	      $exp2=explode('-',$c_exp1[1]);
	      $c_exp2=count($exp2);
		  
	      if($c_exp2>1)
	      {
		if($c_exp2>2)
		{
		  msg('error-syntax','character - can only be used once after ^ in sub-entity of definition',$in);
		}
		
		$in_sub['c_max']=$dum=(int)$exp2[1];
		if(''.$dum!=$exp2[1]||$dum<0)
		{
		  msg('error-syntax','second index-paramater (max) is not valid in sub-entity of definition',$in);
		}
	      }
	      else
	      {
		//check for +
		if($exp2[0][count($exp2[0])-1]=='+')
		{
		  $exp2[0]=substr($exp2[0],0,-1);
		  $in_sub['c_max']=0;
		}
	      }

	      $in_sub['c_min']=$dum=(int)$exp2[0];
	      if(''.$dum!=$exp2[0])
	      {
		msg('error-syntax','first index-paramater (min) is not valid',$in);
	      }
	      
	      if($dum==0)
	      {
		if(!isset($in_sub['c_max']))
		{
		  msg('error-syntax','if the min-index is set to 0, the max-index must be set as well',$in);
		}
		
		$in_sub['is_optional']=1;
	      }
	      else
	      {
		$in_sub['is_optional']=0;
		
		if(!isset($in_sub['c_max']))
		{
		  $in_sub['c_max']=$in_sub['c_min'];
		}
	      }
		
	      if($in_sub['c_max']==0)
	      {
		$in_sub['is_plural']=1;
		$in_sub['is_inf']=1;
	      }
	      elseif($in_sub['c_max']>1)
	      {
		$in_sub['is_plural']=1;
	      }
	    }
	    
	    $c_n=strlen($exp1[0]);
	    if($c_n>255)
	    {
	      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is too long',$in);
	    }
	    
	    if($c_n<1)
	    {
	      msg('error-syntax','EXA-parsing failed: entity-name inside p inside definition is no valid ',$in);
	    }
	    
	    $n_parts=explode('-',$exp1[0]);
	    foreach($n_parts as $n_part)
	    {
	      if(empty($n_part))
	      {
		msg('error-syntax','EXA-parsing failed: at a entity-name inside a definition (invalid use of -, not allowed at beginning or end and only once)',$in);
	      }

	      if(!ctype_lower($n_part))
	      {
		msg('error-syntax','EXA-parsing failed: invalid characters in entity-name inside definition, only a-z are allowed, and -',$in);
	      }
	    }
	    
	    $in_sub['v']=$exp1[0];
	  } // if is sub e-name
	  
	  $GLOBALS['in'][]=$in_sub;
	  $GLOBALS['in-i']++;
	  $in_sub['i_op']=$GLOBALS['in-i'];
	  
	  if($in['y']=='def-e')
	  {
	    if(strpos($subl,"'{$in_sub['v']}'"))
	    {
	      msg('error-syntax','EXA-parsing failed: The sub-entities in a e-definition must be unique.',$in);
	    }
	    $subl.=",'{$in['sub'][$i]['n']}'";
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
	  msg('error-syntax','The character ^ can only be used once to identify a index/range',$in);
	}
	
	$exp2=explode('-',$exp1[1]);
	$c_exp2=count($exp2);	
	    
	if($c_exp2>1)
	{
	  if($in['y']!='alias')
	  {
	    msg('error-syntax','Only an alias can have a range as index',$in);
	  }

	  if($c_exp2>2)
	  {
	    msg('error-syntax','character - can only be used once after ^',$in);
	  }
	  
	  $in['c_max']=$dum=(int)$exp2[1];
	  if(''.$dum!=$exp2[1]||$dum<0)
	  {
	    msg('error-syntax','second index-paramater (max) is not valid',$in);
	  }
	}
	else
	{
	  //check for +
	  $dum=strlen($exp2[0])-1;
	  if(isset($exp2[0][$dum])&&$exp2[0][$dum]=='+')
	  {
	    if($in['y']!='alias')
	    {
	      msg('error-syntax','only an alias can have an +inf-range as index',$in);
	    }
	  
	    $exp2[0]=substr($exp2[0],0,-1);
	    $in['c_max']=0;
	  }
	}

	$in['c_min']=$dum=(int)$exp2[0];
	if(''.$dum!=$exp2[0])
	{
	  msg('error-syntax','first index-paramater (min) is not valid',$in);
	}
	
	if($dum==0)
	{
	  if($in['y']!='alias')
	  {
	    msg('error-syntax','the index of a definition must be greater than zero',$in);
	  }
	  
	  if(!isset($in['c_max']))
	  {
	    msg('error-syntax','if the min-index is set to 0, the max-index must be set as well',$in);
	  }
	  
	  $in['is_optional']=1;
	}
	else
	{
	  $in['is_optional']=0;
	  
	  if(!isset($in['c_max']))
	  {
	    $in['c_max']=$in['c_min'];
	  }
	}
	  
	if($in['c_max']==0)
	{
	  $in['is_plural']=1;
	  $in['is_inf']=1;
	}
	elseif($in['c_max']>1)
	{
	  $in['is_plural']=1;
	}
	
	$n[0]=$exp1[0];
      }
	

      //------------------------- check of first definition name
      $c_n_d=strlen($n[0]);

      if($c_n_d>255)
      {
	msg('error-syntax','EXA-parsing failed: The first element of definition_head is too long',$in);
      }
      
      if($c_n_d<1)
      {
	msg('error-syntax','EXA-parsing failed: The first element of definition_head is no valid string',$in);
      }

	
      $n_parts=explode('-',$n[0]);
      foreach($n_parts as $n_part)
      {
	if(empty($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: at the first name of definition (invalid use of -, not allowed at beginning or end and only once)',$in);
	}
	
	if(!ctype_lower($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: invalid characters in first name of definition, only a-z are allowed, and -',$in);
	}
      }

      if($in['y']=='alias')
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
	    if(!$GLOBALS['context']['is-exalot'])
	    {
	      msg('error-syntax','EXA-parsing failed: protected core-entities can not be used for an alias, ',$in);
	    }
	  default:
	    //ok
	  break;  
	}
      }
      
      $c_n_def=strlen($n[0]);
      if($c_n_def>1)
      {
	if($n[0][1]=='-')
	{
	  switch($n[0][0])
	  {	
	      case 'p':
		msg('error-syntax','definitions with p- can not be used',$in);
	      break;
	      case 'f':
		if($in['y']=='alias')
		{
		  msg('error-syntax','an alias can not be made on a function',$in);
		}
	      break;
	  }
	}
      }
	
      $in['n_def']=$n[0];

      //------------------------- check of second definition name

		
//       $ok=preg_match('/^[a-z][a-z\-]*$/',$n[1]);
//       if(!$ok)
//       {
// 	msg('error-syntax','EXA-parsing failed: The second element of a definition_head must be an entity-name: '.preg_last_error(),$in);
//       }


      $c_n=strlen($n[1]);

      if($c_n>255)
      {
	msg('error-syntax','EXA-parsing failed: The second element of definition_head is too long',$in);
      }

      if($c_n<1)
      {
	msg('error-syntax','EXA-parsing failed: The name of new definition/alias is no valid string',$in);
      }

      $n_parts=explode('-',$n[1]);
      foreach($n_parts as $n_part)
      {
	if(empty($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: at the new name of definition (invalid use of -, not allowed at beginning or end and only once)',$in);
	}
	
	if(!ctype_lower($n_part))
	{
	  msg('error-syntax','EXA-parsing failed: invalid characters in second name of definition, only a-z are allowed (lowercase), and -',$in);
	}
      }
      
      $in['v']=$n[1];
      
      
      // ---------------------- Check if definiton already deined

      if(strpos($GLOBALS['temp']['n-list-def'],"'{$in['n_def']}'"))
      {
	//already in definition_name-list, is ok
      }
      else
      {
	$GLOBALS['temp']['n-list-def'].=",'{$in['n_def']}'";
	$GLOBALS['temp']['i-list-def']+=1;
      }

      if(strpos($GLOBALS['temp']['n-list-new'],"'{$in['v']}'"))
      {
	msg('error-syntax','EXA-parsing failed: the new definition name is already defined in this statement ',$in);
      }

      if(strlen($in['v'])<2)
      {
	if(!$GLOBALS['context']['is-exalot'])
	{
	     msg('error-syntax','EXA-parsing failed: a new definition name is too short (min: 2 char) ',$in);
	}
      }
      
      if(isset($in['v'][1])&&$in['v'][1]=='-')
      {
	$need_f=0;
	if($in['y']=='def_f')
	{
	  $need_f=1;
	}
	
	switch($in['v'][0])
	{
	    case 'f':
	      if($in['y']!='def_f')
	      {
		msg('error-syntax','EXA-parsing failed: only function-definitions must have a name starting with f',$in);
	      }
	      $need_f=0;
	    break;
	    case 'b':
	    case 'n':
	    case 'e':
	    case 's':
	    case 'p':
	    case 'x':
	    case 'u':
	    case 'g':
	      if(!$GLOBALS['context']['is-exalot'])
	      {
		  msg('error-syntax','EXA-parsing failed: a '.$in['v'][0].'-definition can not be made explicitely. ',$in);
	      }
	      break;
	}
	if($need_f)
	{
	  msg('error-syntax','EXA-parsing failed: a function-definition must start with f-',$in);
	}
      }
      else
      {
	if($in['y']=='def_f')
	{
	  msg('error-syntax','EXA-parsing failed: a function-definition must start with f-',$in);
	}
      }
	
      $GLOBALS['temp']['n-list-new'].=",'{$in['v']}'";
      $GLOBALS['temp']['i-list-new']+=1;
    //----------------------------------------------------------------------

      $GLOBALS['in'][]=$in;
      $GLOBALS['in-i']++;
      $in['i_op']=$GLOBALS['in-i'];

    
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
	$GLOBALS['context']['i-xin']=$i;
	$GLOBALS['in-top'][]=array('v'=>'',  	
		       'y'=>'',  	
		       'depth'=>0, // is increased soon inside ckeckExp
		       'x_path'=>'@'.$i,
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




?>