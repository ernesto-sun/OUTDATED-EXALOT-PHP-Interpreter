<?php

/**
 *  EXALOT digital language for all agents
 *
 *  api_07_context.php is part of semantic checking and does the work for
 *  context-variables 
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

      //$in['y']='e_usage';
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
      //$in['y']='e_usage';
      $in['n_def']='context-agent';
    break;
    case 'con':
      // protected con-view		READ-ONLY
      //$in['y']='e_usage';
      $in['n_def']='context-con';
    break;
    case 'st':
      // protected statement-view	READ-ONLY
      //$in['y']='e_usage';
      $in['n_def']='context-statement';
    break;
    case 'g':
      // protected group-view	READ-WRITE (depending on user-permissions)
      //$in['y']='e_usage';
      $in['n_def']='context-group';
      //$in['c_sub']=4;
      //$in['sub']=array(0=>0)
    break;
    case 'service':
      // useful data about the current-exalot-service-execution	READ-ONLY
      //$in['y']='e_usage';
      $in['n_def']='context-service';
      //$in['c_sub']=4;
    break;
    case 'now':	
	// timestamp of first now-usage	READ-ONLY
      
	$in['n_def']='cl';
      
    break;
    case 'random':
      // a random number in float between 0 and 100 (PHP-random)	READ-ONLY
        
        $in['n_def']='float';
    break;
    default:
      msg('error-internal','invalid case in a context',$in);
    break;
  }  
}
     
