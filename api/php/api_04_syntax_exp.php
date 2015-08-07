<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_04_syntax_exp.php uses a recursive function to check the syntax
 *  for each entity and sub-entity
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


//---------------------------------------------------------------
function validate_cl_from_x($v)
{
  $count=count($v);
  if(is_array($v[1])||$count>2)
  {
    //expect cl-array in format [y,m,d],[h,m,s],ms,tz
    // or y,m,d,h,m,s,tz,ms ()
    // or [y,m,d],tz,ms
    // a.s.o.
    
    if($count==2&&is_array($v[1]))
    {
      // make direct sub array flat (for easier processing)
      $dummy=$v[1];
      foreach($dummy as $dummy_sub)
      {
	$v[$count-1]=$dummy_sub;
	$count+=1;
      }
    }
    
    $date='';
    $time='';
    $rest='';
    
    if(is_array($v[1]))
    {
      // must be date-array
      $date=implode('-',$v[1]);
    
      if(isset($v[2]))
      {
	if(is_array($v[2]))
	{
	  // must be time-array
	  $time=implode(':',$v[2]);
	}
	else $rest.=" {$v[2]}";

	if(isset($v[3]))
	{
	  if(is_array($v[3]))
	  {
	    msg('error-syntax','EXA-parsing failed: The clock-format was not recognized correctly. timezone or ms expected but array given.');
	  }
	  else $rest.=" {$v[3]}";

	  if(isset($v[4]))
	  {
	    if(is_array($v[4]))
	    {
	      msg('error-syntax','EXA-parsing failed: The clock-format was not recognized correctly. timezone or ms expected but array given.');
	    }
	    else $rest.=" {$v[4]}";

	    if(isset($v[5]))
	    {
	      msg('error-syntax','EXA-parsing failed: The clock-format was not recognized correctly: too many cl-sub-entities');
	    }
	  }
	}
      }
    } // end if is_array($v[1]
    else
    {
      // first sub-entity is not an array, so no arrays are expected
      $date.=$v[1];
      if(isset($v[2]))
      {
	  $date.="-{$v[2]}";
	  if(isset($v[3]))
	  {
	    $date.="-{$v[3]}";
	    if(isset($v[4]))
	    {
	      if(!is_numeric($v[4]))
	      {
		$rest.=" {$v[4]}";
		if(isset($v[5]))
		{
		  $rest.=" {$v[5]}";
		  if(isset($v[6]))
		  {
		    msg('error-syntax','EXA-parsing failed: The EXA-clock-format as flat array is invalid.');
		  }
		}
	      }
	      else
	      {
		$time.=$v[4];
		if(isset($v[5]))
		{
		  $time.=":{$v[5]}";
		  if(isset($v[6]))
		  {
		    if(is_numeric($v[6]))
		    {
		      $time.=":{$v[6]}";
		    }
		    else
		    {
		      $rest.=" {$v[6]}";
		    }
		    if(isset($v[7]))
		    {
		      $rest.=" {$v[7]}";
		      if(isset($v[8]))
		      {
			$rest.=" {$v[8]}";
			if(isset($v[9]))
			{
			  msg('error-syntax','EXA-parsing failed: The EXA-clock-format as flat array has too many entities.');
			}
		      }
		    }
		  }
		}
	      }
	    }
	  }
      }
    }
    $v[1]="{$date} {$time} {$rest}";
    $count=1;
  }

  // direct conversation to utc TODO: better timezone/daylight saving/user-timezone-handling, ... 
  $cl=strToClock($v[1],1); 

  if($cl['syntax_bad'])
  {
    msg('error-syntax','EXA-parsing failed: The clock-format was not recognized correctly: '.$v[1]);
  }
  
  return $cl;  
}



