<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_07_sys.php is performs semantic checking for system-commands like
 *  op, co, limited, privacy
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



//----------------------------------------------------------------------
function resolve_sys(&$in)
{  
  
  switch($in['y'])
  {
    case 'op': //---------------------------------------------------------
      // define the operation for a new f,

      $in['r']=$GLOBALS['row-0'];        
      $in['r']['n_def']=$in['r']['n_def_non_alias']=$in['v'];
      $in['r']['p']=$in['r']['e']=$in['r']['r']='e';
      $in['r']['l']='usage';
        
      $f_name=$in['sub'][1]['v'];
      $f_plural=$in['sub'][2]['v']; // must be an integer or p >=0
      
      $in['r_f']=&$GLOBALS['data-new'][$in['v']]; // must be a newly defined function
      
      if($f_plural=='p')
      {
	if(!$in['r_f']['is_plural'])
	{
	  msg('error-semantic','EXA-resolving failed: a plural op can only be defined on a function with plural input',$in);
	}
	$in['r']['is_plural']=1;
	$in['r']['c_min']=$in['r']['c_max']=0;
      }
      elseif(is_numeric($f_plural))
      {
	if(!$in['r_f']['is_plural'])
	{
	  msg('error-semantic','EXA-resolving failed: index-numbers can only be used on plural op',$in);
	}

	if($f_plural>$in['r_f']['c_max'])
	{
	  msg('error-semantic','EXA-resolving failed: the int input-param for plural is too great for definition. max: '.$in['r_f']['c_max'],$in);
	}	

	if($f_plural<$in['r_f']['c_min'])
	{
	  msg('error-semantic','EXA-resolving failed: the int input-param for plural is too small for definition. min: '.$in['r_f']['c_min'],$in);
	}	

	$in['r']['is_plural']=1;
	$in['r']['c_min']=$in['r']['c_max']=$f_plural;
      }
      else
      {
	// single entity, $f_plural holds the name of the entity
	if($in['r_f']['is_plural'])
	{
	  msg('error-semantic','EXA-resolving failed: a plural op can only be defined by index-numbers or p',$in);
	}

	if(!is_sub_rec_of($in['r_f']['n_sub1'],$f_plural))
	{
	  msg('error-semantic','EXA-resolving failed: the input-param defined at op does not match the defintion',$in);
	}
	$in['r']['c_min']=$in['r']['c_max']=1;
      }

      $in['r']['n']="op-{$f_name}-{$f_plural}";
      if(isset($GLOBALS['data-new'][$in['r']['n']]))
      {
	  msg('error-semantic','EXA-resolving failed: this op was already defined in this statement',$in);
      }

      $GLOBALS['list-f-def'][$f_name][]=$f_plural; // remember 
      
      $in['r']['n_sub1']=$f_name;
      $in['r']['n_sub2']=$f_plural;  // attention, this is actually not always a name but also int
      $in['r']['n_sub3']=$in['r_f']['n_sub2'];
      
      $in['r']['sup']=array();

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

      $GLOBALS['data-new'][$in['r']['n']]=&$in['r'];
      
    break;  
    case 'default': //---------------------------------------------------------
      // perform system-operation,

      $in['r_e']=&$GLOBALS['data-new'][$in['sub'][1]['v']]; // must be a newly defined entity
        
      if(!count($in['r_e']))
      {
	msg('error-semantic','EXA-resolving failed: The default-entity has to be used on a new definition in this statement',$in);
      }
      
      if(!is_sub_rec_of($r['n_sub1'],$r['n_sub2']))
      {
	msg('error-semantic','EXA-resolving failed: the default value does not match the entity',$in);
      }

      if(isset($in['r_e']['has_default']))
      {
	msg('error-semantic','EXA-resolving failed: default-expression given twice on entity: '.$in['r_e']['n'],$in);
      }
      $in['r_e']['has_default']=1;
        
    break;
    case 'co': //---------------------------------------------------------

      $in['r_e']=&$GLOBALS['data-new'][$in['sub'][1]['v']]; // must be a newly defined entity

      if(!count($in['r_e']))
      {
	msg('error-semantic','EXA-resolving failed: condition (co) has to be used on a new entity, created at this statement',$in);
      }
      
      // TODO: maybe make further checks on th expression

    break;
    case 'privacy': //---------------------------------------------------------
      // perform system-operation,
      // privacy can be used several times (update-operation)
      // privacy rules can include group-decalrations and thus are not trivial
      // TODO: Perform saving of entity (by setting to e_usage) or handle privacy directly
        

      $in['r_e']=&get_row_e_by_n($in['sub'][1]['v']); 
      if(!count($in['r_e']))
      {
	msg('error-semantic','EXA-resolving failed: entity not found for privacy-rule.',$in);
      }
  
      if(isset($in['r_e']['n_u_cr']))
      {
	if($in['r_e']['n_u_cr']!=$GLOBALS['n-u'])
	{
	  msg('error-semantic','EXA-resolving failed: Only the creator-user can set privacy-rules.',$in);
	}
      }
  
//      $n="privacy-{$in['r_e']['n']}";
//      
//      $r['n']=$n;
//      $r['n_def']='privacy';
//
//      $r['n_sub1']=$in['r_e']['n'];
      
      
    break;
    case 'limited': //---------------------------------------------------------
      // is fully checked at syntax already, refers to a new entity

        
      $in['r_e']=&$GLOBALS['data-new'][$in['sub'][1]['v']]; // must be a newly defined entity

      if($in['r_e']['is_limited'])
      {
	msg('error-semantic','EXA-resolving failed: limited-expression given twice on entity: '.$in['r_e']['n'],$in);
      }
      
      $in['r_e']['is_limited']=1;
      
    break;
    default:
      msg('error-internal','invalid case in a sys:'.$in['y'],$in);
    break;
  }
    
}