//--------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------
//--------------------------------------------------------------------------------------
function checkEXP($x_node,&$in)
{
  $in['depth']+=1;	

  //$in['x_node']=&$x_node;

  if(is_object($x_node))
  {
    msg('error-syntax','EXA-parsing failed: JSON-object given but only JSON-arrays allowed',$in);
  }
  elseif(is_numeric($x_node))
  {
    $number=(float)$x_node;
    //ok
    if ((int) $number == $number) 
    {
      $in['v']=(int)$number;
      $in['y']='int';
    }
    else
    {
      $in['v']=$number;
      $in['y']='float';
    }
  }
  elseif(is_array($x_node))
  {
    // ------------------------ PLURAL --------------------- BEGIN
    
    $count=count($x_node);
    if(array_keys($x_node) !== range(0,$count-1))
    {
      msg('error-syntax','EXA-parsing failed: JSON-object given but only JSON-arrays allowed (only sequential)',$in);
    }  
    
    if($count<1)
    {
      msg('error-syntax','EXA-parsing failed: expression can not be empty array',$in);
    }
    
    $in['c_sub']=$count-1;

    $i=1;
    if(is_array($x_node[0]))
    {
	//this is a definition or an alias
      msg('error-syntax','EXA-parsing failed: a definition must be at a top-level-expression',$in);
	
    }

    // Here we are at the first sub-entity that is not an array, thus, no defintion or alias
    
    if($count<2)
    {
      msg('error-syntax','EXA-parsing failed: An expression can not be an array with only one entity.',$in);
    }
    
    if(is_numeric($x_node[0]))
    {
      msg('error-syntax','EXA-parsing failed: An expression can not be an array starting with a literal',$in);
    }

    $n=$x_node[0];
    $c_x_node=strlen($n);
    if($c_x_node<1)
    {
      msg('error-syntax','EXA-parsing failed: An expression-head can not be a empty string',$in);
    }

    if($c_x_node>255)
    {
	msg('error-syntax','EXA-parsing failed: The head of expression is too long',$in);
    }
    
    
    $offset=0;
    if($n[0]=='!')
    {
      if(isset($n[1])&&$n[1]=='!')
      {
	$in['is_notnot']=1;
	$offset=2;
      }
      else
      {
	$in['is_not']=1;
	$offset=1;
      }
    }
    
    if($n[$offset]=='@')
    {
      if(isset($n[$offset+1])&&$n[$offset+1]=='@')
      {
	if($in['c_sub']!=1)
	{
	  msg('error-syntax','EXA-parsing failed: variable can only be used with one sub-entitiy for setting the value.',$in);
	}
	
	if(!isset($in['var_top']))
	{
	  msg('error-syntax','EXA-parsing failed: variable-set needs a valid context. e.g. pile, each, op, lot, sub, if, f_usage, or e_usage',$in);
	}
	
	$in['y']='var_set';
	$offset=$offset+2;
      }
      else
      {
	msg('error-syntax','EXA-parsing failed: context can only be used without sub-entities (read only)',$in);
      }
    }

    // direct check by 
    if($offset)
    {
      $n=substr($n,$offset);
    }

    $in['v']=$n;

    
    if($in['y']=='var_set')
    {
      if(empty($n))
      {
	msg('error-syntax','EXA-parsing failed: a variable name is invalid',$in);
      }

      if(!ctype_lower($n))
      {
	msg('error-syntax','EXA-parsing failed: invalid characters at in a variable, only a-z are allowed (no -)',$in);
      }

      if(!isset($in['var_top']['var'][$n]))
      {
	$in['var_top']['var'][$n]=array('c_set'=>1);
      }
      else
      {
	$in['var_top']['var'][$n]['c_set']++;
      }
    }
    else
    {
      
      switch($n)
      {
	case 'cl':
	  $in['v']=validate_cl_from_x($x_node);
	  $in['y']='cl';
	
	  $in['c_sub']=0;
	  $count=1; // This disables looking for the cl-details. All done already.

	break;
	case 's':

	  if (is_array($x_node[1]))
	  {
	    msg('error-syntax','EXA-parsing failed: An array given where a string is expected!',$in);
	  }

	  $in['s']=validInput($x_node[1]);
	  $in['y']='s';

          $in['c_str']=strlen($in['s']);
          if($in['c_str']>255)
          {
            $in['v']=substr($in['s'],0,255);
            if($in['v'][254]=='\\')
            {
              // 'bad luck' after shortening an escaped string to have \ at the end, would escape the following '
              $in['v'][254]='.';
            }
          }
          else 
          {
              $in['v']=$in['s'];
          }
          
	  $in['c_sub']=0;
	  $count=1; // This disables looking for the string-details.
	break;
	case 'p':
	  if($in['depth']<2)
	  {
	    msg('error-syntax','EXA-parsing failed: a p can only happen as sub-expression',$in);
	  }
	  
	  if($in['c_sub']<1)
	  {
	    msg('error-syntax','EXA-parsing failed: an empty p is not allowed in syntax, use noo instead',$in);
	  }
	  $in['y']='p';
	
          break;
       case 'e':
	  if($in['depth']<2)
	  {
	    msg('error-syntax','EXA-parsing failed: an \'e\' can only happen as sub-expression.',$in);
	  }
	  
	  if($in['c_sub']<1)
	  {
	    msg('error-syntax','EXA-parsing failed: an empty  \'e\' is not allowed in syntax',$in);
	  }
	  $in['y']='e_usage';
          
          break;
	case 'pile':
	  $in['y']='pile';

	  if($in['c_sub']<2)
	  {
	    msg('error-syntax','EXA-parsing failed: a pile needs to have 2 sub-expressions at least',$in);
	  }
	  
	  if(!isset($in['var_top']))
	  {
	    $in['var_top']=&$in;
	    $in['var']=array();
	  }
	break;
	case '#if':
	  $in['y']='if';

          if($in['c_sub']<2||$in['c_sub']>3)
	  {
	    msg('error-syntax','EXA-parsing failed: if-expression must have 2 or 3 sub-entity.',$in);
	  }

	  if(!isset($in['var_top']))
	  {
	    $in['var_top']=&$in;
	    $in['var']=array();
	  }
	  
	  break;
	case '#each':
	  $in['y']='each';

          if($in['c_sub']!=3)
	  {
	    msg('error-syntax','EXA-parsing failed: each statement needs three sub-entities',$in);
	  }

	  if(isset($in['in_lot'])||
	     isset($in['in_sub'])||
	     isset($in['in_each']))
	  {
	    msg('error-syntax','EXA-parsing failed: each-statement can not happen within lot, sub or each',$in);
	  }
	  
	  $in['subr_e']=array();
	  $in['subr_i']=array();

          $in['context']=array(); // holds e,r,i,c later

	break;
	case '#break':
	  $in['y']='break';

            if($in['c_sub']!=1)
	  {
	    msg('error-syntax','EXA-parsing failed: a break-statement needs one sub-entity',$in);
	  }
	  if(!isset($in['in_each']))
	  {
	    msg('error-syntax','EXA-parsing failed: a break-statement can only happen in a each-statement',$in);
	  }
	  if(!isset($in['in_if']))
	  {
	    msg('error-syntax','EXA-parsing failed: a break-statement can only happen in a if-branch in a each-statement',$in);
	  }
	break;	
	case '#lot':
	  $in['y']='lot';

            if($in['c_sub']<2)
	  {
	    msg('error-syntax','EXA-parsing failed: lot-statement needs at least two sub-entities',$in);
	  }
	  
	  if(isset($in['in_lot'])||
	     isset($in['in_sub'])||
	     isset($in['in_each']))
	  {
	    msg('error-syntax','EXA-parsing failed: lot-statement can not happen within lot, sub or each',$in);
	  }
	  
	  $in['subr_in']=array();
	  
	break;
	case '#sub':
	  $in['y']='sub';

          if($in['c_sub']<2)
	  {
	    msg('error-syntax','EXA-parsing failed: sub-statement needs at least two sub-entities',$in);
	  }

	  if(isset($in['in_lot'])||
	     isset($in['in_sub'])||
	     isset($in['in_each']))
	  {
	    msg('error-syntax','EXA-parsing failed: sub-statement can not happen within lot, sub or each',$in);
	  }
	  
	  $in['subr_in']=array();

	break;
	case 'limited':
	  $in['y']='limited';

          if($in['depth']!=1)
	  {
	    msg('error-syntax','EXA-parsing failed: limited-definition must be top-level.',$in);
	  }
	  if($in['c_sub']!=1)
	  {
	    msg('error-syntax','EXA-parsing failed: limited-definition must have 1 sub-entity.',$in);
	  }
	  
	  $in['is_sys']=1;
	  $GLOBALS['temp']['i-sys']++;
	break;
	case 'default':
	  $in['y']='default';

          if($in['depth']!=1)
	  {
	    msg('error-syntax','EXA-parsing failed: default-definition must be top-level.',$in);
	  }
	  if($in['c_sub']!=2)
	  {
	    msg('error-syntax','EXA-parsing failed: limited-definition must have 2 sub-entities.',$in);
	  }
	  
	  $in['is_sys']=1;
	  $GLOBALS['temp']['i-sys']++;
	break;
	case 'co':
	  $in['y']='co';
            
	  if($in['depth']!=1)
	  {
	    msg('error-syntax','EXA-parsing failed: co-definition (condition) must be top-level.',$in);
	  }
	  if($in['c_sub']!=2)
	  {
	    msg('error-syntax','EXA-parsing failed: co-definition must have 2 sub-entities.',$in);
	  }
	  
	  $in['is_sys']=1;
	  $GLOBALS['temp']['i-sys']++;
	break;
	case 'privacy':
	  $in['y']='privacy';

          if($in['depth']!=1)
	  {
	    msg('error-syntax','EXA-parsing failed: privacy-definition must be top-level.',$in);
	  }

	  // privacy can be used on functions as well, and also later as the definition-statement
	  
	  $in['is_sys']=1;
	  $GLOBALS['temp']['i-sys']++;
	break;
	case 'op':
	  $in['y']='op';
            
	  if($in['depth']!=1)
	  {
	    msg('error-syntax','EXA-parsing failed: op-definition must be top-level.',$in);
	  }
	  if($in['c_sub']!=3)
	  {
	    msg('error-syntax','EXA-parsing failed: op-definition must have 3 sub-entities.',$in);
	  }

	  $in['subr_in']=array();
	  
	  $in['is_sys']=1;
	  $GLOBALS['temp']['i-sys']++;
	break;
	case '<=':
	    $in['y']='f_usage';
	    $in['v']='#lteq';
	    if($in['is_not'])
	    {
	      unset($in['is_not']);
	      $in['v']='#gt';
	    }
	    unset($in['is_notnot']); // notnot has no effect on binary operations 
	  break;
	case '<':
	    $in['y']='f_usage';
	    $in['v']='#lt';
	    if($in['is_not'])
	    {
	      unset($in['is_not']);
	      $in['v']='#gteq';
	    }
	    unset($in['is_notnot']); // notnot has no effect on binary operations 
	  break;
	case '>=':
	    $in['y']='f_usage';
	    $in['v']='#gteq';
	    if($in['is_not'])
	    {
	      unset($in['is_not']);
	      $in['v']='#lt';
	    }
	    unset($in['is_notnot']); // notnot has no effect on binary operations 
	  break;
	case '>':
	    $in['y']='f_usage';
	    $in['v']='#gt';
	    if($in['is_not'])
	    {
	      unset($in['is_not']);
	      $in['v']='#lteq';
	    }
	    unset($in['is_notnot']); // notnot has no effect on binary operations 
	  break;
	case '=':
	    $in['y']='f_usage';
	    $in['v']='#eq';
	    unset($in['is_notnot']); // notnot has no effect on binary operations 
	  break;
	case '==':
	    $in['y']='f_usage';
	    $in['v']='#eqeq';
	    unset($in['is_notnot']); // notnot has no effect on binary operations 
	  break;
	default:
	  $n_parts=explode('-',$n);
	  foreach($n_parts as $n_part)
	  {
	    if(empty($n_part))
	    {
	      msg('error-syntax','EXA-parsing failed: at expression-head (invalid use of -, not allowed at beginning or end and only once)',$in);
	    }

	    if(!ctype_lower($n_part))
	    {
	      msg('error-syntax','EXA-parsing failed: invalid characters at expression-head, only a-z are allowed, and -',$in);
	    }
	  }
	  
	  if($n[0]=='#')
	  {
	    $in['y']='f_usage';
            
            if($in['c_sub']>1)
            {
              $x_node=array($in['v'],$x_node);
              $x_node[1][0]='e';
                              
              // create special in that put's together the entity  
//              $in['sub']=array(0=>0,1=>array('v'=>'',
//			   'y'=>'e_usage',
//			   'x_path'=>"{$in['x_path']}:1",
//			   'depth'=>$in['depth'],        // increased in checkEXP soon
//			   'x_i'=>$in['x_i'],
//			   'i'=>1,
//			   'c_sub'=>$in['c_sub'],
//			   'sub'=>array(0=>0));
//              $in=&$in_e_usage;
            }
	  }
	  else
	  {
	    $in['y']='e_usage';
	  }
	  break;
      }
      
      
      if($in['y']=='e_usage'||$in['y']=='f_usage')
      {
	$GLOBALS['temp']['c-exp']+=1;
	
	if(strpos($GLOBALS['temp']['n-list-exp'],"'{$in['v']}'"))
	{
	  //already used
	}
	else
	{
	  $GLOBALS['temp']['n-list-exp'].=",'{$in['v']}'";
	  $GLOBALS['temp']['i-list-exp']+=1;
	}
      }
    }
    
    //call function for rest of the entities
    
    for($i;$i<$count;$i++)
    {
      $in['sub'][$i]=array('v'=>'',
			   'y'=>'',
			   'x_path'=>"{$in['x_path']}:{$i}",
			   'depth'=>$in['depth'],        // increased in checkEXP soon
			   'x_i'=>$in['x_i'],
			   'i'=>$i,
			   'in_sup'=>&$in,
			   'c_sub'=>0,
			   'sub'=>array(0=>0));

      
      if(isset($in['sub'][$i]['is_contextual']))$in['is_contextual']=1;                     
                           
      switch($in['y'])
      {
	case 'if':
	  $in['sub'][$i]['in_if']=&$in;	  
	  if($i==1)
	  {
	    if(!isset($in['var_top']))
	    {
	      $in['var_top']=&$in;
	      $in['var']=array();
	    }
	  }
	break;
	case 'each':
	  $in['sub'][$i]['in_each']=&$in;
	  if($i==3)
	  {
	    $in['sub'][$i]['is_exp']=1;

	    if(!isset($in['var_top']))
	    {
	      $in['sub'][$i]['var_top']=&$in;
	      $in['sub'][$i]['var']=array();
	    }
	  }
	break;
	case 'op':
	  $in['sub'][$i]['is_in_op']=1;
	  if($i==3)
	  {
	    $in['sub'][$i]['is_exp']=1;
	    
	    if(!isset($in['var_top']))
	    {
	      $in['sub'][$i]['var_top']=&$in;
	      $in['sub'][$i]['var']=array();
	    }
	    
	  }
	break;
	case 'lot':
	  $in['sub'][$i]['in_lot']=&$in;
	  if($i==2)
	  {
	    $in['sub'][$i]['is_exp']=1;
	    
	    if(!isset($in['var_top']))
	    {
	      $in['sub'][$i]['var_top']=&$in;
	      $in['sub'][$i]['var']=array();
	    }
	    
	  }
	break;
	case 'sub':
	  $in['sub'][$i]['in_sub']=&$in;
	  if($i==2)
	  {
	    $in['sub'][$i]['is_exp']=1;
	    
	    if(!isset($in['var_top']))
	    {
	      $in['sub'][$i]['var_top']=&$in;
	      $in['sub'][$i]['var']=array();
	    }
	    
	  }
	break;
	case 'co':
	  $in['sub'][$i]['in_co']=&$in;
	  if($i==2)
	  {
	    $in['sub'][$i]['is_exp']=1;
	    
	    if(!isset($in['var_top']))
	    {
	      $in['sub'][$i]['var_top']=&$in;
	      $in['sub'][$i]['var']=array();
	    }
	    
	  }
	break;
      }
			   
      if(isset($in['in_each']))
      {
	$in['sub'][$i]['in_each']=&$in['in_each'];
      }
      
      if(isset($in['is_in_op']))
      {
	$in['sub'][$i]['is_in_op']=1;
      }

      if(isset($in['in_sub']))
      {
	$in['sub'][$i]['in_sub']=&$in['in_sub'];
      }

      if(isset($in['in_lot']))
      {
	$in['sub'][$i]['in_lot']=&$in['in_lot'];
      }

      if(isset($in['is_exp']))
      {
	$in['sub'][$i]['is_exp']=1;
      }      

      if(isset($in['in_if']))
      {
	$in['sub'][$i]['in_if']=&$in['in_if'];
      }      

      if(isset($in['var_top']))
      {
	$in['sub'][$i]['var_top']=&$in['var_top'];
      }      
      
      checkEXP($x_node[$i],$in['sub'][$i]);

      // checks directly after sub
      switch($in['y'])
      {
	case 'pile':
	  if($i<$count-1) // not the last element
	  {
	    if($in['sub'][$i]['y']!='var_set')
	    {
	      msg('error-syntax','EXA-parsing failed: Inside a pile each statement (but the last) has to set a variable',$in);
	    }
	  }
	break;
      }
      
    }
    

    // check if all variables are get
    if(isset($in['var']))
    {
      //this is the element containing variables
      foreach($in['var'] AS $dum=>$var)
      {
	if($var['c_get']<1)
	{
	  msg('error-syntax','EXA-parsing failed: Unused variable found: @@'.$dum,$in);
	}
      }
    }
    
    // check some rules related to op/lot/sub/each/...
    
    switch($in['y'])
    {
      case 'limited':

	if(!strpos($GLOBALS['temp']['n-list-new'],"'{$in['sub'][1]['v']}'"))
	{
	  msg('error-syntax','EXA-parsing failed: the entity-name: '.$in['sub'][1]['v'].' for limited expression is not defined (in this statement!)',$in);
	}
	
	if($in['sub'][1]['v'][0]=='#')
	{
	  msg('error-syntax','EXA-parsing failed: limited expressions can not be used on functions',$in);
	}
      break;
      case 'default':
	if(!strpos($GLOBALS['temp']['n-list-new'],"'{$in['sub'][1]['v']}'"))
	{
	  msg('error-syntax','EXA-parsing failed: the entity-name for default expression is not defined in this statement',$in);
	}

	if($in['sub'][1]['v'][0]=='#')
	{
	  msg('error-syntax','EXA-parsing failed: default expressions can not be used on functions',$in);
	}
      break;
      case 'co':
	if(!strpos($GLOBALS['temp']['n-list-new'],"'{$in['sub'][1]['v']}'"))
	{
	  msg('error-syntax','EXA-parsing failed: the entity-name for co-definition (condition) is not defined in this statement',$in);
	}

	if($in['sub'][1]['v'][0]=='#')
	{
	  msg('error-syntax','EXA-parsing failed: co-definition (condition) can not be used on functions',$in);
	}
      break;
      case 'each':
        if(count($in['subr_e'])<1)
        {
	  if(count($in['subr_i'])<1)
	  {
	     msg('error-syntax','EXA-parsing failed: each-definition must include the @e-context. At least @i instead. Exact reason described in the docu.',$in);
	  }
	  else
	  {
	     msg('warning-syntax','EXA-parsing: lot-definition must include the @in-context. Exact reason described in the docu.',$in);
	  }
        }
      break;
      case 'lot':
        if(count($in['subr_in'])<1)
        {
	  msg('error-syntax','EXA-parsing failed: lot-definition must include the @in-context. Exact reason described in the docu.',$in);
        }
      break;
      case 'sub':
        if(count($in['subr_in'])<1)
        {
	  msg('error-syntax','EXA-parsing failed: sub-definition must include the @in-context. Exact reason described in the docu.',$in);
        }
      break;
      case 'op':
        if(count($in['subr_in'])<1)
        {
	  msg('error-syntax','EXA-parsing failed: op-definition must include the @in-context. Exact reason described in the docu.',$in);
        }
      break;
    }
    
    // ---------------------------------------- PLURAL ------------- END 
  } // end if is-array
  else
  {
    // ---------------------------------------- SINGULAR ------------- BEGIN
	// a direct non-literal expression, must be a name only or var, context (incl. path)
      
    $offset=0;
    if($x_node[0]=='!')
    {
      if($x_node[1]=='!')
      {
	$in['is_notnot']=1;
	$offset=2;
      }
      else
      {
	$in['is_not']=1;
	$offset=1;
      }
    }

    if($x_node[$offset]=='@')
    {
      if(isset($x_node[$offset+1])&&$x_node[$offset+1]=='@')
      {
	$in['y']='var_get';
	$offset+=2;

	if(!isset($in['var_top']))
	{
	  msg('error-syntax','EXA-parsing failed: a variable is used at invalid position',$in);
	}
      }
      else
      {
	$in['y']='context';
	$offset+=1;
	$GLOBALS['temp']['i-context']++;
	$in['is_contextual']=1;
      }
    }

    // direct check by 
    if($offset)
    {
      $x_node=substr($x_node,$offset);
    }
    

    
    $exp=explode(':',$x_node);
    $n=$exp[0];

    if(isset($exp[1]))
    {
      // -------------- path begin
      $c_exp=count($exp);
      $in['path']=array();
      
      $GLOBALS['temp']['i-path']++;    
      
      for($m=1;$m<$c_exp;$m++)
      {
	$v=$exp[$m];
	if(is_numeric($v))
	{
	  if($v<1)
	  {
	    msg('error-syntax','EXA-parsing failed: a numeric path-entity must be a natural number (min: 1) ',$in);
	  }
	  $in['path'][]=(int)$v;
	}
	else
	{
	  // TODO support textual path
	  msg('error-syntax','EXA-parsing failed: a textual path-entities are not supported',$in);
	
	  if(empty($v))
	  {
	    msg('error-syntax','EXA-parsing failed: a textual path-entity is invalid',$in);
	  }
	  $in['path'][]=$v;
	}
      } 
    } // -------------- path end

    $c_n=strlen($n);
    if($c_n>255)
    {
      msg('error-syntax','EXA-parsing failed: an single expression is too long ',$in);
    }
    
    switch($in['y'])
    {
      case 'var_get':
      
	if(empty($n))
	{
	  msg('error-syntax','EXA-parsing failed: a variable name to get is invalid',$in);
	}

	if(!ctype_lower($n))
	{
	  msg('error-syntax','EXA-parsing failed: invalid characters at in a variable to get, only a-z are allowed (no -)',$in);
	}      

	if(!isset($in['var_top']['var'][$n]))
	{
	  msg('error-syntax','EXA-parsing failed: Use of unset @@variable.',$in);
	}
	else
	{
	  $in['var_top']['var'][$n]['c_get']++;
	}
      break;
      case 'context':
	switch($n)
	{
	  case 'in':
	    if(!$in['is_exp'])
	    {
	      msg('error-syntax','The context @in can only be used in explizit exp-expressions (op, lot, sub)',$in);
	    }

	    if(isset($in['is_in_op']))
	    {
	      $in_op=&$GLOBALS['in-top'][$in['x_i']];
	      $in_op['subr_in'][]=&$in;
	    }
	    elseif(isset($in['in_lot']))
	    {
	      $in['in_lot']['subr_in'][]=&$in;
	    }
	    elseif(isset($in['in_sub']))
	    {
	      $in['in_sub']['subr_in'][]=&$in;
	    }
	    else
	    {
	      msg('error-syntax','The context @in can only be used within op, lot and sub',$in);
	    }
	    // TODO: Support @out probably at some point
	    break;
	  case 'e':

	    if(!$in['is_exp'])
	    {
	      msg('error-syntax','The context @e can only be used in exp-part of each',$in);
	    }
	  
	    if(!isset($in['in_each']))
	    {
	      msg('error-syntax','The context @e can only be used within each',$in);
	    }
	    
	    $in['sub']['subr_e'][]=&$in;
	    
	    break;
	  case 'r':
	    if(!$in['is_exp'])
	    {
	      msg('error-syntax','The context @r can only be used in exp-part of each',$in);
	    }

	    if(!isset($in['in_each']))
	    {
	      msg('error-syntax','The context @r can only be used within each',$in);
	    }
	    break;
	  case 'i':
	    if(!$in['is_exp'])
	    {
	      msg('error-syntax','The context @i can only be used in exp-part of each',$in);
	    }
	  
	    if(!isset($in['in_each']))
	    {
	      msg('error-syntax','The context @i can only be used within each',$in);
	    }

	    $in['sub']['subr_i'][]=&$in;

	    break;
	  case 'c':
	    if(!$in['is_exp'])
	    {
	      msg('error-syntax','The context @c can only be used in exp-part of each',$in);
	    }
	  
	    if(!isset($in['in_each']))
	    {
	      msg('error-syntax','The context @c can only be used within each.',$in);
	    }
	    break;
	  case 'u':
	  case 'agent':
	  case 'con':
	  case 'st':
	  case 'g':
	  case 'service':
	    if(!$GLOBALS['login'])
	    {
	      msg('error-unauthorized','only logged in users can use certain @context.',$in);
	    }
	  break;
	  case 'now':	
	  case 'random':
	  
	    // TODO further checks if context can be used
	  break;
	  default:
	    msg('error-syntax','EXA-parsing failed: Invalid @context used.',$in);
	}
      break;
      default:
	
	if(empty($n))
	{
	  msg('error-syntax','EXA-parsing failed: entity name is empty and invalid',$in);
	}
	
	switch($n)
	{
	  case 'break':
	  case 'lot':
	  case 'sub':
	  case 'each':
	  case 'if':
	  case 'limited':
	  case 'default':   //maybe default can be made available in some context
	  case 'privacy':
	  case 'op':
	      msg('error-syntax','EXA-parsing failed: this entity can not be used stand-alone',$in);
	  break;
	  case 'f':
//	    if(!$GLOBALS['is-exalot'])
//	    {
	      msg('error-syntax','EXA-parsing failed: a single \'f\' can not be used.',$in);
//	    }
	  break;
	  case 'p':
	    if(!isset($in['is_in_op'])||$in['depth']!=2||$in['i']!=2)
	    {
	      msg('error-syntax','EXA-parsing failed: a single p is only as second op-parameter, otherwise it needs sub-entities',$in);
	    }
	    $in['y']='p';
	  break;
	  case 'b':
	    $in['y']='b';
	    if(isset($in['is_not'])||isset($in['is_notnot']))
	    {
	      msg('error-syntax','EXA-parsing failed: a non-literal bit-entity can not have the !-operators',$in);
	    }
	    if(isset($in['path']))
	    {
	      msg('error-syntax','EXA-parsing failed: a non-literal bit-entity can not have a path',$in);
	    }
	  break;
	  case 'noo':
	    $in['y']='noo';
	    if(isset($in['path']))
	    {
	      msg('error-syntax','EXA-parsing failed: a single bit noo can not have a path',$in);
	    }
	    
	    if(isset($in['is_not']))
	    {
	      msg('error-syntax','EXA-parsing failed: the construction !noo is not allowed, use x instead',$in);
	    }

	    if(isset($in['is_notnot']))
	    {
	      msg('error-syntax','EXA-parsing failed: the construction !!noo is not allowed, use just noo instead',$in);
	    }
	  break;
	  case 'x':
	    $in['y']='x';
	    if(isset($in['path']))
	    {
	      msg('error-syntax','EXA-parsing failed: a single bit x can not have a path',$in);
	    }

	    if(isset($in['is_not']))
	    {
	      msg('error-syntax','EXA-parsing failed: the construction !x is not allowed, use noo instead',$in);
	    }
	    
	    if(isset($in['is_notnot']))
	    {
	      msg('error-syntax','EXA-parsing failed: the construction !!x is not allowed, use just x instead',$in);
	    }
	  break;
	  default:
	    if($n[0]=='#')
	    {
	      if(!isset($in['is_in_op'])||$in['depth']!=2||$in['i']!=1)
	      {
		msg('error-syntax','EXA-parsing failed: single function name is only allowed directly in op',$in);
	      }

	      if(isset($in['path']))
	      {
		msg('error-syntax','EXA-parsing failed: a function name can not include a path',$in);
	      }
	      
	      $in['y']='f_name';
	      
	      if(!strpos($GLOBALS['temp']['n-list-new'],"'{$in['v']}'"))
	      {
		msg('error-syntax','EXA-parsing failed: the function-name of new op is not defined in the same statement',$in);
	      }
	    }
	    else $in['y']='e_name';
	    
	    
	    $GLOBALS['temp']['c-exp']+=1;
	    if(strpos($GLOBALS['temp']['n-list-exp'],"'{$n}'"))
	    {
	      //already used
	    }
	    else
	    {
	      $n_parts=explode('-',$n);               
	      foreach($n_parts as $n_part)
	      {
		if(empty($n_part))
		{
		  msg('error-syntax','EXA-parsing failed: at single expression (invalid use of -, not allowed at beginning or end and only once)',$in);
		}

		if(!ctype_lower($n_part))
		{
		  msg('error-syntax','EXA-parsing failed: invalid characters at at single expression, only a-z are allowed, and -',$in);
		}
	      }	
	    
	      $GLOBALS['temp']['n-list-exp'].=",'{$n}'";
	      $GLOBALS['temp']['i-list-exp']+=1;
	    }
	  break;
	}
      break;
    }

    $in['v']=$n;
    
  } // ------------------------------  SINGULAR  ------------------------ END
      
  $GLOBALS['in'][]=$in;
  $GLOBALS['in-i']++;  
  $in['in_i']=$GLOBALS['in-i'];
  
}

